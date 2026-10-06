# ADR-0011: The skills reference import — what the export actually carries, and what stays unrendered

Status: **accepted in part.** Owner directed "import + UI in this pass" on 2026-09-29, authorized `rarity`
as a PRD amendment rather than a schema tidy-up, and ruled the hint-level copy to **level only, no
percentage**. The three field decisions below are owner-chosen; the measurements that forced them are
this document's, recorded because two of the three overturned what the brief assumed.

Date: 2026-09-29
Deciders: product owner (scope, rarity authorization, copy ruling), Data Engineer + Architect (measurement
and recording), implementing agent (draft)
Amends: `PRD.md` FR-D-1 (adds `rarity`, and states where the values come from)
Relates to: `ADR-0003` Amendment R3 (reference data arrives through the fetch engine), `ADR-0004`
(reference data with provenance), `ADR-0009` Option A (the committed-export precedent), `ADR-0010`
(the Legacy payload, whose Spark `target` string this import makes resolvable), `KI-23`, `KI-24`,
`docs/design-research/SKILLS-GAPS.md`, `docs/SOURCE-OF-TRUTH.md` §4.1 and §5, `CONSTRAINTS.md` C-4, D-20, D-30

## Context

`skills` holds ten rows of names. `SkillSeeder` says cost, type and uniqueness "arrive with the fetch
engine", and no fetch engine for skills was ever built: `config/uma.php:44-93` declares two sources and
`Parsers/` holds three parsers, none of them skills. The skills audit pass (`SKILLS-GAPS.md`) found the
GameTora skills document and measured it before writing a column against it.

**Measured on `skills.609afe88.json` (1,910 records, pulled 2026-09-29):**

| Field               | Present on                        | What it is                                                                                     |
| ------------------- | --------------------------------- | ---------------------------------------------------------------------------------------------- |
| `id`                | 1,910                             | The export's own skill id. Not the app's `id`.                                                 |
| `enname`            | 1,910                             | A literal rendering of the Japanese name — **not the client string**                           |
| `name_en`           | 985 (all 623 Global rows)         | **The `[Global]` client string.** 535 of 623 differ from `enname`                              |
| `unreleased`        | 1,287                             | Array of server codes. Every populated value contains `en`; absence is the Global statement    |
| `rarity`            | 1,910                             | Six values: 1 (598), 2 (346), 3 (22), 4 (22), 5 (250), 6 (672)                                 |
| `cost`              | 906                               | SP cost. Present on rarity 1 and 2 only; absent on 3–6                                         |
| `type`              | 1,910                             | An array of **gate keys** (`nac`, `med`, `l_1`, `ldr`, `dir`, `mil`, `cor`, `run`, `f_s`, …)   |
| `char`              | 1,490                             | The trainee cards whose unique skill this is                                                   |
| `gene_version`      | 290 (rarity 3/4/5 only)           | A nested record: `inherited: true`, `parent_skills: [host id]`, own `id`, own `cost`           |
| `evo` / `pre_evo`   | 256 (rarity 2) / 672 (rarity 6)   | The evolution pair, both directions                                                            |

Three findings changed the shape of this decision:

1. **`name_en` is the client string, and `enname` is a translation of the Japanese.** Pinned by the
   corpus's own evidence: `UMAMUSUME_REFERENCE.md` §1.2.6 fixes grade 100 → G1 from client copy
   "G1 Averseness", and export id 200311 reads `enname: "G1 Dislike"` / `name_en: "G1 Averseness"`.
   Choosing `enname` would put a third-party rendering into UI copy, which is what `REFERENCE` §2.7's
   Air Messiah ruling bars.
2. **`rarity` is not the client's rarity.** Six values, and the axes they encode are *learnable vs
   unique vs evolved* (cost on 1/2, `char` on 3/4/5, `pre_evo` on all of 6) — not Game8's
   Normal/Rare/Unique. `SKILLS-GAPS.md` §6 G-SK-1's proposal to "pull them from the export's `rarity`
   field" as three values was wrong, and a migration written from it would have shipped a label the
   data cannot support.
