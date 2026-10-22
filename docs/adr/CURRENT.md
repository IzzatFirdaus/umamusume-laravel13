# Current ADR facts

**What is true right now**, one row per fact, derived from the ADR set and corrected against
the tree. Read this before any ADR body: the bodies are rulings as of their date, and a
ruling's status line is a snapshot that goes stale. This file and the generated index at the
bottom of `README.md` are the two things a session should read before opening a body.

Columns: **Fact** · **Value** · **decision** (the ADR that rules it) · **code** (what proves
it: the test file, the migration, the config key — never a claim) · **owner** (`NNNN §clause`).

The **negative rows** — "do not do X", "this is not permission" — are the highest-value lines
here. A status block cannot hold them, and they are what stop a session re-proposing a dead
option.

## 1. Energy, mood and turn state

| Fact | Value | decision | code | owner |
|---|---|---|---|---|
| Energy is stored as a number, a band, or unknown | `energy` (int) + `energy_state` (`exact`/`band`/`unknown`) + `energy_band` (`low`/`mid`/`high`) | 0024 | migration `2026_10_09_170000_add_energy_state_to_turn_entries`, `App\Enums\EnergyState`, `TurnEntry` casts | 0024 §Decision |
| Backfill rule | non-null `energy` → `exact`; null → `unknown`; `energy_band` left null | 0024 | same migration | 0024 §Decision |
| Band is a word, not a coarse number | no midpoint is derived from a band | 0024 | `EnergyState` enum | 0024 §Decision |
| Mood tiers are the five client strings | `Great`/`Good`/`Normal`/`Bad`/`Awful` | 0003 | `App\Enums\MoodTier` | 0003 §`turn_entries` |
| Turn values are end-of-turn totals, not deltas | every numeric column stores the absolute value | 0003 | `turn_entries` convention | 0003 §Design principle |
| **Do not** treat null energy as `0` | null is an absence, renders `N/A` with a `title` | 0024 | `AGENTS.md` §5 | 0024 §Decision |
| **Do not** reopen the Energy non-goal lift | 0001 lifted §6.11 for Energy guidance only | 0024, 0001 | — | 0024 §Status |

## 2. Stat ceilings and validation bounds

| Fact | Value | decision | code | owner |
|---|---|---|---|---|
| A stat's ceiling is its scenario's per-stat cap | `base_cap` + scenario `cap_bonus`, clamped to `hard_cap` | 0015 | `App\Services\ScenarioCaps::forRun()`; `tests/Feature/StatCeilingsTest.php` | 0015 §Decision |
| `ScenarioCaps` is the single ceiling owner | every validator, band and advisor calls `forRun($run)` | 0015 | `StoreTurnEntryRequest:70`, `NextDecision:338`, `CockpitController:386` | 0015 §Decision |
| A no-scenario run is held to `base_cap`, no bonus | five stats validate at 0..1200 | 0015 | `ScenarioCaps::scenarioCaps()`; KI-47 closed by `04658f2` | 0015 §Decision |
| Skill points are uncapped | `sp` stays `nullable\|integer\|min:0` | 0015 | `StoreTurnEntryRequest` | 0015 §Skill points |
| A run may record entered per-stat ceilings | four nullable `cap_speed`/`cap_stamina`/`cap_power`/`cap_guts` override the scenario figure | 0025 | migration `2026_10_12_140000_add_entered_caps_to_training_runs_table` (Ran on `database/database.sqlite` as batch 16, 2026-10-12), `ScenarioCaps::ENTERED_CAPS` | 0025 §Decision |
| Entered caps are Trainer data, never engine-owned | no `data_sources` row, fetch engine never writes them | 0025 | `AGENTS.md` §8 | 0025 §Decision |
| Wit has no entered cap | `cap_wit` is not a column; scenario bound is 1800 | 0025 | `ScenarioCaps::ENTERED_CAPS` (four entries) | 0025 §Decision |
| **Do not** widen validation to a flat `0..2000` | 0015 superseded 0002 option B; a flat constant is wrong in principle | 0002 (superseded by 0015) | `ScenarioCaps` | 0015 §Why this is the shape |
| **Do not** read `hard_cap` as the target | it is the clamp, not the ceiling a Trainer can reach | 0015 | `ScenarioCaps::caps()` `min()` | 0015 §Why this is the shape |
| **Settled 2026-10-12**: `config/scenarios.php` and the `scenarios` table caps have one parity test | ADR-0015 recorded two homes for one number with nothing checking they agreed; `ScenarioCapParityTest` now spans both, and a further drift in either fails it | 0015 | `tests/Feature/ScenarioCapParityTest.php` | 0015 §Deviation |
| **Open**: nothing else checks a config value against a fetched row | the parity test spans config and the table; a third home for a cap figure would still go unnoticed | 0015 | — | 0015 §Deviation |

