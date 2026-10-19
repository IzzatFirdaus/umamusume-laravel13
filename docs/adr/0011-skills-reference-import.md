# ADR-0011: The skills reference import — what the export carries, what stays unrendered

<!-- ADR-CONTRACT
  decision: accepted in part (owner ruling 2026-09-29) — import + UI in this pass; rarity authorized as a PRD amendment; hint-level copy ruled to level only, no percentage
  code: built — migration 2026_09_29_021157_add_reference_fields_to_skills_table; GametoraSkillsParser; GametoraSkillsParserTest; tests/Feature/SkillSearchScreenTest.php
  superseded-by: —
  amended-by: 0012 (its card-detail reader relies on the skill join this establishes), 0018 (the filter surface's facet contract reads this screen's vocabulary)
  open: no hint levels and no discounts are imported; support-card hint pools stay cut; the export's row counts are a dated measurement of a gitignored body, not restated here
-->

Status: **accepted in part.** Owner directed "import + UI in this pass" on 2026-09-29, authorized `rarity` as
a PRD amendment rather than a schema tidy-up, and ruled the hint-level copy to **level only, no
percentage**.

## Context

`skills` held ten rows of names. `SkillSeeder` said cost, type and uniqueness "arrive with the fetch
engine", and no fetch engine for skills was ever built: `config/uma.php` declared two sources and `Parsers/`
held three parsers, none of them skills. The skills audit pass (SKILLS-GAPS.md) found the GameTora skills
document and measured it before writing a column against it.

**Three findings changed the shape of this decision**, and each one overturned what the brief assumed:

1. **`name_en` is the client string, and `enname` is a translation of the Japanese.** Pinned by the corpus's
   own evidence: the reference guide fixes grade 100 → G1 from client copy "G1 Averseness", and the export
   id for it reads `enname: "G1 Dislike"` / `name_en: "G1 Averseness"`. Choosing `enname` would put a
   third-party rendering into UI copy, which is what the Air Messiah ruling bars.
2. **`rarity` is not the client's rarity.** Six values, and the axes they encode are *learnable vs unique vs
   evolved* (cost on 1/2, `char` on 3/4/5, `pre_evo` on all of 6) — not Game8's Normal/Rare/Unique. The
   proposal to "pull them from the export's `rarity` field" as three values was wrong, and a migration
   written from it would have shipped a label the data cannot support.
3. **`type` is not the skill category.** It is the set of conditions the skill gates on. The client's four
   categories are not in this document as a field.

## Decision

**1. Source.** One new `config('uma.sources')` entry, `gametora-skills`, resolving its URL through the
GameTora manifest at fetch time rather than pinning a hash. The politeness argument is re-made, not
inherited, because this is a different document: same host, same publisher, same static-JSON response class,
one request per fetch, and the existing `delay_ms` / lock / TTL bounds already cap the load. `KI-24` records
why a pinned hash is the wrong mechanism — a stale hash answers `200` with stale content.

**2. Global filtering happens at read, not at write.** Every row imports, carrying the server flag the
source states; the `[Global]` subset is what every Trainer-facing surface selects. Deleting JP-only rows at
import would destroy the audit trail, and `docs/research-scratch/GOVERNANCE.md` §4.1's import filter is about *surfaces*, not
storage. Cross-checked against the card document: evolved skills are absent from `[Global]` even for
characters who are on it, which is a conflict row reproduced mechanically from two documents.

**3. `rarity` stores the export's integer, unchanged, and renders nothing.** The column is authorized; the
*word* is not evidenced. This follows the `race_catalog_slots` split exactly — the export's number stays
auditable and a label is carried only where a source pins it, and a tier whose label has no per-row source is
nulled rather than generalised.