3. **`type` is not the skill category.** It is the set of conditions the skill gates on. The client's
   four categories (Speed / Passive / Recovery / Debuff, Game8's `[A]`-tier classification as the brief
   cites it) are not in this document as a field.

## Decision

**1. Source.** One new `config('uma.sources')` entry, `gametora-skills`, resolving its URL through
`https://gametora.com/data/manifests/umamusume.json` at fetch time rather than pinning a hash. The
politeness argument at `config/uma.php:69-77` is re-made, not inherited, because this is a different
document: same host, same publisher, same static-JSON response class, one request per fetch, and the
existing `delay_ms` / lock / TTL bounds already cap the load. `KI-24` records why a pinned hash is the
wrong mechanism here — a stale hash answers `200` with stale content.

**2. Global filtering happens at read, not at write.** All 1,910 rows import, carrying the server flag
the source states; the `[Global]` subset (623 rows) is what every Trainer-facing surface selects.
Deleting JP-only rows at import would destroy the audit trail and make the 1,287 uncountable, and
`SOURCE-OF-TRUTH.md` §4.1's import filter is about *surfaces*, not about storage. Cross-checked against
the card document: of 673 skills whose owner card carries a `release_en` date, **403 agree** with the
skill's own flag and **all 270 disagreements are rarity-6 evolved rows** — evolved skills are absent
from `[Global]` even for characters who are on it, which is conflict row 45 reproduced mechanically from
two documents (`rarity 6`: 672 rows, `unreleased` containing `en`: 672, on Global: 0).

**3. `rarity` stores the export's integer, unchanged, and renders nothing.** The column is authorized;
the *word* is not evidenced. This follows the `race_catalog_slots` split exactly — `grade_code` keeps
the export's number auditable and `tier` carries a label only where a source pins it (R65, as applied in
`40df14c`, which nulls a tier whose label has no per-race source rather than generalising one). A
`rarity` column that displayed "Rare" would be the tier-label defect repeated one table over.

**4. `skills.type` is filled by derivation and marked as derived.** Owner chose derivation over NULL. The
codes and their meanings, read off the export's own English descriptions:

| effect code   | rows            | what the copy says                                                       |
| ------------- | --------------- | ------------------------------------------------------------------------ |
| 27            | 1,238           | velocity ("increase velocity")                                           |
| 31            | 431             | position-hold / determination effects                                    |
| 22            | 380             | acceleration ("surge ahead … increase acceleration")                     |
| 9 / 28        | 334 / 40        | endurance recovery                                                       |
| 1 / 2 / 3     | 124 / 78 / 59   | performance on track side, venue, ground condition                       |
| 21            | 60              | **negative values** (`v=-2000`) on self: `Corner Adept ×`, `Defeatist`   |