## 3. Scenario composition and the calendar

| Fact | Value | decision | code | owner |
|---|---|---|---|---|
| Scenario names enter the layout path only from `config/scenarios.php` | no component branches on a scenario name | — | `config/scenarios.php`; gate G-33, D-240 | `AGENTS.md` §7 |
| `config/scenarios.php` owns panels, steps, grade objectives, shop | composition descriptors, not a data store | 0003 R1 | `config/scenarios.php` | 0003 §Amendment R1 |
| `scenario_slots` is the forward timeline store | one canonical store, `kind` discriminator | 0003 R1 | migration `2026_09_27_153416` | 0003 §Amendment R1 |
| `scenario_races` is deprecated and frozen | no new rows, no new queries | 0003 R1 | `Adr0003SchemaTest` | 0003 §Amendment R1 |
| `race_entries.scenario_slot_id` stays nullable | a Trackblazer free race has no slot | 0003 R2 | `tests/Feature/Schema/RaceEntrySlotLinkTest.php` | 0003 §Amendment R2 |
| URA Finale slot seeding is Option A (committed export) | Slice 11; other scenarios and fetch-engine work await owner decision | 0009 | `ScenarioSlotSeeder` | 0009 §Status |
| **Do not** cite 0009 as permission to seed | it is evidence the source question is answered, not the schema question | 0009 | — | 0009 §Decision |
| **Do not** re-litigate aptitude coverage | FR-A-5 letters are stored and complete | 0004 | `umamusume` aptitude columns | 0004 §Decision |

## 4. Historical run import

| Fact | Value | decision | code | owner |
|---|---|---|---|---|
| A historical run imports as the CSV the app exports | eleven columns, two POSTs (preview + write) | 0017 | `ImportHistoricalRun`, `ImportHistoricalRunRequest` | 0017 §Decision |
| The write surface is web-only | no `POST /api/v1/...` import route | 0017 | `ApiV1ValidationEnvelopeTest` | 0017 §Decision |
| Turns only; skills, deck and races are named on screen as omitted | importing a name→id best match would attach an unstated `skill_id` | 0017 | preview page `role="note"` | 0017 §Decision |
| `imported_at` / `import_source` are a new pair | not borrowed from provenance | 0017 | migration `2026_09_30_174646_add_import_provenance_to_training_runs_table` | 0017 §Decision |

## 5. Support cards, deck and ownership

| Fact | Value | decision | code | owner |
|---|---|---|---|---|
| Support-card reference entities are authorized | `support_cards` + `support_effects`, engine-owned | 0014 | migrations `2026_09_30_142618`, `2026_09_30_151945` | 0014 §Decision |
| The deck records six cards per run | `deck_slots` join table, `slot_position` 1..6 unique per run | 0014 | `DeckSlot::booted()` guard | 0014 §Linkage shape |
| `slot_position = 6` is the friend slot | the role belongs to the slot, never the card | 0014 | `DeckSlot` | 0014 §Linkage shape (0005 correction 1 stands) |
| A deck slot may record owned-or-rented | nullable `deck_slots.ownership` (`OWNED`/`RENTED`), null = not recorded | 0023 | migration `2026_10_09_120000_add_ownership_to_deck_slots_table` (**Ran**), `DeckSlot::OWNERSHIP` | 0023 §Decision |
| Ownership is per slot, never derived from inventory | a card owned in one run may be borrowed in another | 0023 | `DeckSlot::booted()` | 0023 §Explicitly unchanged |
| The collection stays cut | no `UserSupportCard`, no level, no limit break, no perk level | 0014, 0023 | — | 0023 §Explicitly unchanged |
| **Do not** cite 0005 as permission | 0005 is DECLINED; 0014 supersedes it | 0014 | — | 0005 §Status |
| **Do not** add card tier labels | no current Global source for card-tier assessment | 0014 | — | 0014 §Held |