**4. `skills.type` is filled by derivation and marked as derived.** Owner chose derivation over NULL. The
codes and their meanings are read off the export's own English descriptions, and **the sign is part of the
rule, not a detail:** one code carries both `Right-Handed ◎` (+600000, "increase performance on
right-handed tracks") and `G1 Averseness` (−400000, "decrease performance in G1 or otherwise important
races"), so a code alone would have labelled an averseness `Passive`. A non-positive value voids the claim.
`type` holds a derived label where code and sign agree and null elsewhere, no screen presents the value as
client copy (D-20), and the parser's table names each code and its citation rather than leaving the mapping
in prose.

**5. `is_unique` is answered by the code set, with the card join as its corroboration — and the two differ
by a few rows, which is stated rather than smoothed.** Every id reachable from a card's `skills_unique` has
`rarity` ∈ {3, 4, 5}, which is also exactly the population carrying `gene_version`. The rule the parser can
actually apply is the code set, because a parser sees one document and cannot join to the card document — and
the code set is slightly larger. The extra rows are unique-shaped skills this catalogue cannot attribute to a
trainee, and no card names a skill outside the set.

**6. `gene_version` and the evolution pair are stored raw or not stored.** `gene_version` is the
`[Global]`-relevant **inherited form** of a unique skill, and its nested id is **not a top-level row**, so it
cannot be a foreign key to self. `evo`/`pre_evo` is the JP-only system and imports no column. Both stay out
of the schema until a screen needs them.

## Consequences

- **`PRD.md` FR-D-1 is amended**, dated, with the source of every value named in the diff. The amendment says
  explicitly that the stored value is the export's class code and that no client rarity word is claimed.
- **`SkillSeeder`'s ten names are corrected against `name_en`** (owner ruling). Three are not client strings:
  one has no counterpart at all, one is missing a word the client carries, one is missing its trailing bang.
  The seeder's docblock celebrated them *as* verbatim client copy, so the correction propagates to the design
  corpus rules that recorded them from Game8, not only to the seeder. This is the fabricated-name defect
  class found through the export instead of through the gate.
- **The characters parser's key bug is not inherited.** Its test fixture uses the same wrong key as its code,
  so the suite is blind to it. The skills parser's fixture is **cut from the live document** rather than
  hand-written, so the keys are the source's and a rename cannot pass silently.
- **`ADR-0010`'s Spark `target` becomes resolvable.** The Legacy payload stores a Spark's target as a name
  string; with Global skill rows carrying `match_key`, a Green Spark naming a skill can join to a `skills`
  row by normalized key. That is read-path work for the Legacy surface's owner, not storage work.

## What this does not decide

- **No hint levels, no discounts.** Nothing in this import puts a hint level anywhere; `run_skills` gains no
  column here, and the copy ruling (level, no percentage) binds any future one.
- **No skill search (FR-D-2).** The catalog screen is a separate slice.
- **Support-card hint pools stay cut** (`ADR-0005`, R37). The hint pools exist in this export and are
  **not** imported: importing them would be a support-card surface wearing a skill column.
- **Fetch approval scope.** The owner authorized this pass's import, which is what escalation 5 asks for on
  this host. No robots.txt re-check was run — the same omission `config/uma.php` already records for the
  first two sources, inherited rather than hidden.

## Links

- `PRD.md` FR-D-1, FR-D-2, US-4; `config/uma.php` (`gametora-skills`); `App\Enums` skill vocabulary
- `ADR-0003` R3 (reference data arrives through the fetch engine), `ADR-0004` (reference data with
  provenance), `ADR-0005` (R37, the support-card cut), `ADR-0009` (the committed-export precedent),
  `ADR-0010` (the Spark target this makes resolvable), `ADR-0012`, `ADR-0018`
- `GametoraSkillsParser`; `tests/Feature/GametoraSkillsParserTest.php`,
  `tests/Feature/SkillSearchScreenTest.php`, `tests/Feature/EpithetRewardNamesTest.php`
- docs/design-research/SKILLS-GAPS.md; docs/UMAMUSUME_REFERENCE.md §1.2.6, §2.7
- `CONSTRAINTS.md` C-4, D-20, D-30, D-210; KI-5, KI-23, KI-24