So Speed-type (27, 22) and Recovery-type (9, 28) are evidenced, a large "condition performance" family
(1/2/3) maps to Passive, and **Debuff is not evidenced at all** — nothing in this file is an effect on
another runner, and code 21 is a *self-negative* skill, which is the `SKILLS-GAPS.md` path-8 family, not
Debuff. **The sign is part of the rule, not a detail:** code 1 carries both `Right-Handed ◎` (+600000,
"increase performance on right-handed tracks") and `G1 Averseness` (−400000, "decrease performance in G1
or otherwise important races"), so a code alone would have labelled an averseness `Passive`. A non-positive
value voids the claim.

`type` therefore holds a derived label where code and sign agree and null elsewhere, measured on the first
live import: across all 1,910 rows Speed 913 / Passive 120 / Recovery 82 / null 795, and on the 623
`[Global]` rows Speed 199 / Passive 82 / Recovery 54 / null 288. The sign rule is what withholds 53 of
those labels. The parser's table names each code and its citation, and no screen presents the value as
client copy (D-20). Effect code 21 is why `SKILLS-GAPS.md` **G-SK-9** is now a data question rather than a
source question: the negative-skill family is identifiable in the export, and its removal cost is not.

**5. `is_unique` is answered by the code set, with the card join as its corroboration — and the two
differ by four rows, which is stated here rather than smoothed.** Every id reachable from a card's
`skills_unique` has `rarity` ∈ {3, 4, 5}: 22 + 22 + 246 = **290**, which is also exactly the population
carrying `gene_version`, the inherited form only a trainee's own unique skill can have. The rule the
parser can actually apply is the code set, because a parser sees one document and cannot join to the
card document — and the code set is **294**. The four extra rows are `300131`, `300141`, `1400011` and
`1400021`: rarity 5 with an empty `char`, no `gene_version`, named by no card. So the stored count is
294, the evidenced count is 290, and the gap is four unique-shaped skills this catalogue cannot
attribute to a trainee. Nothing outside the four is affected, and no card names a skill outside the set
(checked: 0 rows reachable from `skills_unique` carry a rarity other than 3, 4 or 5).

**6. `gene_version` and the evolution pair are stored raw or not stored.** `gene_version` is the
`[Global]`-relevant **inherited form** of a unique skill (`inherited: true`, `parent_skills: [host]`,
own cost) — it is neither the Normal→Rare link the brief implied nor evolution, and its nested id is
**not a top-level row** (0 of 290 resolve), so it cannot be a foreign key to self. `evo`/`pre_evo` is the
JP-only system (row 45) and imports no column. Both stay out of the schema until a screen needs them;
an unreadable relationship column is worse than no column.

## Consequences

- **`PRD.md` FR-D-1 is amended**, dated, with the source of every value named in the diff. `rarity` gains
  a citation; the amendment says explicitly that the stored value is the export's class code and that no
  client rarity word is claimed.
- **`SkillSeeder`'s ten names are corrected against `name_en`** (owner ruling). Three are not client
  strings: `Traightaways` has no counterpart at all (nearest family `Straightaway Adept`, `Sprint
  Straightaways ◎`); `Come What May, See Ya Later!` is `Come What May` in the client (`Prepared to Die`
  is the `enname` rendering); `Playtime's Over` is missing its trailing bang. The seeder's docblock
  currently celebrates the first two *as* verbatim client copy, and `D-210` records them from Game8, so
  the correction propagates to `docs/design-research/CONSTRAINTS.md` D-210 and to `G-16`'s evidence, not
  only to the seeder. `KI-5` closed the fabricated-name defect on names read from a local
  `skills.json`; this is the same class, found through the export instead of through the gate.
- **The characters parser's key bug is not inherited.** `KI-23`: `GametoraCharacterParser.php:92` reads
  `name_ja` from a document whose key is `name_jp`, and its test fixture uses the same wrong key, so the
  suite is blind to it. The skills parser reads `name_en`/`jpname`, and its fixture is **cut from the
  live document** rather than hand-written, so the keys are the source's and a rename cannot pass silently.
- **`ADR-0010`'s Spark `target` becomes resolvable.** The Legacy payload stores a Spark's target as a
  name string; with 623 Global skill rows carrying `match_key`, a Green Spark naming `Certain Victory`
  can join to a `skills` row by normalized key. That is read-path work for the Legacy surface's owner,
  not storage work, and it is recorded here rather than done here.
- **`SKILLS-GAPS.md` moves**: G-SK-1 (columns) and G-SK-2 (the import) become built; G-SK-9 gains a data
  path (effect code 21); G-SK-10 gains mechanical confirmation and stays closed; G-SK-3/G-SK-4 (hint
  level) stay open — the import carries no per-run hint level, and conflict row 16 still forbids the
  multiplier.

## What this does not decide

- **No hint levels, no discounts.** Nothing in this import puts a hint level anywhere; `run_skills` gains
  no column here, and the copy ruling (level, no percentage) binds any future one.
- **No skill search (FR-D-2).** The catalog screen is a separate slice; 623 Global rows make it
  buildable, they do not build it.
- **Support-card hint pools stay cut** (`ADR-0005`, R37). `sup_hint` (379 rows) and `sup_e` (495 rows)
  exist in this export and are **not** imported: importing them would be a support-card surface wearing a
  skill column.
- **Fetch approval scope.** The owner authorized this pass's import, which is what `AGENTS.md` escalation
  5 asks for on this host. No robots.txt re-check was run — the same omission `config/uma.php:46-49`
  already records for the first two sources, inherited rather than hidden.
