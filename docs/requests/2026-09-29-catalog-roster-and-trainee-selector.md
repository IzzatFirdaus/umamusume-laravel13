# Request: Populate the Global catalog and add a searchable trainee selector

**Received:** 2026-09-29, in session (no file was attached; this is the recorded brief).
**Recorded by:** Architect pass, 2026-09-29, against tree `ec0ee2f`.
**Plan:** `2026-09-29-catalog-roster-and-trainee-selector-plan.md`
**Status:** planned, not built.

## 1. What was asked

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

## 2. Owner rulings, taken in session 2026-09-29

| Question | Ruling |
|---|---|
| The card layer has no PRD citation. | **Build the card table, and put a card reference on the run.** The form is to submit the card, not only the trainee. |
| Where does the data come from? | **Re-fetch live from GameTora through `uma:fetch`.** Not the 2026-09-27 gitignored snapshot. This answers `AGENTS.md` escalation 5 for the live fetch of the already-declared host. |
| Tier B needs Tier A confirmation. How deep? | **Cross-check all 105 Global cards** against `umamusu.wiki` and Game8, not a spot-check. |

Consequences the rulings accept: a new table plus a second migration on `training_runs`;
an ADR and a PRD citation before merge; an amendment to the D-30 permitted-surface list;
a second declared source and parser class.

## 3. Erratum: what the brief asserts that is not true

Every line below was measured against the dataset on 2026-09-29, not argued.

| # | The brief says | The data and the code say |
|---|---|---|
| E-1 | "~68 H1s plus ~140 H2s" | 68 trainees is right. **105** cards carry a Global release date, out of 268 total. 33 trainees have one form, 33 have two, two have three. Rarity split: 8 x 1-star, 9 x 2-star, 88 x 3-star. Global window 2025-06-26 to 2026-09-24. |
| E-2 | Gold Ship: "3 forms", `[Red Strife]` "3-star … Mar 12, 2026" | Gold Ship has three cards, **two** of them Global. `[Red Strife]` is **2-star** and shipped on Global **2025-06-26**. "Mar 12, 2026" is the Trackblazer scenario start. Her third card, `La Mode 564`, has no `release_en` and is excluded by the brief's own Global-only rule. |
| E-3 | Special Week: "2 forms" | **Three** Global forms: `[Special Dreamer]` 2025-06-26, `[Hopp'n♪Happy Heart]` 2025-10-14, `[Ruler of Japan]` 2026-06-25. |
| E-4 | "the export marks one card per trainee as the debut form" | No such field exists in the export. Debut is **derived**, and this repo already has the rule: `GametoraCharacterParser.php:74-79` takes the card with the earliest JP `release` per `char_id`. Measured across all 68 Global trainees that derivation is unambiguous (no date ties) and the JP-earliest card, the Global-earliest card and the parser's debut are the same card in every case. |
| E-5 | `Fe` "narrows the F-set" | Under the brief's own prefix-match rule, `F` returns Fine Motion and Fuji Kiseki plus nine cards whose epithet starts with F. **`Fe` returns nothing**: no Global trainee and no Global epithet begins with those two letters. |
| E-6 | `Fenomeno` "Fenomeno, with her card forms as options" | Fenomeno has two cards, `Black Flame of Righteousness` (JP 2025-04-21) and `Violet Flame of Fortitude` (JP 2026-08-31), and **neither has a `release_en`**. By the Global-only rule she is one of the 67 trainees who must not appear at all. The same applies to Furioso and Fusaichi Pandora. |
| E-7 | Selected state `Gold Ship — [RUN! RUIN! LAUNCHER!]` | The em dash is a **test failure**. `RenderedCopyHygieneTest.php:154` asserts zero em or en dashes in rendered Blade copy, on the standing R-02 and D-79 rulings. The catalog's own separator is `·` (`catalog/index.blade.php:41`). |
| E-8 | "Rarity badge per H2" | Fine, but no new colour token: `DesignTokensTest.php:200` pins the theme at exactly 60 tokens, and G-4 bans hex and arbitrary-value utilities. The badge reuses existing tokens and carries its ordinal in the star glyph, the way `mood-pill.blade.php` carries its arrow. |
| E-9 | "Re-fetch live via `uma:fetch`" populates the catalog | `CrossReferenceMatcher::match()` returns `None` for any name not already stored, and `PipelineRunner.php:61-74` sends `None` to `match_candidates`. A live fetch therefore **creates zero trainee rows**; it queues 68 candidates. `PRD.md` FR-B-3 is deliberate: only Exact and Alias auto-promote. The roster has to leave the review queue through `ResolveMatchCandidate`, which is the path this plan uses. |
| E-10 | Catalog search should "filter live" | The existing catalog filter is a GET form behind a submit button, and search is substring (`match_key LIKE %…%`), not prefix. Live-typing there means a page reload per keystroke. The **selector** is the live surface; the catalog page keeps its submit. Recorded as a scope reading, not a silent drop. |
| E-11 | Unstated | The dataset already sits at `research-scratch/data/json/character-cards.json` (268 rows, fetched 2026-09-27) but `research-scratch/` is gitignored and nothing under `app/` reads it. |
| E-12 | Unstated | **Live defect, in scope.** `GametoraCharacterParser.php:92` reads `$card['name_ja']`. The export publishes `name_jp`: `name_ja` appears **0** times in 268 rows, `name_jp` 268 times. So every fetched trainee stores a null Japanese name, and `PRD.md` US-1's acceptance test ("each detail page shows `name`, `name_ja`, …") is not met by fetched data. The test fixture at `tests/Fixtures/gametora-character-cards.sample.json` carries the same wrong key with null values, which is why the suite is green. The brief needs the Japanese name as the `<h1>` secondary label and as a search field, so this is a prerequisite. |
| E-13 | "One H1 per trainee … The H2 label is the bracket title alone" | The `<h2>` label is `title_en_gl`, the Global client string, which is **bracketed** (`[Red Strife]`). The prose examples drop the brackets. The binding constraint is "verbatim client names stay verbatim; do not normalize them", so the brackets stay. The Global string is not the JP string: `Run! Fun! Watergun!` is `[RUN! RUIN! LAUNCHER!]`, `Supreme Commander of the Rising Sun` is `[Ruler of Japan]`. |
| E-14 | Unstated | The parser emits `external_ref` (`gametora:char:{id}`) at line 100 and there is **no such column** on `umamusume`, so `PromoteMatchedRecord` drops it. Cards are keyed by `card_id` and have to be attached to a stored trainee, which needs that link. The plan adds the column rather than re-matching on name. |