## 6. Character cards and the profile block

The `feat/catalog-detail-page` branch record (`SUPERSEDED-feat-catalog-detail-page.md`) moved to
`archive/adr/` on 2026-10-12. Its "canonical branch / canonical schema is ADR-0013" claim described a
merge-time state the 2026-09-30 port reversed: master's `ADR-0012` Decision 4 and migration
`2026_09_29_182820` are what shipped. Its table-hazard warning (two migrations creating one table under
different filenames, so git reports a clean merge and the second migrate throws) is still worth reading
before any merge of that branch.

| Fact | Value | decision | code | owner |
|---|---|---|---|---|
| `character_cards` is the costume-card catalog layer | keyed by the source's `card_id` | 0008 | migration `2026_09_29_120100_create_character_cards_table` | 0008 §Decision |
| `training_runs.character_card_id` references the card a run started on | nullable FK | 0008 | migration `2026_09_29_120200` | 0008 §Decision |
| The card's stat arrays are stored on the card | `base_stats`, `four_star_stats`, `five_star_stats`, `stat_bonus` (json) | 0012 | `CharacterCardSchemaTest` (guards the shipped list) | 0012 §Decision 1 |
| The profile block is a sibling `umamusume_profiles` table | `height`/`three_sizes_b`/`_h`/`_w`, not `height_cm` etc. | 0012 Decision 4 | migration `2026_09_29_182820_create_umamusume_profiles_table` | 0012 §Decision 4 |
| The profile field list is an allowlist | `rl`, `sex`, `race` are never stored, never rendered | 0012, 0013 | `GametoraCharacterProfileParser`; `StoreCharacterProfiles` | 0012 §Decision 4 |
| `va_en` is the romanisation of `va_ja` | one performer in two scripts, not a dub cast | 0013 | docs/data/2026-09-30-characters-source-probe.md | 0013 §Consequences |
| **Do not** cite 0013 as authority | 0013 is withdrawn; 0012 Decision 4 is the authority | 0012 | — | 0012 §Decision 4 blockquote |
| **Do not** read the stat arrays for anything but display | §6.11 forbids a prediction engine; storage is not the thing forbidden | 0012 | — | 0012 §use-side constraint |

## 7. Veterans and the Legacy payload

| Fact | Value | decision | code | owner |
|---|---|---|---|---|
| The Legacy Select read-back is one typed payload on the run | `legacy_selection` json, existing FKs stay | 0010 | `LegacySelectionPayload` | 0010 §Decision |
| The Veteran tag filter matches a folded twin | `veterans.tags_normalized` holds the lowercased tags | 0022 | migration `2026_10_08_180000_add_tags_normalized_to_veterans_table` (**Ran**), `Veteran::booted()` saving guard | 0022 §Decision |
| `tags` keeps the Trainer's own spelling | the twin is a filter key, never rendered | 0022 | `ListVeterans` | 0022 §Decision |
| A bulk query-builder write can leave the twin stale | `Veteran::query()->update()` bypasses model events | 0022 | — | 0022 §Consequences |

## 8. Design, docs and release

| Fact | Value | decision | code | owner |
|---|---|---|---|---|
| Root `DESIGN.md` is the single source of truth for the visual contract | research `DESIGN.md` is the measured-anchor artifact | 0006 | root `DESIGN.md` §2.1 | 0006 §Decision |
| Theme default is Light base + preference resolution, dark opt-in | stored preference → `prefers-color-scheme` → light | 0006 | preference resolver | 0006 §Decision |
| C-7 loading obligation: browser-native loading satisfies initial render | custom indicator required only for user-initiated async | 0007 | `docs/research-scratch/GOVERNANCE.md` §"CONSTRAINTS.md (root quality bar)" C-7 row + interpretation blockquote | 0007 §Decision |
| One facet contract for the three filter surfaces | unknown facet refused, `pageSize` honoured and clamped | 0018 | `PageSize`, `FilterContractTest` | 0018 §Decision |
| The label is `2.0.0` and the release is a pre-release | release must not target `master` | 0019 | — | 0019 §Decision |
| **Owed**: `AGENTS.md` §14/§3 correction from 0019 | the "no changelog or version file" line and the doc table | 0019 | not landed | 0019 §Status |

