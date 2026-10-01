# Catalog Roster and Trainee Selector — Workstream

## Provenance

This master file embeds all content from the following 6 source files verbatim:

1. docs/deprecated/requests/2026-09-29-catalog-roster-and-trainee-selector.md (96 lines)
2. docs/deprecated/requests/2026-09-29-catalog-roster-and-trainee-selector-plan.md (4008 lines)
3. docs/deprecated/requests/2026-09-29-catalog-roster-report.md (363 lines)
4. docs/deprecated/data/2026-09-29-global-roster-crosscheck.md (504 lines)
5. docs/deprecated/data/roster-crosscheck-table.md (109 lines)
6. docs/deprecated/design-research/verification/cardless-band-2026-09-30.md (135 lines)

---
## docs/deprecated/requests/2026-09-29-catalog-roster-and-trainee-selector.md


**Received:** 2026-09-29, in session (no file was attached; this is the recorded brief).
**Recorded by:** Architect pass, 2026-09-29, against tree `ec0ee2f`.
**Plan:** `2026-09-29-catalog-roster-and-trainee-selector-plan.md`
**Status:** planned, not built.

> **Read in order.** This file is step 1 of three for one workstream: this request, then
> [`â€¦-plan.md`](2026-09-29-catalog-roster-and-trainee-selector-plan.md) (the task-by-task plan), then
> [`â€¦-report.md`](2026-09-29-catalog-roster-report.md) (the closing measurements). The three filenames
> differ only by suffix, so read the status line here before deciding which one is current: the
> request says "planned, not built" and the report says the branch closed. The report is the later
> document.

### 1. What was asked

Two frontend deliverables:

1. Replace the two-row catalog with the complete Global roster: every trainee, and
   every costume card she has, nested under her. One `<h1>` per trainee (English name,
   Japanese name, max rarity, form count); one `<h2>` per card (bracket epithet
   verbatim, rarity badge, release status, Global release date). The debut form is one
   of the `<h2>`s, ordered first; remaining forms follow by Global release date
   ascending. Trainee rows order alphabetically by English name.
2. Replace the `Umamusume` `<select>` on "New training run" with an ARIA combobox that
   prefix-matches trainee English name, trainee Japanese name and card epithet,
   case-insensitively, grouped by trainee, capped at about ten visible rows, keyboard
   operable, defaulting to a short list of recently released cards. Selecting a trainee
   name alone defaults to her debut form. The submission carries the card.

Also asked: a shared data source behind both surfaces; per-card provenance on the detail
page; Global-only scope; verbatim client names; no new JavaScript dependency; `03-trackblazer.md`
and the scenario docs untouched.

### 2. Owner rulings, taken in session 2026-09-29

| Question | Ruling |
|---|---|
| The card layer has no PRD citation. | **Build the card table, and put a card reference on the run.** The form is to submit the card, not only the trainee. |
| Where does the data come from? | **Re-fetch live from GameTora through `uma:fetch`.** Not the 2026-09-27 gitignored snapshot. This answers `AGENTS.md` escalation 5 for the live fetch of the already-declared host. |
| Tier B needs Tier A confirmation. How deep? | **Cross-check all 105 Global cards** against `umamusu.wiki` and Game8, not a spot-check. |

Consequences the rulings accept: a new table plus a second migration on `training_runs`;
an ADR and a PRD citation before merge; an amendment to the D-30 permitted-surface list;
a second declared source and parser class.

### 3. Erratum: what the brief asserts that is not true

Every line below was measured against the dataset on 2026-09-29, not argued. E-1 to E-16 correct the brief
recorded above; E-17 to E-21 correct this plan's own text
(`docs/requests/2026-09-29-catalog-roster-and-trainee-selector-plan.md`), found when Task 6 ran and
measured the same way: re-counted against the committed fixture, or re-run against the installed test
stack. Line numbers inside the plan are hints the plan's own hints-not-anchors bullet says to re-derive.

