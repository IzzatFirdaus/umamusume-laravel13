# Flows — Veterans and Legacy Lab

Date: 2026-11-06. HEAD: `67a4830`. Documentation only; no code changed.
Evidence: `app/Http/Controllers/VeteranController.php`, `app/Http/Controllers/LegacyController.php`, `app/Actions/{ListVeterans,ShowVeteran,RecordVeteran}.php`, `app/Services/Legacy/{AncestryGraph,VeteranRow,StartingCapProjection}.php`, `app/Models/Legacy/LegacySelectionPayload.php`.

Two surfaces, one stored fact. A **Veteran** is a finished career filed into a library. A **Legacy selection** is an ancestry record attached to a run. They are separate objects with separate tables, and neither computes anything.

---

## 1. Veterans library

### 1.1 Routes

| Method | URI | Name | Controller |
| --- | --- | --- | --- |
| GET | `/veterans` | `veterans.index` | `VeteranController::index` |
| GET | `/veterans/compare` | `veterans.compare` | `VeteranController::compare` |
| GET | `/veterans/{veteran}` | `veterans.show` | `VeteranController::show` |

`/veterans/compare` is declared **before** `/veterans/{veteran}` and the binding is `whereNumber`, so the literal cannot be read as a model id. The same pair of decisions is made for `/legacy/compare` and `runs.import`.

### 1.2 Index

Filters (`VeteranSearchRequest`): `search` (matches `trainingRun.umamusume.name` or `name_ja`), `scenario`, `tags` (list), `order` (`newest|oldest`), `pageSize` (clamped by `PageSize`).

`ListVeterans::handle(filters, pageSize, order)`:

- Eager loads `trainingRun.characterCard` and `trainingRun.umamusume.cards` — the chosen-form frame the row reads first, and the fallback the identity frame falls back to. Both are loaded so neither path is a query per row.
- An absent filter is no filter; an empty string or empty tag list reads the same way, so a cleared field does not filter the library to nothing.
- **No ranking, no score, no "best parent".** The library stores and searches what the Trainer recorded; it never orders by a computed value (FR-G-4). The plan's §7 "rating" filter is deliberately not built.

Props: `veterans` (paginated `VeteranRow::from`), `order`, `filters`, `scenarios`, `totalCount`, `notice` (`LegacyController::RECORD_ONLY_NOTICE`), `filingNotice`, `file_veteran_url`, `find_parents_url`, `compare_url`, `compare_max` (`VeteranCompareRequest::MAX_VETERANS` = 6), `absences`, `searchAction`.

`find_parents_url` carries the library's tag filter onto the Legacy Lab browse list, because those tags are the distance/surface/style facets a parent would need. It applies no score. The trainee and scenario filters are deliberately not carried.

The `pageSize` clamp is `PageSize::clamp()` — `MIN = 1`, `MAX = 100`, `DEFAULT = 25` — one owner for every filter surface, web and API (`ADR-0018`).

### 1.3 Detail

`ShowVeteran::handle()` eager-loads `trainingRun.{umamusume, turnEntries, skills, raceEntries, inheritanceParentA, inheritanceParentB}` — exactly the facts FR-G-1 names, nothing derived. The two parents join the list for SCREEN-021's Ancestry view.

The turn-by-turn record stays on the run; this screen links to it rather than restating it. A Veteran is a pointer to one career plus the Trainer's own metadata, and a second reading of the same turns would be a second place for the two to disagree.

### 1.4 Compare

`VeteranCompareRequest::MAX_VETERANS = 6`. Columns are built by mapping each Veteran to `VeteranRow::from` merged with:

- `career` = `{turns, energy, fans}` from `stripValues()`;
- `stats` = the **last logged turn's** totals (never a sum of deltas, `ADR-0003`);
- `counts` = `{skills, races}`;
- `aptitudes` = `umamusume->aptitudeAxes()`.

`aptitude_axes` travels once beside the cells (`Umamusume::APTITUDE_AXES`), not derived from the first column: a career with no published letters must not decide the shape for every career.

The selection list is gone — the library owns the checkboxes now, so this page reads only the ids the URL named. Empty state: `Nothing is selected, so there is nothing to line up. Tick up to 6 careers on the library and open the comparison from there.`

### 1.5 Record-only notice