## 9. Trainer Desk 2.0 program

| Fact | Value | decision | code | owner |
|---|---|---|---|---|
| The web surface is Inertia + Vue 3 | §6.2 lifted in full | 0020 | `resources/js/`; `AGENTS.md` §1 | 0020 §Decision 1 |
| Race prediction stays deferred | §6.11 unchanged for race outcomes; the data blocker is unclosed | 0020, 0016 | — | 0020 §Decision 4 |
| Sourced artwork is mirrored locally and derived from ids | fetch half built (`uma:fetch-art`); display half built (`ArtworkSlot.vue`, `artwork.show`) | 0021 | `ArtworkMirrorTest`; `uma:fetch-art --dry-run` | 0021 §Decision |
| No uploads | `PRD.md` §6.13 untouched | 0021 | — | 0021 §Preserves |
| **Do not** cite 0016 as permission to build readiness | 0016 is an OPEN QUESTION, held | 0016, 0020 | — | 0016 §Status |

## 10. Framework check (2026-10-22)

The ADR set was checked against `docs/framework-ui-ux/` (the ten-framework corpus and its re-check
table). These are the facts the check established; the reasoning is in each ADR's ledger, and the rule
ids are the corpus's own.

| Fact | Value | decision | code | owner |
|---|---|---|---|---|
| The client-side visit's loading signal is owned by no ADR | `VisitProgressBar.vue` discharges ADR-0007's clause 2 on the Inertia surface; the ruling that made the surface an SPA never revisited it | 0007, 0020 | `resources/js/components/VisitProgressBar.vue` | 0007 §Ledger, 0020 §Ledger |
| **Do not** read ADR-0007 clause 1 as covering a client-side visit | it describes a server-rendered first navigation; on an Inertia visit the previous screen stays on screen | 0007, 0020 | `resources/js/spa.ts` | 0007 §Ledger |
| The deck picker's one-copy rule is refused after the write, not by the control | the picker offers a card already equipped in another slot; `StoreDeckRequest` refuses the duplicate | 0014 | `DeckSelectController::picker()`; `StoreDeckRequest` | 0014 §Ledger |
| A committed turn's correction is an in-place update | `runs.turns.update` writes the row in place; ADR-0027's `corrected_from`/`superseded_by` have no writer, and the Timeline reads `updated_at` | 0027 | `TrainingRunController::updateTurn()`; `Career/TimelineController` | 0027 §Ledger |
| The two-marker ceiling condition is unbuilt | no shipped component draws the 1,200 halved-gains line or the ceiling marker | 0002 | `docs/audits/rice-shower-unity-cup-ux-walk-resolutions.md`; `CareerStatePanel.vue` | 0002 §Ledger |
| The design-authority ruling predates the framework corpus | `docs/framework-ui-ux/` states visual rules of its own (type scale, one accent, 44px) and no document says which wins | 0006 | `docs/framework-ui-ux/design-framework-index-2026-10-21.md` | 0006 §Ledger |
| **Do not** read the SPA accessibility carry-across as covering contrast | ADR-0020 names four behaviours and cites no success criterion; no contrast pair is measured | 0020 | `DESIGN.md` §2.1; gate G-18 | 0020 §Ledger |
| **Do not** read ADR-0021's pre-run pick blocker as a control-type problem | the pick screen mounts `TraineeCombobox`; the blocker is that it renders no frame | 0021 | `resources/js/pages/Runs/Create.vue` | 0021 §Ledger |
| The filter refusal is presented in text and announced | `role="alert"`, `aria-invalid` and `aria-describedby` on the picker; the Decision pins the landing place, not the presentation | 0018 | `resources/js/components/catalog/TraineeCatalog.vue`; `Form/BaseSelect.vue` | 0018 §Ledger |
