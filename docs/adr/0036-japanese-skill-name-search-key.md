# ADR-0036: A Japan-only skill is searched by a folded twin of its Japanese name

<!-- ADR-CONTRACT
  decision: proposed — skills gains a nullable match_key_ja holding JapaneseNameNormalizer's fold of name_ja, so the japan_only search scope reaches the one string that scope is allowed to print
  code: built — migration 2026_11_05_084524_add_match_key_ja_to_skills_table (with index, down() drops the index before the column); Skill::booted() fold; App\Services\DataPipeline\JapaneseNameNormalizer; SkillController scope search; tests/Unit/JapaneseNameNormalizerTest.php, tests/Feature/SkillSearchScreenTest.php
  superseded-by: —
  amended-by: —
  open: the column is written by the parser and the model guard, never back-filled in the migration — rows reach a filled key only by re-running the skills import
-->

Status: **Proposed.** Drafted 2026-11-18 from shipped code; the column landed at `ef245171`. **Nothing here binds
until the owner records it as Accepted.**

## Context

A skill row carries `name`, `name_ja` and a `match_key` folded from `name`. On a Japan-only row `name` holds the
export's English rendering, which the Air Messiah ruling bars from UI copy; the string such a row actually prints is
`name_ja`. So the one searchable key the row had was folded from a string that scope refuses to render.

The search surface has three scopes: `global`, `japan_only` and `all`. `japan_only` could reach a Japan-only row only
through `description_match_key`, a channel whose text that scope also refuses to print. A Trainer searching for a
skill by the name they could see had no path to it.

## Decision

**`skills.match_key_ja` is the nullable folded twin of `name_ja`.** Built by `JapaneseNameNormalizer`, the same
normalisation family as `match_key`, and searched only where the row class makes that text visible.

- **Nullable with no default.** "No `name_ja` recorded" is a distinct state from "recorded and normalises to empty",
  the distinction `description_match_key` already keeps.
- **No back-fill in the migration.** The value is the parser's to write through `StoreSkills`, and a SQL approximation
  of the fold would be a second implementation that drifts the moment `JapaneseNameNormalizer` changes. Rows fill on
  a re-run of the import, which is how `match_key` itself was populated.
- **Each scope reads the keys whose text it prints.** `japan_only` reads `match_key_ja`; `all` reads all three, each
  on the row class where its text is visible, so a key never matches text the scope will not render.
- **The index is dropped before the column in `down()`.** SQLite will not drop a column an index still references,
  and the migration states that in its own docblock.

## What this does not license

- **No folding of the display name.** `name_ja` keeps its own spelling; only the key is folded. Matching on display
  strings is a Floor violation, and this column is the opposite of that: a twin exists precisely so no surface has to
  match on what it prints.
- **No JP-only import.** `ADR-0011`'s read-time Global filtering stands.
- **No fuzzy or phonetic search.** The key is a fold, not a transliteration; a romaji search for a Japanese name is
  not served by this column.

## Links

- `ADR-0011` (the import, the `name_is_client` rule and the Air Messiah naming ruling), `ADR-0018` (the facet
  contract the search surface obeys)
- `UMAMUSUME_REFERENCE.md` §2.7; `PRD.md` FR-D-1, FR-D-2, US-4
- `app/Services/DataPipeline/JapaneseNameNormalizer.php`; `app/Actions/StoreSkills.php`; `App\Models\Skill`
  (`booted()` fold); `SkillController` (scope search)
- `tests/Unit/JapaneseNameNormalizerTest.php`, `tests/Feature/SkillSearchScreenTest.php`

## Ledger

- 2026-11-18 · **drafted** — the decision was recorded only in the migration's docblock as "Phase 7, choice A", a
  reference to a plan section rather than a ruling. This ADR is written from the shipped code and names no plan
  document, because none of the candidates on disk states the choice.