One constant, one owner: `LegacyController::RECORD_ONLY_NOTICE = 'Record only. This screen stores and compares what you enter. It does not compute inheritance.'` Used by the library index, the library compare, the Legacy index, the Legacy builder and the Legacy compare — three surfaces, one sentence, so the ban cannot be reworded into promising less.

---

## 2. Filing a Veteran

`GET /training-runs/{run}/veteran` (`runs.veteran`) and `POST` (`runs.veteran.store`); see `flows-career-workspace.md` §10 for the payload. The action is `RecordVeteran`:

```php
if ($run->status !== RunStatus::Completed) {
    throw new InvalidArgumentException(
        "Run [{$run->id}] is {$run->status->value}; only a Completed run is recordable as a Veteran.",
    );
}

return Veteran::updateOrCreate(
    ['training_run_id' => $run->id],
    ['tags' => $tags, 'notes' => $notes],
);
```

Two consequences worth stating: a Veteran is keyed on its run (one run is one Veteran, so re-saving rewrites rather than appending), and a career still `Active` or `Retired` is refused — filing an unfinished career would put something that reads as finished into a library that means finished.

`ADR-0010` keeps the run's foreign keys as the Veteran's identity; a Veteran is never a standalone record.

---

## 3. Legacy Lab

### 3.1 Routes

| Method | URI | Name | Controller | Gate |
| --- | --- | --- | --- | --- |
| GET | `/legacy` | `legacy.index` | `LegacyController::index` | — |
| GET | `/legacy/compare` | `legacy.compare` | `LegacyController::compare` | `LegacyCompareRequest` (`MAX_RUNS = 4`) |
| GET | `/legacy/{run}` | `legacy.builder` | `LegacyController::builder` | `whereNumber('run')`; `abort_unless(status === Active, 404)` |
| PUT | `/legacy/{run}` | `legacy.update` | `LegacyController::update` | `StoreLegacySelectionRequest`, `whereNumber` |

### 3.2 Index — saved pairs

A **pair is one object**: two parents plus their four ancestor slots, stored as one `training_runs.legacy_selection` column (`ADR-0010`). "A saved pair" is a run that carries a selection, not two Veteran rows.

- `PAIR_LIMIT = 12`: a window on the most recent pairs, not a paginated collection. The library below it is the paginated list, and a second pager over a projection of the same rows would be two controls over one set.
- Each row projects `{run_id, trainee, scenario_label, pair_label, parent_a, parent_b, blue_grades, pink_range, hint_family, builder_url, compare_url}`.
- `blue_grades` = the stat sparks' star counts; `pink_range` = the width of the aptitude sparks' stars; `hint_family` = the green plus white sparks (hint levels, not flat stat boosts, DM-3). All three are read straight off the payload.
- `parent_a` / `parent_b` are each null when that slot holds no parent, and the page prints the absence rather than a pair label hiding which half is missing.
- Read-only: no table, no write, no derived score. Owner ruling 2026-10-28 moved the Veteran library to `/veterans`, so this page no longer carries a second filtered copy of it.

### 3.3 Builder

`abort_unless($run->status === RunStatus::Active, 404)` — an ancestry is recorded for a live career, not a finished one.

Props: `run`, `trainee`, `hasSelection`, `graph`, `caps`, `projection`, `projectionNote`, `affinity`, `roster`, `knownNames`, `sparkKinds`, `affinityGrades`, `rankLetters`, `sparkTargets`, `notice`.

- `hasSelection` is the third state `TrainingRun::legacySelection()` distinguishes: "you never opened this screen" is a disclosure, while two empty parent records is a claim about a client screen that cannot be completed that way.
- `caps` reads `ScenarioCaps::forRun()` — the run's **entered** ceiling where it states one, the scenario's published cap otherwise, with Wit carrying its reason because `ADR-0025` stores no `cap_wit`. The same method feeds the compare page, so the builder and the compare screen cannot disagree about a cap.
- `projection` is a separate prop from `caps`: `caps` is the ceiling the run starts **from**, `projection` is what the recorded three-star factors add to it (`StartingCapProjection::project()`), and the page shows both so the arithmetic stays visible (`ADR-0025` 2026-10-31 amendment).
- `knownNames` is a spelling aid over the local catalogue, never a requirement: grandparents are frequently absent from it, which is why they are names and not ids (`ADR-0010` Consequences §2).
- `sparkTargets` per category: `Stat` from `config('scenarios.stat_order')`, `Aptitude` from `LegacySelectionPayload::SPARK_APTITUDES`, `Skill` from the skill table. The same assembly the wizard's ancestry step sends, because both screens post against the one request.