| E-15 | Implicit: fetching fills the catalog with the 68 Global trainees | `GametoraCharacterParser` emits one record per `char_id` across the **whole** export, deriving `release_status` from the debut card's `release_en`. Measured on the live body 2026-09-29: **135 records, 68 `GlobalReleased`, 67 `JapanOnly`.** A live fetch therefore queues 133 candidates, not 66. The parser must keep emitting all 135: `PRD.md` US-2 is P0 and its acceptance text is "new JP releases appear with a **JapanOnly** or GlobalAnnounced flag instead of silently missing", which a Global-only guard would delete. The filter belongs at the promotion verdict, so 66 become rows and 67 stay in `/review` awaiting one. |
| E-16 | Unstated | `SourceFetcher` sends `User-Agent: UmamusumeTrainerCompanion/0.2 (personal local tool)` and no `Accept`. Probed 2026-09-29 before any of this was built: the data endpoint returns **200 with the full 251,242 bytes**, and `679f7c2e` has not rotated. Tasks 8-9 are runnable as ruled, with no header change and no fallback to the gitignored snapshot. |

## 4. Deliverables as accepted

1. `character_cards` table, model, factory, seeder-free, populated from a live fetch,
   Global rows only, one row per card with `card_id` unique.
2. `training_runs.character_card_id`, nullable, validated as belonging to the
   trainee on the same row.
3. Catalog `<h1>`/`<h2>` tree over the 68 trainees and their 105 Global cards, with rarity,
   Global date, form count, and a default filter of Released (Global).
4. Detail page that names its source and fetch date instead of "No fetched sources."
5. ARIA combobox on "New training run", vanilla TypeScript, no dependency, progressive
   enhancement over the existing `<select>`, submitting the card.
6. `ADR-0008` plus the `PRD.md` citation, `ARCHITECTURE.md` and its ESSENTIALS digest,
   and the D-30 surface amendment.
7. A tracked Tier A cross-check file covering all 105 cards, with anything unconfirmed
   marked and hidden by default.
8. Report: trainees and cards added, sources used, unverified entries, and the counts
   this table records as corrections.