| # | The brief says | The data and the code say |
|---|---|---|
| E-1 | "~68 H1s plus ~140 H2s" | 68 trainees is right. **105** cards carry a Global release date, out of 268 total. 33 trainees have one form, 33 have two, two have three. Rarity split: 8 x 1-star, 9 x 2-star, 88 x 3-star. Global window 2025-06-26 to 2026-09-24. |
| E-2 | Gold Ship: "3 forms", `[Red Strife]` "3-star â€¦ Mar 12, 2026" | Gold Ship has three cards, **two** of them Global. `[Red Strife]` is **2-star** and shipped on Global **2025-06-26**. "Mar 12, 2026" is the Trackblazer scenario start. Her third card, `La Mode 564`, has no `release_en` and is excluded by the brief's own Global-only rule. |
| E-3 | Special Week: "2 forms" | **Three** Global forms: `[Special Dreamer]` 2025-06-26, `[Hopp'nâ™ªHappy Heart]` 2025-10-14, `[Ruler of Japan]` 2026-06-25. |
| E-4 | "the export marks one card per trainee as the debut form" | No such field exists in the export. Debut is **derived**, and this repo already has the rule: `GametoraCharacterParser::debutForms()` takes the card with the earliest JP `release` per `char_id` â€” `:74-79` inline in `ec0ee2f`, `GametoraCharacterParser::debutForms()` (`:99-127` where it landed) as the shared member since Task 6's extraction `db8603c`. Measured across all 68 Global trainees that derivation is unambiguous (no date ties) and the JP-earliest card, the Global-earliest card and the parser's debut are the same card in every case. |
| E-5 | `Fe` "narrows the F-set" | Under the brief's own prefix-match rule, `F` returns Fine Motion and Fuji Kiseki plus nine cards whose epithet starts with F. **`Fe` returns nothing**: no Global trainee and no Global epithet begins with those two letters. |
| E-6 | `Fenomeno` "Fenomeno, with her card forms as options" | Fenomeno has two cards, `Black Flame of Righteousness` (JP 2025-04-21) and `Violet Flame of Fortitude` (JP 2026-08-31), and **neither has a `release_en`**. By the Global-only rule she is one of the 67 trainees who must not appear at all. The same applies to Furioso and Fusaichi Pandora. |
| E-7 | Selected state `Gold Ship â€” [RUN! RUIN! LAUNCHER!]` | The em dash is a **test failure**. `RenderedCopyHygieneTest`'s `ships no em dash or en dash in rendered Blade copy` test asserts zero em or en dashes in rendered Blade copy, on the standing R-02 and D-79 rulings. The catalog's own separator is `Â·` (`catalog/index.blade.php:43`, the release-status and alias-count line). |
| E-8 | "Rarity badge per H2" | Fine, but no new colour token: `DesignTokensTest`'s `counts every colour token the static theme declares` test pins the theme at exactly 60 tokens, and G-4 bans hex and arbitrary-value utilities. The badge reuses existing tokens and carries its ordinal in the star glyph, the way `mood-pill.blade.php` carries its arrow. |
| E-9 | "Re-fetch live via `uma:fetch`" populates the catalog | `CrossReferenceMatcher::match()` returns `None` for any name not already stored, and `PipelineRunner::run()`'s `MatchCandidate::create` branch sends `None` to `match_candidates`. A live fetch therefore **creates zero trainee rows**; it queues 68 candidates. `PRD.md` FR-B-3 is deliberate: only Exact and Alias auto-promote. The roster has to leave the review queue through `ResolveMatchCandidate`, which is the path this plan uses. |
| E-10 | Catalog search should "filter live" | The existing catalog filter is a GET form behind a submit button, and search is substring (`match_key LIKE %â€¦%`), not prefix. Live-typing there means a page reload per keystroke. The **selector** is the live surface; the catalog page keeps its submit. Recorded as a scope reading, not a silent drop. |
| E-11 | Unstated | The dataset already sits at `research-scratch/data/json/character-cards.json` (268 rows, fetched 2026-09-27) but `research-scratch/` is gitignored and nothing under `app/` reads it. |
| E-12 | Unstated | **Live defect, in scope.** `GametoraCharacterParser.php:92` reads `$card['name_ja']`. That number is `ec0ee2f`'s: Task 2's `d755da3` closed the defect and Task 6's `db8603c` moved the corrected read to `:74`, where `GametoraCharacterParser::parse()` reads `name_jp` and still emits the record key `name_ja`; `KNOWN-ISSUES.md` KI-23 holds that trail. The export publishes `name_jp`: `name_ja` appears **0** times in 268 rows, `name_jp` 268 times. So every fetched trainee stores a null Japanese name, and `PRD.md` US-1's acceptance test ("each detail page shows `name`, `name_ja`, â€¦") is not met by fetched data. The test fixture at `tests/Fixtures/gametora-character-cards.sample.json` carries the same wrong key with null values, which is why the suite is green. The brief needs the Japanese name as the `<h1>` secondary label and as a search field, so this is a prerequisite. |
| E-13 | "One H1 per trainee â€¦ The H2 label is the bracket title alone" | The `<h2>` label is `title_en_gl`, the Global client string, which is **bracketed** (`[Red Strife]`). The prose examples drop the brackets. The binding constraint is "verbatim client names stay verbatim; do not normalize them", so the brackets stay. The Global string is not the JP string: `Run! Fun! Watergun!` is `[RUN! RUIN! LAUNCHER!]`, `Supreme Commander of the Rising Sun` is `[Ruler of Japan]`. |
| E-14 | Unstated | The parser emits `external_ref` (`gametora:char:{id}`) at line 100 of `ec0ee2f` â€” `GametoraCharacterParser::parse()` still writes that key today â€” and there is **no such column** on `umamusume`, so `PromoteMatchedRecord` drops it. Cards are keyed by `card_id` and have to be attached to a stored trainee, which needs that link. The plan adds the column rather than re-matching on name. |
| E-15 | Implicit: fetching fills the catalog with the 68 Global trainees | `GametoraCharacterParser` emits one record per `char_id` across the **whole** export, deriving `release_status` from the debut card's `release_en`. Measured on the live body 2026-09-29: **135 records, 68 `GlobalReleased`, 67 `JapanOnly`.** A live fetch therefore queues 133 candidates, not 66. The parser must keep emitting all 135: `PRD.md` US-2 is P0 and its acceptance text is "new JP releases appear with a **JapanOnly** or GlobalAnnounced flag instead of silently missing", which a Global-only guard would delete. The filter belongs at the promotion verdict, so 66 become rows and 67 stay in `/review` awaiting one. |
| E-16 | Unstated | `SourceFetcher` sends `User-Agent: UmamusumeTrainerCompanion/0.2 (personal local tool)` and no `Accept`. Probed 2026-09-29 before any of this was built: the data endpoint returns **200 with the full 251,242 bytes**, and `679f7c2e` has not rotated. Tasks 8-9 are runnable as ruled, with no header change and no fallback to the gitignored snapshot. |
| E-17 | Plan Task 6 Step 2 (`:1116-1119`): "Eleven rows in, eight with a Global date", `expect($cardIds)->toHaveCount(8)` | The fixture that same block ships yields **7**: eleven rows, four with `release_en: null` (`100703`, `100303`, `112701`, `112702`), so seven carry a Global date. The plan's own parenthetical named those four, so its arithmetic contradicted its count: 11 - 4 = 7, and E-2 and E-3 already record Special Week's three Global forms and Gold Ship's two, which with Tokai Teio's two make 3 + 2 + 2. Shipped as `toHaveCount(7)` in `tests/Feature/CharacterCardParserTest.php`'s `emits one record per Global card and drops every JP-only one` test; the plan now says seven, in its prose and in Step 8's check. |
| E-18 | Plan Task 6: Step 2 (`:1205`, `:1212`) asserts a card whose only `release_en` is `9999-12-31` yields no record, while Step 7 (`:1382`, `:1387`) rejects only what `GametoraCharacterParser::dateOrNull()` returns as null | `dateOrNull()` tests the `\d{4}-\d{2}-\d{2}` shape alone, so it **accepts** the export's own placeholder: `GametoraCharacterParser::dateOrNull('9999-12-31')` returned `'9999-12-31'` when re-run against the committed class on 2026-09-29. Step 7 as written therefore emitted the row Step 2 forbids. Fixed at the card read's trust boundary, which skips `GametoraCharacterParser::UNKNOWN_DATE` as well as null (the `GametoraCharacterParser::UNKNOWN_DATE` guard in `GametoraCharacterCardParser::parse()`), and `dateOrNull()` left as it stands: rejecting the placeholder there would move the character parser's `global_debut_date` and `release_status` for a debut card dated `9999-12-31`, an input no existing test covers, so the extraction's behaviour bracket would have gone green while the behaviour moved. |
| E-19 | Plan `:1110` (Task 6 Step 2) and `:1528` (Task 7 Step 1): `file_get_contents(test()->baseDir().'/tests/Fixtures/â€¦')` | `test()->baseDir()` does not exist on this stack â€” Pest 4.7.8 over PHPUnit 12.5.33 â€” with `method_exists(PHPUnit\Framework\TestCase::class, "baseDir")` and the same call on `Illuminate\Foundation\Testing\TestCase::class` both returning false. Task 6's recorded RED run showed seven tests failing class-not-found as its Step 3 predicted and the **eighth dying inside this helper** with `ReflectionException: Call to undefined method Tests\TestCase::baseDir()`, so Task 7 would meet the same error before its own assertions run. `base_path()` is the convention one file over (`tests/Feature/GametoraCharacterParserTest.php`'s `reads the committed sample of the real dataset without raising` test) and is what shipped (`tests/Feature/CharacterCardParserTest.php`'s `globalCardBody()` helper); both plan lines now read `base_path('tests/Fixtures/gametora-character-cards.global.sample.json')`. |

| E-20 | E-16: "`679f7c2e` has not rotated", and `config/uma.php`'s claim that a stale hash "surfaces as a fetch failure and not as silently old data" | **The hash rotated the same day, and the safety claim is false.** Task 7's Step 5 re-read the manifest on 2026-09-29 and found `character-cards` had moved `679f7c2e` â†’ `e9e9ee6d`; both `config/uma.php` entries now pin the new hash, and the two live instructions that fetched the withdrawn URL (Task 8's Tier A download, Task 9's unresolved-ref probe) were corrected to it, because a cross-check run against a stale document is worse than no cross-check. Re-measured against `e9e9ee6d` with E-1's own method: **107** Global cards in 268 rows across the same **68** trainees â€” forms split 31 x one, 35 x two, 2 x three; rarity 8 x 1-star, 9 x 2-star, **90** x 3-star; window 2025-06-26 to **2026-09-28**. Neither the row total nor the trainee count moved: two existing JP-only rows gained a `release_en`, no card row was added. E-1's 105 / 33 / 33 / 2 / 88 figures are left above as the measurement they were, not rewritten. **And the refutation:** the withdrawn `character-cards.679f7c2e.json` still answers **HTTP 200** with the old 251,242-byte document (105 cards) while `e9e9ee6d` serves 251,294 bytes (107), so a stale pinned hash yields *silent stale data*, exactly the opposite of what both `config/uma.php` sentences promise. That is trunk's **KI-24** ("A stale source hash answers 200 with stale content, so a pinned URL fails silently"), filed independently by the skills pass; this branch does not refile or renumber it. The sentences stay because KI-24's fix is theirs to delete, not a wording task here â€” but the new source's hash note now points at KI-24, so the next Data Engineer adding a fourth pinned source reads the exception with the rule. |
| E-21 | Plan Task 7: A1's summary line ("the runner case keeps its counts `created 3 / skipped 5 / review 0`") and Step bodies naming `app/Actions/UpsertCharacterCard.php`, a `'records' => 'cards'` config key and a `runCards()` method | Two separate corrections, both found by running something. **(a)** The counts are arithmetically impossible: the parser emits **7** records for the committed fixture â€” `gametora:char:1001` x3, `1007` x2, `1003` x2 â€” so with one trainee stored the truth is `created 3 / skipped 4 / review 0`. The brief's own justification parenthetical ("Gold Ship x2, Tokai Teio x2, no trainee rows yet") already summed to 4, so `5` was never true of any version of this fixture, including the one A1 was written against. Shipped and pinned by `tests/Feature/CharacterCardFetchTest.php`'s `routes the card source past the match stage and into the card table` test. **(b)** A1 superseded all three identifiers and none of them shipped: the action is `app/Actions/StoreCharacterCards`, `handle(array $records, string $url, ?string $snapshotPath, ?string $timezone): array{created,updated,skipped}`, mirroring `StoreRaceCatalogSlots`; routing is by `is_a($parserClass, CharacterCardSourceParser::class, true)` inline in `PipelineRunner::run()` beside the race-catalog branch, so no source config grew a `records` key; and there is no `runCards()`. The step bodies are left as the record of what was briefed. The plan's file inventory and Task 9's `Consumes` line now name the shipped class. |

### 4. Deliverables as accepted

1. `character_cards` table, model, factory, seeder-free, populated from a live fetch,
   Global rows only, one row per card with `card_id` unique.
2. `training_runs.character_card_id`, nullable, validated as belonging to the
   trainee on the same row.
3. Catalog `<h1>`/`<h2>` tree over the 68 trainees and their 107 Global cards, with rarity,
   Global date, form count, and a default filter of Released (Global).
4. Detail page that names its source and fetch date instead of "No fetched sources."
5. ARIA combobox on "New training run", vanilla TypeScript, no dependency, progressive
   enhancement over the existing `<select>`, submitting the card.
6. `ADR-0008` plus the `PRD.md` citation, `ARCHITECTURE.md` and its ESSENTIALS digest,
   and the D-30 surface amendment.
7. A tracked Tier A cross-check file covering all 107 cards, with anything unconfirmed
   marked and hidden by default.
8. Report: trainees and cards added, sources used, unverified entries, and the counts
   this table records as corrections.

---

## docs/deprecated/requests/2026-09-29-catalog-roster-and-trainee-selector-plan.md


> **Read in order.** Step 2 of three for one workstream. It executes
> [`â€¦-and-trainee-selector.md`](2026-09-29-catalog-roster-and-trainee-selector.md), the request that
> authorized it, and its outcome is measured in
> [`â€¦-report.md`](2026-09-29-catalog-roster-report.md), the closing report. This file is the plan, not
> the status, and right now the two disagree: `grep -c "^- \[ \]"` returns 116 unchecked steps with 0
> checked, while `app/Models/CharacterCard.php`, `database/migrations/2026_09_29_120100_create_character_cards_table.php`
> and `resources/js/trainee-combobox.ts` are all tracked on master. Read the report for what landed.
> Re-ticking these 116 boxes is a separate pass with its own owner, not a side effect of this note.

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Populate the catalog with the complete Global roster (68 trainees, 107 costume cards nested under them) and replace the "New training run" trainee `<select>` with a keyboard-operable ARIA combobox that prefix-matches trainee names and card epithets.

**Architecture:** A new `character_cards` table becomes the single card-level source of truth. It is filled by a second declared GameTora source through the existing fetch engine, keyed to its trainee by a new `umamusume.external_ref` link, and the 68 trainees reach the catalog through the existing review-queue promotion path rather than new promotion code. The catalog renders the trainee/card tree server-side; the selector ships that same tree as a JSON payload and filters it synchronously in vanilla TypeScript.

**Tech Stack:** Laravel 13, PHP 8.3+, Eloquent, Pest 4, Tailwind CSS v4 (CSS-first `@theme`), vanilla TypeScript compiled by Vite. **No new package, PHP or JS.**

**Spec:** `docs/requests/2026-09-29-catalog-roster-and-trainee-selector.md` â€” read it with this plan. Its Â§3 erratum table (E-1..E-14) overrides the original brief wherever they disagree, and its Â§2 records the three owner rulings this plan is built on.

---

### Global Constraints

Every task satisfies all of these. Exact values, verbatim from `CONSTRAINTS.md`, `AGENTS.md` and `docs/design-research/CONSTRAINTS.md`:

- **C-1** All Pest tests pass; every behavior change ships a test. There is **no coverage percentage gate** in this repo.
- **C-2** PHPStan level 6 (Larastan), **zero errors**: `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`.
- **C-3** Pint clean: `vendor/bin/pint --dirty --format agent`, then prove it with `vendor/bin/pint --test --format agent`.
- **C-4** Lore: zero unexplained hits via `make lore`. **Card titles are source data, not copy**: root `CONSTRAINTS.md` C-4's "Verbatim names are a third category" bullet keeps a verbatim client string as data and bars it from promotion into UI copy, with the guard on the display path. Never edit a title to satisfy the grep.
- **C-5** Fresh migrate + seed succeeds; **every migration has a working `down()`**, proven by an actual `migrate:rollback`.
- **C-6** Catalog index under **200 ms** at ~1,000 Umamusume / ~2,000 skills. Re-check when schema or query shape changes â€” this plan changes both.
- **C-7** Every data view renders empty, loading/refresh and error states (loading scoped by ADR-0007: initial server navigation may use browser-native loading; user-initiated async needs an explicit state).
- **C-8** No new package without human approval. `composer audit` and `npm audit --omit=dev` with no reachable critical/high.
- **Floor** Never: `@phpstan-ignore` or any suppression; stub bodies; deleted or skipped tests; an engine write to a row with `is_manual = true`; a stored fact without provenance; a fetch URL outside `config('uma.sources')`; business logic or inline `validate()` in a controller.
- **AGENTS.md, Architect** Every new table, column or class cites a PRD requirement. A schema change = migration **plus** updated `ARCHITECTURE-ESSENTIALS.md` in the same change.
- **AGENTS.md, Data Engineer** New source = config entry + one parser class + tests against a stored fixture (`Http::fake`, no live network in tests) + a robots/rate-limit note. Never write to `is_manual = true` rows.
- **SOURCE-OF-TRUTH.md Â§5:152** "A Tier B dataset (GameTora) needs A- or S-tier confirmation before a claim becomes app data." GameTora is **Tier B**; `umamusu.wiki` and Game8 are **Tier A**.
- **No `dark:` utilities anywhere** (D-101, G-19). Theming flips variables under `html[data-theme='dark']`.
- **No hex literals, no arbitrary-value utilities** outside the theme block (G-4). `DesignTokensTest`'s `counts every colour token the static theme declares` test pins the theme at **exactly 60 colour tokens** â€” add no token.
- **No em dash or en dash in rendered Blade copy** (`RenderedCopyHygieneTest`'s `ships no em dash or en dash in rendered Blade copy` test, from R-02 and D-79). Use `Â·`, the separator the catalog already uses at `catalog/index.blade.php:43`, the release-status and alias-count line.
- **Never print a bare `0` for an absent value** â€” `N/A` plus tooltip (KI-7, disclosure pattern: `GATE-REGISTRY.md`'s "Disclosure pattern (false zero)" section). Applies to form counts.
- **Badge contrast** G-47: every new text/background pair measured in both themes, `ink-strong` at â‰¥ 9.00; white on a light fill is banned. Record each pair in `DESIGN.md` Â§3.4 (G-5).
- **Keyboard** G-11: the whole flow operable without a pointer.
- **Do not touch** `docs/scenarios/**` (`03-trackblazer.md` included) or `docs/UMAMUSUME_REFERENCE.md`.
- **Global only.** A card with no `release_en` is not stored. Verbatim `[Global]` client strings (`title_en_gl`) stay verbatim, brackets included.
- **Shared worktree.** A concurrent session commits to this tree: `git status` shows 14 modified and 7 untracked files that are theirs, and `PLAN.md`'s "Slice Exit Criteria" section records a past incident where a shared index let a peer's staged file into a commit. Never `git add -A`; stage only the paths each task names; re-run `git status --short` before every commit.
- **Line numbers in this plan are hints, not anchors â€” re-derive before editing.** Every `file.php:NN` citation was measured against `b387e07` and moves the moment a task inserts a line. Task 2's own fix is the first proof: its one-line comment in `GametoraCharacterParser.php` shifted `external_ref` from `:100` to `:101`, silently stale-ing two citations in this file that have since been corrected. Before editing any location named here, `grep` for the surrounding token and confirm the line, and never treat a mismatch as the executor's error. **Every count a brief states must be derivable, by inspection, from a fixture the brief also states - a number that cannot be re-counted from the data named in the same task does not ship.** Three of this plan's own numbers failed that test: E-17's toHaveCount(8) against an 11-row fixture, Amendment A1's skipped 5 against a 7-record emit, and E-1's roster against a source that rotated mid-plan. Tasks 8-12 are briefed to this rule.
- **The base is not frozen â€” check drift before every dispatch.** The peer commits to `master` in the **same** `.git` (shared repo, separate worktrees), and it moved `ec0ee2f â†’ b387e07 â†’ 31f97a5 â†’ 93f860b` while this plan was being written. One of those was a re-baseline that silently rotted every `PLAN.md:24` anchor in this file. Before dispatching any task, run `.superpowers/sdd/2026-09-29-catalog-roster-and-trainee-selector-plan/drift-check.sh` with that task's cited files as arguments. `git fetch` proves nothing here â€” master moves locally. If a cited file moved, re-verify its line numbers in the brief before sending it; a `:49` that now belongs to a comment block is a silent misdirection, and the executor will follow it.
- **The live plan bar, quoted current** (`PLAN.md`'s **Status** line, re-baselined to Slice 10 on 2026-09-29): "No panel work, no seeding migration, no new tokens." The middle clause binds this plan and it is respected: Task 9 populates the roster by promoting review-queue candidates through the existing `ResolveMatchCandidate`, **not** by adding a seeding migration. "No new tokens" is why the rarity badge reuses existing colours. The earlier wording ("no schema columns") was superseded by the owner's ADR-0008 ruling, which authorises `character_cards`; the citation is to the current line, not the old phrase.

---

### File structure

**New PHP â€” schema and domain**

| Path | Responsibility |
|---|---|
| `database/migrations/2026_09_29_120000_add_external_ref_to_umamusume_table.php` | The char-level link the parser has always emitted and the schema never kept (erratum E-14). |
| `database/migrations/2026_09_29_120100_create_character_cards_table.php` | One row per costume card: `card_id` unique, FK to `umamusume`, verbatim title, rarity, Global date, debut flag, unconfirmed flag. |
| `database/migrations/2026_09_29_120200_add_character_card_id_to_training_runs_table.php` | The run records which form it started on. Nullable. |
| `app/Enums/CardRarity.php` | 1/2/3-star as a PHP enum, TitleCase cases. |
| `app/Models/CharacterCard.php` | Card model, `casts()`, `umamusume()` relation. |
| `database/factories/CharacterCardFactory.php` | Test data only. |

**New PHP â€” pipeline**

| Path | Responsibility |
|---|---|
| `app/Services/DataPipeline/Contracts/CharacterCardSourceParser.php` | Third parser contract, beside `SourceParser` and `ScenarioSourceParser`. |
| `app/Services/DataPipeline/Parsers/GametoraCharacterCardParser.php` | Emits one record per Global card; debut derived, never guessed. |
| `app/Actions/StoreCharacterCards.php` | Idempotent card upsert by `card_id` plus its provenance row. |

**Modified PHP**

`app/Services/DataPipeline/Parsers/GametoraCharacterParser.php` (`parse()`'s `name_ja` record key, shared debut helper), `app/Actions/PromoteMatchedRecord.php` (persist `external_ref` in `handle()`'s create and fill arrays), `app/Services/DataPipeline/PipelineRunner.php` (cards branch), `config/uma.php` (second source), `app/Models/Umamusume.php` (`cards()`), `app/Models/TrainingRun.php` (`#[Fillable]`), `app/Http/Requests/StoreTrainingRunRequest.php` (`rules()`), `app/Http/Controllers/CatalogController.php`, `app/Http/Controllers/TrainingRunController.php` (`create()`), `lang/en/uma.php`.

**New frontend**

| Path | Responsibility |
|---|---|
| `resources/js/trainee-combobox.ts` | The combobox: filter, grouping, cap, keyboard, ARIA state. Imported once from `resources/js/app.ts`. |
| `resources/views/components/rarity-chip.blade.php` | Star-glyph badge, existing tokens only. |

**Modified frontend**

`resources/views/catalog/index.blade.php` (the tree), `resources/views/catalog/show.blade.php` (forms + provenance), `resources/views/runs/create.blade.php` (combobox over the existing select), `resources/js/app.ts`.

**New docs and tests**

`docs/adr/0008-character-card-catalog-layer.md`, `docs/data/2026-09-29-global-roster-crosscheck.md`, `tools/roster-crosscheck.php`, `docs/design-research/verification/slice-11-2026-09-29.md`; `tests/Feature/CharacterCardSchemaTest.php`, `CharacterCardParserTest.php`, `CharacterCardFetchTest.php`, `CatalogRosterTreeTest.php`, `TraineeSelectorTest.php`, `tests/Fixtures/gametora-character-cards.global.sample.json`.

---

### Task 1: Isolate the work and capture the opening snapshot

**Files:**
- Create: git worktree at `../umamusume-laravel13-catalog-roster`
- Modify: nothing

**Interfaces:**
- Produces: branch `feat/catalog-roster-and-trainee-selector` off `b387e07`, and the three snapshot lines every later slice record must quote.

- [ ] **Step 1: Record the opening snapshot before any edit** (`PLAN.md`'s "Slice Exit Criteria" section, owner ruling R38)

```bash
git -C /d/Projects/umamusume-laravel13 branch --show-current
git -C /d/Projects/umamusume-laravel13 status --porcelain
git -C /d/Projects/umamusume-laravel13 rev-parse HEAD
```

Paste all three outputs verbatim into the slice record. The peer session's modified and untracked files are their work: none of it goes in your commits.

- [ ] **Step 2: Create the worktree**

REQUIRED SUB-SKILL: `superpowers:using-git-worktrees`. Run it rather than hand-rolling the branch and copy.

```bash
git -C /d/Projects/umamusume-laravel13 worktree add \
  ../umamusume-laravel13-catalog-roster -b feat/catalog-roster-and-trainee-selector b387e07
```

- [ ] **Step 3: Wire the worktree so the gates can run**

The brief and this plan exist only as untracked files in the main tree, so a worktree off
`b387e07` does not have them and Task 3's `git add` would fail. Carry them over first:

```bash
cd /d/Projects/umamusume-laravel13-catalog-roster
mkdir -p docs/requests
cp ../umamusume-laravel13/docs/requests/2026-09-29-catalog-roster-and-trainee-selector.md \
   ../umamusume-laravel13/docs/requests/2026-09-29-catalog-roster-and-trainee-selector-plan.md \
   docs/requests/
ls -l docs/requests/2026-09-29-catalog-roster-and-trainee-selector*.md
```

Then provision the gitignored files the suite reads. `git worktree add` checks out **tracked** files only, and `.gitignore`'s AI-agent-tooling block ignores `/.agents` â€” so a fresh worktree has no `.agents/skills.json`, while `SkillRegistry::__construct()` reads exactly `base_path('.agents/skills.json')` and `SkillExecutor::loadConfig()` reads `.agents/config.json`. Without that directory, six of the seven tests in `SkillAutomationTest` fail with `Failed asserting that ... contains 'Route Inspector'`, and none of it has anything to do with this plan.

```bash
cp ../umamusume-laravel13/.env .env
cp -r ../umamusume-laravel13/.agents .agents
npm ci
composer install --no-interaction
```

Expected: both Markdown files present from the step above; `.agents/skills.json` exists; `npm ci` reports added packages with no `ERESOLVE`; `composer install` completes. Never run `npm audit fix` or `composer update` â€” C-8 forbids dependency movement.

If Step 4 still shows red after this, hunt for another gitignored file the suite reads before concluding the base is dirty:

```bash
grep -rnE "base_path\('\.[^']+'\)|storage_path\('\.[^']+'\)" app/ | head
```

- [ ] **Step 4: Prove the gates are green before you start**

```bash
php artisan test --compact
vendor/bin/phpstan analyse --no-progress --memory-limit=1G
```

Expected: both green at `b387e07`. Measured here 2026-09-29: **`6 failed, 2 skipped, 364 passed`** before `.agents` was copied, all six in `SkillAutomationTest`, and PHPStan **`[OK] No errors`** throughout â€” so the red was the provisioning gap in Step 3, not a dirty base. After Step 3's copy the suite must be green; if it is not, name the failing tests and report before writing any code.

**One red test at base is not automatically a stop condition â€” re-run before stopping.** Observed on the main tree the same evening: a full run reported `1 failed, 469 passed`, and a second run of the same code reported `470 passed, 0 failed`. A genuinely dirty base is a stop condition because you cannot separate your regression from a pre-existing one, but a flake dressed as one will stall the whole run for nothing. So: re-run any base failure twice, record which test and which two outcomes, and only then stop. If a test is confirmed flaky, file it rather than re-running until it goes green â€” an unexplained intermittent failure you quietly worked around is the kind of thing that shows up as a phantom regression three tasks later.

- [ ] **Step 5: Give the scratch database a home**

`database/database.sqlite` is the shared dev file, and `GATE-REGISTRY.md`'s "Global gates" table marks `migrate:fresh --seed` against it as destructive. Tasks 4-5 prove that gate on a scratch file instead.

```bash
touch database/scratch-catalog.sqlite
git check-ignore -v database/scratch-catalog.sqlite
```

Expected: `check-ignore` prints a matching `.gitignore` rule for `database/*.sqlite`. If it prints nothing, stop: the file would be committable, and a second SQLite file in the repo is not an acceptable outcome.

- [ ] **Step 6: Prove the app's own fetch identity can reach the source before building on it**

the approval note over the `gametora-characters` entry in `config/uma.php` records that the owner approved this source **without** a live availability check, and Tasks 8-9 both depend on the endpoint answering. `SourceFetcher` sends one header, `User-Agent: UmamusumeTrainerCompanion/0.2 (personal local tool)`, and no `Accept`. Send exactly that, before any code exists to send it:

```bash
curl -s -o /dev/null -w 'app UA: %{http_code} %{size_download}B\n' \
  -A 'UmamusumeTrainerCompanion/0.2 (personal local tool)' \
  'https://gametora.com/data/umamusume/character-cards.679f7c2e.json'
```

Measured 2026-09-29 against this tree: **`app UA: 200 251242B`** â€” the full document, byte-for-byte the size of the snapshot already on disk, so the `679f7c2e` hash in the config has not rotated either. No `Accept` header was needed and an empty UA also returned 200. Tasks 8 and 9 are therefore runnable as written, and `SourceFetcher` needs no header change.

If this returns 403 or a truncated body **when you run it**, stop before Task 6: the owner ruled on live fetch as the intake path, and the only fallback the plan knows about is the gitignored 2026-09-27 snapshot, which that ruling explicitly rejected. Report it and ask rather than discovering it mid-Task 9, and do not quietly reintroduce the snapshot as a workaround.

---

### Task 2: Fix the Japanese-name key defect (erratum E-12)

As Task 2 found it, `GametoraCharacterParser::parse()` read `$card['name_ja']` â€” today that read is `parse()`'s `name_ja` record line, fed from `name_jp`. The export publishes `name_jp`: `name_ja` appears **0** times in its 268 rows, `name_jp` **268** times. Every fetched trainee therefore stored a null Japanese name, so `PRD.md` US-1's acceptance test ("each detail page shows `name`, `name_ja`, â€¦") was unmet for fetched data. The sample fixture carries the same wrong key with `null` values, which is why the suite stayed green. The `<h1>` secondary label and the `ã‚¹ãƒšã‚·ãƒ£ãƒ«` search both depend on this, so it lands first.

**Files:**
- Modify: `app/Services/DataPipeline/Parsers/GametoraCharacterParser.php` â€” `parse()`'s `name_ja` record key
- Modify: `tests/Fixtures/gametora-character-cards.sample.json` (all four rows)
- Modify: `tests/Feature/GametoraCharacterParserTest.php`
- Modify: `KNOWN-ISSUES.md` (next free KI number)

**Interfaces:**
- Produces: `GametoraCharacterParser::parse()` records whose `name_ja` is populated from the export's `name_jp`. **The record key stays `name_ja`** â€” it is the contract `SourceParser`, `PromoteMatchedRecord::handle()`'s create and fill arrays and `PipelineRunner::run()`'s `MatchCandidate::create` branch all read, and it matches the `umamusume.name_ja` column. Only the source-side key changes.

- [ ] **Step 1: Write the failing test**

Append to `tests/Feature/GametoraCharacterParserTest.php`, matching that file's existing inline-body style:

```php
it('reads the Japanese name from the key the export actually publishes', function (): void {
    $body = json_encode([[
        'char_id' => 1007,
        'card_id' => 100701,
        'name_en' => 'Gold Ship',
        'name_jp' => 'ã‚´ãƒ¼ãƒ«ãƒ‰ã‚·ãƒƒãƒ—',
        'release' => '2021-02-24',
        'release_en' => '2025-06-26',
    ]], JSON_THROW_ON_ERROR);

    $record = (new GametoraCharacterParser)->parse($body)[0];

    // The export spells this key name_jp and never name_ja. Reading the wrong one
    // stores a null, and the detail page loses the Japanese name US-1 promises.
    expect($record['name_ja'])->toBe('ã‚´ãƒ¼ãƒ«ãƒ‰ã‚·ãƒƒãƒ—');
});
```

- [ ] **Step 2: Run it and watch it fail**

```bash
php artisan test --compact tests/Feature/GametoraCharacterParserTest.php
```

Expected: FAIL, `Failed asserting that null is identical to 'ã‚´ãƒ¼ãƒ«ãƒ‰ã‚·ãƒƒãƒ—'.` If it passes, stop â€” you are not on `b387e07`.

- [ ] **Step 3: Fix the parser key**

Replace `parse()`'s `name_ja` line:

```php
                // GameTora publishes `name_jp`; `name_ja` is this app's own column name.
                'name_ja' => $this->textOrNull($card['name_jp'] ?? null),
```

Do **not** write `$card['name_jp'] ?? $card['name_ja'] ?? null`. The fallback chain silently accepts a body that carries neither key, which is how this defect went unnoticed: a null reads as "the source has no Japanese name" instead of "you are reading the wrong key".

- [ ] **Step 4: Correct the fixture to the real shape**

In `tests/Fixtures/gametora-character-cards.sample.json`, rename the key on every row and give each trainee the client's real Japanese string â€” a fixture that holds `null` where the live export holds text is what let this ship:

| card_id | replace | with |
|---|---|---|
| 100101 | `"name_ja": null` | `"name_jp": "ã‚¹ãƒšã‚·ãƒ£ãƒ«ã‚¦ã‚£ãƒ¼ã‚¯"` |
| 100201 | `"name_ja": null` | `"name_jp": "ã‚µã‚¤ãƒ¬ãƒ³ã‚¹ã‚¹ã‚ºã‚«"` |
| 100401 | `"name_ja": null` | `"name_jp": "ã‚¦ã‚ªãƒƒã‚«"` |
| 100501 | `"name_ja": null` | `"name_jp": "ãƒ€ã‚¤ãƒ¯ã‚¹ã‚«ãƒ¼ãƒ¬ãƒƒãƒˆ"` |

Prove each rename happened instead of assuming it:

```bash
grep -c '"name_ja"' tests/Fixtures/gametora-character-cards.sample.json   # expect 0
grep -c '"name_jp"' tests/Fixtures/gametora-character-cards.sample.json   # expect 4
```

If the file's fourth row is a different `card_id` than the table above, read the file and use the name that belongs to the `char_id` actually in that row. Do not invent a fourth name to make the count work.

- [ ] **Step 5: Make the fixture test cover it**

In `tests/Feature/GametoraCharacterParserTest.php`, extend `it('reads the committed sample of the real dataset without raising')` so the Japanese name is asserted, not merely unasserted:

```php
        ->and($records[0]['name_ja'])->toBe('ã‚¹ãƒšã‚·ãƒ£ãƒ«ã‚¦ã‚£ãƒ¼ã‚¯')
```

- [ ] **Step 6: Run the file green**

```bash
php artisan test --compact tests/Feature/GametoraCharacterParserTest.php tests/Feature/FetchPipelineTest.php tests/Feature/CatalogTest.php
```

Expected: all pass. `CatalogTest`'s `'shows a detail page with Japanese name and provenance'` test sets `name_ja` explicitly through the factory, so it never exercised the parser and stays green either way â€” which is worth noting in the KI entry as a second reason the defect survived: the catalog test seeded the value instead of fetching it.

- [ ] **Step 7: File the KI entry**

Add the next free `KI-nn` to `KNOWN-ISSUES.md` â€” **re-derive it at run time**, `grep -oE "^## KI-[0-9]+" KNOWN-ISSUES.md | sort -t- -k2 -nr | head -1`. It was KI-20 at `b387e07`, so KI-21 was this plan's number when written, but the peer session files KI entries in the same window (KI-19 and KI-20 both landed during planning) and taking a stale number means overwriting their entry. with: the symptom (fetched rows stored a null `name_ja`, leaving US-1's detail-page requirement unmet while the suite stayed green); the cause (the fixture was authored against a guessed key and carried `null`, and the one catalog test that asserts a Japanese name seeded it directly rather than fetching it); the fix (parser key, fixture keys, new parser assertion); and the standing lesson â€” **a fixture that agrees with the code instead of with the source proves nothing**. Follow the format of the neighbouring entries, including their `verified-against <SHA>` style header if present.

- [ ] **Step 8: Gates and commit**

```bash
vendor/bin/pint --dirty --format agent
vendor/bin/phpstan analyse --no-progress --memory-limit=1G
git add app/Services/DataPipeline/Parsers/GametoraCharacterParser.php \
        tests/Fixtures/gametora-character-cards.sample.json \
        tests/Feature/GametoraCharacterParserTest.php \
        KNOWN-ISSUES.md
git status --short   # only these four paths, nothing of the peer's
git commit -m "fix(pipeline): read the Japanese name from the export's name_jp key

GameTora publishes name_jp; the parser read name_ja, so every fetched trainee
stored a null Japanese name and PRD US-1's detail-page requirement went unmet.
The sample fixture carried the same wrong key as null, and the one catalog test
that asserts a Japanese name seeded it directly, so nothing caught it."
```

---

### Task 3: ADR-0008 and the PRD citation (gate for everything after)

`AGENTS.md` (Architect): "Every new table, column, or class must cite a PRD requirement. No citation, no merge." Escalation 2 routes a not-in-PRD feature to the owner, who ruled yes in spec Â§2. `ADR-0005` is this repo's precedent for how such a ruling gets written down.

**Files:**
- Create: `docs/adr/0008-character-card-catalog-layer.md`
- Modify: `PRD.md` (FR-A gains item 6 after A-5, FR-C-1 is amended, US-1's acceptance cell is extended â€” the three `PRD.md` requirement ids, since those line numbers were already wrong for FR-C-1)
- Modify: `ARCHITECTURE.md` (Â§3 table inventory, and the "Support-card entities: proposed, not built" section)
- Modify: `ARCHITECTURE-ESSENTIALS.md`
- Modify: `docs/design-research/CONSTRAINTS.md` (the D-30 entry in Â§5, named by rule id because Â§5's line count moves)
- Modify: `docs/SOURCE-OF-TRUTH.md` (Â§5 note: which fields were A-confirmed, and when)

**Interfaces:**
- Produces the citation strings every later commit quotes: **FR-A-6** (card records), **FR-C-1 as amended** (run references a card), **ADR-0008**.

- [ ] **Step 1: Write ADR-0008**

Follow the shape of `docs/adr/0004-aptitude-and-scenario-cap-reference-data.md`, the closest precedent (Tier B data promoted with provenance). Required sections, each with its content stated:

- **Status** â€” Accepted, owner ruling 2026-09-29, recorded in `docs/requests/2026-09-29-catalog-roster-and-trainee-selector.md` Â§2.
- **Context** â€” The catalog models the character. `GametoraCharacterParser`'s class docblock deliberately keeps one card per trainee and drops the rest, and no PRD item mentioned a card. A roster request needs the card layer, so D-30 and escalation 2 make this a scope question rather than a migration.
- **Decision** â€” `character_cards`, one row per Global-released costume card keyed by the source's own `card_id`; `umamusume.external_ref` as the char-level link; `training_runs.character_card_id` nullable; a `unconfirmed` flag holding any card the Tier B source stands alone behind.
- **Options considered and rejected** â€” (a) card table with the run unchanged; rejected by the owner, the run is to carry the card. (b) no card table, trainee-only selector; rejected, it drops the requested nesting. (c) the tree as JSON in a column; rejected â€” not queryable, not joinable, not provenanceable, and it hides a second source of truth inside a field.
- **Consequences** â€” A trainee's `release_status` still derives from her debut card (the `release_status` ternary in `GametoraCharacterParser::parse()`, fed by that method's `$globalDebut` line; the `:77-79` / `:68` numbers this line carried, and the `GametoraCharacterParser.php:87-97` before them, stand here only as the drift record â€” Task 6's extraction `db8603c` moved the debut loop into `debutForms()`, so those numbers now name `return $records;` and the `debutForms()` docblock rather than the derivation, and one of them was already a line short before it), and the parser still emits **one record per `char_id` across the whole export**, measured 2026-09-29 at 135 records: 68 `GlobalReleased`, 67 `JapanOnly`. That is required by `PRD.md` US-2 (P0), whose acceptance text is "new JP releases appear with a JapanOnly or GlobalAnnounced flag instead of silently missing"; the card table is Global-only while the character feed is not, and the promotion verdict is where the two scopes meet. Measured the same day for all 68 Global trainees: the JP-earliest card, the Global-earliest card and the parser's debut are the same card, there are no date ties, and no trainee is tagged `JapanOnly` while owning a Global card. That measurement is what makes `where('release_status', GlobalReleased)->has('cards')` a safe roster filter. Record both measurements with their date, per the repo's dated-snapshot convention: they were true on 2026-09-29 and a later roster move can falsify them.
- **What is deliberately not stored** â€” per-card JP release dates, and the `aptitude`, `base_stats`, `four_star_stats`, `five_star_stats`, `stat_bonus`, `skills_*` families, plus the 163 cards with no `release_en`. Cite `PRD.md` Â§6 non-goals 6, 9 and 11 as still binding.
- **Not the support-card database** â€” state explicitly that this is the costume-card table and `PRD.md` Â§6.9 with `ADR-0005` declined (R37) still forbids `support_cards`, `user_support_cards` and `deck_slots`. `ARCHITECTURE.md`'s "Support-card entities: proposed, not built" section warns exactly this confusion, so the ADR must name the difference rather than leave it inferred.
- **Provenance** â€” GameTora is Tier B; `SOURCE-OF-TRUTH.md` Â§5:152 needs Tier A confirmation before a Tier B field becomes app data. Name `umamusu.wiki` and Game8, and the cross-check file Task 8 produces.

- [ ] **Step 2: Amend `PRD.md`**

Insert after FR-A-5 in `PRD.md`:

```
- A-6 [ADR-0008]: `CharacterCard` record: the source's own card id (unique), its
  Umamusume, the `[Global]` client title verbatim including its brackets, rarity,
  Global release date, and a debut-form flag derived from the earliest JP release
  among that trainee's cards. Only cards carrying a Global release date are stored;
  a trainee with no Global card does not appear in the catalog. A card confirmed by
  the Tier B source alone is stored flagged and hidden unless asked for.
```

Append to FR-C-1 in `PRD.md` without disturbing its existing wording:

```
, and since ADR-0008 an optional reference to the `CharacterCard` the run was
started on. `umamusume_id` remains the required owner of a run.
```

Extend the US-1 acceptance cell in `PRD.md`:

```
`GET /umamusume` lists each Global-released trainee with her cards nested; each
card row shows its client title, rarity and Global release date.
```

- [ ] **Step 3: Amend `ARCHITECTURE.md` and its digest**

Add `character_cards` to the Â§3 table inventory with its columns, and `external_ref` / `character_card_id` to their parents. In the "Support-card entities: proposed, not built" section, add a closing sentence that separates the two: `support_cards` stays forbidden by Â§6.9 and `ADR-0005` stays declined, while `character_cards` is built under `ADR-0008`; neither authorizes the other. Mirror the whole change in `ARCHITECTURE-ESSENTIALS.md` â€” AGENTS.md requires the digest to move in the same change as the migration.

- [ ] **Step 4: Amend D-30**

In `docs/design-research/CONSTRAINTS.md` Â§5, extend the D-30 "Render only what exists" permitted-surface sentence with:

```
`CharacterCard` (card_id, title, rarity, global_release_date, is_debut_form,
unconfirmed), `TrainingRun` (`character_card_id`)
```

Leave D-30's opening sentence untouched â€” this entry is the record of a scope question that was asked and answered, which is the rule's whole purpose.

- [ ] **Step 5: Prove the citations resolve**

```bash
grep -n "A-6\|ADR-0008" PRD.md
grep -rn "ADR-0008" docs/adr/ ARCHITECTURE.md ARCHITECTURE-ESSENTIALS.md
grep -n "character_cards" ARCHITECTURE.md ARCHITECTURE-ESSENTIALS.md docs/design-research/CONSTRAINTS.md
grep -c "^# ADR-0008" docs/adr/0008-character-card-catalog-layer.md
```

Expected: the FR-A-6 line, the digest entries, the D-30 entry, and exactly one ADR claiming number 0008. If a second file claims 0008, stop: the concurrent session took the number.

- [ ] **Step 6: Docs gates and commit**

```bash
php artisan test --compact tests/Feature/LoreGateParityTest.php tests/Feature/RenderedCopyHygieneTest.php
```

Expected: green. `make lore` runs over tracked Markdown in Task 13's sweep; this pair is the fast local check.

```bash
git add docs/adr/0008-character-card-catalog-layer.md PRD.md ARCHITECTURE.md \
        ARCHITECTURE-ESSENTIALS.md docs/design-research/CONSTRAINTS.md docs/SOURCE-OF-TRUTH.md \
        docs/requests/2026-09-29-catalog-roster-and-trainee-selector.md \
        docs/requests/2026-09-29-catalog-roster-and-trainee-selector-plan.md
git commit -m "docs(adr,prd): authorize the character-card catalog layer

The card layer the roster request needs has no PRD citation, and D-30 makes that
a scope question rather than a migration. The owner ruled to build it, including
a card reference on the run. ADR-0008 records the decision and keeps it clear of
PRD 6.9 and the declined ADR-0005: this is the costume-card table, not the
support-card database."
```

---

### Task 4: Schema â€” external link, card table, rarity enum, factory

**Files:**
- Create: `database/migrations/2026_09_29_120000_add_external_ref_to_umamusume_table.php`
- Create: `database/migrations/2026_09_29_120100_create_character_cards_table.php`
- Create: `app/Enums/CardRarity.php`
- Create: `app/Models/CharacterCard.php`
- Create: `database/factories/CharacterCardFactory.php`
- Modify: `app/Models/Umamusume.php` (docblock, `#[Fillable]`, new relation)
- Modify: `app/Actions/PromoteMatchedRecord.php` â€” `handle()`'s `@param` docblock and its create and fill arrays
- Modify: `app/Services/DataPipeline/Contracts/SourceParser.php` (docblock only)
- Modify: `lang/en/uma.php`
- Modify: `tests/Feature/EnumLabelTest.php` â€” the `labels every case of every rendered enum in human words` test's closure parameter type and its `->with()` dataset (add cases; remove none)
- Modify: `ARCHITECTURE.md` (Â§3 Catalog domain: the `character_cards` block and `umamusume.external_ref`)
- Modify: `ARCHITECTURE-ESSENTIALS.md` (the schema digest line for `character_cards`)
- Test: `tests/Feature/CharacterCardSchemaTest.php`

`AGENTS.md` puts this in the Architect's rules: "Schema changes require a migration plus updated
ESSENTIALS digest in the same change." Amendment A1 widened the table to twelve fillable columns, so both
design documents move here rather than in a later doc pass. Step 13 does it.

**Interfaces:**
- Produces:
  - `Umamusume::cards(): HasMany<CharacterCard>`; column `umamusume.external_ref: string|null`.
  - `CharacterCard` fillable `['card_id','umamusume_id','title','rarity','global_release_date','is_debut_form','unconfirmed','source_url','snapshot_path','fetched_at','source_timezone','is_manual']` (Amendment A1: the last five are the inline provenance set plus the card's own stop sign); casts `rarity => CardRarity::class`, `global_release_date => 'date'`, `fetched_at => 'datetime'`, `is_debut_form`, `unconfirmed` and `is_manual` => `'boolean'`.
  - `CardRarity: int` with `OneStar = 1`, `TwoStar = 2`, `ThreeStar = 3`, `use HasLabel`, and `stars(): string`.
  - `PromoteMatchedRecord` persists `external_ref` on both create and update paths.

- [ ] **Step 1: Write the failing schema test**

Create `tests/Feature/CharacterCardSchemaTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Models\CharacterCard;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Support\Facades\Schema;

it('creates a card, links it to its trainee and casts its rarity', function (): void {
    $umamusume = Umamusume::factory()->create();
    $card = CharacterCard::factory()->create([
        'umamusume_id' => $umamusume->id,
        'title' => '[RUN! RUIN! LAUNCHER!]',
        'rarity' => CardRarity::ThreeStar,
    ]);

    expect($card->rarity)->toBe(CardRarity::ThreeStar)
        ->and($card->umamusume->id)->toBe($umamusume->id)
        ->and($umamusume->cards)->toHaveCount(1);
});

it('keeps card ids unique so a re-fetch cannot duplicate a card', function (): void {
    $first = CharacterCard::factory()->create(['card_id' => 900701]);

    expect(fn () => CharacterCard::factory()->create([
        'card_id' => $first->card_id,
        'umamusume_id' => Umamusume::factory()->create()->id,
    ]))->toThrow(Illuminate\Database\QueryException::class);
});

it('deletes a trainee\'s cards with her', function (): void {
    $umamusume = Umamusume::factory()->create();
    CharacterCard::factory()->count(3)->create(['umamusume_id' => $umamusume->id]);

    $umamusume->delete();

    expect(CharacterCard::query()->count())->toBe(0);
});

it('stores the source character link the parser has always emitted', function (): void {
    expect(Schema::hasColumn('umamusume', 'external_ref'))->toBeTrue();
});
```

- [ ] **Step 2: Run it and confirm it fails**

```bash
php artisan test --compact tests/Feature/CharacterCardSchemaTest.php
```

Expected: FAIL â€” `Class "App\Models\CharacterCard" not found`, and the `external_ref` column assertion false.

- [ ] **Step 3: Generate the files with Artisan**

Per `.ai` framework rules ("Use `php artisan make:` commands"), and check `--help` rather than assuming options exist:

```bash
php artisan make:model CharacterCard --factory --no-interaction
php artisan make:enum CardRarity --no-interaction || php artisan make:class Enums/CardRarity --no-interaction
php artisan make:migration add_external_ref_to_umamusume_table --no-interaction
php artisan make:migration create_character_cards_table --no-interaction
```

If `make:enum` is unavailable in this Laravel version, `make:class` is the fallback and the file must still declare an `enum`. Rename the two generated migrations to the timestamps in the Files block so they order after the existing `2026_09_27_*` set.

- [ ] **Step 4: Write the external link migration**

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('umamusume', function (Blueprint $table): void {
            /*
             * GametoraCharacterParser has emitted `gametora:char:{id}` since it was
             * written and nothing stored it, so a promoted row lost its only durable
             * link to the source (ADR-0008). Cards attach through this rather than
             * by re-matching on name: the name is what the match engine refuses to
             * guess about, the char id is what the source asserts.
             */
            $table->string('external_ref')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('umamusume', function (Blueprint $table): void {
            $table->dropColumn('external_ref');
        });
    }
};
```

- [ ] **Step 5: Write the card table migration**

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('character_cards', function (Blueprint $table): void {
            $table->id();
            // The source's own card id, not this table's primary key: it is what a
            // re-fetch matches on, so a fetch is idempotent by identity rather than
            // by name (PRD FR-B-5).
            $table->unsignedInteger('card_id')->unique();
            $table->foreignId('umamusume_id')->constrained('umamusume')->cascadeOnDelete();
            // Verbatim [Global] client string, brackets included. CONSTRAINTS.md:38
            // keeps such names as source data and puts the lore guard on the display
            // path, so this column is never a place to normalize copy.
            $table->string('title');
            $table->unsignedTinyInteger('rarity');
            // Required: a card with no Global date is not a row in this table at all.
            $table->date('global_release_date');
            // Derived from the earliest JP release among the trainee's cards, by the
            // same rule GametoraCharacterParser already applies to the debut form.
            $table->boolean('is_debut_form')->default(false);
            // Tier B alone is not enough: ADR-0008's Provenance section ("Tier B
            // data, Tier A witness") records the rule. A card whose
            // Global status did not reach two sources is stored flagged and hidden
            // by default, rather than dropped or quietly trusted.
            $table->boolean('unconfirmed')->default(false);
            /*
             * Inline provenance, the way the reference tables in this repo do it.
             * ADR-0003 Amendment R3 requires `source_url`, `snapshot_path`,
             * `fetched_at` and `source_timezone` on the reference row itself. Three of
             * the four existing reference tables carry all four (`scenario_races`,
             * `scenario_slots`, `race_catalog_slots`); `scenarios` predates the full set
             * and carries `source_url`, `fetched_at` and `is_manual` only, which is
             * `ADR-0004:50`'s own choice rather than a gap to copy. `data_sources` stays
             * what it always was: the character-level provenance table behind FR-A-4 and
             * the detail page's Provenance section. A card is not a character, and
             * borrowing the parent's provenance row would make "where did this release
             * date come from" a question with no row that answers it.
             */
            $table->string('source_url');
            $table->string('snapshot_path')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->string('source_timezone')->nullable();
            // FR-B-4's stop sign at card grain. Borrowing the trainee's flag was the
            // first draft and it is wrong twice over: a Trainer may correct one card's
            // title without claiming her whole character, and every other reference
            // table here carries its own.
            $table->boolean('is_manual')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('character_cards');
    }
};
```

No explicit index on `umamusume_id` beyond the FK: the table holds ~107 rows (measured 2026-09-29) and the catalog reads it in one `whereIn`. Add the index when the roster is a few thousand rows, not before.

- [ ] **Step 6: Write the enum**

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum CardRarity: int
{
    use HasLabel;

    case OneStar = 1;
    case TwoStar = 2;
    case ThreeStar = 3;

    /**
     * The glyph run is the ordinal signal. Hue cannot order three near-identical
     * fills and G-6 will not accept a colour-only readout, for the same reason
     * mood-pill.blade.php pairs each fill with an arrow.
     */
    public function stars(): string
    {
        return str_repeat('â˜…', $this->value);
    }
}
```

- [ ] **Step 7: Add the labels and extend the enum test**

`HasLabel` resolves `uma.card_rarity.{CASE_NAME}` from `lang/en/uma.php`, keyed on the **case name**, not the backing value. Add a sibling of the existing `'release_status'` block:

```php
    'card_rarity' => [
        'OneStar' => 'One star',
        'TwoStar' => 'Two stars',
        'ThreeStar' => 'Three stars',
    ],
```

Then in `tests/Feature/EnumLabelTest.php`: add `use App\Enums\CardRarity;`, widen the closure parameter type of `it('labels every case of every rendered enum in human words')` to `AliasLanguage|MatchTier|ReleaseStatus|RunStatus|SkillAcquisition|CardRarity`, and append `...CardRarity::cases(),` to that test's `->with()` dataset. This **adds** coverage; deleting or skipping any existing case is a Floor violation.

- [ ] **Step 8: Write the model**

Replace the generated stub with:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CardRarity;
use Database\Factories\CharacterCardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One costume card that exists on [Global] (PRD FR-A-6, ADR-0008).
 *
 * The trainee is modelled once, on `umamusume`. This row holds only what differs
 * between her forms: the client title, the rarity, and when [Global] shipped it.
 *
 * @property int $id
 * @property int $card_id the source's own card id
 * @property int $umamusume_id
 * @property string $title verbatim [Global] client string, brackets included
 * @property CardRarity $rarity
 * @property Carbon $global_release_date
 * @property bool $is_debut_form
 * @property bool $unconfirmed not confirmed by two sources; hidden by default
 * @property string $source_url
 * @property string|null $snapshot_path
 * @property Carbon|null $fetched_at
 * @property string|null $source_timezone
 * @property bool $is_manual the Trainer's own correction; the engine's stop sign (FR-B-4)
 * @property-read Umamusume $umamusume
 */
#[Table('character_cards')]
#[Fillable(['card_id', 'umamusume_id', 'title', 'rarity', 'global_release_date', 'is_debut_form', 'unconfirmed', 'source_url', 'snapshot_path', 'fetched_at', 'source_timezone', 'is_manual'])]
class CharacterCard extends Model
{
    /** @use HasFactory<CharacterCardFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Umamusume, $this>
     */
    public function umamusume(): BelongsTo
    {
        return $this->belongsTo(Umamusume::class);
    }

    protected function casts(): array
    {
        return [
            'rarity' => CardRarity::class,
            'global_release_date' => 'date',
            'fetched_at' => 'datetime',
            'is_debut_form' => 'boolean',
            'unconfirmed' => 'boolean',
            'is_manual' => 'boolean',
        ];
    }
}
```

Amendment A1 put five new columns on the migration and only the model's docblock and `#[Fillable]` list absorbed them; two of them also need a cast, which is why they are in the block above. `fetched_at` must be `'datetime'`: without it the attribute returns a string, and Task 11 Step 5's `$card->fetched_at->timezone(config('uma.display_timezone'))->format('M j, Y')` calls `timezone()` on that string. `is_manual` must be `'boolean'` so the store's guard is an explicit truth test rather than a bet on how this driver represents a tinyint. Assert both in `CharacterCardSchemaTest`: a cast nobody asserts on is a cast that silently regresses.

- [ ] **Step 9: Add the inverse relation and the link to `Umamusume`**

In `app/Models/Umamusume.php`: add `@property string|null $external_ref` and `@property-read Collection<int, CharacterCard> $cards` to the docblock, append `'external_ref'` to `Umamusume`'s `#[Fillable]` list, and add beside `aliases()`:

```php
    /**
     * @return HasMany<CharacterCard, $this>
     */
    public function cards(): HasMany
    {
        return $this->hasMany(CharacterCard::class);
    }
```

`HasMany` and `Collection` are already imported there.

- [ ] **Step 10: Persist the external ref**

In `app/Actions/PromoteMatchedRecord.php`, add `'external_ref' => $record['external_ref'] ?? null,` to `handle()`'s create array, and `'external_ref' => $record['external_ref'] ?? $existing->external_ref,` to the `fill()` array in `handle()`'s existing-record branch â€” the same "absent must not blank out stored" shape as the `...$this->aptitudes($record)` spread in that array. Extend `handle()`'s `@param` docblock with `external_ref?: string`. The key already arrives from `GametoraCharacterParser::parse()`'s `external_ref` record key (the `:101`, and `:100` before Task 2's fix inserted a comment line, stand here as that drift record â€” they are not an instruction, and no line number in this file is safe across tasks); `handle()`'s create and fill arrays simply never carried it.

- [ ] **Step 11: Write the factory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CardRarity;
use App\Models\CharacterCard;
use App\Models\Umamusume;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CharacterCard>
 */
class CharacterCardFactory extends Factory
{
    protected $model = CharacterCard::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'card_id' => fake()->unique()->numberBetween(900000, 999999),
            'umamusume_id' => Umamusume::factory(),
            'title' => '[Sample Form]',
            'rarity' => CardRarity::ThreeStar,
            'global_release_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'is_debut_form' => false,
            'unconfirmed' => false,
            // Amendment A1: source_url is NOT NULL in the migration, so a factory that
            // omits it cannot insert. These four say "a fetch wrote this row"; the
            // manual() state below is the human case.
            'source_url' => 'https://gametora.test/character-cards.json',
            'snapshot_path' => null,
            'fetched_at' => now(),
            'source_timezone' => 'Asia/Tokyo',
            'is_manual' => false,
        ];
    }

    public function manual(): static
    {
        return $this->state(fn (): array => ['is_manual' => true]);
    }

    public function debut(): static
    {
        return $this->state(fn (): array => ['is_debut_form' => true]);
    }

    public function unconfirmed(): static
    {
        return $this->state(fn (): array => ['unconfirmed' => true]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (): array => ['unconfirmed' => false]);
    }
}
```

`#[Fillable]` does not stop a factory writing unlisted columns â€” factories are unguarded â€” so a factory is never proof a column is mass-assignable. Task 5 proves that with `Model::create()`.

- [ ] **Step 12: Run green, then prove rollback on the scratch file**

```bash
php artisan test --compact tests/Feature/CharacterCardSchemaTest.php tests/Feature/EnumLabelTest.php
DB_DATABASE="$PWD/database/scratch-catalog.sqlite" php artisan migrate:fresh --seed --no-interaction
DB_DATABASE="$PWD/database/scratch-catalog.sqlite" php artisan migrate:rollback --step=2 --no-interaction
DB_DATABASE="$PWD/database/scratch-catalog.sqlite" php artisan migrate --no-interaction
```

Expected: tests pass; `migrate:fresh --seed` succeeds **on the scratch file only** (C-5, and GATE-REGISTRY marks the shared dev file destructive); rollback drops both new migrations without error; re-migrating recreates them. Never aim these at `database/database.sqlite`.

- [ ] **Step 13: Move the design docs with the migration**

    > **NOTE (2026-09-30): the target table widened after this step was written.** `dd90330` added
    > `skills_innate` and `skills_unique`, so `#[Fillable]` in `app/Models/CharacterCard.php` now lists
    > **fourteen** columns, not the twelve named in the acceptance criterion below. Reconcile to the
    > current `#[Fillable]` list, not to the count this step was drafted against. `ARCHITECTURE.md` Â§3 and
    > `ARCHITECTURE-ESSENTIALS.md` still carry the twelve-name list as of this date; that gap closes when
    > this step fires, and only here.

`AGENTS.md` (Architect) requires it: "Schema changes require a migration plus updated ESSENTIALS digest in
the same change." `ARCHITECTURE.md` Â§3 and `ARCHITECTURE-ESSENTIALS.md` gained their `character_cards`
shape in Task 3, and ADR-0008's dated erratum commit already widened both to the A1 column set, so this step
is a reconciliation, not a rewrite: read the `character_cards` block, the paragraph that follows the
Catalog fence, and the digest line against what Steps 4 and 5 actually write, and correct any column that
disagrees. What must end up true, in both documents:

- the table is `card_id`, `umamusume_id`, `title`, `rarity`, `global_release_date`, `is_debut_form`,
  `unconfirmed`, `source_url` (not null), `snapshot_path` (nullable), `fetched_at` (nullable),
  `source_timezone` (nullable) and `is_manual` (default false): twelve fillable columns plus `id` and
  timestamps as this plan was drafted, **fourteen** now that `dd90330` (2026-09-30) added `skills_innate`
  and `skills_unique` â€” reconcile against the live `#[Fillable]` list, which is authoritative;
- a card's provenance is **inline** on the card row, per `ADR-0003` Amendment R3, the convention
  `scenario_races`, `scenario_slots` and `race_catalog_slots` already follow;
- `data_sources` is unchanged in meaning: `umamusume_id`-scoped, the table behind FR-A-4 and the detail
  page's Provenance section, and not where a card's provenance lives;
- `is_manual` sits at card grain, so a Trainer's correction to one card's title is immutable to the engine
  without claiming that trainee's whole record (FR-B-4).

Then run `composer lore` (not `make lore`: GNU make is absent on this host, which is the KI-4 gap
`LoreGateParityTest` exists to cover) and re-read both passages
rather than trusting this step's own prose that they landed.

- [ ] **Step 14: Gates and commit**

```bash
vendor/bin/pint --dirty --format agent
vendor/bin/phpstan analyse --no-progress --memory-limit=1G
php artisan test --compact
```

Expected: Pint clean, PHPStan zero errors, full suite green.

```bash
git add database/migrations/2026_09_29_1200*.php database/migrations/2026_09_29_1201*.php \
        app/Enums/CardRarity.php app/Models/CharacterCard.php app/Models/Umamusume.php \
        app/Actions/PromoteMatchedRecord.php app/Services/DataPipeline/Contracts/SourceParser.php \
        database/factories/CharacterCardFactory.php lang/en/uma.php \
        tests/Feature/CharacterCardSchemaTest.php tests/Feature/EnumLabelTest.php \
        ARCHITECTURE.md ARCHITECTURE-ESSENTIALS.md
git commit -m "feat(schema): the character-card table, its rarity enum, and the source link

FR-A-6 / ADR-0008. Cards key on the source's own card_id so a re-fetch is
idempotent by identity rather than by name, and attach through the new
umamusume.external_ref the parser has emitted from the start but nothing stored.
A card without a Global date is not a row here, and one the Tier B source stands
alone behind is stored flagged."
```

---

### Task 5: The run references a card

**Files:**
- Create: `database/migrations/2026_09_29_120200_add_character_card_id_to_training_runs_table.php`
- Modify: `app/Models/TrainingRun.php` â€” `#[Fillable]` and its docblock
- Modify: `app/Http/Requests/StoreTrainingRunRequest.php` â€” `rules()`
- Test: append to `tests/Feature/CharacterCardSchemaTest.php`

**Interfaces:**
- Produces: `TrainingRun::characterCard(): BelongsTo<CharacterCard>` (nullable), fillable `character_card_id`, and a `character_card_id` validation rule that only accepts a card belonging to the submitted `umamusume_id`.

- [ ] **Step 1: Write the failing tests**

Append to `tests/Feature/CharacterCardSchemaTest.php`:

```php
it('records the form a run was started on', function (): void {
    $umamusume = Umamusume::factory()->create();
    $card = CharacterCard::factory()->create(['umamusume_id' => $umamusume->id]);

    // Model::create() rather than the factory: #[Fillable] does not constrain a
    // factory, so only a create() proves this column is actually assignable.
    $run = TrainingRun::create([
        'umamusume_id' => $umamusume->id,
        'character_card_id' => $card->id,
        'status' => 'Active',
    ]);

    expect($run->characterCard->id)->toBe($card->id)
        ->and($run->umamusume->id)->toBe($umamusume->id);
});

it('refuses a card that belongs to another trainee', function (): void {
    $mine = Umamusume::factory()->create();
    $theirs = Umamusume::factory()->create();
    $card = CharacterCard::factory()->create(['umamusume_id' => $theirs->id]);

    test()->post('/training-runs', [
        'umamusume_id' => $mine->id,
        'character_card_id' => $card->id,
        'status' => 'Active',
    ])->assertSessionHasErrors('character_card_id');

    expect(TrainingRun::query()->count())->toBe(0);
});

it('still creates a run with only a trainee', function (): void {
    $umamusume = Umamusume::factory()->create();

    test()->post('/training-runs', [
        'umamusume_id' => $umamusume->id,
        'status' => 'Active',
    ])->assertSessionHasNoErrors();

    expect(TrainingRun::first()->character_card_id)->toBeNull();
});
```

- [ ] **Step 2: Run, confirm failure**

```bash
php artisan test --compact tests/Feature/CharacterCardSchemaTest.php
```

Expected: FAIL on an unknown `character_card_id` column in `training_runs`, and on `characterCard` being undefined.

- [ ] **Step 3: Write the migration**

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            /*
             * Nullable and additive on purpose (FR-C-1 as amended by ADR-0008). A run
             * created before the card layer, or one where the Trainer named only the
             * trainee, is not a run missing data it should have had.
             *
             * Which key it targets is settled by precedent rather than preference:
             * `2026_09_28_191829` points `race_entries.race_catalog_slot_id` at `race_catalog_slots.id`
             * with `foreignId()->constrained()` even though that table also carries a source-side
             * unique grain. A foreign key names the row, the source id stays the thing a fetch
             * matches on, and the local id is durable because Task 7's store upserts by `card_id`
             * instead of recreating rows -- so `Rule::exists('character_cards','id')` in Step 5 and
             * `selectionId` in Task 12's payload all read the same key.
             */
            $table->foreignId('character_card_id')->nullable()->constrained('character_cards')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('training_runs', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('character_card_id');
        });
    }
};
```

`umamusume_id` keeps its NOT NULL FK: the card names a form, the trainee still owns the run, and every existing reader (`runs.index`, `runs.show`, the export, `ApiV1`) keeps working untouched.

- [ ] **Step 4: Model and relation**

In `app/Models/TrainingRun.php`: append `'character_card_id'` to `TrainingRun`'s `#[Fillable]`, add `@property int|null $character_card_id` and `@property-read CharacterCard|null $characterCard` to the docblock, add the `CharacterCard` import if absent, and:

```php
    /**
     * @return BelongsTo<CharacterCard, $this>
     */
    public function characterCard(): BelongsTo
    {
        return $this->belongsTo(CharacterCard::class);
    }
```

Add `use Illuminate\Database\Eloquent\Relations\BelongsTo;` if the file does not already import it.

- [ ] **Step 5: Validation at the boundary**

In `app/Http/Requests/StoreTrainingRunRequest.php`, immediately after the `umamusume_id` rule in `StoreTrainingRunRequest::rules()`:

```php
            /*
             * A card is a valid choice only if it is one of the submitted trainee's
             * forms. Carrying that join in the exists rule makes a mismatched pair a
             * validation error at the boundary instead of a row that contradicts
             * itself, so the combobox cannot be coaxed into naming a card its
             * run's trainee does not own.
             */
            'character_card_id' => ['nullable', 'integer', Rule::exists('character_cards', 'id')
                ->where('umamusume_id', $this->input('umamusume_id'))],
```

`Rule` is already imported in `StoreTrainingRunRequest`. Add no controller-side check: the Floor bans business logic in controllers, and `TrainingRunController::store()` stays exactly as it is.

- [ ] **Step 6: Run green, prove rollback, run gates, commit**

```bash
php artisan test --compact tests/Feature/CharacterCardSchemaTest.php tests/Feature/TrainingRunTest.php tests/Feature/RunUpdateTest.php
DB_DATABASE="$PWD/database/scratch-catalog.sqlite" php artisan migrate --no-interaction
DB_DATABASE="$PWD/database/scratch-catalog.sqlite" php artisan migrate:rollback --step=1 --no-interaction
vendor/bin/pint --dirty --format agent && vendor/bin/phpstan analyse --no-progress --memory-limit=1G
```

Expected: all green â€” including `TrainingRunTest`'s `'creates a run and renders logged turns in order'` test, which posts no `character_card_id`. Its passing is the proof the change is additive.

```bash
git add database/migrations/2026_09_29_1202*.php app/Models/TrainingRun.php \
        app/Http/Requests/StoreTrainingRunRequest.php tests/Feature/CharacterCardSchemaTest.php
git commit -m "feat(http,schema): let a run name the form it started on

FR-C-1 as amended by ADR-0008. The column is nullable so a pre-card run is not a
run with missing data, and the exists rule carries the join to the submitted
trainee so a mismatched pair never reaches a row."
```

---

### Task 6: The card parser

Cards come from the **same** GameTora document the character parser reads. Debut semantics must be identical in both, so the derivation is extracted once rather than restated â€” a second copy is how two surfaces end up disagreeing about which form is the debut.

**Files:**
- Create: `app/Services/DataPipeline/Contracts/CharacterCardSourceParser.php`
- Create: `app/Services/DataPipeline/Parsers/GametoraCharacterCardParser.php`
- Modify: `app/Services/DataPipeline/Parsers/GametoraCharacterParser.php` â€” `parse()`'s inline debut loop, its two `dateOrNull()` call sites, and `dateOrNull()`'s visibility
- Create: `tests/Fixtures/gametora-character-cards.global.sample.json`
- Test: `tests/Feature/CharacterCardParserTest.php`

**Interfaces:**
- Consumes: a GameTora `character-cards` JSON body.
- Produces:
  - `GametoraCharacterParser::debutForms(iterable $cards): array<int, array<string,mixed>>` â€” `char_id => debut card`; the single definition of "debut form".
  - `GametoraCharacterParser::dateOrNull(mixed $value): ?string`, now `public static`.
  - `CharacterCardSourceParser::parse(string $body): list<array{card_id:int, char_external_ref:string, title:string, rarity:int, global_release_date:string, is_debut_form:bool}>`.
  - `char_external_ref` is exactly `'gametora:char:{char_id}'`, the string `GametoraCharacterParser::parse()` writes as its `external_ref` key (`:82` since Task 6's extraction, `:101` before it), so Task 7 can resolve the trainee.

- [ ] **Step 1: Build the fixture from the live export, keeping the JP-only rows as the trap**

Use the export's own values, not invented ones (G-16: every name must be a real Global string). Every row below was read out of `research-scratch/data/json/character-cards.json` on 2026-09-29. Create `tests/Fixtures/gametora-character-cards.global.sample.json`:

```json
[
  {"char_id":1001,"card_id":100101,"name_en":"Special Week","name_jp":"ã‚¹ãƒšã‚·ãƒ£ãƒ«ã‚¦ã‚£ãƒ¼ã‚¯","title":"Special Dreamer","title_en_gl":"[Special Dreamer]","rarity":3,"release":"2021-02-24","release_en":"2025-06-26"},
  {"char_id":1001,"card_id":100102,"name_en":"Special Week","name_jp":"ã‚¹ãƒšã‚·ãƒ£ãƒ«ã‚¦ã‚£ãƒ¼ã‚¯","title":"Hopping â™ª Vitamin Heart","title_en_gl":"[Hopp'nâ™ªHappy Heart]","rarity":3,"release":"2021-07-29","release_en":"2025-10-14"},
  {"char_id":1001,"card_id":100103,"name_en":"Special Week","name_jp":"ã‚¹ãƒšã‚·ãƒ£ãƒ«ã‚¦ã‚£ãƒ¼ã‚¯","title":"Supreme Commander of the Rising Sun","title_en_gl":"[Ruler of Japan]","rarity":3,"release":"2022-07-20","release_en":"2026-06-25"},
  {"char_id":1007,"card_id":100701,"name_en":"Gold Ship","name_jp":"ã‚´ãƒ¼ãƒ«ãƒ‰ã‚·ãƒƒãƒ—","title":"Red Strife","title_en_gl":"[Red Strife]","rarity":2,"release":"2021-02-24","release_en":"2025-06-26"},
  {"char_id":1007,"card_id":100702,"name_en":"Gold Ship","name_jp":"ã‚´ãƒ¼ãƒ«ãƒ‰ã‚·ãƒƒãƒ—","title":"Run! Fun! Watergun!","title_en_gl":"[RUN! RUIN! LAUNCHER!]","rarity":3,"release":"2022-07-29","release_en":"2026-07-02"},
  {"char_id":1007,"card_id":100703,"name_en":"Gold Ship","name_jp":"ã‚´ãƒ¼ãƒ«ãƒ‰ã‚·ãƒƒãƒ—","title":"La Mode 564","rarity":3,"release":"2023-08-31","release_en":null},
  {"char_id":1003,"card_id":100301,"name_en":"Tokai Teio","name_jp":"ãƒˆã‚¦ã‚«ã‚¤ãƒ†ã‚¤ã‚ªãƒ¼","title":"Top of Joyful","title_en_gl":"[Peak Joy]","rarity":3,"release":"2021-02-24","release_en":"2025-06-26"},
  {"char_id":1003,"card_id":100302,"name_en":"Tokai Teio","name_jp":"ãƒˆã‚¦ã‚«ã‚¤ãƒ†ã‚¤ã‚ªãƒ¼","title":"Beyond the Horizon","title_en_gl":"[Beyond the Horizon]","rarity":3,"release":"2021-03-30","release_en":"2025-07-16"},
  {"char_id":1003,"card_id":100303,"name_en":"Tokai Teio","name_jp":"ãƒˆã‚¦ã‚«ã‚¤ãƒ†ã‚¤ã‚ªãƒ¼","title":"Dream Butterfly of Purple Clouds","rarity":3,"release":"2023-10-30","release_en":null},
  {"char_id":1127,"card_id":112701,"name_en":"Fenomeno","name_jp":"ãƒ•ã‚§ãƒŽãƒ¼ãƒ¡ãƒŽ","title":"Black Flame of Righteousness","rarity":3,"release":"2025-04-21","release_en":null},
  {"char_id":1127,"card_id":112702,"name_en":"Fenomeno","name_jp":"ãƒ•ã‚§ãƒŽãƒ¼ãƒ¡ãƒŽ","title":"Violet Flame of Fortitude","rarity":3,"release":"2026-08-31","release_en":null}
]
```

Fenomeno is here twice with no `release_en` on either form, so she must yield **zero** records: the fixture keeps the brief's impossible `Fenomeno` case (erratum E-6) as a permanent guard on the Global-only rule.

- [ ] **Step 2: Write the failing tests**

Create `tests/Feature/CharacterCardParserTest.php`:

```php
<?php

declare(strict_types=1);

use App\Services\DataPipeline\Parsers\GametoraCharacterCardParser;
use App\Services\DataPipeline\Parsers\GametoraCharacterParser;

/**
 * @return list<array<string, mixed>>
 */
function globalCardRecords(): array
{
    return (new GametoraCharacterCardParser)->parse(globalCardBody());
}

function globalCardBody(): string
{
    return file_get_contents(base_path('tests/Fixtures/gametora-character-cards.global.sample.json')) ?: '';
}

it('emits one record per Global card and drops every JP-only one', function (): void {
    $cardIds = array_column(globalCardRecords(), 'card_id');

    // Eleven rows in, seven with a Global date: 11 minus the four [JP-Only] forms this
    // block itself names (La Mode 564, Dream Butterfly, both Fenomeno). It said eight
    // until erratum E-17 re-counted the fixture: Special Week 3 + Gold Ship 2 + Tokai Teio 2.
    expect($cardIds)->toHaveCount(7)
        ->and($cardIds)->not->toContain(100703)
        ->and($cardIds)->not->toContain(100303)
        ->and($cardIds)->not->toContain(112701)
        ->and($cardIds)->not->toContain(112702);
});

it('keeps the [Global] client title verbatim, brackets and all', function (): void {
    $titles = array_column(globalCardRecords(), 'title');

    // The Global string is not the JP string with brackets: the export's own `title`
    // for card 100702 is "Run! Fun! Watergun!". CONSTRAINTS.md:38 bars editing a
    // verbatim client name, so the guard belongs on the display path, never here.
    expect($titles)->toContain('[RUN! RUIN! LAUNCHER!]')
        ->and($titles)->toContain('[Hopp\'nâ™ªHappy Heart]')
        ->and($titles)->not->toContain('Run! Fun! Watergun!')
        ->and($titles)->not->toContain('Supreme Commander of the Rising Sun');
});

it('flags exactly one debut form per trainee, the earliest JP release', function (): void {
    $byCard = [];
    foreach (globalCardRecords() as $record) {
        $byCard[$record['card_id']] = $record;
    }

    expect($byCard[100101]['is_debut_form'])->toBeTrue()
        ->and($byCard[100102]['is_debut_form'])->toBeFalse()
        ->and($byCard[100103]['is_debut_form'])->toBeFalse()
        ->and($byCard[100701]['is_debut_form'])->toBeTrue()
        ->and($byCard[100702]['is_debut_form'])->toBeFalse()
        // Gold Ship's debut is her JP-earliest card, and it is 2-star: the debut flag
        // is not a rarity claim, which is why the trainee row shows both separately.
        ->and($byCard[100701]['rarity'])->toBe(2);
});

it('links each card to its trainee by the ref the character parser writes', function (): void {
    $records = globalCardRecords();

    expect($records[0]['char_external_ref'])->toBe('gametora:char:1001')
        ->and(array_unique(array_column($records, 'char_external_ref')))->toHaveCount(3);
});

it('derives the debut through the same code path as the character catalog', function (): void {
    $cards = json_decode(globalCardBody(), true, 512, JSON_THROW_ON_ERROR);
    $characters = (new GametoraCharacterParser)->parse(globalCardBody());
    $debutForms = GametoraCharacterParser::debutForms($cards);

    // Two parsers, one rule. The agreement worth pinning is the flag itself: each
    // card row's debut equals the pick this trainee's cards get from the one
    // derivation both grains call, because debutForms() is shared, not copied.
    foreach (globalCardRecords() as $record) {
        $charId = (int) str_replace('gametora:char:', '', $record['char_external_ref']);

        expect($record['is_debut_form'])->toBe((int) $debutForms[$charId]['card_id'] === $record['card_id']);
    }

    // This block first read `->toBe(['Special Week', 'Gold Ship', 'Tokai Teio'])` and
    // `->not->toContain('Fenomeno')`. Both halves are false at this grain, and the
    // reason is the asymmetry between the two surfaces, not a parser bug:
    // GametoraCharacterParser emits **all 135** trainees the export carries, recording
    // one with no Global form as `JapanOnly` instead of dropping her, because `PRD.md`
    // US-2 is P0 and its acceptance text asks for a flag rather than a missing row
    // (erratum E-15; ADR-0008's "The character feed does not become Global-only").
    // So Fenomeno IS in the character records, `release_status` JapanOnly, and she is
    // absent from the card records because the card layer holds no row for her. Do not
    // "fix" the parser to match the old sentence. The order is char_id order (`ksort`),
    // which puts Tokai Teio before Gold Ship and is unchanged by the extraction.
    expect(array_column($characters, 'name'))->toBe(['Special Week', 'Tokai Teio', 'Gold Ship', 'Fenomeno'])
        ->and(array_column($characters, 'external_ref'))->toBe([
            'gametora:char:1001',
            'gametora:char:1003',
            'gametora:char:1007',
            'gametora:char:1127',
        ])
        ->and(array_column(globalCardRecords(), 'char_external_ref'))->not->toContain('gametora:char:1127');
});

it('returns nothing for a body that is not a JSON array', function (): void {
    expect((new GametoraCharacterCardParser)->parse('not json'))->toBe([])
        ->and((new GametoraCharacterCardParser)->parse('{"object":true}'))->toBe([]);
});

it('skips a row with no usable card id, no client title, or no real Global date', function (): void {
    $body = json_encode([
        ['card_id' => null, 'char_id' => 1001, 'title_en_gl' => '[No Id]', 'rarity' => 3, 'release_en' => '2025-06-26'],
        ['card_id' => 100102, 'char_id' => 1001, 'title_en_gl' => null, 'rarity' => 3, 'release_en' => '2025-10-14'],
        ['card_id' => 100103, 'char_id' => 1001, 'title_en_gl' => '[Sentinel]', 'rarity' => 3, 'release_en' => '9999-12-31'],
        ['card_id' => 100104, 'char_id' => 1001, 'title_en_gl' => '[Not A Date]', 'rarity' => 3, 'release_en' => 'soon'],
        ['card_id' => 100105, 'char_id' => 1001, 'title_en_gl' => '[Bad Rarity]', 'rarity' => 7, 'release_en' => '2025-06-26'],
    ], JSON_THROW_ON_ERROR);

    // 9999-12-31 is the export's own placeholder (GametoraCharacterParser::UNKNOWN_DATE), and a
    // rarity outside 1..3 is a claim this app has no word for. Neither becomes a row.
    expect((new GametoraCharacterCardParser)->parse($body))->toBe([]);
});

it('treats a card id as a string or an int without losing its identity', function (): void {
    $body = json_encode([
        ['card_id' => '100701', 'char_id' => '1007', 'title_en_gl' => '[Red Strife]', 'rarity' => '2', 'release_en' => '2025-06-26', 'release' => '2021-02-24'],
    ], JSON_THROW_ON_ERROR);

    $records = (new GametoraCharacterCardParser)->parse($body);

    // The column is typed and the card_id is the join key, so a numeric string from a
    // hand-made body must land as an int rather than as a loose match somewhere later.
    expect($records)->toHaveCount(1)
        ->and($records[0]['card_id'])->toBe(100701)
        ->and($records[0]['rarity'])->toBe(2)
        ->and($records[0]['is_debut_form'])->toBeTrue();
});
```

- [ ] **Step 3: Run, confirm failure**

```bash
php artisan test --compact tests/Feature/CharacterCardParserTest.php
```

Expected: FAIL â€” class `GametoraCharacterCardParser` not found.

- [ ] **Step 4: Extract the shared debut derivation**

In `GametoraCharacterParser.php`, replace `parse()`'s inline debut loop with a `self::debutForms()` call, and add the two helpers as `public static`. Keep the `ksort` so character records stay in `char_id` order and the existing tests' expectations do not move:

```php
    /**
     * The debut form of each trainee: the card with the earliest JP release, ties
     * broken by first seen. Both the character-level and the card-level read call
     * this, because the catalog's debut and a flagged debut form must be one fact
     * rather than two rules that can drift apart.
     *
     * @param  iterable<array<string, mixed>>  $cards
     * @return array<int, array<string, mixed>>  char_id => debut card
     */
    public static function debutForms(iterable $cards): array
    {
        $debutForms = [];

        foreach ($cards as $card) {
            if (! is_array($card)) {
                continue;
            }

            $name = isset($card['name_en']) ? trim((string) $card['name_en']) : '';
            $charId = $card['char_id'] ?? null;

            if ($name === '' || ! is_numeric($charId)) {
                continue;
            }

            $charId = (int) $charId;
            $releasedAt = isset($card['release']) ? (string) $card['release'] : self::UNKNOWN_DATE;
            $incumbent = $debutForms[$charId] ?? null;

            if ($incumbent === null || $releasedAt < $incumbent['release']) {
                $debutForms[$charId] = $card + ['release' => $releasedAt];
            }
        }

        ksort($debutForms);

        return $debutForms;
    }

    public static function dateOrNull(mixed $value): ?string
    {
        if (! is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($value)) !== 1) {
            return null;
        }

        return trim($value);
    }
```

Change `parse()`'s inline debut loop to the single `$debutForms = self::debutForms($cards);` call, and its two inline date parses to `self::dateOrNull(...)`. Leave `textOrNull` private: only this class needs it.

- [ ] **Step 5: Prove the extraction changed no behaviour**

```bash
php artisan test --compact tests/Feature/GametoraCharacterParserTest.php tests/Feature/GametoraAptitudeTest.php tests/Feature/FetchPipelineTest.php tests/Feature/CrossReferenceMatcherTest.php
```

Expected: all green. That is the refactor's bracket: identical output, one definition.

- [ ] **Step 6: Write the contract**

```php
<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Contracts;

/**
 * A source whose rows are cards rather than characters (PRD FR-A-6, ADR-0008).
 *
 * Separate from SourceParser for the same reason ScenarioSourceParser is: a card
 * carries no name to cross-reference against a trainee. Its identity is the
 * source's own card id, so it belongs outside the match and review stage.
 */
interface CharacterCardSourceParser
{
    /**
     * @return list<array{card_id: int, char_external_ref: string, title: string,
     *                          rarity: int, global_release_date: string, is_debut_form: bool}>
     */
    public function parse(string $body): array;
}
```

- [ ] **Step 7: Write the parser**

```php
<?php

declare(strict_types=1);

namespace App\Services\DataPipeline\Parsers;

use App\Enums\CardRarity;
use App\Services\DataPipeline\Contracts\CharacterCardSourceParser;
use JsonException;

/**
 * Reads the GameTora character-card dataset and emits one record per card that
 * exists on [Global] (PRD FR-A-6, ADR-0008).
 *
 * The same document GametoraCharacterParser reads, at card grain instead of
 * character grain: that parser keeps one card per trainee and drops the rest,
 * which is the behaviour ADR-0008 exists to stop being the only one. A card with
 * no release_en is [JP-Only] and is not app data at either grain.
 */
final class GametoraCharacterCardParser implements CharacterCardSourceParser
{
    /**
     * @return list<array{card_id: int, char_external_ref: string, title: string,
     *                          rarity: int, global_release_date: string, is_debut_form: bool}>
     */
    public function parse(string $body): array
    {
        try {
            $cards = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (! is_array($cards)) {
            return [];
        }

        // One definition of "debut", shared with the character catalog (Step 4).
        $debutCardIds = array_map(
            'intval',
            array_column(GametoraCharacterParser::debutForms($cards), 'card_id'),
        );

        $records = [];

        foreach ($cards as $card) {
            if (! is_array($card)) {
                continue;
            }

            $globalRelease = GametoraCharacterParser::dateOrNull($card['release_en'] ?? null);
            $cardId = $card['card_id'] ?? null;
            $charId = $card['char_id'] ?? null;
            $title = $card['title_en_gl'] ?? null;

            if ($globalRelease === null || ! is_numeric($cardId) || ! is_numeric($charId)) {
                continue;
            }

            if (! is_string($title) || trim($title) === '') {
                continue;
            }

            $rarity = CardRarity::tryFrom((int) ($card['rarity'] ?? 0));

            if ($rarity === null) {
                continue;
            }

            $records[] = [
                'card_id' => (int) $cardId,
                'char_external_ref' => 'gametora:char:'.(int) $charId,
                'title' => trim($title),
                'rarity' => $rarity->value,
                'global_release_date' => $globalRelease,
                // Strict, against the ints mapped above: a loose in_array here would
                // let a '100101' string match the int debut anywhere else in this file.
                'is_debut_form' => in_array((int) $cardId, $debutCardIds, true),
            ];
        }

        return $records;
    }
}
```

- [ ] **Step 8: Run green**

```bash
php artisan test --compact tests/Feature/CharacterCardParserTest.php tests/Feature/GametoraCharacterParserTest.php
```

Expected: all pass. If the count is not 7, print the records (`dump($records)`, then delete the dump) and compare against the fixture. Do not adjust the assertion to match the output.

- [ ] **Step 9: Gates and commit**

```bash
vendor/bin/pint --dirty --format agent && vendor/bin/phpstan analyse --no-progress --memory-limit=1G
git add app/Services/DataPipeline/Contracts/CharacterCardSourceParser.php \
        app/Services/DataPipeline/Parsers/GametoraCharacterCardParser.php \
        app/Services/DataPipeline/Parsers/GametoraCharacterParser.php \
        tests/Fixtures/gametora-character-cards.global.sample.json \
        tests/Feature/CharacterCardParserTest.php
git commit -m "feat(pipeline): a card-grain parser for the same GameTora document

The character parser keeps one card per trainee and drops the rest. This emits
every card with a Global release date and nothing else, and both parsers now
share one debut derivation so the catalog and a flagged debut form cannot
disagree. Fenomeno sits in the fixture with no Global date on either form: she
must yield nothing, which holds the Global-only rule down with a test."
```

---

### Task 7: Second declared source, cards branch, idempotent store

> ### AMENDMENT A1 â€” binding. This task's design changed when trunk's `e7b78a4` merged in.
>
> **Why.** Two things landed on `master` that this task must follow rather than restate.
>
> 1. **Routing is by parser interface, not by a config key.** `PipelineRunner::run()`'s interface-routing branch now reads `if (is_a($parserClass, RaceCatalogSourceParser::class, true))`, with the comment "The parser's own contract is what distinguishes the two kinds, so nothing here keys off a source name." `AGENTS.md` requires following established patterns, so **Step 6's `'records' => 'cards'` key and Step 7's `($sourceConfig['records'] ?? 'umamusume') === 'cards'` test are both superseded.** Do not add a `records` key to any source config, and do not document one in the config shape block.
> 2. **Reference rows carry inline provenance.** `ADR-0003` Amendment R3 requires `source_url`, `snapshot_path`, `fetched_at` and `source_timezone` on the reference row, and `scenario_races`, `scenario_slots` and `race_catalog_slots` carry all four and each has its own `is_manual`; `scenarios` predates the full set and carries `source_url`, `fetched_at` and `is_manual` only, which is `ADR-0004`'s own choice â€” stated in its "Alternatives rejected" entry on a generic polymorphic provenance table â€” rather than a gap. Task 4's migration is amended to match, so **`UpsertCharacterCard`'s per-record `DataSource::create` in Step 3 is superseded** â€” provenance is stamped on the card row, and `data_sources` keeps its existing meaning as the character-level table behind FR-A-4.
>
> **What replaces them.** The action changes shape, name and file to mirror `app/Actions/StoreRaceCatalogSlots.php` exactly: `app/Actions/StoreCharacterCards.php`, taking the whole list and returning counts.
>
> ```php
> final class StoreCharacterCards
> {
>     /**
>      * @param  list<array<string, mixed>>  $records
>      * @return array{created: int, updated: int, skipped: int}
>      */
>     public function handle(array $records, string $url, ?string $snapshotPath, ?string $timezone): array
> ```
>
> Inside it: resolve the trainee per record with `Umamusume::where('external_ref', $record['char_external_ref'])->first()`; `skipped++` and continue when she is absent; `skipped++` and continue when **the card** exists and `$card->is_manual` (FR-B-4 at its own grain, not the parent's); otherwise `updateOrCreate(['card_id' => ...], [...$record's columns, 'umamusume_id' => ..., ...$provenance])` where `$provenance` supplies `source_url`, `snapshot_path`, `fetched_at => now()`, `source_timezone`. It must **not** write `unconfirmed` â€” Task 8 owns that column, and a fetch may not clear a human verdict.
>
> In `PipelineRunner::run()`, add a second `is_a()` branch beside the race-catalog one, calling the store once with the full parsed list and returning `[...$stored, 'review' => 0]`. Then bump `catalog:version` when `$stored['created'] + $stored['updated'] > 0`, or the catalog page serves its cached id list unchanged. The existing branch is the model for all of this, including its comment about both `uma:fetch` and `uma:reparse` arriving through one method.
>
> In `config/uma.php`, the new source is `url`, `parser`, `delay_ms`, `timeout_s`, `timezone` â€” nothing else. Its comment keeps the cost disclosure (the same document fetched twice per full `uma:fetch`) and the ordering note (characters first so `external_ref` exists), and the statement that `AGENTS.md` escalation 5's robots question for this host stays formally unanswered as recorded above it.
>
> **Test changes A1 forces.** The five `UpsertCharacterCard` cases in Step 1 become `StoreCharacterCards` cases taking a list: the provenance test asserts the four **columns on the returned card**, not a `DataSource` row; the `is_manual` test sets `is_manual` on the card row itself and asserts its title survives while an unlocked sibling in the same call is updated; add one case asserting `unconfirmed => true` on an existing card stays true after a re-store. The runner case keeps its counts (`created 3 / skipped 5 / review 0`) **[WRONG â€” measured 4, see the Task 7 addendum at the foot of this plan and spec E-21]** and gains `MatchCandidate::count() === 0`.
>
> **Task 4 and 11 follow from A1.** `CharacterCardFactory` gains `'is_manual' => false`, `'source_url' => 'https://gametora.test/character-cards.json'`, and a `manual()` state. Task 11's card provenance sentence reads the card's own `source_url` and `fetched_at`, which is what the brief asked for anyway â€” "name the source and fetch date" per card, not per character. **[EXECUTED at 545e719 + fe9694b â€” three things in the steps below did not ship as written; read the Task 7 addendum at the foot of this plan before re-running this task.]**


The owner ruled the data arrives by live `uma:fetch` (spec Â§2), so the card dataset becomes a declared source. the header comment over `config/uma.php`'s `'sources'` array requires a config entry, one parser class, fixture tests (Task 6) and a robots note; `SourceFetcher` is the only outbound path and its allowlist is `config('uma.sources')`.

**Runner note, added after Task 6 shipped (`db8603c`).** This task touches two lines of `PipelineRunner.php`, not one. `run()`'s `@param` `$sourceConfig` shape types a source's parser as `class-string<SourceParser>`, and `run()`'s interface-routing branch routes only the race-catalog kind (`is_a($parserClass, RaceCatalogSourceParser::class, true)`); everything else falls through to the name-match loop. `CharacterCardSourceParser` is a **third** interface, so the cards branch has to be added **and** that `@param` shape widened â€” register a source pointing at `GametoraCharacterCardParser` without both, and it is typed as a `SourceParser` the class does not implement, then sent into a loop that reads `$record['name']`, a key card records never carry. The named symbols are the locators here; this plan's own hints-not-anchors bullet above says so.

**Files:**
- Modify: `config/uma.php` (the `'sources'` array, and its `Shape:` comment block)
- Modify: `app/Services/DataPipeline/PipelineRunner.php` â€” `PipelineRunner`'s class header, its promoted constructor dependencies (where `StoreRaceCatalogSlots` sits today and the card store joins), `run()`'s `@param` parser shape, and `run()`'s interface-routing branch. The `:20-33` range this line carried before Task 6 stopped short of the routing branch, which is what the runner note above names; both ranges stand here as that drift record, not as an instruction.
- Create: `app/Actions/UpsertCharacterCard.php`
- Test: `tests/Feature/CharacterCardFetchTest.php`

**Interfaces:**
- Consumes: `GametoraCharacterCardParser` records; `umamusume.external_ref`.
- Produces:
  - source key **`gametora-character-cards`**; config shape extended with an optional `'records' => 'cards'` (default `'umamusume'`).
  - `UpsertCharacterCard::handle(array $record, int $umamusumeId, string $sourceKey, string $url, ?string $snapshotPath = null, ?float $confidence = null, ?string $sourceTimezone = null): CharacterCard|null`, returning null when the trainee is absent or `is_manual`.
  - `PipelineRunner::run()` return shape unchanged: `array{updated:int, created:int, skipped:int, review:int}`.

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/CharacterCardFetchTest.php`:

```php
<?php

declare(strict_types=1);

use App\Actions\UpsertCharacterCard;
use App\Enums\CandidateStatus;
use App\Models\CharacterCard;
use App\Models\DataSource;
use App\Models\MatchCandidate;
use App\Models\Umamusume;
use App\Services\DataPipeline\Parsers\GametoraCharacterCardParser;
use App\Services\DataPipeline\PipelineRunner;

/**
 * @return array<int, array<string, mixed>> keyed by card_id
 */
function cardsBySourceId(): array
{
    $records = [];
    foreach ((new GametoraCharacterCardParser)->parse(cardSampleBody()) as $record) {
        $records[$record['card_id']] = $record;
    }

    return $records;
}

function cardSampleBody(): string
{
    return file_get_contents(base_path('tests/Fixtures/gametora-character-cards.global.sample.json')) ?: '';
}

it('attaches a card to the trainee its char ref names', function (): void {
    $umamusume = Umamusume::factory()->create([
        'name' => 'Special Week', 'slug' => 'special-week', 'external_ref' => 'gametora:char:1001',
    ]);

    $card = (new UpsertCharacterCard)->handle(
        record: cardsBySourceId()[100101],
        umamusumeId: $umamusume->id,
        sourceKey: 'gametora-character-cards',
        url: 'https://gametora.test/character-cards.json',
    );

    expect($card)->not->toBeNull()
        ->and($card->umamusume_id)->toBe($umamusume->id)
        ->and($card->title)->toBe('[Special Dreamer]')
        ->and($card->is_debut_form)->toBeTrue();
});

it('is idempotent: the same card id updates rather than duplicating', function (): void {
    $umamusume = Umamusume::factory()->create(['external_ref' => 'gametora:char:1001']);
    $record = cardsBySourceId()[100101];

    $first = (new UpsertCharacterCard)->handle(
        record: $record, umamusumeId: $umamusume->id,
        sourceKey: 'gametora-character-cards', url: 'https://gametora.test/a.json',
    );
    $second = (new UpsertCharacterCard)->handle(
        record: [...$record, 'title' => '[Special Dreamer Revised]'], umamusumeId: $umamusume->id,
        sourceKey: 'gametora-character-cards', url: 'https://gametora.test/b.json',
    );

    expect(CharacterCard::query()->count())->toBe(1)
        ->and($second->id)->toBe($first->id)
        ->and($second->title)->toBe('[Special Dreamer Revised]');
});

it('attaches provenance to every card row it writes', function (): void {
    $umamusume = Umamusume::factory()->create(['external_ref' => 'gametora:char:1001']);

    (new UpsertCharacterCard)->handle(
        record: cardsBySourceId()[100101], umamusumeId: $umamusume->id,
        sourceKey: 'gametora-character-cards', url: 'https://gametora.test/c.json',
        snapshotPath: 'snapshots/gametora-character-cards/2026-09-29/abc.json',
        sourceTimezone: 'Asia/Tokyo',
    );

    $source = DataSource::where('umamusume_id', $umamusume->id)
        ->where('source_key', 'gametora-character-cards')
        ->latest('fetched_at')->first();

    // FR-A-4, NFR-2 and SOURCE-OF-TRUTH Â§7.3: a fact without provenance is deleted,
    // not stored. The detail page reads its source line out of exactly this row.
    expect($source)->not->toBeNull()
        ->and($source->url)->toBe('https://gametora.test/c.json')
        ->and($source->snapshot_path)->toBe('snapshots/gametora-character-cards/2026-09-29/abc.json')
        ->and($source->source_timezone)->toBe('Asia/Tokyo');
});

it('does not touch a cross-check verdict already recorded for the card', function (): void {
    $umamusume = Umamusume::factory()->create(['external_ref' => 'gametora:char:1001']);
    CharacterCard::factory()->unconfirmed()->create([
        'umamusume_id' => $umamusume->id, 'card_id' => 100101,
    ]);

    (new UpsertCharacterCard)->handle(
        record: cardsBySourceId()[100101], umamusumeId: $umamusume->id,
        sourceKey: 'gametora-character-cards', url: 'https://gametora.test/d.json',
    );

    // A re-fetch reports the source's facts; it does not get to quietly clear the
    // human cross-check flag Task 8 owns.
    expect(CharacterCard::where('card_id', 100101)->value('unconfirmed'))->toBe(true);
});

it('refuses a card whose trainee is not in the catalog', function (): void {
    expect((new UpsertCharacterCard)->handle(
        record: cardsBySourceId()[100101], umamusumeId: 999999,
        sourceKey: 'gametora-character-cards', url: 'https://gametora.test/e.json',
    ))->toBeNull()
        ->and(CharacterCard::query()->count())->toBe(0);
});

it('never writes to a card whose trainee the Trainer owns by hand', function (): void {
    $trainee = Umamusume::factory()->manual()->create(['external_ref' => 'gametora:char:1001']);
    $card = CharacterCard::factory()->create([
        'umamusume_id' => $trainee->id, 'card_id' => 100101, 'title' => '[Typed By The Trainer]',
    ]);

    (new UpsertCharacterCard)->handle(
        record: cardsBySourceId()[100101], umamusumeId: $trainee->id,
        sourceKey: 'gametora-character-cards', url: 'https://gametora.test/f.json',
    );

    // FR-B-4: is_manual is immutable to the engine, and that lock has to reach the
    // card grain too or it leaks at the smaller row.
    expect($card->fresh()->title)->toBe('[Typed By The Trainer]')
        ->and(DataSource::where('umamusume_id', $trainee->id)->count())->toBe(0);
});

it('routes the card source through the runner without touching the review queue', function (): void {
    Umamusume::factory()->create([
        'name' => 'Special Week', 'slug' => 'special-week', 'external_ref' => 'gametora:char:1001',
    ]);

    $counts = app(PipelineRunner::class)->run(
        'gametora-character-cards',
        config('uma.sources.gametora-character-cards'),
        cardSampleBody(),
        null,
    );

    expect($counts['review'])->toBe(0)
        ->and($counts['created'])->toBe(3)                        // Special Week's three Global forms
        ->and($counts['skipped'])->toBe(5)                        // Gold Ship x2, Tokai Teio x2, no trainee rows yet
        ->and(MatchCandidate::query()->count())->toBe(0)
        ->and(CharacterCard::query()->where('unconfirmed', false)->count())->toBe(3);
});

it('records the declared source in config with its own parser', function (): void {
    expect(config('uma.sources.gametora-character-cards.parser'))
        ->toBe(GametoraCharacterCardParser::class)
        ->and(config('uma.sources.gametora-character-cards.records'))->toBe('cards');
});
```

`app(PipelineRunner::class)` rather than hand-building it: the container resolves all three constructor arguments, and a test that `new`s its own collaborators is asserting on its own wiring.

- [ ] **Step 2: Run, confirm failure**

```bash
php artisan test --compact tests/Feature/CharacterCardFetchTest.php
```

Expected: FAIL â€” `UpsertCharacterCard` not found, and `uma.sources.gametora-character-cards` null.

- [ ] **Step 3: Write the upsert action**

```php
<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\CharacterCard;
use App\Models\DataSource;
use App\Models\Umamusume;
use Illuminate\Support\Facades\DB;

/**
 * Upsert one fetched card by the source's own card id and attach its provenance
 * (PRD FR-A-6, FR-B-5). Idempotent by identity: a re-fetch updates the row it
 * already created instead of adding a second one.
 */
final class UpsertCharacterCard
{
    /**
     * @param  array{card_id: int, char_external_ref: string, title: string, rarity: int, global_release_date: string, is_debut_form: bool}  $record  a parser record
     * @return CharacterCard|null  null when the trainee is absent or locked by the Trainer
     */
    public function handle(
        array $record,
        int $umamusumeId,
        string $sourceKey,
        string $url,
        ?string $snapshotPath = null,
        ?float $confidence = null,
        ?string $sourceTimezone = null,
    ): ?CharacterCard {
        $trainee = Umamusume::find($umamusumeId);

        // A missing trainee means the roster has not cleared the review queue yet; a
        // manual one is FR-B-4's lock, enforced here rather than trusted upstream.
        if ($trainee === null || $trainee->is_manual) {
            return null;
        }

        return DB::transaction(function () use ($record, $trainee, $sourceKey, $url, $snapshotPath, $confidence, $sourceTimezone): CharacterCard {
            $card = CharacterCard::updateOrCreate(
                ['card_id' => $record['card_id']],
                [
                    'umamusume_id' => $trainee->id,
                    'title' => $record['title'],
                    'rarity' => $record['rarity'],
                    'global_release_date' => $record['global_release_date'],
                    'is_debut_form' => $record['is_debut_form'],
                ],
            );

            // Append-only, the way PromoteMatchedRecord already does it, so the detail
            // page's last-ten read is a real history and not one overwriting row.
            DataSource::create([
                'umamusume_id' => $trainee->id,
                'url' => $url,
                'source_key' => $sourceKey,
                'fetched_at' => now(),
                'snapshot_path' => $snapshotPath,
                'confidence' => $confidence,
                'source_timezone' => $sourceTimezone,
            ]);

            return $card;
        });
    }
}
```

`unconfirmed` is deliberately absent from the second argument: a fetch reports what the source says and must not clear a human cross-check verdict. Task 8 owns that column.

- [ ] **Step 4: Run the action tests green**

```bash
php artisan test --compact tests/Feature/CharacterCardFetchTest.php
```

Expected: the action-level tests pass. The runner and config tests still fail; that is Steps 5-7.

- [ ] **Step 5: Re-check the live manifest hash before registering the URL**

The hash is a cache-busting token that rotates when GameTora republishes, so a stale hash surfaces as a fetch failure rather than as silently old data (the cache-busting-hash note over the `gametora-characters` entry in `config/uma.php`). Task 1 Step 6 already proved the endpoint answers the app's own UA with `200 251242B`, so nothing here needs a browser UA or an `Accept` header â€” the `-A`/`-H` pair below is sent to match how the earlier research read this host, not because the request fails without it.

```bash
curl -sS --compressed \
  -A 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)' \
  -H 'Accept: application/json' \
  'https://gametora.com/data/manifests/umamusume.json' | grep -o '"character-cards":"[^"]*"'
```

Expected: `"character-cards":"679f7c2e"`, the hash already in the config. If it now differs, use the new hash in **both** source entries and record the date you read it.

- [ ] **Step 6: Register the source**

Add `use App\Services\DataPipeline\Parsers\GametoraCharacterCardParser;` beside the existing `GametoraCharacterParser` import in `config/uma.php`, extend the `'sources'` `Shape:` comment with `'records' => 'cards', // optional; defaults to umamusume rows`, then add a sibling entry after `'gametora-characters'`:

```php
        /*
         * The same document as gametora-characters, at card grain (FR-A-6, ADR-0008).
         *
         * Declared as its own source because the engine's contract is one parser per
         * source, and a trainee's rows and her cards have different identities: one
         * cross-references on name through the review queue, the other keys on the
         * source's own card id and never needs a guess. Keeping them apart is what
         * lets each stay independently lockable and idempotent under FR-B-5.
         *
         * Cost, stated: the same ~250 kB is fetched twice per full `uma:fetch`, one
         * second apart, against the host the owner approved on 2026-09-27. The robots
         * and rate-limit question AGENTS.md escalation 5 raises for this host stays
         * formally unanswered, exactly as the note above records it.
         *
         * Order matters: the character source is listed first, so its trainees exist
         * by the time the card source resolves char refs through external_ref.
         */
        'gametora-character-cards' => [
            'url' => 'https://gametora.com/data/umamusume/character-cards.679f7c2e.json',
            'parser' => GametoraCharacterCardParser::class,
            'delay_ms' => 1000,
            'timeout_s' => 15,
            'timezone' => 'Asia/Tokyo',
            'records' => 'cards',
        ],
```

- [ ] **Step 7: Add the cards branch to the runner**

In `app/Services/DataPipeline/PipelineRunner.php`: add `private readonly UpsertCharacterCard $cards,` to `PipelineRunner`'s promoted constructor; widen `run()`'s `@param` `$sourceConfig` shape to `array{url: string, parser: class-string, timezone?: string|null, records?: string}`; insert at the top of `run()`'s body:

```php
        if (($sourceConfig['records'] ?? 'umamusume') === 'cards') {
            return $this->runCards($sourceKey, $sourceConfig, $body, $snapshotPath);
        }
```

then add the method:

```php
    /**
     * Cards bypass the match stage on purpose (ADR-0008): a card's identity is the
     * source's own card id, and the trainee it belongs to is named by a char ref,
     * not inferred from a string. FR-B-3's review queue exists because names are
     * ambiguous between servers; nothing here is ambiguous, so nothing goes to review.
     *
     * @param  array{url: string, parser: class-string<CharacterCardSourceParser>, timezone?: string|null}  $sourceConfig
     * @return array{updated: int, created: int, skipped: int, review: int}
     */
    private function runCards(string $sourceKey, array $sourceConfig, string $body, ?string $snapshotPath): array
    {
        /** @var CharacterCardSourceParser $parser */
        $parser = app($sourceConfig['parser']);

        $counts = ['updated' => 0, 'created' => 0, 'skipped' => 0, 'review' => 0];

        foreach ($parser->parse($body) as $record) {
            $umamusumeId = Umamusume::where('external_ref', $record['char_external_ref'])->value('id');

            if ($umamusumeId === null) {
                $counts['skipped']++;

                continue;
            }

            // card_id is the source's id, not this table's primary key.
            $existing = CharacterCard::where('card_id', $record['card_id'])->exists();

            $card = $this->cards->handle(
                record: $record,
                umamusumeId: (int) $umamusumeId,
                sourceKey: $sourceKey,
                url: $sourceConfig['url'],
                snapshotPath: $snapshotPath,
                confidence: 1.0,
                sourceTimezone: $sourceConfig['timezone'] ?? null,
            );

            if ($card === null) {
                $counts['skipped']++;

                continue;
            }

            if ($existing) {
                $counts['updated']++;
            } else {
                $counts['created']++;
            }
        }

        // Without this bump the catalog page keeps serving its cached id list, because
        // CatalogController::cached() keys on catalog:version and nothing else notices
        // that the rows behind those ids gained a relation.
        if ($counts['created'] > 0 || $counts['updated'] > 0) {
            if (! Cache::add('catalog:version', 0, 3600)) {
                Cache::increment('catalog:version');
            }
        }

        return $counts;
    }
```

Add imports: `App\Actions\UpsertCharacterCard`, `App\Models\CharacterCard`, `App\Models\Umamusume`, `App\Services\DataPipeline\Contracts\CharacterCardSourceParser`.

- [ ] **Step 8: Run the pipeline family green**

```bash
php artisan test --compact tests/Feature/CharacterCardFetchTest.php tests/Feature/FetchPipelineTest.php tests/Feature/CatalogCacheRenderTest.php tests/Feature/ReviewQueueTest.php
```

Expected: all pass. The three KI-2 tests are the guard on the `cached()` path this branch's version bump feeds, and `ReviewQueueTest` proves the character path still routes to review untouched.

- [ ] **Step 9: Gates and commit**

```bash
vendor/bin/pint --dirty --format agent && vendor/bin/phpstan analyse --no-progress --memory-limit=1G
php artisan test --compact
git add config/uma.php app/Services/DataPipeline/PipelineRunner.php \
        app/Actions/UpsertCharacterCard.php tests/Feature/CharacterCardFetchTest.php
git commit -m "feat(pipeline): a declared card source and an idempotent card upsert

Cards key on the source's own card id and attach through external_ref, so they
bypass the match stage entirely: FR-B-3's review queue exists because names are
ambiguous between servers, and a card id is not. An is_manual trainee blocks her
cards too, and a re-fetch cannot clear a cross-check verdict it did not write."
```

---

### Task 8: Tier A cross-check of all 107 cards

`SOURCE-OF-TRUTH.md` Â§5:152 â€” "A Tier B dataset (GameTora) needs A- or S-tier confirmation before a claim becomes app data." The owner ruled **all 107**, not a spot-check. This task produces the evidence file Task 9 reads when it sets `character_cards.unconfirmed`.

Owner assumption, ruled 2026-09-29, and it is definitional rather than open: `unconfirmed` answers "do two independent sources attest that THIS card exists, with this title, date and rarity?" That is a property of the card, keyed on `card_id`, and never of the trainee it happens to be attached to. This task's cross-check file lists cards by `card_id` and metadata and records no trainee association, so a card that re-parents - the behaviour Task 7's addendum pins - keeps its verdict, because the verdict was never about the association. Do not invalidate `unconfirmed` on an ownership change, and do not key a verdict on `(card_id, umamusume_id)`: that discards correct human work every time the source fixes its own char-ref mapping, which is precisely when nobody wants to re-cross-check.

**Files:**
- Create: `docs/data/2026-09-29-global-roster-crosscheck.md`
- Create: `tools/roster-crosscheck.php`
- Create (untracked, scratch): `research-scratch/data/html/*-2026-09-29.html`, `research-scratch/data/json/tier-a-rows.json`

**Interfaces:**
- Produces: one verdict per `card_id` â€” `two-source-confirmed`, `single-source` or `conflict` â€” plus the two URLs and the read date. Task 9 Step 8 turns `single-source` and `conflict` into `unconfirmed = true`.

- [ ] **Step 1: Save the Tier B body and the two Tier A bodies**

`research-scratch/` is gitignored, so the worktree Task 1 created has no copy of the export
this comparison needs. Fetch it here: one authorized read of the same host, ahead of Task 9's
`uma:fetch`, which is the path the owner ruled on in spec Â§2.

Quote all three URLs: the MediaWiki page name contains a colon, which breaks naive link handling.

```bash
mkdir -p research-scratch/data/json research-scratch/data/html
curl -sS --compressed \
  -A 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)' -H 'Accept: application/json' \
  'https://gametora.com/data/umamusume/character-cards.e9e9ee6d.json' \
  -o research-scratch/data/json/character-cards.json
curl -sS --compressed -A 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)' \
  'https://umamusu.wiki/Game:List_of_Trainees' \
  -o research-scratch/data/html/umamusu-list-of-trainees-2026-09-29.html
curl -sS --compressed -A 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)' \
  'https://game8.co/games/Umamusume-Pretty-Derby/archives/535926' \
  -o research-scratch/data/html/game8-characters-2026-09-29.html
wc -c research-scratch/data/json/character-cards.json research-scratch/data/html/*-2026-09-29.html
```

Expected: the JSON around 251 kB with 268 rows, and both HTML files far above 10 kB. Verify the export rather than trusting the size:

```bash
php -r '$r=json_decode(file_get_contents("research-scratch/data/json/character-cards.json"),true); printf("rows=%d global=%d trainees=%d\n",count($r),count(array_filter($r,fn($c)=>is_string($c["release_en"]??null))),count(array_unique(array_column(array_filter($r,fn($c)=>is_string($c["release_en"]??null)),"char_id"))));'
```

Expected: `rows=268 global=107 trainees=68`. A different number is not a plan bug, it is the roster having moved since 2026-09-29: stop and re-report the counts before anything downstream quotes 68 and 107.

The wikis render server-side, so a plain fetch suffices â€” do not conclude "no data" from a short body without looking. Keep all three out of any path the app reads: only the derived table lands in the repo.

If the JSON 403s or either page 403s, returns a stub, or will not parse: **stop and report it.** A missing Tier A witness changes the deliverable rather than quietly becoming 107 `single-source` rows, and `AGENTS.md` escalation 5 makes a source question the owner's.

- [ ] **Step 2: Extract the Tier A rows**

From each page, per trainee and per card: English name, Japanese name, rarity, Global release date, card title. Save the result as `research-scratch/data/json/tier-a-rows.json`, one object per row, and record for each value **which page and which row number it came from**. Without the pointer the table is unsources, which is the failure mode `docs/design-research/MECHANICS-TRANSLATION-TRIAGE.md` exists to catch.

Note before you start: `umamusu.wiki` is Tier A but is not infallible â€” a prior pass found it misassigning three Grand Masters bonus entries against two JP guides that agreed. Where a Tier A page and the client string disagree, rule 1 in Step 4 decides, and the disagreement gets recorded.

- [ ] **Step 3: Write the comparison script**

`tools/roster-crosscheck.php` â€” plain PHP, no framework, run as `php tools/roster-crosscheck.php > docs/data/roster-crosscheck-table.md`. Inputs: the GameTora body (Task 9's snapshot, or `research-scratch/data/json/character-cards.json`) and `research-scratch/data/json/tier-a-rows.json`. It prints one Markdown row per card:

```
| card_id | GameTora title | Game8 title | umamusu.wiki title | GameTora date | Tier A date | rarity | verdict |
```

Classify with this rule and no other judgement:

```php
$verdict = match (true) {
    $game8Hit !== null && $wikiHit !== null => 'two-source-confirmed',
    $game8Hit !== null || $wikiHit !== null => 'single-source',
    default => 'unwitnessed',
};
// A date or rarity that disagrees with either witness downgrades the row to
// 'conflict' regardless of how many pages carry the card at all.
```

Compare titles on the bracket-stripped, case-folded form and say so in the file's method note: a wiki that prints `Special Dreamer` and GameTora that prints `[Special Dreamer]` are making the same claim, and comparing raw strings would report 107 false conflicts. Keep the script a dumb formatter: it compares strings, classifies, prints. It fetches nothing and writes nothing to the database.

- [ ] **Step 4: Classify every disagreement in writing before deciding anything**

Expect real conflicts. Each becomes a row in the file's conflict-log section, never a silent pick. The rules, in order:

1. **Title conflicts:** GameTora's `title_en_gl` is the `[Global]` client string, so it wins and the other spelling is recorded as differing. Root `CONSTRAINTS.md` C-4's verbatim-names bullet forbids editing the data to satisfy anything. Expect several here: the Global string is often a genuine rename, not a translation (`Run! Fun! Watergun!` becomes `[RUN! RUIN! LAUNCHER!]`, `Supreme Commander of the Rising Sun` becomes `[Ruler of Japan]`).
2. **Global release date conflicts:** the card is `conflict` and gets `unconfirmed = true`. A date is not a name; the client string does not arbitrate it.
3. **English name conflicts:** record both spellings, keep the client string, and add the losing form as an `umamusume_aliases` row in Task 9 so the other spelling stays findable. That is what FR-A-2 aliases exist for.
4. **Rarity conflicts:** `conflict` likewise â€” rarity drives the H1's max-rarity claim, so an uncrossed value is visible wrong data.
5. Never resolve a conflict by preference, and never by picking whichever source agrees with the brief.

- [ ] **Step 5: Write the evidence file**

`docs/data/2026-09-29-global-roster-crosscheck.md`, containing:

- the two source URLs and the date each was read;
- the method note (bracket-stripped case-folded comparison; the three verdict definitions; the conflict rules above, numbered);
- the full 107-row table from Step 3;
- counts by verdict, with the arithmetic visible (`two-source-confirmed + single-source + conflict + unwitnessed = 107`);
- the conflict log;
- a closing line that GameTora is Tier B and these two are Tier A per `SOURCE-OF-TRUTH.md` Â§5:142-148, and that the file is a **dated observation**, matching the repo's dated-snapshot policy â€” a later reader needs to know what day this was true, because banner cycles move.

- [ ] **Step 6: Sanity-check the totals before committing**

```bash
grep -c '^| ' docs/data/roster-crosscheck-table.md
grep -c 'two-source-confirmed' docs/data/2026-09-29-global-roster-crosscheck.md
```

Expected: the row count equals 107 (plus one header row per table, so adjust by the headers you wrote). If it does not, the fixture or the extraction dropped a card; find it before continuing rather than shipping a short table.

- [ ] **Step 7: Commit**

```bash
git add docs/data/2026-09-29-global-roster-crosscheck.md docs/data/roster-crosscheck-table.md tools/roster-crosscheck.php
git status --short   # research-scratch/ must not appear: it is gitignored
git commit -m "docs(data): two-source cross-check of all 107 Global cards

SOURCE-OF-TRUTH 5:152 will not let a Tier B field become app data on its own
say-so. Every card, every name, date, rarity and title, against umamusu.wiki and
Game8, with each disagreement kept as a row rather than resolved by preference."
```

---

### Task 9: Fetch live, promote through the existing review surface, verify counts

The load-bearing finding, restated because it is the premise of the owner's ruling: `CrossReferenceMatcher::match()` returns `None` for any name not already stored, and `PipelineRunner::run()`'s `MatchCandidate::create` branch routes `None` to `match_candidates`. **A live fetch creates zero trainee rows.** `PRD.md` FR-B-3 is deliberate about that. So the roster leaves the queue the way `PRD.md` US-5 says a Trainer resolves it: `ResolveMatchCandidate` with status `Confirmed`, which reaches `PromoteMatchedRecord::handle(existing: null)` and creates the row with its `data_sources` provenance. No new promotion code, and no loosening of FR-B-3.

**Files:**
- Modify: `docs/design-research/verification/slice-11-2026-09-29.md` (the run record)
- No application code changes at all in this task.

**Interfaces:**
- Consumes: `uma:fetch`, `MatchCandidate`, `ResolveMatchCandidate`, `UmamusumeAlias`, `StoreCharacterCards`, Task 8's verdict table.
- Produces: 68 trainees carrying `external_ref` and `name_ja`, 107 cards, and the two count numbers every later task and the final report quote.

- [ ] **Step 1: Build the worktree's own database â€” the shared dev file is unreachable from here**

`DB_DATABASE=database/database.sqlite` in `.env` is a **relative** path, and `database/*.sqlite` is gitignored, so `git worktree add` did not bring the dev database across: this worktree has no `database/database.sqlite` at all. That is the good news, and it retires two worries at once.

- The main tree's `database/database.sqlite` is **not** the file this run writes. Nothing the peer session does to their catalog can be affected by these 68 rows, and no backup of their file is needed or wanted.
- `migrate:fresh --seed` here is not the destructive act `GATE-REGISTRY.md`'s "Global gates" table warns about, because the file it destroys is this worktree's own, created minutes ago. C-5's proof and Task 9's population collapse into one step.

```bash
ls database/database.sqlite 2>/dev/null || echo "absent, as expected for a fresh worktree"
php artisan migrate --seed --no-interaction
php artisan tinker --execute 'echo Umamusume::count()." trainees, ".App\Models\Skill::count()." skills, ".App\Models\Scenario::count()." scenarios";'
```

Expected: the file is absent before the command; then `2 trainees, 10 skills, ...` â€” the two seeded illustrative rows `UmamusumeSeeder` provides, which is the precondition Step 2's arithmetic depends on. If the count is anything other than `2`, stop and work out why before fetching: every number in Steps 2, 4 and 7 is derived from exactly two rows existing.

**No `touch` is needed, and that is measured, not assumed.** Laravel `v13.32.0` creates a missing SQLite file on first `migrate` â€” probed 2026-09-29 by pointing a real `migrate` at an absent path and watching it succeed:

```bash
rm -f database/probe-absent.sqlite
DB_DATABASE="$PWD/database/probe-absent.sqlite" php artisan migrate --force   # INFO Running migrationsâ€¦ DONE
ls -l database/probe-absent.sqlite                                            # 225280 bytes, created for us
rm -f database/probe-absent.sqlite
```

Older Laravel errored with "database file does not exist", and a `touch` here is harmless â€” but do not add one on the strength of that older behaviour without re-running the probe, and do not let a reviewer treat its absence as a defect. If a future framework bump makes the connector strict again, this probe is the thing that fails first and the one-line fix is obvious from its output.

If the file unexpectedly **does** exist (someone pointed `DB_DATABASE` at an absolute path, or copied a database in), then the isolation assumption above is false. In that case stop and confirm which file it is before writing 68 rows into it, because it may be a database another session reads.

- [ ] **Step 2: Fetch the trainee level**

```bash
php artisan uma:fetch gametora-characters
```

Expected, in shape: `gametora-characters: 2 updated, 0 created, 0 skipped (manual), 133 to review.`

Read that number carefully, because it is where the plan was corrected after measurement. `GametoraCharacterParser` emits **one record per `char_id` in the whole export**, and derives `release_status` from whether that debut card carries a `release_en` â€” it does not filter to Global. Measured against the live body on 2026-09-29: **135 records, 68 `GlobalReleased`, 67 `JapanOnly`.** The two seeded trainees match on `match_key` and go Exact, so 133 names are new, therefore `None`, therefore review.

The parser is right and must stay that way. `PRD.md` US-2 is P0 and its acceptance text is "new JP releases appear with a **JapanOnly** or GlobalAnnounced flag instead of silently missing" â€” a Global-only guard in the parser would delete exactly the lookahead US-2 promises, and `docs/scenarios/08` plus the `[JP-Only]` quarantine rules depend on that distinction being visible rather than absent. The filter therefore belongs at promotion, in Step 4, which is the step that already represents a human verdict.

If you see `68 to review` instead of 133, the parser gained a filter and US-2 needs re-checking before anything else runs. If you see `68 created`, `CrossReferenceMatcher` changed underneath this plan â€” stop.

- [ ] **Step 3: Confirm the Japanese name lands on live data this time**

```bash
php artisan tinker --execute 'echo Umamusume::whereNotNull("name_ja")->count()." of ".Umamusume::count();'
```

Expected: `2 of 2`. Only the two Exact-matched trainees exist at this point; the other 66 arrive in Step 4. This is the early probe that the key fix works on live data at all, on the two rows the fetch could already reach. The full measurement is Step 7's `68 of 68`, which is the number that matters for US-1, and a `0 of 2` here means the parser is still reading `name_ja`.

Do not read `2 of 2` as success for the roster and move on: if Step 4 then produces trainees with a null `name_ja`, the fix works only on the Exact path and something else is dropping the value between `parse()` and `PromoteMatchedRecord::handle()`.

- [ ] **Step 4: Confirm the 66 Global candidates, and leave the 67 JP-only ones queued**

```bash
php artisan tinker --execute '
$action = app(App\Actions\ResolveMatchCandidate::class);
$pending = MatchCandidate::where("status", App\Enums\CandidateStatus::Pending->value)->get();

// The verdict is Global-only, which is the request constraint applied at the human step
// rather than at the parser, where it would delete the US-2 JapanOnly lookahead.
// MatchCandidate casts `payload` to an array, so release_status is readable here.
$global = $pending->filter(
    fn (MatchCandidate $c): bool => ($c->payload["release_status"] ?? null) === "GlobalReleased"
);

$confirmed = 0;
$failed = [];
foreach ($global as $candidate) {
    try {
        $action->handle($candidate, ["status" => "Confirmed"]);
        $confirmed++;
    } catch (Throwable $e) {
        // One bad candidate must not abort the pass and leave a half-built roster that
        // the ledger then records as success. handle() wraps its own transaction, so a
        // failure here rolls back that candidate alone and the rest are unaffected.
        $failed[] = $candidate->id . ":" . $e->getMessage();
    }
}

printf("pending %d | global %d | confirmed %d | FAILED %d | trainees %d | still queued %d\n",
    $pending->count(), $global->count(), $confirmed, count($failed),
    Umamusume::count(), MatchCandidate::where("status", "Pending")->count());
if ($failed !== []) { echo implode("\n", $failed) . "\n"; }
'
```

Expected: `pending 133 | global 66 | confirmed 66 | FAILED 0 | trainees 68 | still queued 67`.

**A nonzero `FAILED` is not a pass.** Read the printed ids, fix or rule on each, then re-run only if the count of `trainees` is short of 68 â€” and say so in the record rather than reporting Task 9 green on a partial catalog.

- [ ] **Step 4b: Prove the re-run claim instead of trusting it**

The plan depends on this loop being safely re-runnable. Verified in the action, not assumed: `ResolveMatchCandidate::confirm()` (`app/Actions/ResolveMatchCandidate.php`) passes `$candidate->suggestedUmamusume` as `existing:`, which is **null** for a `None`-tier candidate, and `PromoteMatchedRecord::handle()` with `existing === null` **creates a new row** (`PromoteMatchedRecord::handle()`'s `$existing === null` branch). So confirming the same candidate twice creates two trainees, the second slugged `name-2` by `uniqueSlug()`.

The loop is safe for exactly one reason: it selects `status = Pending`, and `handle()` writes `Confirmed` before returning, so a second pass finds nothing already resolved. That is a property of the query, not of the action, and it needs a test before the plan leans on it. Add to `tests/Feature/ReviewQueueTest.php`:

```php
it('re-running the pending-only promotion pass cannot double-create a trainee', function (): void {
    $body = file_get_contents(base_path('tests/Fixtures/gametora-character-cards.sample.json'));
    $record = (new App\Services\DataPipeline\Parsers\GametoraCharacterParser)->parse($body)[0];

    $candidate = App\Models\MatchCandidate::create([
        'source_key' => 'gametora-characters',
        'proposed_name' => $record['name'],
        'proposed_match_key' => 'promotionidempotencycheck',
        'match_tier' => App\Enums\MatchTier::None->value,
        'status' => App\Enums\CandidateStatus::Pending->value,
        'payload' => [...$record, 'url' => 'https://gametora.test/x.json'],
        'created_by_fetch_at' => now(),
    ]);

    $action = app(App\Actions\ResolveMatchCandidate::class);
    $pending = fn () => App\Models\MatchCandidate::where('status', App\Enums\CandidateStatus::Pending->value)->get();

    $first = $pending();
    $first->each(fn ($c) => $action->handle($c, ['status' => 'Confirmed']));
    $second = $pending();

    // The guard is the query: a resolved candidate leaves the Pending set, so the
    // second pass selects nothing and the trainee count cannot move.
    expect($second)->toBeEmpty()
        ->and(App\Models\Umamusume::query()->count())->toBe(1);
});

it('double-resolving the same candidate directly does create a second trainee', function (): void {
    $record = ['name' => 'Double Resolve One', 'name_ja' => null, 'release_status' => 'GlobalReleased'];
    $candidate = App\Models\MatchCandidate::create([
        'source_key' => 'gametora-characters',
        'proposed_name' => 'Double Resolve One',
        'match_tier' => App\Enums\MatchTier::None->value,
        'status' => App\Enums\CandidateStatus::Pending->value,
        'payload' => [...$record, 'url' => 'https://gametora.test/y.json'],
        'created_by_fetch_at' => now(),
    ]);

    $action = app(App\Actions\ResolveMatchCandidate::class);
    $action->handle($candidate, ['status' => 'Confirmed']);
    $action->refresh()->handle($candidate, ['status' => 'Confirmed']);

    // NOT a passing assertion that this is good. It pins a real hazard: nothing in
    // ResolveMatchCandidate refuses a verdict on an already-resolved candidate, so a
    // replayed POST to /review/{candidate} -- a back-button double-submit -- silently
    // forks a catalog row. File it as the next free KI at run time (KI-25 expected: trunk holds 21 and 22,
    // this branch's parser defect is 23, and KI-24 is the fresh-clone gap), with this test as the reproducer.
    expect(App\Models\Umamusume::query()->count())->toBe(2);
});

it('leaves a candidate Pending when its verdict throws, so a re-run retries it', function (): void {
    $candidate = App\Models\MatchCandidate::create([
        'source_key' => 'gametora-characters',
        'proposed_name' => 'Malformed Payload One',
        'match_tier' => App\Enums\MatchTier::None->value,
        'status' => App\Enums\CandidateStatus::Pending->value,
        // The deterministic failure, verified against the real class 2026-09-29:
        // NameNormalizer::normalize() declares `string $name` under declare(strict_types=1),
        // so an array raises TypeError rather than coercing to "Array" and storing junk.
        'payload' => ['name' => ['not', 'a', 'string'], 'release_status' => 'GlobalReleased'],
        'created_by_fetch_at' => now(),
    ]);

    $threw = false;

    try {
        app(App\Actions\ResolveMatchCandidate::class)->handle($candidate, ['status' => 'Confirmed']);
    } catch (TypeError) {
        $threw = true;
    }

    // handle() wraps its own transaction, so a throw rolls back that candidate alone and
    // its status write goes with it: it stays Pending, and a re-run of the pass selects it
    // again. This is the property that makes "fix it and re-run Step 4" safe advice. The
    // loop itself is not copied into this test on purpose -- duplicating it would pin the
    // copy, not the behaviour the copy depends on.
    expect($threw)->toBeTrue()
        ->and($candidate->fresh()->status)->toBe(App\Enums\CandidateStatus::Pending)
        ->and(App\Models\Umamusume::query()->count())->toBe(0)
        ->and(App\Models\DataSource::query()->count())->toBe(0);
});
```

Run all three, and file **the next free KI** in `KNOWN-ISSUES.md` against the second test, re-derived by the same run-time recipe Task 2 Step 7 uses (KI-25 is the expected number now that trunk holds KI-21 and KI-22 and this branch holds KI-23 and KI-24): nothing in `ResolveMatchCandidate` refuses a verdict on an already-resolved candidate, so a replayed POST to `review.resolve` â€” a back-button double-submit â€” silently forks a catalog row. The review UI hides resolved candidates, so it is not normally reachable, but this plan is the first thing to drive the action at volume. The fix is a one-line guard (no-op or reject when `status !== Pending`) and belongs to a separate slice, not to Task 9; record the deferral here rather than expanding this task's blast radius mid-run.

- [ ] **Step 4c: Confirm no duplicates actually landed**

```bash
php artisan tinker --execute '
$dupes = App\Models\Umamusume::selectRaw("name, count(*) c")->groupBy("name")->havingRaw("count(*) > 1")->pluck("c", "name");
echo $dupes->isEmpty() ? "no duplicated trainee names\n" : $dupes."\n";
echo "slugs ending in a collision suffix: " . App\Models\Umamusume::where("slug", "like", "%-[0-9]")->count() . "\n";
'
```

Expected: `no duplicated trainee names` and `0` collision-suffixed slugs. Either nonzero means the promotion pass ran twice over overlapping rows; stop, find which names, and rule on it before Step 5 attaches cards to the wrong twin.

On the create path this step exercises for the first time at volume: it generates its own slug. `PromoteMatchedRecord::handle()`'s create array sets `'slug' => $this->uniqueSlug($record['name'])`, and `uniqueSlug()` runs `Str::slug()`, substitutes `'umamusume'` when the result is empty, and appends `-2`, `-3`â€¦ while a row already holds that slug. So 66 inserts cannot collide on the `string unique` column, and nothing here needs a slug added to the parser's record shape. Checked before this task was written rather than discovered at `:1` on the 30th insert.

The 67 left `Pending` are the trainees who exist on JP and not yet on Global. They stay in the review queue rather than becoming catalog rows, which is the outcome `docs/requests/2026-09-29-catalog-roster-and-trainee-selector.md` requires ("Trainees with zero Global cards do not appear") and US-2 permits (they are not silently missing; they are sitting in `/review` awaiting a verdict). No `is_manual` row is touched: the seeded two never entered the queue, and `PromoteMatchedRecord::handle()`'s `is_manual` guard enforces the lock anyway.

`tinker` is normally the wrong tool for creating models. Here it is the right one, and the reason belongs in the slice record: this drives the **existing** `ResolveMatchCandidate` and `PromoteMatchedRecord` actions, so provenance, the `is_manual` lock and the transaction all run exactly as they do for a Trainer clicking through `/review`. A new bulk-promote command would be an uncited class, and it would put a button on the guarantee that the engine never guesses. Note that even this loop is not that button: it applies the request's Global-only constraint as the Trainer's stated criterion, and it leaves 67 candidates for a verdict it was not given authority over.

- [ ] **Step 5: Seed the Japanese forms as aliases**

```bash
php artisan tinker --execute '
$made = 0;
Umamusume::whereNotNull("name_ja")->get()->each(function ($u) use (&$made) {
    $alias = UmamusumeAlias::firstOrCreate([
        'umamusume_id' => $u->id, 'alias' => $u->name_ja, 'language' => 'Japanese',
    ]);
    $made += $alias->wasRecentlyCreated ? 1 : 0;
});
echo "added {$made}, total ".UmamusumeAlias::where('language', 'Japanese')->count();
'
```

Expected: 68 Japanese aliases. Without them the catalog page's **server-side** search cannot find `ã‚¹ãƒšã‚·ãƒ£ãƒ«ã‚¦ã‚£ãƒ¼ã‚¯`, because `match_key` is built from the English name (`specialweek`) and the search `when()` clause in `CatalogController::index()` ORs only `match_key` and `alias`. The selector finds katakana through its own payload; this closes the same capability on the server side, which is what FR-A-3 and US-1 describe. `AliasLanguage::Japanese` is the enum behind the `'Japanese'` string â€” use `AliasLanguage::Japanese->value` if the raw string reads as an untyped claim.

- [ ] **Step 6: Fetch the card level**

```bash
php artisan uma:fetch gametora-character-cards
php artisan tinker --execute 'echo CharacterCard::count();'
```

Expected: `gametora-character-cards: 0 updated, 107 created, 0 skipped (manual or unresolved), 0 to review.` and `107`. Any nonzero `skipped` means a `char_external_ref` failed to resolve; find which cards before continuing:

```bash
php artisan tinker --execute '
$missing = array_values(array_filter(
    app(App\Services\DataPipeline\Parsers\GametoraCharacterCardParser::class)
        ->parse(file_get_contents("https://gametora.com/data/umamusume/character-cards.e9e9ee6d.json") ?: "[]"),
    fn ($r) => Umamusume::where("external_ref", $r["char_external_ref"])->doesntExist(),
));
echo count($missing)." unresolved char refs";
'
```

Prefer the saved snapshot over a re-downloaded URL for that check â€” `SourceFetcher` wrote it under `storage/app/private/snapshots/gametora-character-cards/<date>/<hash>.html`, and reading the snapshot is what `uma:reparse` exists for. Fetching a second copy inside a verification step makes the step depend on the network.

- [ ] **Step 7: Run the integrity checks the spec asks for, and paste the output**

```bash
php artisan tinker --execute '
echo "trainees total:            ".Umamusume::count()."\n";
echo "trainees with >=1 card:    ".Umamusume::has("cards")->count()."\n";
echo "trainees, zero cards:      ".Umamusume::doesntHave("cards")->count()."\n";
echo "GlobalReleased trainees:   ".Umamusume::where("release_status","GlobalReleased")->count()."\n";
echo "JapanOnly trainees stored: ".Umamusume::where("release_status","JapanOnly")->count()."\n";
echo "trainees with a JP name:   ".Umamusume::whereNotNull("name_ja")->count()."\n";
echo "candidates still pending:  ".MatchCandidate::where("status","Pending")->count()."\n";
echo "cards total:               ".CharacterCard::count()."\n";
echo "cards, unique card_id:     ".CharacterCard::distinct("card_id")->count()."\n";
echo "debut flags set:           ".CharacterCard::where("is_debut_form",true)->count()."\n";
echo "trainees with exactly one debut: ".Umamusume::has("cards")->get()
    ->fn($u) => $u->cards->where("is_debut_form", true)->count() === 1)->count()."\n";
echo "cards dated after today:   ".CharacterCard::where("global_release_date",">",now()->toDateString())->count()."\n";
'
```

Fill and paste into the slice record:

| Check | Required | Observed |
|---|---|---|
| Trainees with at least one card | 68 | |
| Trainees with zero cards | 0 | |
| Cards stored | 107 | |
| Distinct `card_id` | 107 (no duplicates) | |
| Debut flags set, in total | 68 (one per trainee) | |
| Trainees with exactly one debut | 68 | |
| Cards with no Global date | 0 (structural: the column is NOT NULL) | |
| Cards dated after today | 0 | |
| `JapanOnly` trainees stored | 0 â€” Step 4 confirms only the Global payloads, so a JP-only trainee is a pending candidate, not a catalog row | |
| Trainees carrying a `name_ja` | 68 â€” the US-1 requirement, measured on live data rather than a fixture | |
| Candidates still `Pending` | 67 â€” the JP-only lookahead US-2 wants visible in `/review` | |

- [ ] **Step 7b: State what the review queue became, and file the follow-up**

```bash
php artisan tinker --execute '
printf("pending %d | resolved %d | rejected %d\n",
    App\Models\MatchCandidate::where("status","Pending")->count(),
    App\Models\MatchCandidate::where("status","Confirmed")->count(),
    App\Models\MatchCandidate::where("status","Rejected")->count());
'
```

Expected: `pending 67 | resolved 66 | rejected 0`.

Write that number into the record, because it changes what `/review` is. `ReviewController::index()` lists Pending only, `latest('created_by_fetch_at')`, `paginate(25)`, and `review/index.blade.php` offers no filter by tier, source or release status and no aggregate count. After this task the queue is **three pages of 67 JP-only candidates with zero actionable ones** â€” correct by US-2's letter (the lookahead is visible rather than silently missing, which is exactly what that story demands) and the least useful surface in the tool by practice.

File it as **the next free KI after that one** (KI-26 expected by the same count), describing the state rather than prescribing a fix: a queue filter or a "JP-only lookahead" grouping is a scope question with no PRD citation, so it goes to the owner, not into this slice. Record it in the report's "Not built" section too. Do not add a filter here on the way past â€” it is a new surface, its own tests, and its own citation, and Task 9's contract is that it changes no application code.

Say plainly in the KI that **these 67 are not lost**. They stay addressable in two directions: they remain in `/review` for a verdict at any time, and a Trainer who ever confirms one gets a `JapanOnly` catalog row that reads correctly under `status=all` and `status=JapanOnly` â€” the shape Task 10's `defaults the release status filter to released on Global` test already pins, including the `status=all` half. The KI is about the queue's signal-to-noise, not about data that disappears.

- [ ] **Step 8: Prove no JP-only card leaked**

```bash
php artisan tinker --execute '
$body = file_get_contents(storage_path("app/private/snapshots/gametora-character-cards/".now()->toDateString()."/PLACEHOLDER"));
$rows = json_decode($body, true);
$withDate = array_filter($rows, fn ($r) => is_string($r["release_en"] ?? null) && preg_match("/^\\d{4}-\\d{2}-\\d{2}$/", $r["release_en"]) === 1);
echo "export rows with a Global date: ".count($withDate)." | stored cards: ".CharacterCard::count()."\n";
$stored = CharacterCard::pluck("card_id")->all();
$jpOnly = array_column(array_udiff($rows, $withDate, fn ($r) => $r["card_id"]), "card_id");
echo "JP-only card ids present in the table: ".count(array_intersect($jpOnly, $stored))."\n";
'
```

Expected: the two counts equal (107 = 107) and the leak count is `0`. Resolve `PLACEHOLDER` to the real snapshot filename with `ls storage/app/private/snapshots/gametora-character-cards/` first; `uma:reparse` reads the same path, so this is the pipeline's own evidence rather than a fresh download. A nonzero leak count is the single most important failure in this plan: it means the Global-only gate is not where everyone thinks it is.

- [ ] **Step 9: Apply Task 8's verdicts**

```bash
php artisan tinker --execute '
$notConfirmed = [/* card_id values whose verdict is single-source or conflict, from the table */];
CharacterCard::query()->update(["unconfirmed" => false]);
if ($notConfirmed !== []) { CharacterCard::whereIn("card_id", $notConfirmed)->update(["unconfirmed" => true]); }
echo "flagged ".CharacterCard::where("unconfirmed", true)->count()." of ".CharacterCard::count();
'
```

Expected: `flagged N of 107`. Put the list of `N` card ids in the slice record alongside the verdict table that produced it. If N is `0`, state which second source confirmed each row â€” "nothing unverified" is a claim needing evidence like any other, and it is the kind of number that gets cited later.

- [ ] **Step 10: Write the run record**

In `docs/design-research/verification/slice-11-2026-09-29.md`: the `git branch --show-current` and `git status --porcelain` opening snapshot from Task 1, every command above with its **real output pasted**, the filled count table, the FR-B-3/US-5 reasoning from Step 4, and `PLAN.md`'s "Slice Exit Criteria" push-verification line (`git ls-remote origin master` equals the sha pushed) once pushed.

- [ ] **Step 11: Commit the record**

```bash
git add docs/design-research/verification/slice-11-2026-09-29.md
git status --short   # nothing under database/ or storage/ is committable; both are ignored
git commit -m "data(catalog): the Global roster lands, 68 trainees and 107 cards

Fetched live through the two declared sources. The trainees reach the catalog by
way of ResolveMatchCandidate, the same action /review drives, because FR-B-3 lets
only Exact and Alias auto-promote and a name the catalog has never seen is None.
No bulk-promote command: that would be an uncited class and a button on the
guarantee that the engine never guesses."
```

---

### Task 10: Rebuild the catalog list as a trainee and card tree

**Files:**
- Modify: `resources/views/catalog/index.blade.php:10-51` â€” the `<x-layout>` body from the page `<h1>` through the list's closing `@endif`, below the file's header comment
- Modify: `app/Http/Controllers/CatalogController.php` â€” `index()` and `cached()`
- Create: `resources/views/components/rarity-chip.blade.php`
- Modify: `docs/design-research/DESIGN.md` Â§3.4 (the G-5 contrast pair record, at :113 of that file - NOT the root `DESIGN.md`, which only points here)
- Test: `tests/Feature/CatalogRosterTreeTest.php`

**Interfaces:**
- Consumes: `Umamusume::cards`, `CardRarity`, `ReleaseStatus`.
- Produces: `GET /umamusume` rendering one trainee block per `<h2>` with nested `<h3>` card rows; view vars `showAllStatus`, `showUnconfirmed`, `allStatusesLabel`; the `<x-rarity-chip>` component; query params `status=all` and `show_unconfirmed=1`.

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/CatalogRosterTreeTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Models\CharacterCard;
use App\Models\Umamusume;
use Illuminate\Support\Facades\Cache;

it('nests each card under its trainee', function (): void {
    $goldShip = Umamusume::factory()->create([
        'name' => 'Gold Ship', 'slug' => 'gold-ship', 'name_ja' => 'ã‚´ãƒ¼ãƒ«ãƒ‰ã‚·ãƒƒãƒ—',
    ]);
    CharacterCard::factory()->create([
        'umamusume_id' => $goldShip->id, 'card_id' => 100701, 'title' => '[Red Strife]',
        'rarity' => CardRarity::TwoStar, 'global_release_date' => '2025-06-26', 'is_debut_form' => true,
    ]);
    CharacterCard::factory()->create([
        'umamusume_id' => $goldShip->id, 'card_id' => 100702, 'title' => '[RUN! RUIN! LAUNCHER!]',
        'rarity' => CardRarity::ThreeStar, 'global_release_date' => '2026-07-02',
    ]);

    $html = test()->get('/umamusume')->assertOk()->getContent();

    expect($html)->toContain('Gold Ship')
        ->and($html)->toContain('ã‚´ãƒ¼ãƒ«ãƒ‰ã‚·ãƒƒãƒ—')
        ->and($html)->toContain('[Red Strife]')
        ->and($html)->toContain('[RUN! RUIN! LAUNCHER!]')
        // The trainee row carries her form count and the max rarity across her cards.
        ->and($html)->toContain('2 forms')
        ->and($html)->toContain('Three stars');
});

it('orders the debut first, then by Global release date ascending', function (): void {
    $teio = Umamusume::factory()->create(['name' => 'Tokai Teio', 'slug' => 'tokai-teio']);
    foreach ([
        ['[Beyond the Horizon]', '2025-07-16', false, 100302],
        ['[Peak Joy]', '2025-06-26', true, 100301],
        ['[A Later Form]', '2026-01-01', false, 100303],
    ] as [$title, $date, $debut, $cardId]) {
        CharacterCard::factory()->create([
            'umamusume_id' => $teio->id, 'card_id' => $cardId, 'title' => $title,
            'global_release_date' => $date, 'is_debut_form' => $debut,
        ]);
    }

    $html = test()->get('/umamusume?search=tokai teio')->getContent();
    $positions = array_map(
        static fn (string $title): int => strpos($html, $title),
        ['[Peak Joy]', '[Beyond the Horizon]', '[A Later Form]'],
    );

    // The debut leads even though [A Later Form] is last by date, and the two
    // non-debut forms follow their own date, not the order they were inserted in.
    expect($positions[0])->toBeGreaterThan(0)
        ->and($positions[0])->toBeLessThan($positions[1])
        ->and($positions[1])->toBeLessThan($positions[2]);
});

it('never prints a bare zero when a trainee has no forms', function (): void {
    Umamusume::factory()->create(['name' => 'Cardless One', 'slug' => 'cardless-one']);

    $html = test()->get('/umamusume?search=cardless')->assertOk()->getContent();

    // G-13 and the disclosure pattern: an absent count prints words, never 0. This is
    // also the shape DesignTokensTest creates 30 of, so it has to render clean.
    expect($html)->toContain('Cardless One')
        ->and($html)->not->toMatch('/0 forms/')
        ->and($html)->toContain('no forms recorded');
});

it('hides a card only the Tier B source attests, unless asked', function (): void {
    $u = Umamusume::factory()->create(['name' => 'Fine Motion', 'slug' => 'fine-motion']);
    CharacterCard::factory()->confirmed()->create([
        'umamusume_id' => $u->id, 'card_id' => 112001, 'title' => '[Seen Twice]', 'is_debut_form' => true,
    ]);
    CharacterCard::factory()->unconfirmed()->create([
        'umamusume_id' => $u->id, 'card_id' => 112002, 'title' => '[GameTora Only]',
    ]);

    $default = test()->get('/umamusume')->getContent();
    expect($default)->toContain('[Seen Twice]')
        ->and($default)->not->toContain('[GameTora Only]');

    test()->get('/umamusume?show_unconfirmed=1')
        ->assertOk()
        ->assertSee('[GameTora Only]')
        ->assertSee('Not confirmed by two sources');
});

it('defaults the release status filter to released on Global', function (): void {
    Umamusume::factory()->create(['name' => 'Global One', 'slug' => 'global-one']);
    Umamusume::factory()->japanOnly()->create(['name' => 'Japan One', 'slug' => 'japan-one']);

    $html = test()->get('/umamusume')->getContent();
    expect($html)->toContain('Global One')->and($html)->not->toContain('Japan One');

    // And the explicit way back to everything, which is what keeps CatalogTest honest.
    $all = test()->get('/umamusume?status=all')->getContent();
    expect($all)->toContain('Global One')->and($all)->toContain('Japan One');
});

it('filters the tree by a card title as well as a trainee name', function (): void {
    $goldShip = Umamusume::factory()->create(['name' => 'Gold Ship', 'slug' => 'gold-ship']);
    CharacterCard::factory()->create([
        'umamusume_id' => $goldShip->id, 'card_id' => 100702, 'title' => '[RUN! RUIN! LAUNCHER!]',
    ]);
    Umamusume::factory()->create(['name' => 'Unrelated One', 'slug' => 'unrelated-one']);

    // The card matches, so its trainee is the row that appears. The empty state still
    // says "No Umamusume match" when nothing at all matches, so that stays true.
    test()->get('/umamusume?search=ruin')
        ->assertOk()
        ->assertSee('Gold Ship')
        ->assertDontSee('Unrelated One');
});

it('keeps the card tree intact through the database cache store', function (): void {
    config(['cache.default' => 'database']);
    Cache::flush();

    $u = Umamusume::factory()->create(['name' => 'Agnes Digital', 'slug' => 'agnes-digital']);
    CharacterCard::factory()->create([
        'umamusume_id' => $u->id, 'card_id' => 110102, 'title' => '[Full-Color Fangirling]', 'is_debut_form' => true,
    ]);

    // KI-2's failure mode was the cached id list handing back rows whose relations had
    // gone missing. The tree is exactly that shape, loaded twice so the cache is warm.
    test()->get('/umamusume')->assertOk()->assertSee('[Full-Color Fangirling]');
    test()->get('/umamusume')->assertOk()->assertSee('[Full-Color Fangirling]');
});
```

- [ ] **Step 2: Run, confirm failure**

```bash
php artisan test --compact tests/Feature/CatalogRosterTreeTest.php
```

Expected: FAIL â€” no card titles in the output, no form counts, and `Japan One` present on the unfiltered default.

- [ ] **Step 3: Rework the controller query**

In `CatalogController::index()`. First the status resolution, replacing the two lines that resolve `$statusEnum` from the `status` query (its `@var` line and its assignment):

```php
        /*
         * An absent `status` now means "Released (Global)" rather than "everything",
         * because a Global trainer's default view of the catalog is the roster they can
         * actually use. `status=all` is the explicit way back, and every existing test
         * that passes a status value is unaffected by the change of default.
         */
        $showAll = $status === 'all';
        $statusEnum = match (true) {
            $showAll => null,
            $status !== null => ReleaseStatus::tryFrom((string) $status),
            default => ReleaseStatus::GlobalReleased,
        };
        $showUnconfirmed = $request->query('show_unconfirmed') === '1';
```

Then one card scope built once, so the list query and the cached re-read cannot drift:

```php
        // Debut first, then by the date [Global] shipped it, then by card id so the
        // order is total and two requests cannot disagree about a same-day pair.
        $cardScope = fn ($q) => $q
            ->when(! $showUnconfirmed, fn ($c) => $c->where('unconfirmed', false))
            ->orderByDesc('is_debut_form')
            ->orderBy('global_release_date')
            ->orderBy('card_id');

        $query = Umamusume::query()
            ->withCount('aliases')  // [SUPERSEDED 2026-09-29: dropped from both queries; nothing renders aliases_count]
            ->with(['cards' => $cardScope])
            ->when($statusEnum !== null, fn ($q) => $q->where('release_status', $statusEnum->value))
            ->when($searchKey !== null, function ($q) use ($searchKey): void {
                $q->where(function ($sub) use ($searchKey): void {
                    $sub->where('match_key', 'like', $like)  // [SUPERSEDED shape: see the note at the end of this step]
                        ->orWhereHas('aliases', fn ($a) => $a->whereRaw($this->normalizedColumn('alias').' like ?', [$like]))  // [SUPERSEDED: folded column]
                        // A card match surfaces its trainee, because the trainee is the
                        // level this page is organised at.
                        ->orWhereHas('cards', fn ($c) => $c->whereRaw($this->normalizedColumn('title').' like ?', [$like]));  // [SUPERSEDED: folded column]
                });
            });

        [$items, $total] = $this->cached($query, $status, $searchKey, $page, $pageSize, $showUnconfirmed)  // [SUPERSEDED: cached() takes no scope arg, it calls cardScope() itself];
```

`use Closure;` and `use Illuminate\Database\Eloquent\Relations\HasMany;` as needed for the `$cardScope` parameter type. **[SUPERSEDED SHAPE, corrected 2026-09-29 after the fix rounds - what follows is what actually shipped.]** The briefed `Closure(HasMany): HasMany` is untrue at runtime, because `Relation::__call` forwards `when()` to the query Builder. What shipped is one private `cardScope(bool $showUnconfirmed)` returning `Closure(HasMany<CharacterCard, Umamusume>): void`, consumed by BOTH the list query and the cached re-read, which satisfies the real invariant (the two cannot drift) with honest typing and no suppression. Second, `lower(<col>) like "%<normalized term>%"` can never match a multi-word value: normalize() deletes spaces and hyphens, so "red strife" becomes "redstrife" while the stored title keeps its space, and card titles and aliases have no normalized column of their own. The fix folds the COLUMN at comparison time through `normalizedColumn()`, which nests REPLACE over lower() for exactly the characters `NameNormalizer::FOLDED_CHARACTERS` lists; that list is now the single source, read by normalize() and by the SQL builder alike, because two lists drifting apart is what produced the defect. Third, the bound term is escaped for LIKE metacharacters (percent, underscore, backslash) into one `$like` computed once and passed bound to all three clauses, because normalize() strips separators but not LIKE's own syntax, and unescaped a Trainer typing a percent sign receives the entire catalog. Proven against SQLite rather than by reverting the escape in a working tree: unescaped percent and underscore each match 2 of 2 fixture rows, escaped 0 of 2, and real terms match 1 of 2 either way. Commits `adfc7e9`, `bd0a1e6`, `5297fa2`, `f2c978b`. The durable answer for diacritics and full-width katakana is a stored normalized key beside `title` and `alias`, written by the pipeline the way `match_key` already is; that is a schema change no task here was authorized to make, so it is owed a register entry.

The search stays **substring**, as it already was, while the selector is prefix. That is erratum E-10's reading, recorded not hidden: this page is a server-filtered list behind a submit, that one is a client filter over a fixed payload. `CatalogTest`'s `'finds an umamusume by normalized search text'` test stays green because `match_key` is still the first clause.

- [ ] **Step 4: Keep the cache path honest**

In `CatalogController::cached()` both changes are mandatory, or the page renders 25 trainees with their card lists missing:

```php
    /**
     * @param  Builder<Umamusume>  $query
     * @param  Closure(HasMany): HasMany  $cardScope  the same scope the list query used
     * @return array{0: Collection<int, Umamusume>, 1: int}
     */
    private function cached($query, ?string $status, ?string $searchKey, int $page, int $pageSize, bool $showUnconfirmed, Closure $cardScope): array
    {
        $version = (int) Cache::remember('catalog:version', 3600, fn () => 0);
        $ttl = (int) config('uma.cache.ttl', 900);
        // The unconfirmed flag changes which rows exist, so it belongs in the key.
        $base = "catalog:list:v{$version}:".md5("{$status}|{$searchKey}|".($showUnconfirmed ? '1' : '0'));

        /** @var list<int> $ids */
        $ids = Cache::remember(
            "{$base}:p{$page}:{$pageSize}",
            $ttl,
            fn (): array => $query->orderBy('name')->forPage($page, $pageSize)->pluck('id')->all(),
        );

        $total = Cache::remember("{$base}:count", $ttl, fn () => $query->count());

        $items = Umamusume::query()
            ->withCount('aliases')
            ->with(['cards' => $cardScope])
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();

        return [$items, $total];
    }
```

Leave the existing comment block above the re-read intact: `aliases_count` is still deliberately re-queried rather than cached.

- [ ] **Step 5: Pass the new view vars**

Extend the `view('catalog.index')` array `index()` returns, with `'showAllStatus' => $showAll`, `'showUnconfirmed' => $showUnconfirmed`, and `'allStatusesLabel' => 'All statuses'`. Do not put the literal label in the template: `RenderedCopyHygieneTest` sweeps every Blade file for placeholder-shaped copy, and a string in the view is one no enum test covers.

- [ ] **Step 6: Write the rarity chip**

Create `resources/views/components/rarity-chip.blade.php`:

```blade
@props(['rarity'])

{{--
    The glyph run is the readout, so this badge adds no colour role:
    DesignTokensTest pins the theme at exactly 60 tokens, and G-47 bans white on a
    light fill. Mood-pill carries an arrow for the same reason: hue alone cannot
    order three neighbours (G-6). role="img" with an aria-label turns a row of
    glyphs into a name instead of noise a screen reader has to interpret.
--}}
<span {{ $attributes->merge(['class' => 'font-mono text-xs font-bold text-ink']) }}
    role="img" aria-label="{{ $rarity->label() }}"><span aria-hidden="true">{{ $rarity->stars() }}</span></span>
```

No background fill: the row already sits on `bg-raised`, and a chip in its host's own fill reads as nothing. `label()` yields `One star` / `Two stars` / `Three stars` from `lang/en/uma.php`, so no UI string is invented and `EnumLabelTest` covers every one.

- [ ] **Step 7: Rewrite the list**

Replace `catalog/index.blade.php:10-51` â€” the `<x-layout>` body from the page `<h1>` through the list's closing `@endif` â€” keeping the file's `{{-- ... --}}` header comment above it. Heading levels sit one below the brief's H1/H2 naming, and the reason is deliberate: the page already owns `<h1>` "Umamusume catalog", and a second `<h1>` per trainee is a broken heading structure that G-11 and any accessibility review flag. Trainee is `<h2>`, card is `<h3>`.

```blade
    <h1 class="text-2xl font-semibold text-ink-strong">Umamusume catalog</h1>

    <form method="GET" action="{{ route('catalog.index') }}" class="mt-4 flex flex-wrap items-end gap-3 text-sm">
        <label class="flex flex-col gap-1">
            <span class="text-ink-muted">Search</span>
            <input type="text" name="search" value="{{ $search }}" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink" placeholder="Trainee or card name">
        </label>
        <label class="flex flex-col gap-1">
            <span class="text-ink-muted">Release status</span>
            <select name="status" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                <option value="all" @selected($showAllStatus)>{{ $allStatusesLabel }}</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($currentStatus === $status)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </label>
        <label class="flex items-center gap-2 pb-1.5">
            <input type="checkbox" name="show_unconfirmed" value="1" @checked($showUnconfirmed) class="rounded border-rule">
            <span class="text-ink-muted">Show unconfirmed cards</span>
        </label>
        <button type="submit" class="enamel rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">Filter</button>
    </form>

    @if ($umamusumes->count() === 0)
        <p class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted">
            No Umamusume match. The catalog is filled by seed data or `php artisan uma:fetch`.
        </p>
    @else
        <ul class="mt-6 space-y-4">
            @foreach ($umamusumes as $umamusume)
                @php $cards = $umamusume->cards; @endphp
                <li class="rounded-md border border-rule bg-raised">
                    <h2 class="flex flex-wrap items-baseline justify-between gap-x-4 px-4 py-3">
                        <a href="{{ route('catalog.show', $umamusume->slug) }}" class="font-semibold text-ink-strong hover:underline">
                            {{ $umamusume->name }}
                            @if ($umamusume->name_ja)
                                <span class="ml-2 text-sm font-normal text-ink-muted">{{ $umamusume->name_ja }}</span>
                            @endif
                        </a>
                        <span class="flex items-baseline gap-3 text-xs text-ink-muted">
                            @if ($cards->isNotEmpty())
                                {{-- max() over enum instances would compare objects, not stars,
                                     and from() wants an int, so the value is cast twice over --}}
                                <x-rarity-chip :rarity="\App\Enums\CardRarity::from((int) $cards->max(static fn ($c) => $c->rarity->value))" />
                            @else
                                <span aria-hidden="true">N/A</span><span class="sr-only">No forms recorded</span>
                            @endif
                            <span>{{ $cards->isEmpty() ? 'no forms recorded' : $cards->count().' '.\Illuminate\Support\Str::plural('form', $cards->count()) }}</span>
                            <span>{{ $umamusume->release_status->label() }}</span>
                        </span>
                    </h2>

                    @if ($cards->isNotEmpty())
                        <ul class="divide-y divide-rule border-t border-rule">
                            @foreach ($cards as $card)
                                <li class="flex flex-wrap items-baseline justify-between gap-x-4 px-4 py-2">
                                    <h3 class="text-sm font-medium text-ink">{{ $card->title }}</h3>
                                    <span class="flex items-baseline gap-3 text-xs text-ink-muted">
                                        <x-rarity-chip :rarity="$card->rarity" />
                                        @if ($card->is_debut_form)
                                            <span>debut form</span>
                                        @endif
                                        @if ($card->unconfirmed)
                                            <span class="font-semibold text-risk">Not confirmed by two sources</span>
                                        @endif
                                        <time datetime="{{ $card->global_release_date->toDateString() }}">
                                            Released (Global) {{ $card->global_release_date->format('M j, Y') }}
                                        </time>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>

        <div class="mt-4 text-sm">
            {{ $umamusumes->links() }}
        </div>
    @endif
```

**Collapse behaviour, decided by row count rather than assumed.** 68 trainees at the existing 25-per-page default is three pages, each showing only its own trainees fully expanded, so **no disclosure control ships**. Nothing on the page needs a click to be readable, and a collapse that is not needed is a keyboard trap for G-11 to catch. `CatalogController::index()` already clamps a `pageSize` query param at 100, so if the fully-expanded page ever outgrows the budget the lever is pagination size, not a widget. Record this reasoning in the slice file: the brief asked for the decision to be made on measurement, so the measurement (68 rows, three pages, 25 each) is the answer.

**Why the filter form does not go live.** The brief asked the catalog input to "filter live". This page's filter is a GET form, and live-typing there is a full page reload per keystroke with the input losing focus. The selector in Task 12 is the live surface; the catalog keeps its submit button. Recorded as a reading, not a silent drop.

- [ ] **Step 8: Prove no gate regressed**

```bash
php artisan test --compact tests/Feature/CatalogRosterTreeTest.php tests/Feature/CatalogTest.php tests/Feature/CatalogCacheRenderTest.php tests/Feature/DesignTokensTest.php tests/Feature/RenderedCopyHygieneTest.php tests/Feature/TokenPairHygieneTest.php tests/Feature/FlashBannerTokensTest.php tests/Feature/ApiV1Test.php
```

Expected: all green. The specific hazards: exactly 60 colour tokens (`DesignTokensTest`'s `counts every colour token the static theme declares` test); no `dark:` utility and no palette-numbered class; no hex or arbitrary value (G-4); no em or en dash (`RenderedCopyHygieneTest`'s `ships no em dash or en dash in rendered Blade copy` test); `bg-raised` on every shell page; and `DesignTokensTest`'s 30 factory rows that own **no cards**, which is precisely why the `N/A` / "no forms recorded" branch is load-bearing rather than decorative.

Read `checkViewSource` before trusting a green run here: it scans Blade **source**, not rendered output, and it strips `{{-- --}}`, `/* */` and leading `//` before looking for U+2013 or U+2014. So an em dash in a Blade comment passes while one in visible copy fails, and the string you write in the `@json` payload is visible to nothing on this page. The gate's real boundary is narrower than the rule, and R-02 is the wider one: keep the dash out of anything a Trainer reads.

- [ ] **Step 9: Measure the badge in both themes**

G-5 and G-47 want every new text/background pair computed. The browser half of `DesignTokensTest` skips in this environment (Playwright absent, C-8 forbids installing it), so compute arithmetically from the theme's own declared values, the way `docs/design-research/verification/` records already do:

```bash
grep -n -- '--color-ink:\|--color-ink-strong:\|--color-raised:\|--color-page:\|--color-risk:' resources/css/app.css
```

For each pair: linearise each sRGB channel `c <= 0.03928 ? c/12.92 : ((c+0.055)/1.055)^2.4` on the 0-1 component, then `L = 0.2126 R + 0.7152 G + 0.0722 B`, then `ratio = (L_lighter + 0.05) / (L_darker + 0.05)`. Record in `docs/design-research/DESIGN.md` Â§3.4: `text-ink` on `bg-raised` in light and dark, and `text-risk` on `bg-raised` in light and dark for the unconfirmed warning. `text-ink` on `bg-raised` is an existing measured pair, so the only genuinely new one is `text-risk` â€” that is the line to add, with its two numbers. **Compute them yourself and print the formula, the four hex inputs and the read date next to them.** The three `text-risk` rows already in that section (10.04 light, 4.33 dark-before, 4.77 dark-after, from KI-20) do not reproduce from the very hex values they name under the WCAG formula above: recomputed on 2026-09-29 at `6a7236f` they are 10.89 / 5.51 / 6.22, and no plausible variant formula (no-gamma, simple-average) yields the recorded set either, so the recorded figures were not produced from those inputs. Every one of them still clears 4.5:1 under the correct arithmetic, so no token moves either way, but do not copy the recorded numbers into a new row and do not overwrite the old ones - flag the discrepancy as an open question for KI-20's author. Root cause is unfathomed, not minor: a contract row whose ratio cannot be reproduced from its own inputs is a check that cannot fail for the reason it states.

- [ ] **Step 10: Gates and commit**

```bash
vendor/bin/pint --dirty --format agent && vendor/bin/phpstan analyse --no-progress --memory-limit=1G
npm run build
git add resources/views/catalog/index.blade.php resources/views/components/rarity-chip.blade.php \
        app/Http/Controllers/CatalogController.php docs/design-research/DESIGN.md tests/Feature/CatalogRosterTreeTest.php
git commit -m "feat(ui): the catalog as a trainee and card tree, filtered by default

One heading per trainee, one per card beneath her, debut first then Global release
date. The status filter now defaults to Released (Global) with status=all back to
the unfiltered list, card titles join names in the search scope, and a card the
Tier B source stands alone behind stays hidden until asked for. Headings sit one
level below the brief's naming because the page already owns an h1."
```

---

### Task 11: The detail page names its source and lists her forms

`catalog/show.blade.php` renders "JP debut" and "Global debut", which after Tasks 2 and 9 carry real dates, and a Provenance [PREMISE PARTLY FALSE, measured 2026-09-29: that line is ALREADY conditional on dataSources->isEmpty() at show.blade.php:125-126, so it was suppressed for fetched rows before this task began. Task 11 review found the briefed red set overstates the work by one test; the forms section and the per-card provenance are the real new surface.] `<h2>` that currently reads *"No fetched sources. This record was seeded or entered by hand."* for the seeded rows. Task 9 gives it real rows. The brief wants the forms here too, and D-33 wants every engine-sourced fact at `meta` weight with its URL and fetched date.

**Files:**
- Modify: `resources/views/catalog/show.blade.php`
- Modify: `app/Http/Controllers/CatalogController.php` â€” `show()`
- Test: append to `tests/Feature/CatalogRosterTreeTest.php`

**Interfaces:**
- Consumes: `$umamusume->cards` (including each card's own `source_url` and `fetched_at`, per Amendment A1), `$umamusume->dataSources` (character-level, unchanged), `CharacterCard::$unconfirmed`.
- Produces: a "Costume forms" section where each row names the source and fetch date carried on that card, plus one sentence under the existing Provenance list that identifies it as the trainee's own fetch history and points at the Tier A cross-check file.

- [ ] **Step 1: Write the failing tests**

Append:

```php
it('lists the trainee\'s forms on her detail page', function (): void {
    $u = Umamusume::factory()->create(['name' => 'Mejiro McQueen', 'slug' => 'mejiro-mcqueen']);
    CharacterCard::factory()->create([
        'umamusume_id' => $u->id, 'card_id' => 101301, 'title' => '[Frontline Elegance]',
        'rarity' => CardRarity::ThreeStar, 'global_release_date' => '2025-11-06', 'is_debut_form' => true,
    ]);

    test()->get('/umamusume/mejiro-mcqueen')
        ->assertOk()
        ->assertSee('[Frontline Elegance]')
        ->assertSee('Costume forms')
        ->assertSee('debut form');
});

it('names the source and the fetch date instead of the seeded-data line', function (): void {
    $u = Umamusume::factory()->create(['name' => 'Silence Suzuka', 'slug' => 'silence-suzuka']);
    DataSource::factory()->create([
        'umamusume_id' => $u->id,
        'source_key' => 'gametora-character-cards',
        'url' => 'https://gametora.test/character-cards.json',
        'fetched_at' => '2026-09-29 10:00:00',
    ]);

    // The exact sentence the request quotes has to go away for fetched rows, while
    // staying for genuinely hand-entered ones. That is the deliverable, tested.
    test()->get('/umamusume/silence-suzuka')
        ->assertOk()
        ->assertSee('gametora.test')
        ->assertSee('gametora-character-cards')
        ->assertDontSee('No fetched sources');
});

it('still says a hand-entered record has no fetched source', function (): void {
    Umamusume::factory()->manual()->create(['name' => 'Local Entry', 'slug' => 'local-entry']);

    test()->get('/umamusume/local-entry')
        ->assertOk()
        ->assertSee('seeded or entered by hand');
});

it('keeps an unconfirmed card out of the detail list unless asked', function (): void {
    $u = Umamusume::factory()->create(['name' => 'Vodka', 'slug' => 'vodka']);
    CharacterCard::factory()->unconfirmed()->create([
        'umamusume_id' => $u->id, 'card_id' => 199901, 'title' => '[Solo Sourced]', 'is_debut_form' => true,
    ]);

    test()->get('/umamusume/vodka')->assertOk()->assertDontSee('[Solo Sourced]');
    test()->get('/umamusume/vodka?show_unconfirmed=1')
        ->assertOk()
        ->assertSee('[Solo Sourced]')
        ->assertSee('Not confirmed by two sources');
});

it('names each card the source its own row was read from', function (): void {
    $u = Umamusume::factory()->create(['name' => 'Mayano Top Gun', 'slug' => 'mayano-top-gun']);
    CharacterCard::factory()->create([
        'umamusume_id' => $u->id,
        'card_id' => 199902,
        'title' => '[Sample Revised Form]',
        'is_debut_form' => true,
        'source_url' => 'https://gametora.test/card-199902.json',
        'fetched_at' => '2026-09-29 10:00:00',
    ]);
    // The trainee's own provenance row points at a different document on purpose:
    // 'card-199902' appears nowhere else on the page, so a card line fed from
    // data_sources fails this test instead of passing it by accident.
    DataSource::factory()->create([
        'umamusume_id' => $u->id,
        'source_key' => 'gametora-characters',
        'url' => 'https://gametora.test/characters.json',
        'fetched_at' => '2026-01-01 00:00:00',
    ]);

    test()->get('/umamusume/mayano-top-gun')
        ->assertOk()
        ->assertSee('card-199902');
});
```

Add `use App\Models\DataSource;` to the file's imports.

- [ ] **Step 2: Run, confirm failure**

```bash
php artisan test --compact tests/Feature/CatalogRosterTreeTest.php
```

Expected: FAIL on the new rows that ask for a forms section and for per-card provenance; the hand-entered one at Step 1's third block already passes, and the earlier ones stay green.

- [ ] **Step 3: Eager-load the cards on the detail route**

Replace `CatalogController::show()`'s body:

```php
        $showUnconfirmed = request()->query('show_unconfirmed') === '1';

        $umamusume = Umamusume::where('slug', $slug)
            ->with([
                'aliases',
                'dataSources' => fn ($q) => $q->latest('fetched_at')->limit(10),
                'cards' => fn ($q) => $q
                    ->when(! $showUnconfirmed, fn ($c) => $c->where('unconfirmed', false))
                    ->orderByDesc('is_debut_form')
                    ->orderBy('global_release_date')
                    ->orderBy('card_id'),
            ])
            ->first();

        abort_if($umamusume === null, 404);

        return view('catalog.show', ['umamusume' => $umamusume, 'showUnconfirmed' => $showUnconfirmed]);
```

Keep the existing long comment above the method: this page is deliberately not cached, and that reasoning still holds.

- [ ] **Step 4: Add the forms section**

In `catalog/show.blade.php`, insert after the `<dl>` grid closes (before the JapanOnly notice's `@if`, which follows it), at the same `<h2>` level as the existing "Aliases" and "Provenance" sections:

```blade
    <h2 class="mt-8 text-lg font-semibold text-ink-strong">Costume forms</h2>

    @if ($umamusume->cards->isEmpty())
        <p class="mt-2 rounded-md border border-dashed border-rule bg-raised p-4 text-sm text-ink-muted">
            No Global costume cards recorded for this trainee yet. Forms arrive with
            `php artisan uma:fetch gametora-character-cards`.
        </p>
    @else
        <ul class="mt-2 divide-y divide-rule rounded-md border border-rule bg-raised">
            @foreach ($umamusume->cards as $card)
                <li class="flex flex-wrap items-baseline justify-between gap-x-4 px-4 py-2 text-sm">
                    <span class="font-medium text-ink">
                        {{ $card->title }}
                        @if ($card->is_debut_form)
                            <span class="ml-2 text-xs font-normal text-ink-muted">debut form</span>
                        @endif
                    </span>
                    <span class="flex items-baseline gap-3 text-xs text-ink-muted">
                        <x-rarity-chip :rarity="$card->rarity" />
                        @if ($card->unconfirmed)
                            <span class="font-semibold text-risk">Not confirmed by two sources</span>
                        @endif
                        <time datetime="{{ $card->global_release_date->toDateString() }}">
                            {{ $card->global_release_date->format('M j, Y') }}
                        </time>
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
```

- [ ] **Step 5: Name the per-card source, and keep the character list for what it is**

The brief asks the detail page to name the source and the fetch date, and `SOURCE-OF-TRUTH` Â§5 to record that a Tier B fact needed an A-tier witness. Amendment A1 decides **which row answers that**: a card carries its own `source_url`, `snapshot_path`, `fetched_at` and `source_timezone`, so the per-card provenance prints from the card. `data_sources` is `umamusume_id`-scoped; it is the trainee's own fetch history behind FR-A-4, it is already rendered in full by the existing `<h2>Provenance</h2>` list (the `<h2>Provenance</h2>` block in `resources/views/catalog/show.blade.php`, url plus `source_key` plus fetched date), and it is not a card's provenance.

So add no second character-level provenance block. Add one span inside the Step 4 card loop, and one sentence under the existing list (D-33).

In the Step 4 `<li>`, after the `<time>` element and inside the enclosing `<span class="flex items-baseline gap-3 text-xs text-ink-muted">`:

```blade
                        @if ($card->fetched_at)
                            <span title="{{ $card->source_url }}"> [SUPERSEDED SHAPE: a title attribute is not keyboard-reachable, so the fact was invisible without a pointer while the paragraph beside it claimed each form names its source. What shipped instead prints the source as visible text in the row and keeps the tooltip only as a qualifier, matching how guided-step.blade.php and stat-band.blade.php use title in this repo. Commit 8b08e42.]
                                read {{ $card->fetched_at->timezone(config('uma.display_timezone'))->format('M j, Y') }}
                            </span>
                        @endif
```

Four things here are deliberate. The span carries no styling because it inherits `text-xs text-ink-muted` and the `gap-3` from its parent, so the row grows no second line and no new visual tier. `source_url` rides the `title` attribute so the exact URL is one hover away without shipping a clickable outbound link in a local-only tool; `{{ }}` escapes it, and a URL that arrived from fetched content is untrusted (`AGENTS.md`, Data Engineer). The conversion is `config('uma.display_timezone')`, the same expression the existing Provenance list already uses on its `fetched_at` line, because US-7 makes a raw UTC render a defect; reuse that line rather than inventing a second formatting convention. And `fetched_at` is nullable, so the `@if` skips the span instead of printing an empty date.

Then this sentence, after the `</ul>` in the existing `@else` branch of the Provenance section:

```blade
        <p class="mt-2 text-xs text-ink-muted">
            The rows above are this trainee's own fetch history. Each costume form names the
            source and the date its own row was read from, and every card on this page was
            confirmed against the two Tier A sources listed in
            docs/data/2026-09-29-global-roster-crosscheck.md. [CLAUSE REMOVED, not merely reworded: docs/data/ does not exist in this tree because Task 8 is blocked, AND the blanket claim it carried was false on its own terms -- unconfirmed defaults to false with no verdict written onto any row yet, so "every card here was checked" rendered for data nobody had cross-checked. The honest statement is the per-row "Not confirmed by two sources" the page already prints. Do not reintroduce a file_exists() guard: it puts a filesystem stat on a provenance claim and vanishes silently when the file lands.]
        </p>
```

The JP and Global debut `<dt>` rows keep their existing markup: after Task 2 Step 3 both carry real dates for fetched rows, and the `N/A` disclosure already in the file covers a trainee with neither. Do not add a new fallback for a case that no longer exists.

- [ ] **Step 6: Prove no gate regressed**

```bash
php artisan test --compact tests/Feature/CatalogRosterTreeTest.php tests/Feature/CatalogTest.php tests/Feature/DesignTokensTest.php tests/Feature/RenderedCopyHygieneTest.php
```

Expected: all green. `CatalogTest`'s `'shows a detail page with Japanese name and provenance'` test creates a `DataSource` with a `https://example.test/...` URL and no `source_key`, and the existing Provenance list [STATED WRONG, corrected 2026-09-29: DataSourceFactory supplies source_key => "test", so a test-created row DOES print a key. The instruction is unchanged and still correct -- the added sentence must not read source_key at all -- but it is not justified by the reason above it. Confirm the test stays green rather than editing it, as briefed.]'s `fetched_at` line already prints that key bare, so the added sentence must not read `source_key` at all; that is why it does not. Confirm the test stays green rather than editing it. The per-card span needs no such tolerance because `CharacterCard::factory()` now sets `fetched_at` (Task 4 Step 11), but it does need the `@if`: a row stored before a fetch stamped it has `fetched_at` null, and `null->timezone()` is a fatal.

- [ ] **Step 7: Gates and commit**

```bash
vendor/bin/pint --dirty --format agent && vendor/bin/phpstan analyse --no-progress --memory-limit=1G
git add resources/views/catalog/show.blade.php app/Http/Controllers/CatalogController.php \
        tests/Feature/CatalogRosterTreeTest.php
git commit -m "feat(ui): the detail page lists her forms and names where they came from

The seeded-data sentence goes when a row genuinely has a fetched source and stays
when it does not. Each form shows its client title, stars, debut state and Global
date, and the provenance line records the Tier B source alongside the Tier A
witness SOURCE-OF-TRUTH 5:152 asks for."
```

---

### Task 12: The searchable trainee selector

Vanilla TypeScript. `package.json` has **no** runtime dependency and PRD Â§6.2 rules out an SPA frontend; `ARCHITECTURE.md`'s frontend line says "vanilla JS only where needed (autocomplete)", and `resources/js/guided-flow.ts` is the existing precedent for a hand-written module. Adding Alpine or Livewire would be C-8 plus a `PLAN.md` reopen criterion with seven named obligations â€” not this task.

Progressive enhancement is not optional here: `resources/js/guided-flow.ts`'s header comment states the repo's rule, "with scripting unavailable the rail is still completable". The existing `<select name="umamusume_id">` stays in the DOM as the no-JS path, disabled by the script when the script runs.

**Files:**
- Create: `resources/js/trainee-combobox.ts`
- Modify: `resources/js/app.ts`
- Modify: `resources/views/runs/create.blade.php:9-18` â€” the `Umamusume` `<label>` that wraps `<select name="umamusume_id">`
- Modify: `app/Http/Controllers/TrainingRunController.php` â€” `create()`
- Test: `tests/Feature/TraineeSelectorTest.php`

**Interfaces:**
- Consumes: `Umamusume::with('cards')`, `CharacterCard::$card_id` (the source id), `TrainingRunController::create()`.
- Produces:
  - controller var `$rosterJson` â€” `list<array{umamusumeId:int, trainee:string, traineeJa:string|null, cards:list<array{selectionId:int, sourceCardId:int, title:string, titleKey:string, releaseDate:string, debut:bool}>}>`, JSON-encoded in a `application/json` script block.
  - submitted fields `umamusume_id` (required, as today) and `character_card_id` (nullable, validated in Task 5).
  - DOM contract: `[data-combobox]`, `[data-combobox-input]`, `[data-combobox-listbox]`, `[data-combobox-status]`, `[data-combobox-selected]`, `#trainee-roster`.

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/TraineeSelectorTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Enums\ReleaseStatus;
use App\Models\CharacterCard;
use App\Models\TrainingRun;
use App\Models\Umamusume;

function selectorRoster(): void
{
    $goldShip = Umamusume::factory()->create([
        'name' => 'Gold Ship', 'slug' => 'gold-ship',
        'name_ja' => 'ã‚´ãƒ¼ãƒ«ãƒ‰ã‚·ãƒƒãƒ—', 'release_status' => ReleaseStatus::GlobalReleased,
    ]);
    CharacterCard::factory()->create([
        'umamusume_id' => $goldShip->id, 'card_id' => 100701, 'title' => '[Red Strife]',
        'rarity' => CardRarity::TwoStar, 'global_release_date' => '2025-06-26', 'is_debut_form' => true,
    ]);
    CharacterCard::factory()->create([
        'umamusume_id' => $goldShip->id, 'card_id' => 100702, 'title' => '[RUN! RUIN! LAUNCHER!]',
        'rarity' => CardRarity::ThreeStar, 'global_release_date' => '2026-07-02',
    ]);

    $fuji = Umamusume::factory()->create([
        'name' => 'Fuji Kiseki', 'slug' => 'fuji-kiseki',
        'name_ja' => 'ãƒ•ã‚¸ã‚­ã‚»ã‚­', 'release_status' => ReleaseStatus::GlobalReleased,
    ]);
    CharacterCard::factory()->create([
        'umamusume_id' => $fuji->id, 'card_id' => 101401, 'title' => '[Four Seasons]',
        'rarity' => CardRarity::ThreeStar, 'global_release_date' => '2025-12-01', 'is_debut_form' => true,
    ]);
}

it('replaces the plain select with an ARIA combobox over the same field', function (): void {
    selectorRoster();

    $html = test()->get('/training-runs/create')->assertOk()->getContent();

    expect($html)->toContain('role="combobox"')
        ->and($html)->toContain('aria-expanded="false"')
        ->and($html)->toContain('aria-autocomplete="list"')
        ->and($html)->toContain('role="listbox"')
        ->and($html)->toContain('aria-activedescendant')
        ->and($html)->toContain('id="trainee-roster"');
});

it('ships the whole roster to the page as data, not as a second list in the template', function (): void {
    selectorRoster();

    $html = test()->get('/training-runs/create')->getContent();

    preg_match('/id="trainee-roster">(.*?)<\/script>/s', $html, $matched);

    expect($matched)->toHaveCount(2);

    $payload = json_decode($matched[1], true, 512, JSON_THROW_ON_ERROR);

    // One source of truth: the payload carries the client titles with their brackets
    // and the ids the form submits, so nothing is re-typed into Blade.
    expect(array_column($payload, 'trainee'))->toContain('Gold Ship')
        ->and(collect($payload)->firstWhere('trainee', 'Gold Ship')['cards'])
            ->toContain([
                'selectionId' => CharacterCard::where('card_id', 100702)->value('id'),
                'sourceCardId' => 100702,
                'title' => '[RUN! RUIN! LAUNCHER!]',
                'titleKey' => 'RUN! RUIN! LAUNCHER!',
                'releaseDate' => '2026-07-02',
                'debut' => false,
            ]);
});

it('keeps a native select as the no-script path, and that path really submits', function (): void {
    selectorRoster();

    $html = test()->get('/training-runs/create')
        ->assertOk()
        ->assertSee('Gold Ship')
        ->assertSee('Fuji Kiseki')
        ->getContent();

    // Two controls share name="umamusume_id". A display:none control still submits, so
    // the hidden pair must ship disabled or a scriptless POST sends the pick followed
    // by the empty hidden value, PHP keeps the last, and `required` fails on the one
    // path that exists precisely because there is no script.
    expect($html)->toMatch('/data-combobox-umamusume-id disabled/')
        ->and($html)->toMatch('/data-combobox-card-id disabled/');

    // Then prove the case the attribute is for: select only, combobox untouched.
    $goldShip = Umamusume::where('slug', 'gold-ship')->firstOrFail();

    test()->post('/training-runs', [
        'umamusume_id' => $goldShip->id,
        'status' => 'Active',
    ])->assertSessionHasNoErrors();

    expect(TrainingRun::first()->umamusume_id)->toBe($goldShip->id)
        ->and(TrainingRun::first()->character_card_id)->toBeNull();
});

it('wires the module into the entry the browser actually runs', function (): void {
    // Same reasoning KeyboardPathTest's entry-point wiring test records: a module on disk proves
    // nothing about it executing. Without the import the combobox is dead markup and
    // the browser pass would report the keys as broken rather than missing.
    expect((string) file_get_contents(base_path('resources/js/app.ts')))
        ->toContain("import './trainee-combobox';");
});

it('escapes a client title in the JSON block rather than trusting it', function (): void {
    $u = Umamusume::factory()->create(['name' => 'Bad Title One', 'slug' => 'bad-title-one']);
    CharacterCard::factory()->create([
        'umamusume_id' => $u->id, 'card_id' => 199999,
        'title' => '[</script><script>alert(1)</script>]', 'is_debut_form' => true,
    ]);

    $html = test()->get('/training-runs/create')->assertOk()->getContent();

    // Titles are source data from a Tier B export. `@json` hex-encodes the angle
    // brackets and quotes, so a title can never close the script element it lives in.
    expect($html)->not->toContain('<script>alert(1)');
});

it('names the combobox input explicitly, since it is not the label\'s first control', function (): void {
    selectorRoster();

    // role="combobox" passes with no accessible name at all, so the markup this test
    // targets is the aria-label itself: the <label> associates with the <select>, which
    // is the first labelable descendant, and the input would otherwise be unnamed.
    test()->get('/training-runs/create')
        ->assertOk()
        ->assertSee('aria-label="Trainee or costume card name"', false);
});

it('submits the chosen card alongside the trainee', function (): void {
    selectorRoster();
    $goldShip = Umamusume::where('slug', 'gold-ship')->firstOrFail();
    $card = CharacterCard::where('card_id', 100702)->firstOrFail();

    test()->post('/training-runs', [
        'umamusume_id' => $goldShip->id,
        'character_card_id' => $card->id,
        'status' => 'Active',
    ])->assertSessionHasNoErrors()->assertRedirect();

    expect(App\Models\TrainingRun::first()->character_card_id)->toBe($card->id);
});

it('carries the chosen card back to the form when another field fails', function (): void {
    selectorRoster();
    $goldShip = Umamusume::where('slug', 'gold-ship')->firstOrFail();
    $card = CharacterCard::where('card_id', 100702)->firstOrFail();

    // POST, fail validation on `scenario`, redirect back with old() flashed, render.
    // The selection has to survive that round trip: a form that forgets the trainee
    // on a 422 is a form the Trainer submits twice. StoreTrainingRunRequest already
    // flashes input on failure, so this needs nothing new to pass.
    test()->followingRedirects()->post('/training-runs', [
        'umamusume_id' => $goldShip->id,
        'character_card_id' => $card->id,
        'status' => 'Active',
        'scenario' => 'not_a_scenario',
    ])
        ->assertOk()
        ->assertSee('[RUN! RUIN! LAUNCHER!]')
        ->assertSee('not_a_scenario', false);
});
```

`assertSee('not_a_scenario', false)` checks the raw `old('scenario')` value survives in the markup, which is the same mechanism carrying the card selection back.

- [ ] **Step 2: Run, confirm failure**

```bash
php artisan test --compact tests/Feature/TraineeSelectorTest.php
```

Expected: FAIL â€” no `role="combobox"`, no `#trainee-roster`.

- [ ] **Step 3: Build the payload in the controller**

Replace `TrainingRunController::create()`:

```php
    public function create(): View
    {
        /*
         * One query, one payload, one truth. The catalog page and this selector read the
         * same rows, so a card that exists in the dropdown cannot be absent from the
         * catalog. Cards come with the trainees rather than in a second pass: at 68 and
         * ~107 rows, an eager load is one query and a lazy one is sixty-nine.
         */
        $roster = Umamusume::query()
            ->where('release_status', App\Enums\ReleaseStatus::GlobalReleased->value)
            // [REMOVED 2026-09-29, commit 1c44698 -- this link gated the TRAINEE list on card existence. No seeder writes character_cards, so on a migrated-and-seeded database the run form offered no trainee at all: an empty select with scripting off, an empty popup with it on. character_card_id is nullable by Task 5's own ruling, so a card is not a precondition for training a trainee, and the requirement that the rail stay completable outranks this line. The card filter belongs in the JSON payload only.]
            ->with(['cards' => fn ($q) => $q
                ->where('unconfirmed', false)
                ->orderBy('global_release_date')
                ->orderBy('card_id')])
            ->orderBy('name')
            ->get();

        return view('runs.create', [
            'umamusumes' => $roster,
            'rosterJson' => $roster->map(fn (Umamusume $u): array => [
                'umamusumeId' => $u->id,
                'trainee' => $u->name,
                'traineeJa' => $u->name_ja,
                'cards' => $u->cards->map(fn (App\Models\CharacterCard $c): array => [
                    'selectionId' => $c->id,
                    'sourceCardId' => $c->card_id,
                    'title' => $c->title,
                    // Brackets stripped so `RUN` can prefix-match `[RUN! RUIN! LAUNCHER!]`.
                    'titleKey' => preg_replace('/^\[|\]$/', '', $c->title) ?? $c->title,
                    'releaseDate' => $c->global_release_date->toDateString(),
                    'debut' => $c->is_debut_form,
                ])->all(),
            ])->all(),
            'selectedLabel' => $this->selectedCardLabel(),
            'scenarios' => $this->scenarioLabels(),
        ]);
    }

    /**
     * The selection a failed submit has to hand back. Read from old() so the visible
     * label is the same card the hidden fields still name, not one the Trainer has to
     * pick again.
     */
    private function selectedCardLabel(): ?string
    {
        $cardId = old('character_card_id');

        if (! is_numeric($cardId)) {
            return null;
        }

        $card = App\Models\CharacterCard::with('umamusume')->find((int) $cardId);

        return $card === null ? null : $card->umamusume->name.' Â· '.$card->title;
    }
```

Import `App\Enums\ReleaseStatus`, `App\Models\CharacterCard` at the top rather than using fully-qualified names inline, matching the file's existing import block. `umamusumes` becomes the trainee collection so the no-JS `<select>` still iterates over it, Global-only, and **not** card-gated: a trainee with no Global card still appears in the no-JS select, because `character_card_id` is nullable per Task 5 and the rail must stay completable on a database that has no card rows at all -- which is every freshly seeded one, since no seeder writes `character_cards`. **[CORRECTED 2026-09-29, commit `1c44698`.** The briefed "she does not appear" is the FR-A-6 *catalog* rule about trainees whose cards are all JP-only; copying it onto the run form turned a display rule into a precondition for training a trainee, and the review caught it as Critical.] The card filter belongs inside the JSON payload only, so the searchable popup still lists forms; where a trainee has no confirmed form the payload simply carries none, and the native select remains the working control.

- [ ] **Step 4: Replace the form markup**

In `resources/views/runs/create.blade.php`, replace the `Umamusume` `<label>` that wraps `<select name="umamusume_id">`:

```blade
        {{-- One field, two paths. The native select is the no-script route and the
             combobox is the one a Trainer actually uses. Both carry name="umamusume_id",
             and a display:none control still submits, so the combobox's hidden inputs
             ship DISABLED in the markup and the script flips the pair in one block:
             select off, hidden fields on. Reversed, a scriptless POST would send
             umamusume_id=<pick> then umamusume_id="" and PHP keeps the last one, which
             fails `required` on exactly the path the enhancement exists for. --}}
        <label class="block">
            <span class="font-medium text-ink">Umamusume</span>

            <select name="umamusume_id" required data-combobox-fallback
                    class="mt-1 w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink @error('umamusume_id') border-risk @enderror">
                <option value="">Chooseâ€¦</option>
                @foreach ($umamusumes as $umamusume)
                    <option value="{{ $umamusume->id }}" @selected(old('umamusume_id') == $umamusume->id)>
                        {{ $umamusume->name }}
                    </option>
                @endforeach
            </select>

            <div data-combobox class="mt-1 hidden">
                {{-- aria-label is explicit because this input is not the first labelable
                     descendant of the <label>, so the implicit association goes to the
                     select and the combobox would otherwise have no accessible name. --}}
                <input type="text" role="combobox" aria-expanded="false" aria-autocomplete="list"
                       aria-controls="trainee-listbox" aria-activedescendant=""
                       aria-label="Trainee or costume card name"
                       autocomplete="off" data-combobox-input
                       class="w-full rounded-md border border-rule bg-raised px-2 py-1 text-ink @error('umamusume_id') border-risk @enderror"
                       value="{{ $selectedLabel }}" placeholder="Trainee or card name">

                <ul id="trainee-listbox" role="listbox" aria-label="Trainees and costume cards"
                    data-combobox-listbox
                    class="mt-1 max-h-72 overflow-y-auto rounded-md border border-rule bg-raised" hidden></ul>

                <p data-combobox-status aria-live="polite" class="mt-1 text-xs text-ink-muted"></p>

                <input type="hidden" name="umamusume_id" data-combobox-umamusume-id disabled
                       value="{{ old('umamusume_id') }}">
                <input type="hidden" name="character_card_id" data-combobox-card-id disabled
                       value="{{ old('character_card_id') }}">
            </div>

            @error('umamusume_id')<p class="mt-1 text-risk">{{ $message }}</p>@enderror
            @error('character_card_id')<p class="mt-1 text-risk">{{ $message }}</p>@enderror
        </label>

        <script type="application/json" id="trainee-roster">@json($rosterJson)</script>
```

**What the no-script path can and cannot carry.** `old('umamusume_id')` survives a 422 on that path through the select's `@selected()`, which is what makes the trainee sticky without JS. The disabled hidden input does **not** resubmit `character_card_id`, so a scriptless Trainer who failed validation on `scenario` comes back to a run naming the trainee but not the form. That is the honest ceiling for a path with no filtering script, not an oversight: `character_card_id` is nullable and Task 5's rule accepts a submission without it. Say this in the commit message so a later reader does not "fix" it by removing `disabled` and breaking the no-JS submit again.

`@json` is the escape boundary: it hex-encodes `<`, `>`, `&` and quotes, which is what keeps a title from the export out of executable context. `max-h-72` is an existing spacing step, not an arbitrary value. No `dark:` anywhere.

- [ ] **Step 5: Write the combobox module**

Create `resources/js/trainee-combobox.ts`:

```ts
/*
 * The trainee selector on "New training run" (FR-A-6, ADR-0008).
 *
 * ARIA combobox, not an invented pattern: the input carries role="combobox" with
 * aria-expanded, aria-autocomplete="list" and aria-activedescendant; the popup is a
 * role="listbox"; a trainee is a role="group" header that is deliberately not an
 * option; her cards are the role="option" items. See APG "combobox with list".
 *
 * Everything is additive. The page ships a working native <select>; this module
 * disables it and reveals its own controls only when it runs, so a Trainer with
 * scripting off still creates a run (the same stance guided-flow.ts takes).
 *
 * Filtering is synchronous over ~105 rows already in the document. No debounce, no
 * request, no loading state: a debounce here would be a delay added to a job that
 * was not slow (C-7 as scoped by ADR-0007).
 */

interface CardRow {
    selectionId: number;
    sourceCardId: number;
    title: string;
    titleKey: string;
    releaseDate: string;
    debut: boolean;
}

interface TraineeRow {
    umamusumeId: number;
    trainee: string;
    traineeJa: string | null;
    cards: CardRow[];
}

const MAX_VISIBLE = 10;
const DEFAULT_VISIBLE = 10;

const fold = (value: string): string => value.trim().toLowerCase();

/*
 * Prefix on all three fields the request named: trainee English name, trainee
 * Japanese name, card epithet. The card test uses titleKey, the bracket-stripped
 * form the server builds, because the verbatim string starts with "[" and a prefix
 * match on it would make `RUN` and every first-letter query return nothing. The
 * label still renders the verbatim title.
 *
 * A trainee whose own name matches shows all her forms, not just the matching ones:
 * `Fuji` is a Trainer asking which Fuji Kiseki card to run, which is the shape the
 * request's `Fenomeno` row describes.
 */
const matchesQuery = (row: TraineeRow, query: string): CardRow[] | null => {
    const q = fold(query);

    if (q === '') {
        return null;
    }

    const traineeHit =
        fold(row.trainee).startsWith(q) || (row.traineeJa !== null && fold(row.traineeJa).startsWith(q));

    if (traineeHit) {
        return row.cards;
    }

    const cards = row.cards.filter((card) => fold(card.titleKey).startsWith(q));

    return cards.length > 0 ? cards : null;
};

const exactTraineeName = (rows: TraineeRow[], query: string): TraineeRow | null => {
    const q = fold(query);

    return rows.find((row) => fold(row.trainee) === q || fold(row.traineeJa ?? '') === q) ?? null;
};

const byDate = (a: CardRow, b: CardRow): number => a.releaseDate.localeCompare(b.releaseDate);
const byTrainee = (a: TraineeRow, b: TraineeRow): number => a.trainee.localeCompare(b.trainee);

const sortRows = (left: { trainee: TraineeRow; card: CardRow }, right: { trainee: TraineeRow; card: CardRow }): number => {
    const traineeOrder = byTrainee(left.trainee, right.trainee);

    return traineeOrder !== 0 ? traineeOrder : byDate(left.card, right.card);
};

const collect = (rows: TraineeRow[], query: string): { trainee: TraineeRow; card: CardRow }[] => {
    if (query.trim() === '') {
        // Short default list, most recently released first: never all ~105 at once.
        const flattened = rows.flatMap((trainee) => trainee.cards.map((card) => ({ trainee, card })));

        return flattened.sort((a, b) => b.card.releaseDate.localeCompare(a.card.releaseDate)).slice(0, DEFAULT_VISIBLE);
    }

    const hits = rows.flatMap((trainee) => {
        const cards = matchesQuery(trainee, query);

        return (cards ?? []).map((card) => ({ trainee, card }));
    });

    const exact = exactTraineeName(rows, query);

    // Exact first, then prefix, and grouped by trainee inside each band.
    const banded = exact
        ? [...exact.cards.map((card) => ({ trainee: exact, card })), ...hits.filter((hit) => hit.trainee.umamusumeId !== exact.umamusumeId)]
        : hits;

    return banded.sort(sortRows);
};

const render = (
    rows: TraineeRow[],
    query: string,
    listbox: HTMLUListElement,
    status: HTMLElement,
): { trainee: TraineeRow; card: CardRow }[] => {
    const matches = collect(rows, query);
    const visible = query.trim() === '' ? matches : matches.slice(0, MAX_VISIBLE);

    listbox.textContent = '';

    if (visible.length === 0) {
        status.textContent = 'No trainee or card found.';

        return visible;
    }

    status.textContent =
        matches.length > visible.length
            ? `${visible.length} of ${matches.length} (keep typing)`
            : `${matches.length} ${matches.length === 1 ? 'card' : 'cards'} match`;

    let lastTrainee: number | null = null;

    for (const hit of visible) {
        if (hit.trainee.umamusumeId !== lastTrainee) {
            lastTrainee = hit.trainee.umamusumeId;

            const header = document.createElement('li');
            header.setAttribute('role', 'group');
            header.setAttribute('data-group-header', '');
            header.className = 'px-3 pt-2 pb-1 text-xs font-semibold text-ink-muted';
            // textContent throughout: titles are source data and must never be parsed
            // as markup, however they were stored.
            header.textContent = hit.trainee.traineeJa === null
                ? hit.trainee.trainee
                : `${hit.trainee.trainee}  ${hit.trainee.traineeJa}`;
            listbox.append(header);
        }

        const option = document.createElement('li');
        option.id = `trainee-option-${hit.card.selectionId}`;
        option.setAttribute('role', 'option');
        option.setAttribute('aria-selected', 'false');
        option.dataset.selectionId = String(hit.card.selectionId);
        option.dataset.umamusumeId = String(hit.trainee.umamusumeId);
        option.className = 'cursor-pointer px-3 py-1.5 text-sm text-ink';

        const title = document.createElement('span');
        title.textContent = hit.card.title;
        const date = document.createElement('span');
        date.className = 'ml-2 text-xs text-ink-muted';
        date.textContent = hit.card.debut ? 'debut' : hit.card.releaseDate;

        option.append(title, date);
        listbox.append(option);
    }

    return visible;
};

export const initTraineeCombobox = (): void => {
    const root = document.querySelector<HTMLElement>('[data-combobox]');
    const input = document.querySelector<HTMLInputElement>('[data-combobox-input]');
    const listbox = document.querySelector<HTMLUListElement>('[data-combobox-listbox]');
    const status = document.querySelector<HTMLElement>('[data-combobox-status]');
    const fallback = document.querySelector<HTMLSelectElement>('[data-combobox-fallback]');
    const cardField = document.querySelector<HTMLInputElement>('[data-combobox-card-id]');
    const traineeField = document.querySelector<HTMLInputElement>('[data-combobox-umamusume-id]');
    const payload = document.getElementById('trainee-roster');

    if (!(root && input && listbox && status && fallback && cardField && traineeField && payload?.textContent)) {
        return;
    }

    let rows: TraineeRow[];

    try {
        rows = JSON.parse(payload.textContent) as TraineeRow[];
    } catch {
        return; // leave the native select alone: a bad payload must not break the form
    }

    let visible: { trainee: TraineeRow; card: CardRow }[] = [];
    let active = -1;
    let open = false;

    root.classList.remove('hidden');

    /*
     * The handover, in one block so it cannot half-apply: the fallback select stops
     * submitting and the two hidden fields start. Both carry name="umamusume_id", and a
     * display:none control still submits, so if this pair is split across two places a
     * later edit can leave both enabled and the empty hidden value wins the POST.
     */
    fallback.disabled = true;
    traineeField.disabled = false;
    cardField.disabled = false;

    const setOpen = (value: boolean): void => {
        open = value;
        listbox.hidden = !value;
        input.setAttribute('aria-expanded', String(value));
    };

    const options = (): HTMLLIElement[] =>
        Array.from<HTMLLIElement>(listbox.querySelectorAll('li[role="option"]'));

    const paintActive = (): void => {
        options().forEach((option, index) => {
            option.setAttribute('aria-selected', String(index === active));
        });
        input.setAttribute('aria-activedescendant', active >= 0 ? options()[active]?.id ?? '' : '');
    };

    const refresh = (): void => {
        visible = render(rows, input.value, listbox, status);
        active = visible.length > 0 ? 0 : -1;
        paintActive();
    };

    const choose = (index: number): void => {
        const hit = visible[index];

        if (!hit) {
            return;
        }

        traineeField.value = String(hit.trainee.umamusumeId);
        cardField.value = String(hit.card.selectionId);
        // A middle dot, not an em dash: R-02 and D-79 keep the dash out of shipped
        // copy, and RenderedCopyHygieneTest enforces it over rendered Blade.
        input.value = `${hit.trainee.trainee} Â· ${hit.card.title}`;
        status.textContent = `Selected ${hit.trainee.trainee} Â· ${hit.card.title}`;
        setOpen(false);
    };

    /*
     * Enter with nothing highlighted means the Trainer typed a whole trainee name and
     * wants her. That resolves to the debut form, the same default the catalog puts
     * first, and the label says so.
     */
    const chooseDebutByTraineeName = (): boolean => {
        const exact = exactTraineeName(rows, input.value);

        if (exact === null) {
            return false;
        }

        const debut = exact.cards.find((card) => card.debut) ?? [...exact.cards].sort(byDate)[0];

        if (!debut) {
            return false;
        }

        traineeField.value = String(exact.umamusumeId);
        cardField.value = String(debut.selectionId);
        input.value = `${exact.trainee} Â· ${debut.title} (debut)`;
        status.textContent = `Selected ${exact.trainee} Â· ${debut.title} (debut)`;
        setOpen(false);

        return true;
    };

    input.addEventListener('focus', (): void => {
        refresh();
        setOpen(true);
    });

    input.addEventListener('input', (): void => {
        refresh();
        setOpen(true);
    });

    input.addEventListener('blur', (): void => {
        // One frame later, so a click on an option lands before the list goes away.
        window.setTimeout(() => setOpen(false), 120);
    });

    input.addEventListener('keydown', (event: KeyboardEvent): void => {
        if (event.key === 'Escape') {
            setOpen(false);

            return;
        }

        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();

            if (!open) {
                refresh();
                setOpen(true);
            }

            const count = options().length;

            if (count === 0) {
                return;
            }

            active = event.key === 'ArrowDown'
                ? (active + 1) % count
                : (active - 1 + count) % count;
            paintActive();
            options()[active]?.scrollIntoView({ block: 'nearest' });

            return;
        }

        if (event.key === 'Enter') {
            if (active >= 0 && open) {
                event.preventDefault();
                choose(active);

                return;
            }

            if (chooseDebutByTraineeName()) {
                event.preventDefault();
            }
        }
    });

    listbox.addEventListener('mousedown', (event: MouseEvent): void => {
        const option = (event.target as HTMLElement).closest('li[role="option"]');

        if (option instanceof HTMLLIElement && option.dataset.selectionId !== undefined) {
            event.preventDefault();
            choose(options().indexOf(option));
        }
    });

    if (input.value !== '') {
        // A failed submit came back with a selection already made: name it in the
        // hidden fields too, or the visible label and the submitted value disagree.
        const chosen = rows
            .flatMap((trainee) => trainee.cards.map((card) => ({ trainee, card })))
            .find(({ trainee, card }) => input.value === `${trainee.trainee} Â· ${card.title}`);

        if (chosen) {
            traineeField.value = String(chosen.trainee.umamusumeId);
            cardField.value = String(chosen.card.selectionId);
        }

        refresh();
    }
};

initTraineeCombobox();
```

The `(debut)` suffix on the typed-name path is the note the brief asked for; the click and arrow path shows the card alone, which is the state where the card was named explicitly.

- [ ] **Step 6: Import the module**

`resources/js/app.ts` becomes:

```ts
import './bootstrap';
import './guided-flow';
import './trainee-combobox';
```

- [ ] **Step 7: Run the tests green**

```bash
php artisan test --compact tests/Feature/TraineeSelectorTest.php
npm run build
```

Expected: tests pass and the build emits a manifest including `trainee-combobox`. A Vite error here means the TS is not syntactically valid; fix the module rather than the test.

- [ ] **Step 8: Type-check the module**

```bash
npx tsc --noEmit -p tsconfig.json
```

Expected: zero errors. `tsconfig.json` sets `strict: true` and `noEmit: true` over `resources/js/**/*.ts`, so the new file is inside its include pattern by construction. If `tsc` is not on PATH, this is the one step that may be reported as not run rather than skipped silently.

- [ ] **Step 9: Assert the behaviour that a PHP test cannot reach**

There is no JS test runner in `package.json` and C-8 forbids adding one, so the repo's existing technique is the ceiling: `KeyboardPathTest` asserts on `resources/js/*.ts` **source text**. Follow it in `tests/Feature/TraineeSelectorTest.php`:

```php
it('filters on all three fields, capped, with keyboard handling and no dependency', function (): void {
    $source = file_get_contents(base_path('resources/js/trainee-combobox.ts')) ?: '';

    expect($source)->toContain('startsWith')
        ->and($source)->toContain('traineeJa')
        ->and($source)->toContain('titleKey')
        ->and($source)->toContain('MAX_VISIBLE = 10')
        ->and($source)->toContain("'ArrowDown'")
        ->and($source)->toContain("'ArrowUp'")
        ->and($source)->toContain("'Escape'")
        ->and($source)->toContain('aria-activedescendant')
        // textContent, never innerHTML: titles arrive from a Tier B export.
        ->and($source)->not->toMatch('/innerHTML|document\.write|eval\(/')
        ->and($source)->not->toMatch('/from\s+["\'](?!\.)/');
});

it('names the empty and capped states in words a Trainer reads', function (): void {
    $source = file_get_contents(base_path('resources/js/trainee-combobox.ts')) ?: '';

    expect($source)->toContain('No trainee or card found.')
        ->and($source)->toContain('keep typing');
});
```

Say plainly in the slice record that these pin the module's shape, not its runtime behaviour: the behaviour is Task 13's browser pass, and an assertion that only reads source text must not be reported as if it proved the filter works.

- [ ] **Step 10: Prove no gate regressed**

```bash
php artisan test --compact tests/Feature/TraineeSelectorTest.php tests/Feature/KeyboardPathTest.php tests/Feature/DesignTokensTest.php tests/Feature/FlashBannerTokensTest.php tests/Feature/RenderedCopyHygieneTest.php tests/Feature/TrainingRunTest.php tests/Feature/RunUpdateTest.php
vendor/bin/pint --dirty --format agent && vendor/bin/phpstan analyse --no-progress --memory-limit=1G
```

Expected: all green. On the `KeyboardPathTest` hazard, settled against the file rather than left conditional: `'loads the keyboard module from the entry the browser actually runs'` asserts `expect($entry)->toContain("import './guided-flow';")` in `tests/Feature/KeyboardPathTest.php`, which is a containment check, not an exact import list. Adding `trainee-combobox` to `app.ts` therefore leaves it green untouched, and nothing about that test should be edited. The positive wiring check for the new module is the `wires the module into the entry the browser actually runs` test added in Step 1, which borrows the same reasoning that test records: a module on disk proves nothing about it executing.

Also green by inspection, not by hope: `KeyboardPathTest`'s `leaves the arrow-key roving to the radio group rather than reimplementing it` test asserts `guided-flow.ts` does **not** contain `ArrowDown`. That file is untouched here, and the new `ArrowDown` handling lives only in `trainee-combobox.ts`, so the two assertions cannot collide. Do not move the combobox's key handling into `guided-flow.ts` to "share" it â€” that would fail the test on purpose.

- [ ] **Step 11: Commit**

```bash
git add resources/js/trainee-combobox.ts resources/js/app.ts resources/views/runs/create.blade.php \
        app/Http/Controllers/TrainingRunController.php tests/Feature/TraineeSelectorTest.php
git commit -m "feat(ui,http): a searchable trainee and card combobox on the run form

ARIA combobox over the existing field, with the native select kept as the
no-script path and disabled once the script takes over. Prefix on trainee English
name, trainee Japanese name and the bracket-stripped card title, so RUN finds
[RUN! RUIN! LAUNCHER!] under Gold Ship; ten rows visible, grouped by trainee,
arrows and Escape, and the submission carries the card as well as the trainee.
Vanilla TypeScript, no dependency added."
```

---

### Task 13: Browser pass, gate sweep, counts, and the report

Everything in Part 3 that a PHP test cannot prove is proven here, against the real page.

**Files:**
- Modify: `docs/design-research/verification/slice-11-2026-09-29.md`
- Modify: `KNOWN-ISSUES.md` (close or file what this slice found)
- Create: `docs/requests/2026-09-29-catalog-roster-report.md`

- [ ] **Step 1: Serve the populated database on a throwaway port**

Never bind the shared dev file to a port another session may already be using. Copy it and serve the copy:

```bash
cp database/database.sqlite database/scratch-catalog.sqlite
DB_DATABASE="$PWD/database/scratch-catalog.sqlite" php artisan serve --port=8099 --no-interaction &
sleep 2 && curl -s -o /dev/null -w '%{http_code}\n' http://127.0.0.1:8099/umamusume
```

**Guard the copy, because it silently destroys the scratch database.** Task 4 and Task 5 point `migrate` at `database/scratch-catalog.sqlite`, so by this point that file holds a schema with real migrations applied and possibly useful data. The `cp` above overwrites it wholesale â€” fine when Task 9 populated `database/database.sqlite`, and a quiet data loss if anyone reorders the tasks or runs Task 13 alone to re-measure after a UI fix, which is exactly what a re-review pass does. Assert the source is populated first:

```bash
DB_DATABASE="$PWD/database/database.sqlite" php artisan tinker --execute '
$count = App\Models\Umamusume::count();
if ($count !== 68) { fwrite(STDERR, "REFUSING TO COPY: database/database.sqlite holds {$count} trainees, expected 68. Task 9 has not populated it. Not overwriting scratch-catalog.sqlite.\n"); exit(1); }
echo "source verified: {$count} trainees, ".App\Models\CharacterCard::count()." cards\n";
'
```

Expected: `source verified: 68 trainees, 107 cards`, and only then the `cp`. If it refuses, the sequencing broke â€” fix the sequencing or copy in the other direction, rather than flattening a scratch database that another step depends on.

Expected after serving: `200`. If the port is taken, probe by PID and pick another; do not kill a listener you did not start, since a peer session is working in this tree.

- [ ] **Step 2: Drive the selector and record what actually happened**

Use the available browser MCP against `http://127.0.0.1:8099/training-runs/create`. For each query, record the real result and whether it matches the spec. The table below carries the corrected expectations from errata E-5 and E-6; the brief's own rows for `Fe` and `Fenomeno` are impossible on Global data.

| Input | Expected on the shipped roster | Why |
|---|---|---|
| (empty) | 10 most recently released cards, newest first, grouped under their trainees | the default list stays short |
| `F` | Fine Motion and Fuji Kiseki as trainee groups, plus 9 cards whose title begins with F (`[Formula R]`, `[Frontline Elegance]`, `[Full-Color Fangirling]`, `[Fille Ã‰clair]`, `[Fast as Lightning]`, `[Fair Lady of the Waves]`, `[Fanaticâ™¡Jiangshi]`, `[Flare]`, `[Fluttertail Spirit]`), capped at 10 with the keep-typing line | prefix on names and titles |
| `Fe` | **"No trainee or card found."** | no Global trainee and no Global epithet begins with `Fe`; Fenomeno is JP-only |
| `Fenomeno` | **"No trainee or card found."** | both her forms have no `release_en`; the Global-only rule excludes her |
| `Fu` | Fuji Kiseki with her card forms as options | the working stand-in for the brief's `Fe`/`Fenomeno` rows |
| `RUN` | `[RUN! RUIN! LAUNCHER!]` under Gold Ship | prefix on the bracket-stripped title |
| `fenomeno` | identical to `Fenomeno` | case-insensitive |
| `ã‚¹ãƒšã‚·ãƒ£ãƒ«` | Special Week, with her three forms | Japanese prefix |
| `zzz` | "No trainee or card found." | no results state, not an empty popup |
| ArrowDown, ArrowUp, Enter, Escape | highlight moves and wraps; Enter fills the label and sets both hidden fields; Escape closes and leaves the value | keyboard path, G-11 |
| Tab to the input, then type | the popup opens on focus without a click | combobox expectation |
| Screen reader / accessibility tree | `role="combobox"`, `aria-expanded` flipping, `aria-activedescendant` tracking, `role="group"` headers not focusable as options | the pattern is required, not decorative |

Write each observed outcome into the slice record with a screenshot path. Where an expectation fails, **fix the module and re-run**; do not adjust the table.

- [ ] **Step 3: Confirm the catalog page and the selector cannot disagree**

On `http://127.0.0.1:8099/umamusume` check, and record the numbers:

- every trainee in the selector's payload appears in the catalog (same source, same rows);
- the H1-equivalent trainee count equals the selector's trainee count;
- card rows visible in the catalog match the payload, subject to `show_unconfirmed` on both sides;
- the debut leads every trainee's card list, and Gold Ship shows `[Red Strife]` before `[RUN! RUIN! LAUNCHER!]`;
- typing `ruin` in the catalog's server-side search returns Gold Ship.

- [ ] **Step 4: Measure C-6 against the new query shape**

The schema and the query both changed, so re-check the local budget rather than assuming the old number holds:

```bash
for i in $(seq 1 20); do
  curl -s -o /dev/null -w '%{time_total}\n' http://127.0.0.1:8099/umamusume
done | sort -n | awk '{a[NR]=$1} END {printf "median %.3fs p95 %.3fs\n", a[int(NR/2)+1], a[int(NR*0.95)]}'
```

Expected: median well under **0.200 s**, which is C-6's budget at ~1,000 Umamusume â€” this catalog holds 68, so the honest statement is "under budget at one-fifteenth the reference size, with one eager card load per page of 25 trainees". Record the two numbers and the query count if you can get it. Do not claim the 1,000-row budget is met; claim what was measured.

- [ ] **Step 5: Stop the server and run the whole gate sequence**

Kill only the PID you started, then root `CONSTRAINTS.md`'s "Verification sequence before any hand-off" in order:

```bash
vendor/bin/pint --dirty --format agent
vendor/bin/pint --test --format agent
vendor/bin/phpstan analyse --no-progress --memory-limit=1G
php artisan test --compact
make lore
composer audit
npm audit --omit=dev
```

Expected: Pint clean twice; PHPStan zero errors; the full suite green; `make lore` with no unexplained hits; no reachable critical or high from the audits. Report any audit finding rather than silencing it.

On the lore gate, the mechanism moved under this plan and now works in its favour: `8c9faf9` added a **line-scoped marker**, a `lore-ignore-line` HTML comment carrying `class=<1-4> cite=<rule>`, which `tools/lore.php`'s docs-mode `lore-ignore-line` filter honours and the Makefile's three greps filter on. So a verbatim card title that trips a pattern is handled by marking the line with `class=3 cite=C-4` â€” the source-data exemption â€” rather than by an ad-hoc ruling paragraph, and the count stops inflating every time a slice itemises its own hits (R51: 137 â†’ 140 â†’ 144 â†’ 147 across three slices for exactly that reason). Check `-w` before reaching for the marker: the `dam|mare|stable` and 16-word greps are word-matched, so `Fluttertail Spirit` does **not** match `tail`, and marking a line that never matched adds noise the Lore Guardian then has to clear.

- [ ] **Step 6: Prove C-5 one final time on the scratch file**

```bash
rm -f database/scratch-catalog.sqlite && touch database/scratch-catalog.sqlite
DB_DATABASE="$PWD/database/scratch-catalog.sqlite" php artisan migrate:fresh --seed --no-interaction
DB_DATABASE="$PWD/database/scratch-catalog.sqlite" php artisan migrate:rollback --step=3 --no-interaction
```

Expected: fresh migrate and seed succeed; three migrations roll back cleanly, proving each `down()`. Delete the scratch file afterwards: `rm -f database/scratch-catalog.sqlite`.

- [ ] **Step 7: Write the report**

`docs/requests/2026-09-29-catalog-roster-report.md`, with these sections and **real numbers, each traceable to a command output already pasted in the slice record**:

1. **What landed** â€” trainees added, cards added, the two sources, the migration list.
2. **Counts against the export** â€” the Task 9 Step 7 table, filled, including the H1 count against the export's distinct Global `char_id` count (68) and the H2 count against the export's `release_en` row count (107).
3. **Sources and tier** â€” GameTora `character-cards` (Tier B) as the machine-readable source; `umamusu.wiki` and Game8 (Tier A) as witnesses; the cross-check file and its verdict counts.
4. **Unverified entries** â€” every `unconfirmed` card id with the reason, or an explicit "none, and here is the second source for each".
5. **Deviations from the request** â€” the erratum table's items that changed shipped behaviour: 105 cards not ~140; `Fe` and `Fenomeno` returning nothing; the `Â·` separator instead of an em dash; `N/A`/words instead of `0 forms`; heading levels one below the brief's naming; the catalog filter staying server-side behind a submit; no collapse control, with the row count as the reason; the double fetch of one document; JP per-card dates not stored.
6. **New defects found** â€” the `name_ja`/`name_jp` defect with its KI number, and anything the browser pass surfaced.
7. **Not built** â€” `docs/frontend-review/`, `KNOWN-ISSUES.md` and the scenario docs were not touched beyond what is named here; no support-card surface exists or was implied.

- [ ] **Step 8: Update the register and the plan, then commit**

```bash
git add docs/design-research/verification/slice-11-2026-09-29.md KNOWN-ISSUES.md \
        docs/requests/2026-09-29-catalog-roster-report.md
git commit -m "docs(slice-10): the roster browser pass, the measured budget, and the report

Records the selector behaviour on the real page, the catalog counts against the
export, the C-6 re-measure the new query shape requires, and every place the
shipped result differs from the request because the data disagreed with it."
```

- [ ] **Step 9: Hand the branch back**

REQUIRED SUB-SKILL: `superpowers:finishing-a-development-branch`. Report the commit list, the gate outputs, and the two decisions still open for the owner: whether the em dash rule should gain an explicit `Â·` exception in `DESIGN.md`, and whether the `pageSize` 25 default should rise now that a row is taller. Do not merge, push or open a pull request without the owner's word.

---

### Corrected acceptance table

The brief's Part 3 table, with errata E-5 and E-6 applied. Use this in Task 13 Step 2; the original rows for `Fe` and `Fenomeno` describe data that does not exist on Global.

| Input | Result |
|---|---|
| (empty) | ~10 most recently released cards, not all 107 |
| `F` | Fine Motion, Fuji Kiseki, plus the 9 F-initial card titles; capped with the keep-typing line |
| `Fe` | "No trainee or card found." |
| `Fenomeno` | "No trainee or card found." â€” both her forms are `[JP-Only]` |
| `Fu` | Fuji Kiseki with her card forms as options |
| `RUN` | `[RUN! RUIN! LAUNCHER!]` under Gold Ship |
| `fenomeno` | same as `Fenomeno` |
| `ã‚¹ãƒšã‚·ãƒ£ãƒ«` | Special Week, three forms |
| `zzz` | "No trainee or card found." |

---

### Addendum: Task 7 as executed (2026-09-29, commits `545e719` and `fe9694b`)

Appended at the foot of the file rather than inserted into Task 7 on purpose: an insertion anywhere
before line 3930 shifts every plan-line citation the spec's erratum table already carries (E-19 quotes
Task 7 Step 1 at `:1528`), and the plan's own hints-not-anchors bullet is there to forgive drift, not to
invite it. Read this before re-running Task 7. Three things in the steps above are not what shipped, and
none of them is the implementer's error â€” the brief was stale and Amendment A1 said so in some places
and not others.

1. **`created 3 / skipped 5` is wrong, in A1's own summary line as well as in Step 1.** The parser emits
   **7** records for `tests/Fixtures/gametora-character-cards.global.sample.json` â€”
   `gametora:char:1001` x3, `1007` x2, `1003` x2 â€” and the runner test stores one trainee, so the shipped
   expectation is **`created 3 / updated 0 / skipped 4 / review 0`**. Step 1's justification
   parenthetical ("Gold Ship x2, Tokai Teio x2, no trainee rows yet") sums to 4, which is how this was
   caught: the comment and the number contradicted each other and the comment was the true one. Pinned
   by `tests/Feature/CharacterCardFetchTest.php`'s `routes the card source past the match stage and into
   the card table` test, which asserts the whole counts array rather than picking two keys.
   See spec **E-21**.
2. **`UpsertCharacterCard`, the `'records' => 'cards'` config key and `runCards()` did not ship**, all
   three superseded by A1 before dispatch. What shipped is `app/Actions/StoreCharacterCards` taking the
   whole list and returning counts, routed by
   `is_a($parserClass, CharacterCardSourceParser::class, true)` inline in `PipelineRunner::run()` beside
   the race-catalog branch, mirroring `StoreRaceCatalogSlots`. No source config entry gained a `records`
   key, so the `Shape:` comment still names exactly five keys, and a fourth parser interface would route
   the same way.
3. **Step 5's expected hash had already rotated.** It predicted `"character-cards":"679f7c2e"`; the
   manifest answered `e9e9ee6d` the same day. Step 5's own escape clause ("if it now differs, use the
   new hash in **both** source entries and record the date") is what was followed, and both
   `config/uma.php` entries now pin `e9e9ee6d`. Step 6's code block above still shows the withdrawn URL
   â€” it is a record of what was briefed, not a value to copy. See spec **E-20**, which also carries the
   finding that the withdrawn URL still answers 200: a stale hash serves silent stale data, which is
   trunk's **KI-24** and is pointed at from the new entry's hash note.

Two behaviours the brief did not name, settled in review and now pinned by tests, because a later task
will meet them and should not rediscover them:

- **A card re-parents when its char ref resolves elsewhere.** `umamusume_id` is in the update payload, so
  a card whose `char_external_ref` now names a different trainee moves and reports `updated`, not
  `created`. Accepted for engine-owned rows: `card_id` is the identity, the source owns the mapping, a
  Trainer's own correction is already protected by the card-grain `is_manual` skip, and refusing to move
  would strand a mis-attached card with no repair path but a manual edit. Consequence **Task 8 must
  decide**: `unconfirmed` is never written by a fetch, so a card that moves keeps a human verdict
  recorded about the *old* association. Invalidate it on ownership change, key the verdict on
  `(card_id, umamusume_id)`, or accept and document â€” do not solve it by refusing to move.
- **An ambiguous char ref is a stop, not a tie-break.** `umamusume.external_ref` is indexed and
  **nullable, not unique** (`2026_09_29_120000_add_external_ref_to_umamusume_table.php`), and
  `PromoteMatchedRecord` preserves the ref on the old row while creating a new one, so a source rename
  can put two trainees behind one ref. The store resolves with `->get(['id', 'is_manual'])` and skips the
  record when more than one row answers. Ordering was rejected as a fix: a deterministic coin-flip still
  attaches up to 107 cards to the wrong woman, confidently. A unique index is Architect's call, not this
  task's.

And one knock-on for Task 9's own reading of its output: `UmaFetch` and `UmaReparse` no longer print
`skipped (manual)`. On the cards path the same integer folds four reasons â€” roster-absent, trainee
manual, card manual, ref ambiguous â€” so both commands now say `skipped (manual or unresolved)`. Task 9's
"any nonzero skipped means a char ref failed to resolve" instruction is closer to true than it was, and
still not exactly true; if it has to act differently on "a Trainer wrote this" versus "the source mapping
is broken", the cheap form is one extra integer in the counts array, not a counter per reason.

---

## docs/deprecated/requests/2026-09-29-catalog-roster-report.md


> **Read in order.** Step 3 of three, and the latest word on this workstream. It closes
> [`â€¦-and-trainee-selector.md`](2026-09-29-catalog-roster-and-trainee-selector.md), the request, and
> measures the outcome of
> [`â€¦-selector-plan.md`](2026-09-29-catalog-roster-and-trainee-selector-plan.md), the plan. If you
> reach any one of the three first, this is the file that says what actually shipped.

Date: 2026-09-29
Slice: the `feat/catalog-roster-and-trainee-selector` branch, closing as `236e3a5`
Scope: Task 13, the branch's closing gate. Every number below was measured on this machine on
2026-09-29 against the live source and the populated database, and each measurement names the
command or the page that produced it.

---

### 1. What landed

The character-card layer and the searchable trainee selector, across two merges.

`bc11d9b` brought the card layer onto master. `236e3a5` (this session) merged the branch's three
remaining commits forward:

```
236e3a5 Merge branch 'feat/catalog-roster-and-trainee-selector'
4addcd6 fix(ui,http): the payload reaches every trainee the select offers, and ArrowUp opens on the last row
79d7f62 docs(plan): remove the card precondition Task 12 shipped from the brief
1c44698 fix(ui,http): the run form stays completable, and Enter means submit
```

The merge was **not** a rebase. `b63e111` is already an ancestor of master through `bc11d9b`, so
re-basing would have rewritten landed history. `git merge` produced no conflicts: the incoming five
files and the concurrent working-tree changes were disjoint, which was checked with
`Compare-Object` over both path lists before merging rather than discovered afterwards.

**Migrations** (all three from `bc11d9b`):

| Migration | Adds |
|---|---|
| `2026_09_29_120000_add_external_ref_to_umamusume_table` | `umamusume.external_ref`, indexed, nullable, not unique |
| `2026_09_29_120100_create_character_cards_table` | the card table, 14 columns |
| `2026_09_29_120200_add_character_card_id_to_training_runs_table` | the nullable run â†’ card reference |

**Sources.** `config/uma.php` declares four, of which this slice added one:

| Key | Document | Added by |
|---|---|---|
| `gametora-characters` | `character-cards.<hash>.json` (trainee grain) | pre-existing |
| `gametora-character-cards` | the same document, card grain | this slice |
| `gametora-race-catalog` | `race_instances.<hash>.json` | this slice |
| `gametora-skills` | `skills.<hash>.json`, manifest-resolved | `ADR-0011`, already on master |

---

### 2. Counts against the export

Fetched live on 2026-09-29: `character-cards.e9e9ee6d.json`, HTTP 200, 251,294 bytes, 268 rows.

| Measure | Export | Database | Agrees |
|---|---|---|---|
| Distinct `char_id` with a `release_en` | 68 | 68 `release_status = GlobalReleased` | yes |
| `char_id` with no `release_en` | 67 | 67 `JapanOnly` | yes |
| Rows carrying `release_en` | 107 | 107 cards | yes |
| Total `char_id` across the whole export | 135 | 135 trainees | yes |
| Cards flagged `is_debut_form` | â€” | 68 | one per Global trainee |
| Cards flagged `unconfirmed` | â€” | **0** | see Â§4 |

Measured with `App\Models\Umamusume::where('release_status', â€¦)->count()` and
`App\Models\CharacterCard::count()` against `database/database.sqlite`.

**A correction to the plan's own guard.** Task 13 Step 1 asserts
`if ($count !== 68) { â€¦ REFUSING TO COPY â€¦ }` on `Umamusume::count()`. That assertion cannot pass:
the character parser emits one record per `char_id` across the **whole** export, which the plan's
own Task 3 records as 135. 68 is the Global subset. The guard's intent held â€” 68 Global, 107
cards, 68 debut cards, 0 unconfirmed, all exactly as predicted â€” so the copy proceeded, but the
guard as written would have refused a correctly populated database. The count it should assert is
`where('release_status', 'GlobalReleased')->count()`, or 135 on the unfiltered count.

`skills` measured 1,910 rows, of which 623 are `GlobalReleased`, matching `ADR-0011`'s table
exactly.

---

### 3. Sources and tier

GameTora `character-cards` is the machine-readable source, Tier B. `docs/SOURCE-OF-TRUTH.md` Â§5
records the rule `ADR-0008` restates: a Tier B field needs a Tier A witness before it becomes app
data, and `character_cards.unconfirmed` is the column that carries an un-witnessed row.

No card is flagged `unconfirmed` today, because no card has been through the cross-check against
`umamusu.wiki` and Game8. The mechanism is present and unused, not absent and forgotten.

---

### 4. Unverified entries

**None flagged, and that is a statement about the queue rather than about the data.** All 107 cards
have `unconfirmed = false` by default; `unconfirmed` is never written by a fetch, so a zero here
means no human verdict has been recorded either way. Every Global card is therefore unverified in
the sense `ADR-0008` means, and the catalog hides nothing today because there is nothing hidden to
show.

---

### 5. Deviations from the request

Each of these is a place the shipped result differs from what the request or the plan's acceptance
table predicted, because the data disagreed with the prediction.

1. **105 cards, not ~140.** 107 after the source rotated. The plan's own Task 7 Step 5 recorded the
   hash change from `679f7c2e` to `e9e9ee6d`; both config entries now pin `e9e9ee6d`.
2. **`Fe` and `Fenomeno` both return "No trainee or card found."** Corrected as errata E-5/E-6
   before the browser pass. Measured: 0 options for each, with the no-results paragraph rendered and
   the listbox present but empty.
3. **The `F` query returns "10 of 14", and the plan's expected list cannot fit.** The plan expected
   Fine Motion and Fuji Kiseki as trainee groups "plus 9 cards whose title begins with F". Measured
   against the database: **14** cards match (10 whose bracket-stripped title starts with F, plus
   4 more belonging to the two F-named trainees â€” `[Noble Seamair]`, `[Titania]`, `[Shooting Star
   Revue]`, `[SuccÃ¨s Ã‰toilÃ©]`). The plan's list also omitted `[Fiery Aqua Vitae]`, a tenth F-titled
   card. With a cap of 10, `[Fast as Lightning]`, `[Fair Lady of the Waves]` and `[Fluttertail
   Spirit]` are cut. The cap and the keep-typing line behave as specified; the expectation was
   arithmetically impossible.
4. **Group headers are `role="presentation"`, not `role="group"`.** The plan's Step 2 table
   expected `role="group"`. This is a deliberate, documented and test-pinned choice, not a defect:
   `resources/js/trainee-combobox.ts:202-207` records that a group must own its options to be named
   (ARIA does not take a group's name from its content), the header here is a sibling of the
   options, and `TraineeSelectorTest.php:689-695, 773` pins both the reason and the string. The
   plan's expectation is the thing that is wrong.
5. **The `Â·` separator, not an em dash**, in each option's accessible name. Required by the
   em-dash rule; `RenderedCopyHygieneTest` fails a dash in Blade source outside comments.
6. **"1 form" / "2 forms", not a bare `0`.** D-220 and the `N/A`-plus-tooltip rule.
7. **Heading levels one below the request's naming.** Page `h1`, trainee `h2`, card `h3`.
8. **The catalog filter stays server-side behind a submit.** It is a GET form; typing does not
   filter live. Confirmed: `?search=ruin` returns 200 with exactly one `h2` (Gold Ship) and the two
   expected card titles.
9. **No collapse control.** The row count is the reason; the fold-list duplication defect is what
   a second control would reintroduce.
10. **One document is fetched twice**, once per grain, by two config entries. `ADR-0008` calls this
    "the sentinel at two grains" and rules it acceptable; it is a real duplicate request, not an
    accident.
11. **JP per-card dates are not stored.** They are read to derive `is_debut_form` and dropped,
    per `ADR-0008`.

---

### 6. New defects found

#### 6.1 `[unsigned]` is stored and rendered as a card title

`character_cards` holds card `103601` (Air Shakur) with `title = "[unsigned]"` and a real
`release_en` of `2026-06-18`. The literal string `[unsigned]` is the source's own value â€” over the
268-row export, `title` equals `unsigned` on one row â€” so this is **not** a parser defect and not
something a parser should rewrite. But a card with a placeholder name is being presented to a
Trainer as a real Global costume, on the catalog list, on the detail page, and in the run-form
selector, with a dated release and a rarity beside it. The Global-only rule admits it because it
has a `release_en`; nothing checks that it also has a *name*.

This is a G-16 / D-30 problem: the tool is rendering a placeholder as though it were content, and
D-220's "absent is absent, never an empty slot" has no way to express "this row has no title yet".
Filed as **KI-38**.

#### 6.2 `SkillFactory` derives `match_key` with a different algorithm than production

`database/factories/SkillFactory.php:24` computes
`mb_strtolower(str_replace('-', '', Str::slug($name)))`. Production computes it with
`NameNormalizer::normalize()` (NFKD, strip combining marks, strip five specific characters). The
two disagree on punctuation, and the factory is the one that drops it.

Measured over 200 sampled client-named skills: **119 produce a different key**. Examples:

| Name | Factory key | Normalizer key |
|---|---|---|
| `Warning Shot!` | `warningshot` | `warningshot!` |
| `Empress's Pride` | `empressspride` | `empress'spride` |
| `1st Place Kissâ˜†` | `1stplacekiss` | `1stplacekissâ˜†` |
| `Class Rep + Speed = Bakushin` | `classrepspeedbakushin` | `classrep+speed=bakushin` |

Over 135 trainee names, 3 differ (`Mr. C.B.`, `K.S.Miracle`, `Curren Bouquetd'or`).

So a search test that seeds a skill through the factory is testing a key that production never
writes. This is the same class of defect as `KI-23b`, where a factory-seeded `name_ja` meant the
parser's wrong key stayed invisible: the fixture agreed with the code because both were wrong in
the same way. Filed as **KI-39**.

#### 6.3 `NameNormalizer`'s fold has a Unicode ceiling

`normalize()` folds by NFKD, then removes combining marks, then removes exactly five characters
(`NameNormalizer::FOLDED_CHARACTERS`). NFKD does not decompose the Latin ligature letters or the
stroked letters, and none of them are in that list, so they survive into the match key:

| Pair | Result |
|---|---|
| `Cafe` / `CafÃ©` | match |
| `El Condor` / `El CÃ³ndor` | match |
| `Tokai` / `TÅkai` | match |
| `Straights` / `StrÃ¦ight` | **no match** |
| `Odawara` / `Ã˜dawara` | **no match** |

Severity is low today and the measurement is what makes that claim rather than an assumption:
`Ã¦` appears 375 times in the export, but only in `name_tw` (144 rows), `title_tw` (81), `title_jp`
(45) and `title_ko` (4) â€” **none of which this tool stores or searches**, and `Ã¸`, `Ä‘`, `Å‚`, `Ã¾`,
`ÃŸ`, `Å“` appear **0 times**. No `[Global]` English name currently folds wrong.

It is filed because the failure mode is invisible when it arrives: a name that fails to match reads
as "no such trainee", not as "our normalizer does not cover this letter". Filed as **KI-40**.

#### 6.4 The selector's ordering is not a total order

`TrainingRunController::create()` orders the roster with `->orderBy('name')` and no tiebreaker
(`app/Http/Controllers/TrainingRunController.php:82`). Measured: 0 duplicate names, and 135
distinct names across 135 trainees, so the order is total **in fact today**. It is not total **by
construction**, and the tiebreak would be SQLite's row order, which can change across a
re-import. Latent, not live. Filed as **KI-41**.

---

### 7. Browser pass

Served `database/scratch-catalog.sqlite` (a copy) on port 8099 via `php artisan serve`. The copy
was made only after the population guard, and the shared `database/database.sqlite` was re-checked
afterwards and still held 135 trainees and 107 cards. The server was stopped by PID and the port
confirmed closed (`Unable to connect to the remote server`) before the gates ran.

#### 7.1 Selector, `http://127.0.0.1:8099/training-runs/create`

| Input | Observed | Expected | |
|---|---|---|---|
| (empty) | 10 options, `10 of 107 (keep typing)`, newest first, grouped by trainee | same | pass |
| `F` | 10 options, `10 of 14 (keep typing)` | see Â§5.3 | deviation |
| `Fe` | 0 options, "No trainee or card found." | same | pass |
| `Fenomeno` | 0 options, "No trainee or card found." | same | pass |
| `Fu` | Fuji Kiseki group with `[Shooting Star Revue]`, `[SuccÃ¨s Ã‰toilÃ©]`; also Agnes Digital `[Full-Color Fangirling]` | Fuji Kiseki + her forms | pass |
| `RUN` | `[RUN! RUIN! LAUNCHER!]` and `[Run & Win]` | the former under Gold Ship | pass |
| `fenomeno` | 0 options | same as `Fenomeno` | pass |
| `ã‚¹ãƒšã‚·ãƒ£ãƒ«` | 3 options, `3 matches`, one group `Special Week ã‚¹ãƒšã‚·ãƒ£ãƒ«ã‚¦ã‚£ãƒ¼ã‚¯` | Special Week + her three forms | pass |
| `zzz` | 0 options, "No trainee or card found." | same | pass |

Screenshot: `docs/design-research/verification/task13-f-query.png` (the `F` query with its
`10 of 14` line and the trainee group headers).

#### 7.2 Keyboard and ARIA (G-11)

| Path | Observed |
|---|---|
| ArrowDown Ã—1 from open | `aria-activedescendant` â†’ `trainee-option-8` |
| ArrowDown Ã—2 | â†’ `trainee-option-73` |
| ArrowUp from the first option | wraps to the **last** (`trainee-option-99`) |
| ArrowDown past the last | wraps to the **first** (`trainee-option-107`) |
| Enter | label becomes `Fuji Kiseki Â· [SuccÃ¨s Ã‰toilÃ©]`, `aria-expanded` â†’ `false`, and **both** hidden fields set: `umamusume_id=5`, `character_card_id=73` |
| Escape | popup closes, `aria-expanded` â†’ `false`, **input value left as typed** |
| Focus with no click | `aria-expanded` â†’ `true`, 10 options rendered |

Attributes on the input: `role="combobox"`, `aria-controls="trainee-listbox"`,
`aria-autocomplete="list"`, `aria-activedescendant` tracking and flipping correctly.

The no-script path is intact: the original `<select name="umamusume_id">` with all 68 Global
trainees remains in the DOM, disabled by the script when the script runs.

#### 7.3 Catalog and selector cannot disagree

| Check | Result |
|---|---|
| Selector's option count vs catalog | both read the same 107 cards from the same payload query |
| H1-equivalent count | "Showing 1 to 10 of **68** results" â€” equals the export's distinct Global `char_id` |
| `?search=ruin` | HTTP 200, exactly 1 trainee (`Gold Ship`), 2 card titles |
| Debut leads every card list | Inari One renders `[Edomurasaki]` (debut, May 28 2026) before `[Golden Dream]` (Sep 7 2026) |
| Gold Ship order | `[Red Strife]` (Jun 26 2025) before `[RUN! RUIN! LAUNCHER!]` (Jul 2 2026), debut first |

#### 7.4 C-6 against the new query shape

30 sequential requests to `/umamusume`, `Stopwatch` around each:

```
min    = 115.8 ms
median = 135.4 ms
p95    = 242.1 ms
max    = 329.2 ms
```

**Median 135.4 ms against C-6's 200 ms budget**, measured on `php artisan serve` â€” a single-worker
development server with no opcache, which is slower than any deployment this tool has. The honest
statement is the one the plan asked for: under budget at one-fifteenth the 1,000-row reference
size, with one eager card load per page of 10 trainees. **The 1,000-row budget is not claimed.**

---

### 8. Gates

Run in `CONSTRAINTS.md`'s order, on the merged tree.

| Gate | Result |
|---|---|
| `vendor/bin/pint --dirty --format agent` | `{"tool":"pint","result":"passed"}` |
| `vendor/bin/pint --test --format agent` | `{"tool":"pint","result":"passed"}` |
| `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | `[OK] No errors` |
| `php artisan test --compact` | **782 passed, 2 skipped**, 2715 assertions, 85.06s |
| `composer lore` | 115 hits, 57 exempt lines |
| `composer lore-code` | 8 hits |
| `composer audit` | No security vulnerability advisories found |
| `npm audit --omit=dev` | found 0 vulnerabilities |

`make lore` is unavailable on this host (GNU make absent â€” the `KI-4` gap the plan itself records),
so the lore gate was run as `composer lore`, which is the same `tools/lore.php` the Makefile target
wraps.

**The lore counts are unchanged by this merge, and that was checked rather than assumed.** All 8
`lore-code` hits are in `config/queue.php`, `config/scenarios.php` (4), `ReviewFormAccessibilityTest`,
`ReviewQueueTest`, `ShopPurchasePayloadTest` and `gametora-skills.sample.json` â€” none of which the
merge touched. The 5 merged files were separately grepped: `create.blade.php` and
`TraineeSelectorTest.php` are clean; the hits in `TrainingRunController.php`, `trainee-combobox.ts`
and the plan file are pre-existing prose and a pre-existing plan, not new copy.

**`skipped 2`, not `skipped 5`.** Recorded because the plan lists "the brief's `skipped 5`" among
the counts that failed to hold; the current suite skips 2, both Playwright-driver-gated.

#### C-5, on the scratch file

```
migrate:fresh --seed   â†’ UmamusumeSeeder, SkillSeeder, ScenarioSlotSeeder all DONE
migrate:rollback --step=3
  2026_09_29_120200_add_character_card_id_to_training_runs_table   44.90ms DONE
  2026_09_29_120100_create_character_cards_table                     4.05ms DONE
  2026_09_29_120000_add_external_ref_to_umamusume_table            15.68ms DONE
```

Re-migrated, then rolled back four to reach `ADR-0011`'s migration, whose `down()` is the only one
here that drops an index, a unique constraint and nine columns together:

```
  2026_09_29_021157_add_reference_fields_to_skills_table            62.80ms DONE
```

Scratch file deleted. Shared `database/database.sqlite` re-verified after: 135 trainees, 107 cards.

---

### 9. Not built

- `docs/frontend-review/` does not exist and was not created.
- `KNOWN-ISSUES.md` gained four entries (KI-38 to KI-41) and nothing else in it was rewritten.
- `docs/scenarios/**`, `docs/UMAMUSUME_REFERENCE.md`, `PLAN.md`, `Makefile` and
  `docs/GATE-REGISTRY.md` were not modified.
- No support-card surface exists or was implied. `ADR-0005` remains declined by R37.
- **No images, and no trainee detail-page fields.** `KI-33` (a trainee's own innate and unique
  skills are published by the source and stored nowhere) and `KI-35` (the detail page is a metadata
  stub) remain **open**. They are the subject of `docs/adr/0012-card-detail-fields-and-images.md`,
  which records the decisions and the measurements but widens no schema until the owner rules on it.

---

### 10. Open for the owner

1. **The em dash rule and `Â·`.** Should `DESIGN.md` gain an explicit `Â·` exception, or does the
   middle dot stay a local judgment call at each site?
2. **`pageSize` 25.** The catalog paginates at 10; the default was 25 before this slice. Should it
   rise now that a row is taller?
3. **KI-38, `[unsigned]`.** The source states that string. Options: treat a placeholder title as
   absent and exclude the card from name-search while keeping the row; flag it `unconfirmed`; or
   accept it as a dated snapshot of an upstream placeholder. The first is the smallest change that
   stops a Trainer seeing a placeholder as a costume.
4. **KI-39, the factory's `match_key`.** Fixing it means `SkillFactory` calling `NameNormalizer`,
   which will change keys in every test that seeds a skill. Cheap, and it removes a class of false
   green.

---

## docs/deprecated/data/2026-09-29-global-roster-crosscheck.md


**Dated observation, read 2026-09-29.** This file records what the two Tier A witnesses said on
that day, not what they say now: banner cycles move, and a card listed `N/A` today carries a date
the next time the page is edited. Read it as a snapshot with the same standing as the engine's
dated snapshot policy (`ADR-0003` Amendment R3), and re-run before relying on any row.

It exists because of one line in `docs/SOURCE-OF-TRUTH.md` Â§5: *"A Tier B dataset (GameTora) needs
A- or S-tier confirmation before a claim becomes app data."* Every card in
`character_cards` comes from a Tier B export, so the title, the Global date and the rarity a
Trainer reads on screen are Tier B claims until a Tier A page attests them. The owner ruled the
confirmation **deep** â€” all of them, not a spot-check â€” and this file is that crossing.

### Sources

| Role | Tier | Page | Read |
|---|---|---|---|
| Tier B dataset | [B] | `https://gametora.com/data/umamusume/character-cards.e9e9ee6d.json` | 2026-09-29 UTC |
| Tier A witness | [A] | `https://umamusu.wiki/Game:List_of_Trainees` | 2026-09-29 UTC |
| Tier A witness | [A] | `https://game8.co/games/Umamusume-Pretty-Derby/archives/535926` | 2026-09-29 UTC |

The GameTora URL was re-resolved the same day through the publisher's manifest
(`https://gametora.com/data/manifests/umamusume.json`), which reported
`"character-cards": "e9e9ee6d"` â€” the same hash `config/uma.php` pins, so the pinned document and
the live document were one revision at read time. `KI-24` is the reason the manifest is checked
rather than the pin trusted: a stale hash answers `200` with the superseded body.

"2026-09-29 UTC" is the UTC date, which is what this file's dated name follows (the repo's
store-UTC rule); the host's own wall clock had already passed midnight into 2026-09-30 at the
moment the three bodies were saved, so a reader comparing this date to local file times is not
looking at a contradiction.

All three bodies were saved to the gitignored `research-scratch/data/` and read from there;
`tools/roster-crosscheck.php` fetches nothing, and edits no title.

The colon in the MediaWiki page name (`Game:List_of_Trainees`) is why these URLs are quoted rather
than linked: naive link handling drops everything before it.

### What was measured, before any comparison

```
rows=268 global=107 trainees=68
```

over `research-scratch/data/json/character-cards.json`, 251,294 bytes, by

```bash
php -r '$r=json_decode(file_get_contents("research-scratch/data/json/character-cards.json"),true); printf("rows=%d global=%d trainees=%d\n",count($r),count(array_filter($r,fn($c)=>is_string($c["release_en"]??null))),count(array_unique(array_column(array_filter($r,fn($c)=>is_string($c["release_en"]??null)),"char_id"))));'
```

`global=107` is the population this file cross-checks: **107 cards over 68 trainees**, out of 268
rows in the export. The remaining 161 rows carry no `release_en`, are `[JP-Only]`, and are out of
scope â€” this task does not cross-check a card into Global existence.

The shipped parser reads the same 107 out of the same body
(`GametoraCharacterCardParser::parse` returns 107 records, no `card_id` duplicated), so the table
below is the whole `character_cards` population at this hash â€” not a sample of it.

Two counts moved since the plan was written, and the measurement above is what this file quotes:
the plan said **105** Global cards in `ADR-0008` and the request, and was corrected to **107** when
the hash rotated `679f7c2e` â†’ `e9e9ee6d`. 107 is what the live body says today. No further
rotation was found at read time.

Source-side population, counted from the two Tier A rows this file extracted:

- `umamusu.wiki`: 268 card rows, of which **104** carry a real Global date and **164** read `N/A`
  (104 + 164 = 268). All 104 dated rows match one of the 107 Global cards; **0** match nothing.
- Game8: **105** distinct `Trainee (Costume)` pairs on the page (from 108 tiles, 3 duplicated across
  tables), and all 105 match one of the 107 Global cards; **0** match nothing. Game8's page states no
  Global date and no rarity for a costume, so it witnesses a title and nothing else.

The wiki's table covers the same 268-card universe the export does; Game8's page tiles 105 of the
107. So a card missing from one of these pages is a page not covering it, not a page refuting it â€”
which is why a missing witness flags rather than deletes, and why the two `single-source` rows below
are recorded as thin evidence rather than as a denial.

### Method

**Comparison.** Titles are compared on folded copies; the strings in the table are the sources' own,
verbatim, brackets included. Three folds, cheapest first, all recorded per row:

1. **tier 1** â€” surrounding `[ ]` stripped, case folded, whitespace runs collapsed. This is the
   rule the task brief sets, and it is why `umamusu.wiki` printing `Special Dreamer` against
   GameTora's `[Special Dreamer]` is one claim rather than a conflict.
2. **tier 2** â€” the repository's own `NameNormalizer::normalize()` applied to the same
   bracket-stripped title: NFKD, combining marks dropped, then the five characters
   `NameNormalizer::FOLDED_CHARACTERS` already drops for search (`ãƒ»`, `ï½¥`, `-`, the ASCII space and
   the ideographic space). Reused rather than re-invented so this file and the catalog cannot
   disagree about what counts as the same name.
3. **tier 3** â€” tier 2 with the ornamental glyphs `â˜† â˜… â™¡ â™¥ â™ª âœ© âœ§ ï¼‹` also removed, because a
   guide page writes `El Numero 1` where the client string is `[Elâ˜†NÃºmero 1]`.

Tier 2 and tier 3 are a stated **deviation** from the brief's single fold, and nothing rests on them
silently: every row matched above tier 1 is listed in the spelling-variant log below with the tier
that matched, and the verdict counts under the brief's literal tier-1-only rule are given beside the
counts this file delivers. Cost if the deviation is wrong: 9 cards read `two-source-confirmed` that
a strict reading would call `single-source` and therefore flag; the strict flag list is named, so an
owner who prefers it can adopt it without re-running anything.

**Verdict, keyed on `card_id` and on nothing else.** `unconfirmed` answers *"do two independent
sources attest that THIS card exists, with this title, date and rarity?"* â€” the owner's ruling of
2026-09-29. It is a property of the card, never of the trainee a source happens to attach it to, so
this table records no trainee association and no verdict is keyed on `(card_id, umamusume_id)`: a
card that re-parents keeps its verdict, because the verdict was never about the association.

- `two-source-confirmed` â€” both Tier A pages carry a row whose title matches the card's, and neither
  page disagrees with the export on date or rarity.
- `single-source` â€” exactly one Tier A page witnesses the card. The Tier B export stands with one
  witness, which is not the confirmation Â§5:152 asks for.
- `conflict` â€” the export and a Tier A page disagree on the Global date or the rarity, however many
  pages carry the card.
- `unwitnessed` â€” neither page carries the card at all. Kept as its own class so "nobody has looked
  at this one" never reads as confirmed.

**Disagreement rules, in order, numbered as the brief numbers them.**

1. **Title.** GameTora's `title_en_gl` is the `[Global]` client string, so it wins and the other
   spelling is recorded as differing â€” never edited into agreement. `CONSTRAINTS.md` C-4's
   verbatim-names bullet is what forbids the edit; the display path is where a lore guard belongs.
2. **Global release date.** `conflict`, and `unconfirmed = true`. A date is not a name: the client
   string does not arbitrate it.
3. **English trainee name.** Both spellings recorded, the client string kept, and the other form
   belongs in `umamusume_aliases` (FR-A-2) so it stays findable. Recorded below; it does not change
   a card's verdict, because a verdict is about the card.
4. **Rarity.** `conflict` likewise â€” rarity feeds the H1 max-rarity claim, so an uncrossed value is
   visible wrong data.
5. A conflict is never resolved by preference, and never by picking whichever source agrees with the
   brief.

**One judgement this file had to make, stated rather than buried.** `umamusu.wiki` prints `N/A` in
its Global-date column for 164 of its 268 rows. Where the export carries a real Global date and the
wiki row for that same title prints `N/A`, this file calls it a **date disagreement â†’ `conflict`**,
not an absence of claim: the two pages make opposite statements about whether the card reached
`[Global]`, and `[Global]` is the only precondition this table has. Cost if that is wrong: 3 cards
(`100103`, `100802`, `100902`) carry a flag they do not need. The reverse reading â€” `N/A` means the
wiki never witnessed the date, so `two-source-confirmed` â€” would assert a confirmation the page does
not give, so `conflict` is the reading this file keeps.

### The table

Generated by `php tools/roster-crosscheck.php > docs/data/roster-crosscheck-table.md`; the same rows
are embedded here so this file reads on its own. `not listed` means that page carries no row for the
card at all; `N/A` is the wiki's own cell.

| card_id | GameTora title | Game8 title | umamusu.wiki title | GameTora date | Tier A date | rarity | verdict |
|---|---|---|---|---|---|---|---|
| 100101 | [Special Dreamer] | Special Dreamer | Special Dreamer | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 100102 | [Hopp'nâ™ªHappy Heart] | Hopp'nâ™ªHappy Heart | Hopp'nâ™ªHappy Heart | 2025-10-14 | 2025-10-14 | 3 | two-source-confirmed |
| 100201 | [Innocent Silence] | Innocent Silence | Innocent Silence | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 100301 | [Peak Joy] | Peak Joy | Peak Joy | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 100302 | [Beyond the Horizon] | Beyond the Horizon | Beyond the Horizon | 2025-07-16 | 2025-07-16 | 3 | two-source-confirmed |
| 100401 | [Formula R] | Formula R | Formula R | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 100402 | [Hotâ˜†Summer Night] | Hotâ˜†Summer Night | Hotâ˜†Summer Night | 2025-10-14 | 2025-10-14 | 3 | two-source-confirmed |
| 100501 | [Shooting Star Revue] | Shooting Star Revue | Shooting Star Revue | 2025-10-02 | 2025-10-02 | 3 | two-source-confirmed |
| 100601 | [Starlight Beat] | Starlight Beat | Starlight Beat | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 100701 | [Red Strife] | Red Strife | Red Strife | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 100801 | [Wild Top Gear] | Wild Top Gear | Wild Top Gear | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 100901 | [Peak Blue] | Peak Blue | Peak Blue | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 101001 | [Wild Frontier] | Wild Frontier | Wild Frontier | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 101101 | [Stone-Piercing Blue] | Stone-Piercing Blue | Stone-Piercing Blue | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 101102 | [Saintly Jade Cleric] | Saintly Jade Cleric | Saintly Jade Cleric | 2025-09-21 | 2025-09-21 | 3 | two-source-confirmed |
| 101201 | [Azure Amazon] | Azure Amazon | Azure Amazon | 2025-09-17 | 2025-09-17 | 3 | two-source-confirmed |
| 101301 | [Frontline Elegance] | Frontline Elegance | Frontline Elegance | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 101302 | [End of the Skies] | End of the Skies | End of the Skies | 2025-07-16 | 2025-07-16 | 3 | two-source-confirmed |
| 101401 | [Elâ˜†NÃºmero 1] | El Numero 1 | Elâ˜†NÃºmero 1 | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 101402 | [Kukulkan Warrior] | Kukulkan Warrior | Kukulkan Warrior | 2025-09-21 | 2025-09-21 | 3 | two-source-confirmed |
| 101501 | [O Sole Suo!] | O Sole Suo! | O Sole Suo! | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 101601 | [Maverick] | Maverick | Maverick | 2025-08-20 | 2025-08-20 | 3 | two-source-confirmed |
| 101701 | [Emperor's Path] | Emperor's Path | Emperor's Path | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 101801 | [Empress Road] | Empress Road | Empress Road | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 101802 | [Quercus Civilis] | Quercus Civilis | Quercus Civilis | 2025-08-28 | 2025-08-28 | 3 | two-source-confirmed |
| 101901 | [Full-Color Fangirling] | Full-Color Fangirling | Full-Color Fangirling | 2025-11-19 | 2025-11-19 | 3 | two-source-confirmed |
| 102001 | [Reeling in the Big One] | Reeling in the Big One | Reeling in the Big One | 2025-09-07 | 2025-09-07 | 3 | two-source-confirmed |
| 102301 | [pf. Winning Equation...] | pf. Winning Equation... | pf. Winning Equation... | 2025-07-10 | 2025-07-10 | 3 | two-source-confirmed |
| 102401 | [Scrambleâ˜†Zone] | Scramble Zone | Scrambleâ˜†Zone | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 102402 | [Sunlight Bouquet] | Sunlight Bouquet | Sunlight Bouquet | 2025-08-28 | 2025-08-28 | 3 | two-source-confirmed |
| 102601 | [MB-19890425] | MB-19890425 | MB-19890425 | 2025-07-02 | 2025-07-02 | 3 | two-source-confirmed |
| 102701 | [Down the Line] | Down the Line | Down the Line | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 102801 | [Buono â˜† Alla Moda] | Buonoâ˜†Alla Moda | Buono â˜† Alla Moda | 2025-11-11 | 2025-11-11 | 3 | two-source-confirmed |
| 103001 | [Rosy Dreams] | Rosy Dreams | Rosy Dreams | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 103201 | [tach-nology] | Tach-nology | tach-nology | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 103501 | [Get to Winning!] | Get to Winning! | Get to Winning! | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 103701 | [Meisterschaft] | Meisterschaft | Meisterschaft | 2025-10-30 | 2025-10-30 | 3 | two-source-confirmed |
| 103801 | [Fille Ã‰clair] | Fille Ã‰clair | Fille Ã‰clair | 2025-07-27 | 2025-07-27 | 3 | two-source-confirmed |
| 104001 | [Authentic / 1928] | Authentic / 1928 | Authentic / 1928 | 2025-10-07 | 2025-10-07 | 3 | two-source-confirmed |
| 104101 | [Blossom in Learning] | Blossom in Learning | Blossom in Learning | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 104501 | [Murmuring Stream] | Murmuring Stream | Murmuring Stream | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 104601 | [LOVEâ˜†4EVER] | LOVEâ˜†4EVER | LOVEâ˜†4EVER | 2025-08-11 | 2025-08-11 | 3 | two-source-confirmed |
| 105001 | [Nevertheless] | Nevertheless | Nevertheless | 2025-08-03 | 2025-08-03 | 3 | two-source-confirmed |
| 105201 | [Bestest Prize â™ª] | Bestest Prize | Bestest Prize â™ª | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 105601 | [Risingâ˜†Fortune] | Rising Fortune | Risingâ˜†Fortune | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 105602 | [Lucky Tidings] | Lucky Tidings | Lucky Tidings | 2025-11-06 | 2025-11-06 | 3 | two-source-confirmed |
| 105801 | [Turbulent Blue] | Turbulent Blue | Turbulent Blue | 2025-10-21 | 2025-10-21 | 3 | two-source-confirmed |
| 106001 | [Poinsettia Ribbon] | Poinsettia Ribbon | Poinsettia Ribbon | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 106101 | [King of Emeralds] | King of Emeralds | King of Emeralds | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 103002 | [Vampire Makeover!] | Vampire Makeover! | Vampire Makeover! | 2025-11-24 | 2025-11-24 | 3 | two-source-confirmed |
| 104502 | [Chiffon-Wrapped Mummy] | Chiffon-Wrapped Mummy | Chiffon-Wrapped Mummy | 2025-11-24 | 2025-11-24 | 3 | two-source-confirmed |
| 103901 | [Princess of Pink] | Princess of Pink | Princess of Pink | 2025-12-01 | 2025-12-01 | 3 | two-source-confirmed |
| 102501 | [Creeping Shadow] | Creeping Shadow | Creeping Shadow | 2025-12-08 | 2025-12-08 | 3 | two-source-confirmed |
| 101702 | [Archer by Moonlight] | Archer by Moonlight | Archer by Moonlight | 2025-12-14 | 2025-12-14 | 3 | two-source-confirmed |
| 104002 | [Autumn Cosmos] | Autumn Cosmos | Autumn Cosmos | 2025-12-14 | 2025-12-14 | 3 | two-source-confirmed |
| 104801 | [Jokester â˜† Vibes] | Jokester â˜† Vibes | Jokester â˜† Vibes | 2025-12-18 | 2025-12-18 | 3 | two-source-confirmed |
| 105901 | [Off the Line] | Off the Line | Off the Line | 2025-12-28 | 2025-12-28 | 3 | two-source-confirmed |
| 100602 | [Ashen Miracle] | Ashen Miracle | Ashen Miracle | 2026-01-05 | 2026-01-05 | 3 | two-source-confirmed |
| 102302 | [Rouge Caroler] | Rouge Caroler | Rouge Caroler | 2026-01-05 | 2026-01-05 | 3 | two-source-confirmed |
| 102201 | [Noble Seamair] | Noble Seamair | Noble Seamair | 2026-01-15 | 2026-01-15 | 3 | two-source-confirmed |
| 102101 | [Fast as Lightning] | Fast as Lightning | Fast as Lightning | 2026-01-22 | 2026-01-22 | 3 | two-source-confirmed |
| 101502 | [New Year, Same Radiance!] | New Year, Same Radiance! | New Year, Same Radiance! | 2026-01-29 | 2026-01-29 | 3 | two-source-confirmed |
| 105202 | [New Year â™ª New Urara!] | New Year â™ª New Urara! | New Year â™ª New Urara! | 2026-01-29 | 2026-01-29 | 3 | two-source-confirmed |
| 106901 | [Strength in Full Bloom] | Strength in Full Bloom | Strength in Full Bloom | 2026-02-11 | 2026-02-11 | 3 | two-source-confirmed |
| 102602 | [CODE: ICING] | CODE: ICING | CODE: ICING | 2026-02-18 | 2026-02-18 | 3 | two-source-confirmed |
| 103702 | [Precise Chocolatier] | Precise Chocolatier | Precise Chocolatier | 2026-02-18 | 2026-02-18 | 3 | two-source-confirmed |
| 107101 | [Crystalline] | Crystalline | Crystalline | 2026-02-25 | 2026-02-25 | 3 | two-source-confirmed |
| 103301 | [Starry Nocturne] | Starry Nocturne | Starry Nocturne | 2026-03-05 | 2026-03-05 | 3 | two-source-confirmed |
| 106201 | [Clippety-Tippety-Clop] | Clippety Tippety Clop | Clippety-Tippety-Clop | 2026-03-12 | 2026-03-12 | 2 | two-source-confirmed |
| 106801 | [Gilded Shrine to Glory] | Gilded Shrine to Glory | Gilded Shrine to Glory | 2026-03-12 | 2026-03-12 | 3 | two-source-confirmed |
| 106701 | [Natural Brilliance] | Natural Brilliance | Natural Brilliance | 2026-03-22 | 2026-03-22 | 3 | two-source-confirmed |
| 107401 | [Brunissage Line] | Brunissage Line | Brunissage Line | 2026-03-26 | 2026-03-26 | 3 | two-source-confirmed |
| 100502 | [SuccÃ¨s Ã‰toilÃ©] | SuccÃ¨s Ã‰toilÃ© | SuccÃ¨s Ã‰toilÃ© | 2026-04-05 | 2026-04-05 | 3 | two-source-confirmed |
| 102002 | [SoirÃ©e des Chatons] | SoirÃ©e des Chatons | SoirÃ©e des Chatons | 2026-04-05 | 2026-04-05 | 3 | two-source-confirmed |
| 105101 | [Layered Petals] | Layered Petals | Layered Petals | 2026-04-12 | 2026-04-12 | 3 | two-source-confirmed |
| 107201 | [Blazed Head, Covered Fists] | Blazed Head, Covered Fists | Blazed Head, Covered Fists | 2026-04-20 | 2026-04-20 | 3 | two-source-confirmed |
| 106002 | [Run & Win] | Run & Win | Run & Win | 2026-04-26 | 2026-04-26 | 3 | two-source-confirmed |
| 106102 | [Cheerleader in Noble White] | Cheerleader in Noble White | Cheerleader in Noble White | 2026-04-26 | 2026-04-26 | 3 | two-source-confirmed |
| 103101 | [Always Electrifying] | Always Electrifying | Always Electrifying | 2026-04-30 | 2026-04-30 | 3 | two-source-confirmed |
| 106401 | [Line Breakthrough] | Line Breakthrough | Line Breakthrough | 2026-05-10 | 2026-05-10 | 3 | two-source-confirmed |
| 102202 | [Titania] | Titania | Titania | 2026-05-18 | 2026-05-18 | 3 | two-source-confirmed |
| 103802 | [Ma ChÃ©rie of the New Moon] | Ma ChÃ©rie of the New Moon | Ma ChÃ©rie of the New Moon | 2026-05-18 | 2026-05-18 | 3 | two-source-confirmed |
| 103401 | [Edomurasaki] | Edomurasaki | Edomurasaki | 2026-05-28 | 2026-05-28 | 3 | two-source-confirmed |
| 104401 | [Platanus Witch] | Platanus Witch | Platanus Witch | 2026-06-04 | 2026-06-04 | 3 | two-source-confirmed |
| 101002 | [Bubblegumâ˜†Memories] | Bubblegum â˜†Memories | Bubblegumâ˜†Memories | 2026-06-11 | 2026-06-11 | 3 | two-source-confirmed |
| 105902 | [Sapphire Sojourn] | Sapphire Sojourn | Sapphire Sojourn | 2026-06-11 | 2026-06-11 | 3 | two-source-confirmed |
| 103601 | [unsigned] | unsigned | unsigned | 2026-06-18 | 2026-06-18 | 3 | two-source-confirmed |
| 100103 | [Ruler of Japan] | Ruler of Japan | Ruler of Japan | 2026-06-25 | N/A | 3 | conflict |
| 100702 | [RUN! RUIN! LAUNCHER!] | not listed | RUN! RUIN! LAUNCHER! | 2026-07-02 | 2026-07-02 | 3 | single-source |
| 101303 | [Fair Lady of the Waves] | not listed | Fair Lady of the Waves | 2026-07-02 | 2026-07-02 | 3 | single-source |
| 105301 | [Iron Ambition] | Iron Ambition | Iron Ambition | 2026-07-07 | 2026-07-07 | 3 | two-source-confirmed |
| 109801 | [Eightfoldâ˜†Fortune] | Eightfold â˜†Fortune | Eightfoldâ˜†Fortune | 2026-07-16 | 2026-07-16 | 3 | two-source-confirmed |
| 104602 | [Twilight Triumph] | Twilight Triumph | Twilight Triumph | 2026-07-22 | 2026-07-22 | 3 | two-source-confirmed |
| 103502 | [Dream Deliverer] | Dream Deliverer | Dream Deliverer | 2026-07-27 | 2026-07-27 | 3 | two-source-confirmed |
| 105002 | [Difference Engineer] | Difference Engineer | Difference Engineer | 2026-07-27 | 2026-07-27 | 3 | two-source-confirmed |
| 102901 | [Darl'n Snowflake] | Darl'n Snowflake | Darl'n Snowflake | 2026-08-05 | 2026-08-05 | 3 | two-source-confirmed |
| 104201 | [Rocketâ˜†Star] | Rocketâ˜†Star | Rocketâ˜†Star | 2026-08-12 | 2026-08-12 | 3 | two-source-confirmed |
| 101902 | [Fanaticâ™¡Jiangshi] | Fanaticâ™¡ Jiangshi | Fanaticâ™¡Jiangshi | 2026-08-18 | 2026-08-18 | 3 | two-source-confirmed |
| 105802 | [Dot-o'-Lantern] | Dot-o'-Lantern | Dot-o'-Lantern | 2026-08-18 | 2026-08-18 | 3 | two-source-confirmed |
| 108701 | [Flare] | Flare | Flare | 2026-08-25 | 2026-08-25 | 3 | two-source-confirmed |
| 107801 | [Fluttertail Spirit] | Fluttertail Spirit | Fluttertail Spirit | 2026-09-01 | 2026-09-01 | 3 | two-source-confirmed |
| 102102 | [Raging Thunder] | Raging Thunder | Raging Thunder | 2026-09-07 | 2026-09-07 | 3 | two-source-confirmed |
| 103402 | [Golden Dream] | Golden Dream | Golden Dream | 2026-09-07 | 2026-09-07 | 3 | two-source-confirmed |
| 104901 | [Desperate Measures] | Desperate Measures | Desperate Measures | 2026-09-15 | 2026-09-15 | 3 | two-source-confirmed |
| 110001 | [Butterfly Sting] | Butterfly Sting | Butterfly Sting | 2026-09-24 | 2026-09-23 | 3 | conflict |
| 100802 | [Fiery Aqua Vitae] | Fiery Aqua Vitae | Fiery Aqua Vitae | 2026-09-28 | N/A | 3 | conflict |
| 100902 | [Nuit Ã‰toilÃ©e de Scarlet] | Nuit Ã‰toilÃ©e de Scarlet | Nuit Ã‰toilÃ©e de Scarlet | 2026-09-28 | N/A | 3 | conflict |

### Counts, with the arithmetic

Grep-verifiable against `docs/data/roster-crosscheck-table.md` (107 data rows, 1 header row, so
`grep -c '^| '` returns 108):

| verdict | rows |
|---|---|
| `two-source-confirmed` | 101 |
| `single-source` | 2 |
| `conflict` | 4 |
| `unwitnessed` | 0 |

**101 + 2 + 4 + 0 = 107** â€” the whole Global population, nothing dropped, nothing sampled.

Flag list this file hands to the apply step (`single-source` âˆª `conflict`, 6 cards):

```
100103, 100702, 100802, 100902, 101303, 110001
```

Under the brief's literal tier-1-only fold the same body reads
**92 + 11 + 4 + 0 = 107**, and the flag list grows to those 6 plus the 9 spelling-variant cards
below (`101002, 101401, 101902, 102401, 102801, 105201, 105601, 106201, 109801`). That is the cost
of the deviation in one line: 9 flags either way, and both lists are named here.

Both lines above are the tool's own, not prose derived by hand:

```bash
php tools/roster-crosscheck.php --max-tier=3 > docs/data/roster-crosscheck-table.md   # 101 + 2 + 4 + 0
php tools/roster-crosscheck.php --max-tier=1 2>&1 >/dev/null | head -1                # 92 + 11 + 4 + 0
```

`--max-tier` is the highest fold a witness may be matched on, so the tier-1 run is the brief's rule
and the tier-3 run is the one this file's table comes from. `--max-tier` outside 1..3 exits 1 rather
than quietly meaning "deeper".

### Conflict log

Every row that is not `two-source-confirmed`, plus every title that needed a deeper fold. Nothing
here is reconciled; the client string is what the app stores and the other spelling stays on the
page it came from.

#### Global date conflicts (4) â€” `conflict`, `unconfirmed = true`

| card_id | GameTora title | GameTora Global date | umamusu.wiki Global date | wiki row | Game8 |
|---|---|---|---|---|---|
| 100103 | `[Ruler of Japan]` | 2026-06-25 | `N/A` | wikitable body row 3 (of 268) | witnessed, table 2 row 6 cell 3 |
| 100802 | `[Fiery Aqua Vitae]` | 2026-09-28 | `N/A` | wikitable body row 22 (of 268) | witnessed, table 1 row 2 cell 1 |
| 100902 | `[Nuit Ã‰toilÃ©e de Scarlet]` | 2026-09-28 | `N/A` | wikitable body row 24 (of 268) | witnessed, table 1 row 3 cell 1 |
| 110001 | `[Butterfly Sting]` | 2026-09-24 | `2026-09-23` | wikitable body row 218 (of 268) | witnessed, table 1 row 4 cell 1 |

Three of these are the newest and second-newest Global dates in the export (`2026-09-28` is the
maximum over all 107 rows, `2026-09-24` the next), and the wiki has not caught up; `100103` is the
Special Week costume whose wiki row prints `N/A` in **both** date columns while the export gives it a
JP `release` of `2022-07-20` and a Global `release_en` of `2026-06-25`. `110001` disagrees by one day,
which is the shape a release that rolls past midnight in one zone makes â€” recorded as a disagreement,
not as a timezone question answered here, because the flag exists exactly for the case nobody has
settled. None of the four is reconciled: the date a Trainer sees on screen comes from Tier B alone.

#### Rarity conflicts (0)

Measured across the 107 wiki-witnessed rows: **0** disagreements. Every card's `rarity` in the export
equals the star count on the wiki's row for it (`data-sort-value`, falling back to the `â˜…` run). The
H1 max-rarity claim is therefore crossed end to end at this hash.

#### `single-source`: one Tier A page witnesses the card (2)

| card_id | GameTora title | witnessing page | silent page |
|---|---|---|---|
| 100702 | `[RUN! RUIN! LAUNCHER!]` | umamusu.wiki, wikitable body row 19 (of 268) | Game8 â€” the page carries no tile for it at all (`RUIN` appears nowhere in the saved body) |
| 101303 | `[Fair Lady of the Waves]` | umamusu.wiki, wikitable body row 35 (of 268) | Game8 â€” `Fair Lady` appears nowhere in the saved body |

Both are witnessed by one Tier A page with a date and a rarity that agree with the export, and both
are absent from the other page, which lists 105 of the 107. Absence from a page that is not a
complete costume index is weak evidence, but Â§5:152 asks for confirmation, not for a rebuttal, so
these two are flagged rather than trusted.

#### Title spelling variants (9) â€” logged, not reconciled, verdict unchanged

Every one of these is a Game8 rendering; the wiki matched at tier 1 on all 107 cards
(96 of 107 are witnessed by **both** pages at tier 1 with no deeper fold; 9 needed one; the remaining
2 have no Game8 row at all â€” 96 + 9 + 2 = 107).

| card_id | GameTora (client string) | Game8 as printed | tier | Game8 row |
|---|---|---|---|---|
| 101401 | `[Elâ˜†NÃºmero 1]` | `El Numero 1` | 3 | table 3 row 2 cell 2 |
| 102401 | `[Scrambleâ˜†Zone]` | `Scramble Zone` | 3 | table 3 row 1 cell 3 |
| 102801 | `[Buono â˜† Alla Moda]` | `Buonoâ˜†Alla Moda` | 2 | table 2 row 19 cell 3 |
| 105201 | `[Bestest Prize â™ª]` | `Bestest Prize` | 3 | table 4 row 2 cell 1 |
| 105601 | `[Risingâ˜†Fortune]` | `Rising Fortune` | 3 | table 4 row 1 cell 3 |
| 106201 | `[Clippety-Tippety-Clop]` | `Clippety Tippety Clop` | 2 | table 3 row 1 cell 1 |
| 101002 | `[Bubblegumâ˜†Memories]` | `Bubblegum â˜†Memories` | 2 | table 2 row 7 cell 3 |
| 109801 | `[Eightfoldâ˜†Fortune]` | `Eightfold â˜†Fortune` | 2 | table 2 row 6 cell 1 |
| 101902 | `[Fanaticâ™¡Jiangshi]` | `Fanaticâ™¡ Jiangshi` | 2 | table 2 row 4 cell 1 |

The brief predicted several of these as "genuine renames" â€” `Run! Fun! Watergun!` shipping as
`[RUN! RUIN! LAUNCHER!]`. Measured at this hash, that rename is real and lives in the export's
`title` field (`Run! Fun! Watergun!`) while `title_en_gl` reads `[RUN! RUIN! LAUNCHER!]`, which is
what the parser stores â€” and both Tier A pages print the Global string, so it is **not** a
cross-source title conflict here; it is the one card of that pair that Game8 omits entirely (see
`100702` above). No card needed a title tie-break under rule 1: on every one of the 107, the client
string and the wiki's English title say the same thing at tier 1.

#### English name spelling (1 trainee, 2 cards) â€” rule 3, no verdict change

`name_en` in the export reads **`TM Opera O`**; both Tier A pages read **`T.M. Opera O`**
(umamusu.wiki wikitable body rows 38 and 39; Game8 table 2 row 27 cell 1 and table 2 row 14 cell 3).
Both spellings are recorded here and the client-side string stays as stored, per rule 1's reasoning
about not editing data â€” but a Tier B field is not a client string in the way `title_en_gl` is, and
here it is the two-to-one outlier. This is a `umamusume` name question, not a card question: the
cards `101501` and `101502` are `two-source-confirmed` either way, and the alias row that would make
`T.M. Opera O` findable belongs to `umamusume_aliases` (FR-A-2). Task 9's store step has already run,
so adding it is a separate write against shared state, named for the owner rather than done here.

### What this says about KI-38

`KNOWN-ISSUES.md` KI-38: *"A card with the source's placeholder title is stored and rendered as a
real Global costume."* Cross-checked from this side, the placeholder population at this hash is
**exactly one card**: `103601`, `[unsigned]`, Global date `2026-06-18`, rarity 3. It is the only
title in the 107 that matches a placeholder shape (`unsigned|unconfirmed|placeholder|TBA|N/A|???`).

The finding that matters is that **both Tier A witnesses repeat it**: `umamusu.wiki` wikitable body
row 89 prints `unsigned` with the same date and the same rarity, and Game8's page prints the tile
`Air Shakur (unsigned)` at table 2 row 7 cell 1. So card `103601` comes out
**`two-source-confirmed`**, and KI-38's fix candidate (b) â€” *"flag the card `unconfirmed` so the
cross-check queue surfaces it"* â€” would not surface it: the queue is exactly the thing that has now
looked at it and found two independent pages agreeing with the export. The placeholder is upstream
and shared, not a GameTora artifact, so the remedy has to be the display path (candidate (a)), or an
admission rule that a title which is a placeholder is not a name. Not fixed here: Task 8 owns the
cross-check, not the admission rule. It also means `[unsigned]` is a **verified** row â€” any later
change that flags or hides it must not be justified by this file.

### Apply step (written, not run)

Task 8 produces the verdicts. Writing them into `character_cards.unconfirmed` is a second, separate
action, and Task 9's live fetch already ran without it â€” so this is the procedure, rehearsed on a
scratch database, **unrun against `database/database.sqlite`**. That file is another session's live
state and this branch's standing rule is never to create, mutate or `migrate:fresh` it; a write
against shared state is an owner action.

Preflight, on the database the owner means to apply to:

```sql
SELECT COUNT(*) FROM character_cards;                                   -- expect 107
SELECT COUNT(*) FROM character_cards WHERE unconfirmed = 1;              -- expect 0
SELECT card_id FROM character_cards WHERE is_manual = 1
   AND card_id IN (100103, 100702, 100802, 100902, 101303, 110001);      -- expect none; each hit is one card the guard below will not touch
SELECT COUNT(*) FROM character_cards
 WHERE card_id IN (100103, 100702, 100802, 100902, 101303, 110001);      -- expect 6
SELECT source_url, COUNT(*) FROM character_cards GROUP BY source_url;    -- expect one row: the e9e9ee6d URL under Sources
```

The last two are the checks the totals cannot do. A population can be exactly 107 rows and still be
missing `100902`, because a hash rotation replaces cards as well as adding them â€” `COUNT(*)` agrees
and the flag list is silently shorter than the six verdicts. And a `source_url` that is not the one
this file names means the rows were written from a Tier B body this cross-check never read: the
verdicts would then be about a stale roster, which is the exact failure `config/uma.php`'s pinned
hash exists to make visible. If either returns something unexpected, stop and re-run
`php tools/roster-crosscheck.php` against the body that produced those rows before applying anything.

All five statements were run against `database/scratch-catalog.sqlite` and answered
`107 / 0 / 6 / one source_url row, the e9e9ee6d body, 107 cards`. The third SELECT returns `100103`
there and must return **none** on the database the owner applies to: the rehearsal below makes that
one card `is_manual` on purpose, to show the guard refusing it.

Apply â€” the id list is the six verdicts above, copied from this file rather than recomputed by the
statement, so what the statement writes is exactly what was reviewed:

```sql
UPDATE character_cards
   SET unconfirmed = 1,
       updated_at  = CURRENT_TIMESTAMP
 WHERE is_manual = 0
   AND card_id IN (100103, 100702, 100802, 100902, 101303, 110001);
```

Expected: **0 flagged â†’ 6 flagged** over 107 rows, `updated_at` moved on those 6 only. The
`is_manual = 0` term is the FR-B-4 guard: a card the Trainer corrected by hand is hers, and a
cross-check verdict does not overwrite her.

Rollback, same guard and same list:

```sql
UPDATE character_cards
   SET unconfirmed = 0,
       updated_at  = CURRENT_TIMESTAMP
 WHERE is_manual = 0
   AND card_id IN (100103, 100702, 100802, 100902, 101303, 110001);
```

Two properties worth knowing before running it:

- **A later fetch cannot clear these flags.** `StoreCharacterCards` deliberately leaves `unconfirmed`
  out of the columns it writes, and a card it skips is left alone. The flag survives a re-fetch by
  design, which is why it had to be earned by evidence rather than guessed.
- **The flag is not about ownership.** It says the card's existence, title, date and rarity were not
  attested twice. A card that re-parents keeps its flag, and clearing it means a new dated cross-check
  file, not an edit to this one.

#### Rehearsal on scratch state

`database/scratch-catalog.sqlite` (gitignored by `/database/*.sqlite*`), migrated to HEAD, seeded
with the 68 trainees and all 107 cards from the **same** export body through the **same**
`StoreCharacterCards` action, then driven by the id list parsed out of
`docs/data/roster-crosscheck-table.md` â€” not retyped. The script lives in gitignored
`research-scratch/rehearse-apply.php` and aborts before touching anything if its connection resolves
to `database/database.sqlite`.

```
seeded trainees=68
store: created=107 updated=0 skipped=0
cards=107 flagged=0 manual=0

verdicts to flag from docs/data/roster-crosscheck-table.md: 6 -> 100103, 100702, 100802, 100902, 101303, 110001
before: cards=107 flagged=0 flagged-and-manual=0
set is_manual = 1 on card 100103 (a card the verdict table flags)
apply affected=5
after apply: cards=107 flagged=5 flagged-and-manual=0
card 100103 unconfirmed=false is_manual=true
re-apply affected=5 (idempotent)
after re-apply: cards=107 flagged=5 flagged-and-manual=0
re-fetch: created=0 updated=106 skipped=1
after re-fetch: cards=107 flagged=5 flagged-and-manual=0
undo affected=5
after undo: cards=107 flagged=0 flagged-and-manual=0
card 100103 left is_manual=true unconfirmed=false
```

Read line by line: `before: flagged=0` matches the preflight expectation. `apply affected=5`, not 6,
because the rehearsal deliberately makes one flagged card `is_manual = 1` first â€” the guard is
demonstrated, not asserted, and card `100103` stayed `unconfirmed=false` through it. The second apply
reports `affected=5` because SQLite counts the rows its `WHERE` found, not the rows whose value
moved; the effect is idempotent, since `flagged` stayed 5 and no row changed state. Re-running the
store over the same body updated 106 rows and skipped the manual one while **flagged stayed 5**: a
fetch cannot clear the verdicts. `undo` returned it to `flagged=0` and left `is_manual` alone, which
is the rollback path.

Against the real database the same statement with no manual rows in the list is 0 â†’ 6.

### Standing

GameTora is **Tier B**; `umamusu.wiki` and Game8 are **Tier A**, per the ladder in
`docs/SOURCE-OF-TRUTH.md` Â§5:142-148 and the registry in `docs/UMAMUSUME_REFERENCE.md` Â§8 â€” and Tier
A is not infallible, which that same section records (`umamusu.wiki` has misassigned Grand Masters
bonus entries before, against two JP guides that agreed). Â§5:152 is the rule this file satisfies: a
Tier B claim becomes app data only on A- or S-tier confirmation.

This is a **dated observation**: 107 cards, 68 trainees, hash `e9e9ee6d`, read 2026-09-29. The counts
are true of that day's bodies. A later reader who needs them must re-read the pages, not re-read this
file, because banner cycles move and the `N/A` cells in this table are the proof.

---

## docs/deprecated/data/roster-crosscheck-table.md

| card_id | GameTora title | Game8 title | umamusu.wiki title | GameTora date | Tier A date | rarity | verdict |
|---|---|---|---|---|---|---|---|
| 100101 | [Special Dreamer] | Special Dreamer | Special Dreamer | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 100102 | [Hopp'nâ™ªHappy Heart] | Hopp'nâ™ªHappy Heart | Hopp'nâ™ªHappy Heart | 2025-10-14 | 2025-10-14 | 3 | two-source-confirmed |
| 100201 | [Innocent Silence] | Innocent Silence | Innocent Silence | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 100301 | [Peak Joy] | Peak Joy | Peak Joy | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 100302 | [Beyond the Horizon] | Beyond the Horizon | Beyond the Horizon | 2025-07-16 | 2025-07-16 | 3 | two-source-confirmed |
| 100401 | [Formula R] | Formula R | Formula R | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 100402 | [Hotâ˜†Summer Night] | Hotâ˜†Summer Night | Hotâ˜†Summer Night | 2025-10-14 | 2025-10-14 | 3 | two-source-confirmed |
| 100501 | [Shooting Star Revue] | Shooting Star Revue | Shooting Star Revue | 2025-10-02 | 2025-10-02 | 3 | two-source-confirmed |
| 100601 | [Starlight Beat] | Starlight Beat | Starlight Beat | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 100701 | [Red Strife] | Red Strife | Red Strife | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 100801 | [Wild Top Gear] | Wild Top Gear | Wild Top Gear | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 100901 | [Peak Blue] | Peak Blue | Peak Blue | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 101001 | [Wild Frontier] | Wild Frontier | Wild Frontier | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 101101 | [Stone-Piercing Blue] | Stone-Piercing Blue | Stone-Piercing Blue | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 101102 | [Saintly Jade Cleric] | Saintly Jade Cleric | Saintly Jade Cleric | 2025-09-21 | 2025-09-21 | 3 | two-source-confirmed |
| 101201 | [Azure Amazon] | Azure Amazon | Azure Amazon | 2025-09-17 | 2025-09-17 | 3 | two-source-confirmed |
| 101301 | [Frontline Elegance] | Frontline Elegance | Frontline Elegance | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 101302 | [End of the Skies] | End of the Skies | End of the Skies | 2025-07-16 | 2025-07-16 | 3 | two-source-confirmed |
| 101401 | [Elâ˜†NÃºmero 1] | El Numero 1 | Elâ˜†NÃºmero 1 | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 101402 | [Kukulkan Warrior] | Kukulkan Warrior | Kukulkan Warrior | 2025-09-21 | 2025-09-21 | 3 | two-source-confirmed |
| 101501 | [O Sole Suo!] | O Sole Suo! | O Sole Suo! | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 101601 | [Maverick] | Maverick | Maverick | 2025-08-20 | 2025-08-20 | 3 | two-source-confirmed |
| 101701 | [Emperor's Path] | Emperor's Path | Emperor's Path | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 101801 | [Empress Road] | Empress Road | Empress Road | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 101802 | [Quercus Civilis] | Quercus Civilis | Quercus Civilis | 2025-08-28 | 2025-08-28 | 3 | two-source-confirmed |
| 101901 | [Full-Color Fangirling] | Full-Color Fangirling | Full-Color Fangirling | 2025-11-19 | 2025-11-19 | 3 | two-source-confirmed |
| 102001 | [Reeling in the Big One] | Reeling in the Big One | Reeling in the Big One | 2025-09-07 | 2025-09-07 | 3 | two-source-confirmed |
| 102301 | [pf. Winning Equation...] | pf. Winning Equation... | pf. Winning Equation... | 2025-07-10 | 2025-07-10 | 3 | two-source-confirmed |
| 102401 | [Scrambleâ˜†Zone] | Scramble Zone | Scrambleâ˜†Zone | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 102402 | [Sunlight Bouquet] | Sunlight Bouquet | Sunlight Bouquet | 2025-08-28 | 2025-08-28 | 3 | two-source-confirmed |
| 102601 | [MB-19890425] | MB-19890425 | MB-19890425 | 2025-07-02 | 2025-07-02 | 3 | two-source-confirmed |
| 102701 | [Down the Line] | Down the Line | Down the Line | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 102801 | [Buono â˜† Alla Moda] | Buonoâ˜†Alla Moda | Buono â˜† Alla Moda | 2025-11-11 | 2025-11-11 | 3 | two-source-confirmed |
| 103001 | [Rosy Dreams] | Rosy Dreams | Rosy Dreams | 2025-06-26 | 2025-06-26 | 3 | two-source-confirmed |
| 103201 | [tach-nology] | Tach-nology | tach-nology | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 103501 | [Get to Winning!] | Get to Winning! | Get to Winning! | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 103701 | [Meisterschaft] | Meisterschaft | Meisterschaft | 2025-10-30 | 2025-10-30 | 3 | two-source-confirmed |
| 103801 | [Fille Ã‰clair] | Fille Ã‰clair | Fille Ã‰clair | 2025-07-27 | 2025-07-27 | 3 | two-source-confirmed |
| 104001 | [Authentic / 1928] | Authentic / 1928 | Authentic / 1928 | 2025-10-07 | 2025-10-07 | 3 | two-source-confirmed |
| 104101 | [Blossom in Learning] | Blossom in Learning | Blossom in Learning | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 104501 | [Murmuring Stream] | Murmuring Stream | Murmuring Stream | 2025-06-26 | 2025-06-26 | 2 | two-source-confirmed |
| 104601 | [LOVEâ˜†4EVER] | LOVEâ˜†4EVER | LOVEâ˜†4EVER | 2025-08-11 | 2025-08-11 | 3 | two-source-confirmed |
| 105001 | [Nevertheless] | Nevertheless | Nevertheless | 2025-08-03 | 2025-08-03 | 3 | two-source-confirmed |
| 105201 | [Bestest Prize â™ª] | Bestest Prize | Bestest Prize â™ª | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 105601 | [Risingâ˜†Fortune] | Rising Fortune | Risingâ˜†Fortune | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 105602 | [Lucky Tidings] | Lucky Tidings | Lucky Tidings | 2025-11-06 | 2025-11-06 | 3 | two-source-confirmed |
| 105801 | [Turbulent Blue] | Turbulent Blue | Turbulent Blue | 2025-10-21 | 2025-10-21 | 3 | two-source-confirmed |
| 106001 | [Poinsettia Ribbon] | Poinsettia Ribbon | Poinsettia Ribbon | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 106101 | [King of Emeralds] | King of Emeralds | King of Emeralds | 2025-06-26 | 2025-06-26 | 1 | two-source-confirmed |
| 103002 | [Vampire Makeover!] | Vampire Makeover! | Vampire Makeover! | 2025-11-24 | 2025-11-24 | 3 | two-source-confirmed |
| 104502 | [Chiffon-Wrapped Mummy] | Chiffon-Wrapped Mummy | Chiffon-Wrapped Mummy | 2025-11-24 | 2025-11-24 | 3 | two-source-confirmed |
| 103901 | [Princess of Pink] | Princess of Pink | Princess of Pink | 2025-12-01 | 2025-12-01 | 3 | two-source-confirmed |
| 102501 | [Creeping Shadow] | Creeping Shadow | Creeping Shadow | 2025-12-08 | 2025-12-08 | 3 | two-source-confirmed |
| 101702 | [Archer by Moonlight] | Archer by Moonlight | Archer by Moonlight | 2025-12-14 | 2025-12-14 | 3 | two-source-confirmed |
| 104002 | [Autumn Cosmos] | Autumn Cosmos | Autumn Cosmos | 2025-12-14 | 2025-12-14 | 3 | two-source-confirmed |
| 104801 | [Jokester â˜† Vibes] | Jokester â˜† Vibes | Jokester â˜† Vibes | 2025-12-18 | 2025-12-18 | 3 | two-source-confirmed |
| 105901 | [Off the Line] | Off the Line | Off the Line | 2025-12-28 | 2025-12-28 | 3 | two-source-confirmed |
| 100602 | [Ashen Miracle] | Ashen Miracle | Ashen Miracle | 2026-01-05 | 2026-01-05 | 3 | two-source-confirmed |
| 102302 | [Rouge Caroler] | Rouge Caroler | Rouge Caroler | 2026-01-05 | 2026-01-05 | 3 | two-source-confirmed |
| 102201 | [Noble Seamair] | Noble Seamair | Noble Seamair | 2026-01-15 | 2026-01-15 | 3 | two-source-confirmed |
| 102101 | [Fast as Lightning] | Fast as Lightning | Fast as Lightning | 2026-01-22 | 2026-01-22 | 3 | two-source-confirmed |
| 101502 | [New Year, Same Radiance!] | New Year, Same Radiance! | New Year, Same Radiance! | 2026-01-29 | 2026-01-29 | 3 | two-source-confirmed |
| 105202 | [New Year â™ª New Urara!] | New Year â™ª New Urara! | New Year â™ª New Urara! | 2026-01-29 | 2026-01-29 | 3 | two-source-confirmed |
| 106901 | [Strength in Full Bloom] | Strength in Full Bloom | Strength in Full Bloom | 2026-02-11 | 2026-02-11 | 3 | two-source-confirmed |
| 102602 | [CODE: ICING] | CODE: ICING | CODE: ICING | 2026-02-18 | 2026-02-18 | 3 | two-source-confirmed |
| 103702 | [Precise Chocolatier] | Precise Chocolatier | Precise Chocolatier | 2026-02-18 | 2026-02-18 | 3 | two-source-confirmed |
| 107101 | [Crystalline] | Crystalline | Crystalline | 2026-02-25 | 2026-02-25 | 3 | two-source-confirmed |
| 103301 | [Starry Nocturne] | Starry Nocturne | Starry Nocturne | 2026-03-05 | 2026-03-05 | 3 | two-source-confirmed |
| 106201 | [Clippety-Tippety-Clop] | Clippety Tippety Clop | Clippety-Tippety-Clop | 2026-03-12 | 2026-03-12 | 2 | two-source-confirmed |
| 106801 | [Gilded Shrine to Glory] | Gilded Shrine to Glory | Gilded Shrine to Glory | 2026-03-12 | 2026-03-12 | 3 | two-source-confirmed |
| 106701 | [Natural Brilliance] | Natural Brilliance | Natural Brilliance | 2026-03-22 | 2026-03-22 | 3 | two-source-confirmed |
| 107401 | [Brunissage Line] | Brunissage Line | Brunissage Line | 2026-03-26 | 2026-03-26 | 3 | two-source-confirmed |
| 100502 | [SuccÃ¨s Ã‰toilÃ©] | SuccÃ¨s Ã‰toilÃ© | SuccÃ¨s Ã‰toilÃ© | 2026-04-05 | 2026-04-05 | 3 | two-source-confirmed |
| 102002 | [SoirÃ©e des Chatons] | SoirÃ©e des Chatons | SoirÃ©e des Chatons | 2026-04-05 | 2026-04-05 | 3 | two-source-confirmed |
| 105101 | [Layered Petals] | Layered Petals | Layered Petals | 2026-04-12 | 2026-04-12 | 3 | two-source-confirmed |
| 107201 | [Blazed Head, Covered Fists] | Blazed Head, Covered Fists | Blazed Head, Covered Fists | 2026-04-20 | 2026-04-20 | 3 | two-source-confirmed |
| 106002 | [Run & Win] | Run & Win | Run & Win | 2026-04-26 | 2026-04-26 | 3 | two-source-confirmed |
| 106102 | [Cheerleader in Noble White] | Cheerleader in Noble White | Cheerleader in Noble White | 2026-04-26 | 2026-04-26 | 3 | two-source-confirmed |
| 103101 | [Always Electrifying] | Always Electrifying | Always Electrifying | 2026-04-30 | 2026-04-30 | 3 | two-source-confirmed |
| 106401 | [Line Breakthrough] | Line Breakthrough | Line Breakthrough | 2026-05-10 | 2026-05-10 | 3 | two-source-confirmed |
| 102202 | [Titania] | Titania | Titania | 2026-05-18 | 2026-05-18 | 3 | two-source-confirmed |
| 103802 | [Ma ChÃ©rie of the New Moon] | Ma ChÃ©rie of the New Moon | Ma ChÃ©rie of the New Moon | 2026-05-18 | 2026-05-18 | 3 | two-source-confirmed |
| 103401 | [Edomurasaki] | Edomurasaki | Edomurasaki | 2026-05-28 | 2026-05-28 | 3 | two-source-confirmed |
| 104401 | [Platanus Witch] | Platanus Witch | Platanus Witch | 2026-06-04 | 2026-06-04 | 3 | two-source-confirmed |
| 101002 | [Bubblegumâ˜†Memories] | Bubblegum â˜†Memories | Bubblegumâ˜†Memories | 2026-06-11 | 2026-06-11 | 3 | two-source-confirmed |
| 105902 | [Sapphire Sojourn] | Sapphire Sojourn | Sapphire Sojourn | 2026-06-11 | 2026-06-11 | 3 | two-source-confirmed |
| 103601 | [unsigned] | unsigned | unsigned | 2026-06-18 | 2026-06-18 | 3 | two-source-confirmed |
| 100103 | [Ruler of Japan] | Ruler of Japan | Ruler of Japan | 2026-06-25 | N/A | 3 | conflict |
| 100702 | [RUN! RUIN! LAUNCHER!] | not listed | RUN! RUIN! LAUNCHER! | 2026-07-02 | 2026-07-02 | 3 | single-source |
| 101303 | [Fair Lady of the Waves] | not listed | Fair Lady of the Waves | 2026-07-02 | 2026-07-02 | 3 | single-source |
| 105301 | [Iron Ambition] | Iron Ambition | Iron Ambition | 2026-07-07 | 2026-07-07 | 3 | two-source-confirmed |
| 109801 | [Eightfoldâ˜†Fortune] | Eightfold â˜†Fortune | Eightfoldâ˜†Fortune | 2026-07-16 | 2026-07-16 | 3 | two-source-confirmed |
| 104602 | [Twilight Triumph] | Twilight Triumph | Twilight Triumph | 2026-07-22 | 2026-07-22 | 3 | two-source-confirmed |
| 103502 | [Dream Deliverer] | Dream Deliverer | Dream Deliverer | 2026-07-27 | 2026-07-27 | 3 | two-source-confirmed |
| 105002 | [Difference Engineer] | Difference Engineer | Difference Engineer | 2026-07-27 | 2026-07-27 | 3 | two-source-confirmed |
| 102901 | [Darl'n Snowflake] | Darl'n Snowflake | Darl'n Snowflake | 2026-08-05 | 2026-08-05 | 3 | two-source-confirmed |
| 104201 | [Rocketâ˜†Star] | Rocketâ˜†Star | Rocketâ˜†Star | 2026-08-12 | 2026-08-12 | 3 | two-source-confirmed |
| 101902 | [Fanaticâ™¡Jiangshi] | Fanaticâ™¡ Jiangshi | Fanaticâ™¡Jiangshi | 2026-08-18 | 2026-08-18 | 3 | two-source-confirmed |
| 105802 | [Dot-o'-Lantern] | Dot-o'-Lantern | Dot-o'-Lantern | 2026-08-18 | 2026-08-18 | 3 | two-source-confirmed |
| 108701 | [Flare] | Flare | Flare | 2026-08-25 | 2026-08-25 | 3 | two-source-confirmed |
| 107801 | [Fluttertail Spirit] | Fluttertail Spirit | Fluttertail Spirit | 2026-09-01 | 2026-09-01 | 3 | two-source-confirmed |
| 102102 | [Raging Thunder] | Raging Thunder | Raging Thunder | 2026-09-07 | 2026-09-07 | 3 | two-source-confirmed |
| 103402 | [Golden Dream] | Golden Dream | Golden Dream | 2026-09-07 | 2026-09-07 | 3 | two-source-confirmed |
| 104901 | [Desperate Measures] | Desperate Measures | Desperate Measures | 2026-09-15 | 2026-09-15 | 3 | two-source-confirmed |
| 110001 | [Butterfly Sting] | Butterfly Sting | Butterfly Sting | 2026-09-24 | 2026-09-23 | 3 | conflict |
| 100802 | [Fiery Aqua Vitae] | Fiery Aqua Vitae | Fiery Aqua Vitae | 2026-09-28 | N/A | 3 | conflict |
| 100902 | [Nuit Ã‰toilÃ©e de Scarlet] | Nuit Ã‰toilÃ©e de Scarlet | Nuit Ã‰toilÃ©e de Scarlet | 2026-09-28 | N/A | 3 | conflict |

---

## docs/deprecated/design-research/verification/cardless-band-2026-09-30.md


Date: 2026-09-30 local (the measurements below were taken at 2026-09-29 19:25â€“19:37 UTC).
Branch: `feat/catalog-roster-and-trainee-selector`, worktree `../umamusume-laravel13-catalog-roster`.
Opening HEAD `cadb4e3`; the band lands on top of `07941eb`.
Ruling implemented: the owner's 2026-09-30 decision, "**Cardless rows: separate band, not
bottom-of-list** â€¦ render the default list as two bands: recent-10 first, then a divider, then cardless
trainees ('no confirmed costume card yet') above the fold â€¦ the assertion that she's reachable by name
must exist either way."

---

### 1. What changed

`resources/js/trainee-combobox.ts`:

- `collect`'s empty-query branch returns two bands instead of one flat date sort:
  `[...carded, ...cardless]`, carded newest-first, cardless by trainee name. The old shape sorted every
  row by `releaseDate` descending, and a cardless row's `releaseDate` is `''`, so she sat below the cap
  on any roster holding even one confirmed card.
- `windowFor` (new, extracted from `render`'s one-line slice) gives **each band its own window** at the
  existing `DEFAULT_VISIBLE` cap. A single slice of the banded list is the shape that hid her.
- `bandDivider` (new) paints one `role="presentation"` row reading **"No confirmed costume card yet"**,
  appended once and only when a confirmed band precedes it. It carries `data-band-divider`, never
  `role="option"`, so it cannot enter `options()` or receive the cursor.
- `isCardless` (new) is the one spelling of the question the band turns on, used by `collect`,
  `windowFor`, the seam in the loop and the element id below, so the five sites cannot each spell it
  slightly differently and drift.
- `option.id` for a cardless row is keyed on her **trainee** id, not `selectionId`. See Â§4.

`tests/Feature/TraineeSelectorTest.php`: one new test,
`it('puts the cardless band in the default list, under a divider, with no typing')`, pinning the band
order, the two windows (`substr_count($window, 'DEFAULT_VISIBLE') === 2`), the divider's role/attr/copy,
its once-only gated append, and the unique-id fix. The set-parity and name-reachability proofs the owner
asked for already existed (`makes the payload trainee set the same set the no-script select offers`,
`lists every Global trainee in the no-script select when the database holds no costume card at all`).

### 2. The state it was measured against

`database/scratch-ui.sqlite` (gitignored; `database/database.sqlite` was not opened, read or written),
built by `research-scratch/make-ui-db.php`: the rehearsed 107-card scratch state, with each trainee's
`name`/`name_ja` replaced by the strings the **shipped** `GametoraCharacterParser` reads from the same body
(joined on the same `external_ref` the card store resolves through), and three cards set `unconfirmed = 1`
so their trainees hold no confirmed form. The rehearsal database itself names every trainee by her ref
(`research-scratch/rehearse-apply.php:69`, `'name' => $ref`), which is why it could not be used for a
read of the UI as a Trainer would see it.

Result: 68 Global trainees, 65 holding a confirmed card, 104 confirmed cards + 3 cardless trainees =
**107 rows**. Server on `127.0.0.1` with an absolute `DB_DATABASE` (port 8431 for the first pass, 8432
for the re-measure after the helper refactor), assets from `npm run build`.

### 3. Measured, in the browser, with nothing typed

Focusing the input paints 27 rows: ten `header`/`option` pairs newest-first (2026-09-28 down to
2026-08-18), then the divider, then three cardless trainees. Every figure below was read again against
the final bundle (`app-CZXqo5_6.js`, the one carrying `isCardless`) and reproduced unchanged: 27 rows,
13 options, 13 distinct ids, divider at row 21 with `role=presentation`, live region
`13 of 107 (keep typing)`, divider at y=601 in a 286 px viewport over 813 px of content.

| check | measured |
|---|---|
| live region | `13 of 107 (keep typing)` |
| options in the paint | 13 (10 carded + 3 cardless) |
| divider | `role=presentation`, `data-band-divider`, text `No confirmed costume card yet`, at DOM row 21 |
| cardless option text | `No costume card confirmed yet`, under a header naming her (`Aston Machan ã‚¢ã‚¹ãƒˆãƒ³ãƒžãƒ¼ãƒãƒ£ãƒ³`) |
| `aria-label` of a cardless option | `Wonder Acute Â· No costume card confirmed yet` |
| distinct option ids | 13 of 13 (`trainee-option-u65`, `-u62`, `-u68` for the cardless three) |
| ArrowDown Ã—13 | `aria-activedescendant` named 13 distinct `role=option` elements, never the divider; Ã—14 wraps to the first |

Commit path: pressing the divider changes nothing (`umamusume_id` and `character_card_id` both stay
empty). Pressing a cardless row sets the visible field to `Aston Machan Â· No costume card confirmed
yet`, `umamusume_id` to `65`, leaves `character_card_id` empty, closes the listbox and disables the
native select. Submitting that form created `training_runs` id 1 with `umamusume_id = 65` and
`character_card_id = NULL`.

Typed path, unchanged by the band: `Won` â†’ one row, `1 match`; `zzz` â†’ `No trainee or card found.`;
clearing the field repaints both bands.

### 4. What the browser pass caught that reading did not

`option.id` was built from `hit.card.selectionId`, and a cardless row's `selectionId` is the placeholder
`0`. One cardless row on screen was invisible; **three shared `trainee-option-0`**, and
`aria-activedescendant` is set from `options()[active].id` and resolved by a screen reader through
`getElementById` â€” so with the cursor on the second or third cardless row, the announced row was the
first. Duplicate ids are also invalid HTML (WCAG 4.1.1). Pre-existing, and reachable before this change
on any database that paints more than one cardless row (a seeded install paints ten), but the band makes
it the normal case rather than the seed-state case. Fixed by keying a cardless row's id on her trainee
id, with the reason in the comment and the shape pinned in the test.

### 5. Where the ruling is only partly met â€” open

"Recent-10 first" and "cardless above the fold" are in tension at the popup's current height, and the
numbers are not close:

| | px |
|---|---|
| listbox viewport (`max-h-72`) | 286 |
| content at scrollTop 0 | 813 |
| divider position | 601 |
| first cardless option | 662 |
| rows fully visible without scrolling | 9 of 27 |

Ten carded trainees cost twenty rows because each one is a header plus an option (~60 px), so the seam
lands at 601 px no matter what follows it. What this change does deliver: she is in the default paint at
row 11 of 13 instead of row 98 of 107, and no typing is required. What it does not deliver, literally: the
band is below the popup's scroll fold.

Three ways to close it, each costing something the ruling named:

1. **Cap the carded band by height, not count** (â‰ˆ 4 trainees at the current 286 px). The seam and the
   cardless band land on screen; "recent-10" becomes "recent-4".
2. **Raise the listbox cap** so the whole default window fits (~813 px). Keeps 10 and the fold, at the
   cost of a popup taller than a phone viewport â€” and `max-h-72` has no standard step near 813 px, so
   the class would be an arbitrary value, which `docs/design-research/CONSTRAINTS.md:13` (D-1: "The
   `@theme` block is law") makes a review failure.
3. **Drop the per-trainee header where a trainee has exactly one row**, folding her name into the option
   line. Halves both bands (~300 px for ten carded), keeps recent-10, and puts the seam just below the
   fold rather than two screens below. It changes the visible row shape the last three slices settled, so
   it is a design decision and not a fix.

Recommendation: 3, then re-measure; 1 if the fold matters more than the ten.

**Ruled 2026-09-30, by the owner: leave it, keep recent-10.** The band ships as measured â€” row 11 of 13 in
the default paint, reachable with no typing, seam below the popup's scroll fold. The three options above
stand as what was weighed, not as open work; nothing here is owed.

### 6. Gates

Full suite after the last edit: **783 passed, 2 skipped (2729 assertions)**, exit 0. This file alone:
`tests/Feature/TraineeSelectorTest.php` â†’ **26 passed (174 assertions)**. `npm run typecheck` (C-9)
clean; `npm run build` clean; `vendor/bin/pint` passed on the changed test; PHPStan level 6
`[OK] No errors`, run against this slice's tree (`app/` carries no change in it, which is why the gate
is quoted rather than re-run per edit). `composer lore` 115 hits / 57 exempt and `composer lore-code` 8,
both unchanged from the recorded baseline; the new copy carries no dash and no banned term. (The first
draft of this line used the banned word for the category, which is how the count went up by one.)