### 3.4 The payload shape

`LegacySelectionPayload`:

- `RANK_LETTERS` — the per-stat set `G F E D C B A S`; `UG` **is** in the set (R3-08 does not reproduce).
- `AFFINITY_GRADES` — the stored affinity grades.
- `SPARK_KINDS` / `SPARK_KIND_LABELS` from `AncestryGraph`.
- `SPARK_KEYS = ['kind','target','stars']`, enforced by `assertKeys()` — magnitude is **derived**, never stored: `BLUE_SPARK_BONUS = [1=>5, 2=>12, 3=>21]`.
- `ancestors_sparks` — a list aligned by ancestor index, two per parent; `MAX_ANCESTORS = 2`.
- `ancestors` allows `{id, name, costume}` per ancestor.
- `ancestors_rank_letters` and `ancestors_rank` carry the grandparent ranks (R3-09).

### 3.5 Compare

`LegacyCompareRequest::MAX_RUNS = 4`; validation message `Compare at most 4 runs at once.`

Per column: `veteran_id`, `run_id`, `trainee`, `tags`, `notes`, `hasSelection`, `graph`, `caps`.

- The library row is optional: a run can hold a Legacy selection and never have been filed as a Veteran, and its tags and notes then read as `N/A` rather than as an empty list that looks like "this Veteran has no tags".
- `startingCaps()` is the same owner as the builder's `caps`: `ScenarioCaps::forRun()`, five rows with a note per stat (Wit prints the scenario's cap and says so).
- Rows are properties, columns are runs, and **both are stored**. There is no total row, no "best" marker and no ordering — each would rank configurations on a value the tool does not hold (`ADR-0020` §3).

---

## 4. The record-only boundary

The Legacy Lab:

- records what the Trainer entered;
- aligns stored rows so two configurations can be read side by side;
- prints the sourced probabilities the reference corpus publishes.

It does **not**:

- compute an inheritance outcome;
- price an affinity payout;
- roll a star chance;
- recommend a combination;
- order or rank configurations.

The boundary is stated in `routes/web.php` (the `/legacy` block's comment), in `LegacyController`'s docblock, in the shared `RECORD_ONLY_NOTICE` constant, and on-screen. `ADR-0020` §3 owns the ruling.

Any residual cap arithmetic question (`R6-52`, `Source conflict / open`) is recorded as unresolved rather than approximated: the source document itself reports that its own numbers do not reconcile (`Speed +25 is not a sum of 9s, Stamina +22 is not a sum of 16s, and Power +68 needs four activations`), so a field would encode a mechanism the corpus has not established.

---

## 5. Cross-door relationships

| Door | From | To |
| --- | --- | --- |
| `Save Veteran` | Result screen, `/{run}/veteran` | creates/updates a `veterans` row |
| Veterans index | rail "Veterans" | `ListVeterans` |
| Find parents | Veterans index `find_parents_url` | `/legacy?tags[]=...` |
| Legacy builder | `/legacy` rows, `legacy/{run}` | requires an `Active` run |
| Legacy compare | `/legacy/compare?runs[]=...` | up to 4 run ids |
| Veteran compare | library checkboxes, `/veterans/compare?veterans[]=...` | up to 6 ids |

The two comparisons answer different questions: `/legacy/compare` compares **recorded ancestries** (what parents and sparks a run was built from); `/veterans/compare` compares **finished careers** (their final stats, counts and aptitudes). A Veteran row's own "Compare" link leads to the Legacy Lab, which is a different question — the compare page's empty-state copy was rewritten to say so.

---

## 6. Verification surface

| Claim | Pinned by |
| --- | --- |
| Library query, filters, no ranking | `VeteranLibraryTest`, `VeteranLibraryScreenTest` |
| Library compare | `VeteranCompareTest` |
| Filing rules (Completed only, keyed on run) | `SaveVeteranTest` |
| Legacy index, builder, record-only copy | `LegacyLabPageTest`, `LegacySelectionSchemaTest`, `LegacyDisambiguationTest` |
| Ancestor ranks and grandparent Sparks | `GrandparentRanksTest`, `GrandparentSparksTest`, `AncestorRankTest` |
| Spatial/visual parity | `tests/browser/veterans.spec.ts`, `veterans-parity.spec.ts`, `career-save-veteran.spec.ts` |
