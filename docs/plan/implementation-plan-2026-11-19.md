# Implementation plan - outstanding work at HEAD `fec658f8`

Status: executable plan. Nothing in this file has been implemented; no `app/`, `database/`,
`resources/`, `tools/` or `config/` file was modified to produce it.

Tree state at planning time, all measured in this pass:

| Fact | Value | Command that produced it |
| --- | --- | --- |
| HEAD | `fec658f83d1f3ef594285f3284719b3ab730c73f` | `git rev-parse HEAD` |
| Divergence | `0 0` (level with `origin/master`) | `git rev-list --left-right --count HEAD...origin/master` |
| Branch | `master` | `git branch --show-current` |
| Working tree | 21 modified, 24 untracked | `git status --porcelain=v1 -uall` |
| Tags | `0.1.0`, `2.0.1`, `2.0.2`, `2.1.0`, `2.1.1` (no `v3*`) | `git tag --list --sort=-version:refname` |
| Routes | 110 (72 `GET\|HEAD`, 22 `POST`, 13 `PUT`, 3 `DELETE`) | `php artisan route:list --json` |
| Tracked files | 2126 | `git ls-files` |
| Host | PHP 8.5.8, Node v26.12.1, Composer 2.10.2, GNU make absent | `php --version`, `node --version`, `composer --version` |
| Dev DB | 15 runs, 410 race catalog slots, 304 Pending candidates, 133 provenance rows behind 1 source key | `select count(*) from training_runs`, `race_catalog_slots`, `match_candidates where status='Pending'`, `data_sources` |

Evidence labels used below: **measured** = a command in this pass printed it; **read** = source
read, no execution; **inferred** = reasoning from measured facts, stated as such.

Excluded as genuinely complete, with the evidence that excludes it, in the last section. Nothing
counted as progress here is a documentation update, a plan, or a report.

---

## Laws that bind every slice

Carried forward from `docs/plan/implementation-roadmap-v3.0.0.md` and `AGENTS.md`; they are the
reason several slices below are shaped the way they are.

| # | Law | Consequence for this plan |
| --- | --- | --- |
| L1 | Verify the premise before editing. A `file:line` below is a hypothesis until you read it. | Two of the fifteen claims in the audit that produced this plan were wrong. Re-read every citation. |
| L2 | One slice, one commit, explicit pathspecs. | Never `git add -A`; the tree carries 24 untracked owner files. |
| L3 | Never `pint --dirty` (KI-75). | `pint --test --format agent <paths>` on the files you touched. |
| L4 | Never `migrate:fresh --seed` against `database/database.sqlite` without owner approval. | Verify schema slices on a scratch DB file. |
| L5 | A red gate naming a path you did not touch is contention until proven otherwise. | Classify before fixing. |
| L6 | Do not weaken a threshold to pass a check. | Slices that reduce a count must show the count, not lower the bar. |
| L7 | Owner-gated slices park with the exact artifact the owner must sign; an agent does not simulate them. | Slices marked `OWNER` are not executed by an agent. |

---

## Phase 0 - Make the gate honest before trusting it

Every acceptance criterion later in this plan is a command whose output is the evidence. Four of
those commands currently cannot fail, so a green result from them means nothing. This phase is first
because it is the cheapest work in the plan and because every later slice's proof runs through it.

### S0.1 - Two `gates.sh` steps exit 0 by construction

**Measured.** `tools/doc_census.py` contains exactly one `return 0` inside `main()` and zero
`return 1`; the sole `sys.exit` is `sys.exit(main())` at `:399-400`. `tools/check_untracked.py`
contains no `sys.exit` and no non-zero return at all; it ends with a bare print loop. Both are
registered as steps in `tools/gates.sh`.

```
grep -c 'return 1' tools/doc_census.py      -> 0
grep -c 'sys.exit'  tools/check_untracked.py -> 0
```

**Why it is a defect, not a style note.** `bash tools/gates.sh --fast` reports both steps green at any
count. The 528-vs-708 citation ratchet is therefore enforced only by `DocCitationParityTest` inside
the PHP suite, which `--fast` skips. The untracked-file sweep is decorative on both paths.

**Change.**

1. `tools/doc_census.py`: `main()` returns `1` when the dead-link count exceeds
   `DOC_CITATION_BASELINE` (708, the same constant `tests/Feature/DocCitationParityTest.php:44`
   carries). Import the constant from one place or duplicate it with a comment naming the test; do
   not let the two drift silently.
2. `tools/check_untracked.py`: accumulate the `UNTRACKED` lines and `sys.exit(1)` when the list is
   non-empty. Keep printing them.
3. `tools/gates.sh`: no change needed if the two tools now exit correctly; the existing `step()`
   wrapper already records non-zero codes.

**Acceptance.**
```
python tools/doc_census.py >/dev/null 2>&1; echo $?     # 0 at <=708, 1 above
python tools/check_untracked.py >/dev/null 2>&1; echo $?
bash tools/gates.sh --fast                              # reports the real verdict for both
```
RED-then-GREEN: temporarily raise the baseline to 1 to prove the census step can fail, then restore.

**Risk.** `check_untracked.py` currently prints 46 untracked citations whose targets live under
`.agents/`, `.scratch-uma/` and `docs/sitrep/`. Turning it into a failure reddens the gate on day one.
Decide first: either those citations are wrong and get repointed (preferred, it is the gate's whole
job), or the tool stays report-only and is deleted from `gates.sh` so the script stops claiming
coverage it lacks. Do not ship a gate that is red for a reason nobody intends to fix.

### S0.2 - `ui-lint` cannot fail on the rule its own documentation names

**Measured.** `tools/ui-framework-check.mjs:227-228`:
`const debtIncrease = findings.some((f) => f.rule === 'debt-increase'); process.exit(process.argv.includes('--strict') || debtIncrease ? 1 : 0);`
`card-in-card` is emitted as a finding at `:187` and is never fatal. `AGENTS.md:279` states the check
"fails Card-in-Card and new arbitrary Tailwind values". Today: 0 card-in-card findings, exit 0.

**Change.** Add `card-in-card` to the fatal set:
```js
const fatal = findings.some((f) => f.rule === 'debt-increase' || f.rule === 'card-in-card');
```
Leave `--strict` as the opt-in that widens to every finding.

**Acceptance.** `node tools/ui-framework-check.mjs; echo $?` still exits 0 today (0 card-in-card), and
a fixture with a nested Card exits 1. Extend `tests/Feature/UiLintDebtExitTest.php` with the third
case, using the same throwaway-tree pattern its two cases already use.

### S0.3 - `npm audit` prints "clean" when the audit never ran

**Measured, reproduced.** Running the block at `tools/gates.sh:104-119` against a dead registry
(`--registry http://127.0.0.1:9`) prints `npm audit: clean`. `execSync` throws, the catch keeps
`e.stdout || "{}"`, `JSON.parse("{}")` yields no `metadata`, and `if (!v.total)` takes the clean branch.

**Why this is a Floor-adjacent defect.** C-8 (`docs/research-scratch/GOVERNANCE.md:317`) makes the
audit a pre-tag obligation, and `AGENTS.md` §5 forbids fabricated claims. An offline or
proxy-blocked run asserting "clean" with no data behind it is exactly that.

**Change.** In the `catch`, distinguish the states:
```js
let unavailable = false;
try { out = execSync(...); } catch (e) { out = e.stdout || ""; unavailable = !out.trim(); }
...
if (unavailable) { console.log("npm audit: UNAVAILABLE (scan failed, no data)"); process.exit(0); }
```
Exit 0 is correct: the obligation is pre-tag and advisory (`AGENTS.md` §5), so it reports without
reddening an ordinary slice.

**Acceptance.** Dead-registry run prints `UNAVAILABLE`, not `clean`. Live run on this host prints the
real counts.

### S0.4 - Gate G-62 is registered and never runs

**Measured.** `docs/research-scratch/GOVERNANCE.md:542` registers G-62 as automated with the command
`python tools/verify-backdrop.py`. `tools/gates.sh` (whole file read) contains no such step. The six
backdrop plates currently pass when run by hand: `0.64365..1.00000` floor for daybreak, `0.00044..0.01052`
for midnight, `0.00000..0.04443` for eva01, all `ok`.

**Change.** One line in `tools/gates.sh`, in the automated block after `markdown fences`:
```bash
step "backdrop plates" python tools/verify-backdrop.py
```

**Acceptance.** `bash tools/gates.sh --fast` lists the step and it passes on the six committed plates.
Delete one plate temporarily to prove the step can fail, then restore it.

### S0.5 - Two gate entry points disagree about pint

**Measured.** `tools/hermes-preflight.sh:41` runs `vendor/bin/pint --test || { echo "❌ Pint lint failed"; exit 1; }`
with no path list. That is the KI-75 defect `tools/gates.sh:31-45` was written to avoid: on a shared
worktree (`git diff --name-only HEAD` plus untracked), it collects every peer's dirty file and reports
their style errors as the caller's.

**Change.** Replace the reimplementation with a call to the repaired gate:
```bash
bash tools/gates.sh --fast || { echo "❌ Gate failed"; exit 1; }
```
If the preflight is meant to stay narrower than the full gate, copy `tools/gates.sh`'s `collect()`
body verbatim rather than inventing a third semantics.

**Acceptance.** On this tree (21 modified, 24 untracked) the preflight reports the same pint verdict
`gates.sh` does. Two commands, one answer.

### S0.6 - The status register cannot report its own state

**Measured.** `python tools/audit_status_register.py KNOWN-ISSUES.md --index-lines 130` exits 1:
`index rows: 110`, `body entries: 118 headings / 112 distinct ids`, `NO INDEX ROW: ['KI-104', 'KI-106']`,
`census: {CLOSED: 108, OPEN: 1}` against `body census: {MISSING: 16, CLOSED: 72, OPEN: 24}`,
`RESULT: needs reconciliation`.

**Cause, measured exactly.** `tools/audit_status_register.py:23`:
```python
INDEX_ROW = re.compile(r'^([A-Z][A-Z0-9]*-\d+)\s+([A-Za-z][A-Za-z0-9\-]*)')
```
`KNOWN-ISSUES.md:55` and `:57` begin with a Markdown list marker: `- KI-104 CLOSED ...` and
`- KI-106 OPEN ...`. The regex has no allowance for a leading `- `, so both rows are dropped. Verified
directly: `INDEX_ROW.match(line)` is `None` for both, and `INDEX_ROW.match(line.lstrip('- '))` matches.
The rows are present in the file; the parser cannot see them.

**Change.** Allow an optional leading list marker:
```python
INDEX_ROW = re.compile(r'^[-*]?\s*([A-Z][A-Z0-9]*-\d+)\s+([A-Za-z][A-Za-z0-9\-]*)')
```
Then re-run and reconcile whatever the repaired parser surfaces. Expect the OPEN counts still to
disagree: 24 body entries carry OPEN text while the index summarises 1. That is a real register
problem, not a parser artifact, and it is S0.7.

Add the step to the gate so the drift cannot recur:
```bash
step "status register" python tools/audit_status_register.py KNOWN-ISSUES.md --index-lines 130
```
Only add it once `RESULT:` reads green; a gate red on day one gets ignored.

**Acceptance.** `--index-lines 130` exits 0, `NO INDEX ROW: none`, and the OPEN tally in the index
equals the tally in the bodies.

### S0.7 - The register's OPEN count is internally contradictory

**Measured.** Index census `OPEN: 1` (`KI-162`); body census `OPEN: 24`. `KI-104` carries two
unrelated subjects under one number (`:3198` the nested-disclosure browser case, `:3298` ADR-0027
proposed-not-accepted), a duplicate the file itself documents at `:332-335`.

`VERSION.md:84` identifies the index as the status authority, and Phase-1 exit criterion (b) is
evaluated against it. A reader trusting the index believes the defect backlog is closed.

**Change.** Per-row reconciliation pass, no code: for each of the 24 body entries whose status token
reads OPEN, either (a) it is genuinely closed and the body needs a dated closure line, or (b) it is
open and the index summary needs the row. Renumber the second `KI-104` to the next free number
(`KI-169`, since `:5481` holds `KI-168`) and update every citation to it. Never renumber an existing ID
(`AGENTS.md` §11).

**Acceptance.** `python tools/audit_status_register.py KNOWN-ISSUES.md --index-lines 140` reports
`CONTRADICTIONS: none` and equal OPEN tallies on both sides.

**OWNER-AFFECTED.** Closeout of a defect row is the owner's call where the entry names an owner
ruling. Park those with the exact row and the two candidate dispositions.

---

## Phase 1 - Data-integrity defects

Ordered by blast radius. S1.1 is first because it is a reachable 500 that also writes to the wrong
table, and because every other review-queue and ingestion slice is tested through the same screen.

### S1.1 - Confirm on a race candidate throws, and would write a race into the trainee table

**Root cause, measured.** `app/Services/DataPipeline/PipelineRunner.php` has seven `is_a()` routing
branches, at `:73` (`RaceCatalogSourceParser`), `:99` (`TraineeGoalSourceParser`), `:130`
(`SkillSourceParser`), `:159` (`CharacterCardSourceParser`), `:200` (`ProfileSourceParser`), `:229`
(`SupportCardSourceParser`), `:244` (`SupportEffectSourceParser`). There is **no** branch for
`RaceEnrichmentSourceParser`: `grep -c RaceEnrichmentSourceParser app/Services/DataPipeline/PipelineRunner.php`
returns 0, while the interface's own docblock names four implementations and says its only consumer is
`uma:enrich-races`.

Those four parsers therefore fall through to the generic character loop at `:252-330`. Measured by
parsing each committed snapshot through its own parser:

| Source | Records | Carries `name` | `name_en` | `title` | Consequence today |
| --- | --- | --- | --- | --- | --- |
| `gametora-race-fans` | 61 | no | no | no | `recordName()` throws |
| `gametora-race-rewards` | 66 | no | no | no | `recordName()` throws |
| `gametora-racetracks` | 47 | no | **yes** | no | files 47 Pending candidates |
| `gametora-races` | 331 | no | **yes** | no | files 331 Pending candidates |

`PipelineRunner::recordName()` (`:353-364`) accepts only `name`, `name_en`, `title` and throws
`RuntimeException("{$sourceKey}: record [{$record['id']}] has none of name, name_en or title.")`
otherwise. So `php artisan uma:fetch` with no argument dies on the first name-less enrichment source
and never reaches the remaining keys; and the two sources that do carry `name_en` write one Pending
review row per race.

**The 304 rows are the residue of that leak, not stale data.** Measured in the dev DB:
`SELECT source_key, count(*) FROM match_candidates GROUP BY source_key` returns exactly one row,
`gametora-races = 304`. There is no other entity in the queue.

**Second-order defect, same slice.** `app/Actions/ResolveMatchCandidate.php:50-60` (`confirm()`)
passes that payload straight to `PromoteMatchedRecord::handle()`, whose `:45` calls
`$this->normalizer->normalize($record['name'])`. The race payload carries `name_en`, not `name`:

```json
{"id":100101,"name_en":"February Stakes","name_ja":null,"course":1,"direction":2,"season":4,
 "entries":16,"factor_effect_1":"...","factor_effect_2":"power","track_id":10006,
 "url":"https://gametora.com/data/umamusume/races.51478888.json"}
```

`json_extract(payload,'$.name')` is null for all 304 rows, and `suggested_umamusume_id` is NULL on all
304, so the merge branch has no fallback either. And even when a payload does carry a `name`,
`confirm()` promotes a *race* through a shape that constructs `App\Models\Umamusume`: a race landing in
the trainee table is a data-integrity write, not a crash.
`resources/js/components/review/CandidateForm.vue:186` renders "Confirm this match" on every Pending
row regardless of entity, and `ReviewController.php:33-52` lists all Pending rows with no entity
filter.

**Reproduced, not inferred.** In application context:
```
$p = ['id'=>1,'name_en'=>'February Stakes'];
app(App\Services\DataPipeline\NameNormalizer::class)->normalize($p['name']);
-> TypeError: normalize(): Argument #1 ($name) must be of type string, null given
```
An undefined array key evaluates to `null` under Laravel's error handling, so the request 500s.

**Two fixes, and they are separate slices.** The leak (S1.5) stops the rows being filed. The
resolution path (this slice) makes an already-filed row honest. Do S1.5 first: it is smaller, it
stops `uma:fetch` aborting, and it makes this slice's tests cheap because the queue starts empty.

**Change.**

1. **Schema.** New migration `add_entity_to_match_candidates_table`:
   - `$table->string('entity')->default('umamusume')->after('source_key');`
   - Backfill by source: rows whose `source_key` parses a race family (`gametora-races`,
     `gametora-race-catalog`, `gametora-race-fans`, `gametora-race-rewards`, `gametora-racetracks`)
     become `entity = 'race'`; everything else keeps `umamusume`. Derive from a small map, not from a
     string heuristic.
   - A working `down()` that drops the column.
   - Update the digest in `ARCHITECTURE-ESSENTIALS.md` and add an ADR (schema change rule,
     `AGENTS.md` §11). There is a live decision already queued for this: roadmap D-8 / S5.2.
2. **Queue write.** `PipelineRunner.php:310-325` sets `entity` from the source config. Add an `entity`
   key to each source entry in `config/uma.php` rather than hard-coding a list in the runner; the
   config already owns per-source facts.
3. **Resolve.** `ResolveMatchCandidate.php` gains an entity dispatch. For `entity = 'race'`, a
   Confirm resolves through the race catalog path (`StoreRaceCatalogSlots` / `RaceCatalogSlot`), never
   through `PromoteMatchedRecord`. For `umamusume`, today's behaviour is unchanged.
   `ResolveMatchCandidateRequest.php` validates `status` against `CandidateStatus` only; add a guard so
   `Confirmed` for an unmatched entity is a 422 with a named reason, not a 500.
4. **Payload contract.** Give every candidate a resolvable display name at write time:
   `PipelineRunner::recordName()` (`:353-364`) already knows the precedence
   `name`, `name_en`, `title`. Persist that answer into `payload['display_name']` so the review screen
   and any future resolver never depend on a key that may be absent.

**Frontend.**
- `Review/Index.vue` and `Review/Detail.vue` label the entity (the "Trainee" / "Race" word, from
  `lang/en/uma.php`, not an invented string).
- `CandidateForm.vue` hides the Confirm verdict for an entity with no resolver, with the reason
  printed using the `N/A`-with-title convention, rather than offering a button that 500s.

**Tests.**
- `tests/Feature/ReviewQueueTest.php`: a race-sourced candidate cannot be Confirmed through the
  umamusume path; the endpoint answers 422 with a named code.
- New `tests/Feature/ReviewEntityDispatchTest.php`: one candidate of each entity resolves through its
  own path; the race one lands in `race_catalog_slots` and writes a `data_sources` row.
- `tests/Feature/Schema/` gains the migration's down() proof, matching
  `MaidenGateColumnDroppedTest`'s pattern.
- A non-vacuity case: assert the old shape (payload without `name`) is what the fixture carries, so
  the test fails if someone "fixes" the fixture instead of the code.

**Acceptance.**
```
php artisan test --compact --filter=Review
php artisan test --compact --filter=MatchCandidate
php artisan migrate:status     # clean on the dev DB
```
And on the real dev DB: one race candidate can be Rejected, and Confirm returns 422, never 500.

**Owner decision inside this slice.** Which dispositions a race candidate should offer. Reject is
always safe. "Confirm" needs the owner's word on whether a race can ever be promoted by hand, or
whether race rows are engine-only. Record the answer in the ADR.

### S1.2 - `uma:enrich-races` overwrites `is_manual` rows, breaking FR-B-4

**Measured.** `app/Console/Commands/EnrichRaces.php:89-162` iterates `RaceCatalogSlot::all()`, assigns
`$new[...]` fields, and calls `$slot->save()` at `:159` inside one transaction. The file contains
**zero** occurrences of `is_manual`.

Every other engine writer in the tree guards it: `StoreRaceCatalogSlots.php:40-41`
(`if ($existing !== null && $existing->is_manual) { $counts['skipped']++; ... }`),
`StoreCharacterCards` (4 mentions), `StoreCharacterProfiles` (4), `StoreSkills` (2),
`StoreSupportCards` (2), `StoreSupportEffects` (2), `StoreTraineeGoals` (2),
`PromoteMatchedRecord` (3). The measured pattern is unambiguous: this one command is the outlier.

`PRD.md:234` states the rule as binding: "B-4: `is_manual` rows are immutable to the engine." The
`race_catalog_slots` table carries the column (measured: `pragma_table_info` lists `is_manual` on
`race_catalog_slots`, `umamusume`, `skills`, `scenarios`, `scenario_races`, `scenario_slots`,
`umamusume_profiles`, `support_cards`, `trainee_goals`, `character_cards`).

**Second defect, same command: no provenance of its own.** The command writes `track_name`, `fans_*`,
`course`, `direction`, `season`, `reward_summary` and the three `*_label` columns without touching
`source_url`, `snapshot_path` or `fetched_at`. Measured on the dev DB, which is why nothing has gone
wrong yet: 410 `race_catalog_slots`, **0** with `track_name`, **0** with `direction_label`, **0** with
`fans_first`, and all 410 carrying the same `source_url`
(`race_instances.294424fc.json`, which is the calendar document, not any of the four enrichment
datasets). So the command has never successfully run here, and if it did it would stamp four
publishers' facts under the calendar's URL.

**Change.**
1. Before building `$new`: `if ($slot->is_manual) { $skippedManual++; continue; }`. Report it as its
   own counter, not merged into "no match": the operator needs both numbers.
2. Route the write through `StoreRaceCatalogSlots` (which already skips `is_manual` and stamps
   provenance), or do the same two things here: skip manual rows and write one `data_sources` row per
   contributing source before `$slot->save()`.
3. Document the command in `README.md` beside the other `uma:*` commands; it is absent from
   `:167-169`.

**Tests.** New case (or `tests/Feature/RaceCatalogFetchTest.php`): a slot with `is_manual = true` and
stale `track_name` keeps it while a non-manual sibling updates; the summary line names both skip
reasons; a provenance row exists per contributing source after a run.

**Acceptance.**
```
grep -c is_manual app/Console/Commands/EnrichRaces.php    # >= 1
php artisan test --compact --filter=EnrichRaces
php artisan uma:enrich-races    # on a scratch DB copy, never the shared dev file
```

### S1.3 - The maiden lock self-clears on native runs that won nothing

**Measured.** `app/Models/TrainingRun.php:915-945`. `hasClearedMaidenLock()` opens with:

```php
if ($this->nextTurnNumber() - 1 > 12) {
    return true;
}
```

`nextTurnNumber()` (`:702-716`) returns `max(turn) + 1` for a run with logged turns, so a native run
that has logged 13 turns reports `13 > 12` and returns `true` without ever consulting
`raceEntries`. The record check at `:932-944` (completed, placement 1, grade 800 or 900) is only
reached for a run at or before turn 12.

**Reproduced.** Built a `RunMode::NewCareer` run with no position and 13 `TurnEntry` rows, won nothing:
```
NATIVE next=14 cleared=YES (no race won)
```
`RunMode` has exactly two cases, `NewCareer` and `Snapshot` (`app/Enums/RunMode.php`); no
"wizard" mode exists, so the reproduction used the real native case.

**Why it is a defect rather than a deliberate fallback.** `docs/adr/0031-run-level-maiden-lock.md:40-43`
scopes the position fallback to imported runs: "A run imported mid-career holds no entries for the
turns it never played, so a record-only check would leave every standard race locked for the life of
the run. The method therefore treats a position past the Debut's own slot as cleared". A native run is
the case the record check exists for. The code comment at `TrainingRun.php:917-924` says the same
thing and then applies it unconditionally.

It is also the exact symptom ADR-0031 was written to end, at `:24-25`: a Race desk "offered graded
races a winless run could not enter, with no gate line to explain why".

**Blast radius, measured.** Exactly two surfaces consume it:
`app/Http/Controllers/Career/RaceDecisionController.php:178, 199, 225` and
`app/Http/Controllers/Career/RacePlannerController.php:195, 328, 346`.
`RaceDecisionController.php:225-227` builds `blocked_reason` from `maidenGates()`; a falsely-cleared
run gets `maiden_gated: false` and `blocked_reason: null` on every graded row.

**Change.** Gate the fallback on mode:

```php
if ($this->mode === RunMode::Snapshot && $this->nextTurnNumber() - 1 > 12) {
    return true;
}
```
Keep the comment, and rewrite it to say why the fallback is snapshot-only. Check
`career_position_source` (values `imported` / `derived` / null, per
`database/migrations/2026_10_09_153000_add_career_position_to_training_runs_table.php:17`) if a native
run can also carry a stored position; if it can, `mode` alone is not sufficient and the guard needs
both.

**Tests.** The gap that let this ship is that `tests/Feature/Schema/MaidenGateColumnDroppedTest.php`
only asserts the three columns are gone. It has no behavioural case. Add to
`tests/Feature/CareerRaceDecisionTest.php` (which the roadmap already names as the acceptance for
S3.4):
- native run, 13 logged turns, zero race entries -> `maiden_gated` true on a grade-100 row,
  `blocked_reason` non-null, the sentence still rendered.
- native run, one completed placement-1 Maiden -> cleared.
- snapshot run past turn 12 with no entries -> cleared (the imported case, unchanged).

**Acceptance.**
```
php artisan test --compact --filter=Maiden
php artisan test --compact --filter=CareerRaceDecision
php artisan test --compact --filter=CareerRacePlanner
```
Plus a read-count assertion: the existing planner test pins the calendar to one read; the new branch
must not add a query per row.

**OWNER-ROLE NOTE.** This is roadmap S3.4, recorded as owner-gated on the question "record the
inference as the accepted rule, or add the stored fact". The ruling does not need to block the *bug
fix*: whatever the owner decides about storing a fact, a native winless run must not read as cleared.
Fix the predicate; park only the "stored fact vs inference" question.

### S1.4 - Three declared seed files do not exist on a fresh clone

**Measured.** `config/uma.php` declares thirteen `seed_file` values. Three of them point at files
absent from `database/seeders/data/`:

| Source key | Declared `seed_file` | Declaring line | In `database/seeders/data/` |
| --- | --- | --- | --- |
| `gametora-racetracks` | `racetracks_extended.json` | `config/uma.php:431` | absent |
| `gametora-race-fans` | `race-fans.json` | `config/uma.php:445` | absent |
| `gametora-race-rewards` | `race-rewards.json` | `config/uma.php:473` | absent |

Three committed files are cited by no source: `gametora-characters.e9e9ee6d.json`,
`race-tier-labels-2026-09-29.json`, `ura-races.json`.

`database/seeders/ReadsCommittedSource.php:46-56` logs a warning and returns `null`, so seeding skips
the source and its tables stay empty until `uma:fetch` runs. `AGENTS.md` §11 requires a committed body
so a fresh clone seeds offline; `PRD.md` NFR-2 requires the catalog to work with zero connectivity
after a fetch, which a fresh clone has not performed.

**The bodies already exist on disk, measured.** `storage/app/private/snapshots/` holds one file per
source, and each filename *is* the content hash the URL's cache-busting token names:

| Source dir | Body | Bytes | Parses as |
| --- | --- | --- | --- |
| `gametora-racetracks/` | `effe0119e6a18b0b…f3cc4.html` | 5805 | JSON array, 47 rows |
| `gametora-race-fans/` | `82ab7152713c14f0…f894b5.html` | 26497 | JSON array, 61 rows |
| `gametora-race-rewards/` | `d95f3aa1471ea9fd…34ba630.html` | 40719 | JSON array, 66 rows |

`effe0119…` matches `racetracks_extended.effe0119.json`; `82ab7152…` matches `race-fans.82ab7152.json`;
`d95f3aa1…` matches `race-rewards.d95f3aa1.json`. So this is a **copy**, not a fetch: the fetched
bodies are already in the repo, gitignored, and the committed-body step was simply never done for
these three.

**Change.**
1. Copy each snapshot body to `database/seeders/data/<declared name>` using the declared filenames
   exactly, so `config/uma.php` needs no edit. Do not reformat or re-serialize: the parser reads the
   body as fetched, and re-serializing changes the bytes the provenance hash records.
2. Record provenance honestly. The bodies are genuine fetched documents, and the hash in each
   filename is the evidence. Note the source URL and hash in the commit body.
3. For the three unreferenced committed files, decide per file: either a source now points at it
   (then wire it in `config/uma.php`), or it is dead and gets deleted. Do not leave them unreferenced.

**Tests.** New case asserting every declared `seed_file` exists:
```php
it('ships a committed body for every source that declares one', function (): void {
    foreach (config('uma.sources') as $key => $source) {
        if (! isset($source['seed_file'])) { continue; }
        expect(is_file(database_path('seeders/data/'.$source['seed_file'])))
            ->toBeTrue("Source {$key} declares seed_file {$source['seed_file']}, which is not committed.");
    }
});
```
Name it in the fetch-pipeline area rather than inventing a home: `tests/Feature/FetchPipelineTest.php`
or a sibling `SeedFileCompletenessTest.php`.

**Acceptance.**
```
php artisan test --compact --filter=SeedFile
php artisan migrate:fresh --seed     # on a SCRATCH DB file (AGENTS.md §11: needs owner approval on the dev file)
```
Then confirm the three tables hold rows after seeding, and that `uma:enrich-races` finds no
`skipped (no match)` rows it can now match.

**Ordering note.** Do this before S1.1 (the review-queue disposal), because a cloned repo that seeds
race data produces the same review rows the dev DB already holds, and the disposal path should be
tested against a reproducibly seeded database rather than one hand-carried file.

### S1.5 - `uma:fetch` aborts, and writes race rows into the review queue

**Measured.** See the routing table in S1.1. `PipelineRunner` has no `is_a()` branch for
`RaceEnrichmentSourceParser` (0 occurrences in the file), so the four enrichment sources fall through
to the generic character loop at `:252-330`.

`app/Console/Commands/UmaFetch.php:50-60` skips a key only when it declares **no** parser:

```php
if (! isset($sources[$key]['parser'])) {
    ... 'is an asset host with no document to parse; skipped.' ...
    continue;
}
```

All four enrichment entries declare a parser, so none is skipped.

Two distinct failures, both measured:

1. `gametora-race-fans` (61 records) and `gametora-race-rewards` (66 records) carry no `name`,
   `name_en` or `title` at all. Parsed through their own parsers and tested against
   `recordName()`'s precedence, both throw
   `RuntimeException: {source}: record [N] has none of name, name_en or title.`
   Because `run()` does not catch it, `php artisan uma:fetch` with no argument dies there and never
   reaches the remaining source keys.
2. `gametora-racetracks` (47 records) and `gametora-races` (331 records) **do** carry `name_en`, so
   `recordName()` returns it and `:310-325` files one Pending `MatchCandidate` per record.
   That is the origin of the 304 rows.

*Correction to an earlier reading:* racetracks are not name-less. They carry `name_en` and file 47
candidates; only fans and rewards throw. The distinction matters because it is why the backlog is all
`gametora-races` and not a mix: the run aborted on fans before ever reaching racetracks with the
shipped key order.

**Change.** Extend `UmaFetch`'s existing skip with a second predicate, in the same place and for the
same reason:

```php
if (is_a($sources[$key]['parser'], RaceEnrichmentSourceParser::class, true)) {
    if ($requested !== null) {
        $this->error("'{$key}' is read by uma:enrich-races, not uma:fetch.");
        return self::FAILURE;
    }
    $this->line("'{$key}' is an enrichment source; read by uma:enrich-races. Skipped.");
    continue;
}
```

Do **not** add a `RaceEnrichmentSourceParser` branch to `PipelineRunner` that promotes records: the
contract's docblock states the only consumer is `uma:enrich-races`, and `EnrichRaces` joins them by id
into `race_catalog_slots`. Teaching the generic loop to "handle" them would need an entity model the
queue does not have (that is S1.1's schema work) and would duplicate `EnrichRaces`'s join.

**Third defect, same slice: one source kills the run.** `UmaFetch::fetchOne()` has **zero** `catch`
blocks (`grep -c 'catch' app/Console/Commands/UmaFetch.php` returns 0) and one `finally` that releases
the lock. So any exception from a parser or a store action escapes `handle()` and ends the whole run,
contradicting `AGENTS.md` §8 and NFR-2's per-source degradation ("a failed fetch degrades to a log
entry plus retained previous data"). `SourceDocumentSeeder.php:86-99` already does this correctly for
the seed path and is the pattern to copy.

Fix alongside the skip: wrap the per-source body in `try { ... } catch (Throwable $e) { ... }` that
logs the source key and continues to the next key. Report the failure in the summary so a run that
degraded is not mistaken for a run that succeeded.

**Backlog disposal, one statement, and it is a data change:**

```sql
UPDATE match_candidates
   SET status = 'Rejected', updated_at = CURRENT_TIMESTAMP
 WHERE source_key = 'gametora-races' AND status = 'Pending';
```

304 rows. Back up first (`php artisan uma:backup`), and state the count in the commit body. This is
safe because every one of those rows is a race that can never resolve as a trainee (S1.1) and none
carries a `suggested_umamusume_id`.

**Tests.** `tests/Feature/FetchPipelineTest.php`:
- `uma:fetch` with no argument skips all four enrichment keys, prints the skip line, exits 0.
- `uma:fetch gametora-races` fails with a message naming `uma:enrich-races`, not a `RuntimeException`
  about a name key.
- A non-enrichment source in the same run still promotes (the skip must not be a blanket early exit).
- Non-vacuity: assert `config('uma.sources')` still declares all four enrichment keys, so the test
  fails if someone "fixes" it by deleting config entries.

**Acceptance.**
```
php artisan test --compact --filter=FetchPipeline
php artisan uma:fetch gametora-race-fans       # exits non-zero with the enrich-races message
SELECT count(*) FROM match_candidates WHERE status='Pending'   # 0 after disposal
php artisan uma:enrich-races                   # still the only path into race_catalog_slots
```

### S1.6 - Independent Training: one unconfirmed click retires the whole career

**Measured.** `app/Services/IndependentTraining/Lifecycle.php:95-103`:
```php
$itr->update(['abandoned_at' => now()]);
$itr->trainingRun()->update(['status' => RunStatus::Retired]);
```
`resources/js/pages/Dashboard.vue:373-383` posts `indep.abandon_url` from a plain button with no
confirmation step. `TrainingRunController::abandonIndependent()` (`:1272-1281`) takes no Form Request
and no guard. The flash reads "Independent Training session abandoned." and says nothing about the run
becoming Retired.

`RunStatus` has three cases (`Active`, `Completed`, `Retired`); `Retired` is terminal and nothing in
the tree un-retires a run.

**Why it is a defect.** `AGENTS.md` §5 and the repo's own delete door (`runs.confirm-delete`, a
confirmation page) establish that a destructive irreversible transition asks first. Abandoning a
50-minute timer is not the same act as ending the career, and the code fuses them.

**Change.** Pick one, state it in the commit:
- **(a)** Abandoning the overlay stamps `abandoned_at` and leaves the run `Active`, because the run
  still has its turns and its record. Abandoning is giving up the idle session, not the career.
- **(b)** Keep the retirement, but route it through a confirmation page like `runs.confirm-delete`,
  and make the flash name the retirement explicitly.

(a) is the smaller diff and the better sentence; (b) preserves today's product intent. Do not keep an
unconfirmed destructive click either way.

**Tests.** `tests/Feature/IndependentTrainingLifecycleTest.php` already covers this class (8 passed,
24 assertions, exit 0 at the time of measurement). Add: the chosen transition's resulting
`training_runs.status`, the flash string, and a case proving the other ending (`complete()`) is
unaffected.

**Acceptance.**
```
php artisan test --compact --filter=IndependentTrainingLifecycle
```
A Dashboard click either leaves `status='Active'` or renders a confirm page before the POST.

**OWNER DECISION.** (a) vs (b) is a product ruling: does "abandon" mean "give up the idle session" or
"end the career"? Park the copy with the owner; the confirmation door is the safe default.

### S1.7 - `independent=1` with no policy creates a career and then 500s

**Measured.** `app/Http/Requests/Career/StartCareerRequest.php:113-118`, whose own docblock four lines
above says "`policy` is the one that must be present when the flag is on":

```php
'policy' => ['nullable', 'string', Rule::enum(IndependentTrainingPolicy::class)],
```

`independentConfig()` (`:265-278`) then casts it: `'policy' => (string) $this->input('policy')`, so an
absent policy becomes `''`. `Lifecycle::start()` (`:31-43`) does
`IndependentTrainingPolicy::tryFrom($config['policy'])`, gets `null`, and throws
`InvalidArgumentException('Unknown Independent Training policy.')`.

`PreflightController::store()` calls `Lifecycle::start()` at `:228`, **after** the `DB::transaction`
at `:195-221` has already committed the run, its skills and its deck slots. So the failure leaves a
durable Active career with no overlay.

This is a trust-boundary failure, not a UI bug: the shipped control always posts a policy (the select
at `Preflight.vue:1090` is populated), but a hand-made PUT reaches it.

**Change.** Two lines, both required:
```php
'policy' => ['nullable', 'required_if:independent,1', 'string', Rule::enum(IndependentTrainingPolicy::class)],
```
and move the `Lifecycle::start()` call inside the transaction so a rejected config rolls the run back
rather than orphaning it. `SetupDraft::reset()` stays where it is, after the transaction.

**Tests.** `tests/Feature/CareerPreflightTest.php` (33 passed, 390 assertions at measurement) covers
an unknown policy but not the absent one. Add: `independent=1` with no `policy` returns a `policy`
field error **and** `training_runs` count is unchanged. Assert the count, not just the response: the
orphan is the defect.

**Acceptance.**
```
php artisan test --compact --filter=CareerPreflight
```
No `training_runs` row exists after a refused preflight.

### S1.8 - Both Independent Training transitions write two rows with no transaction

**Measured.** `grep -c 'DB::transaction' app/Services/IndependentTraining/Lifecycle.php` returns **0**.
`complete()` (`:77-90`) updates the overlay then the run; `abandon()` (`:95-103`) does the same pair.
A failure between the two leaves an overlay stamped complete and a run still Active, and nothing ever
re-scans completed overlays (`completeExpired()` filters `whereNull('completed_at')`), so the career
stays Active forever.

The idempotency guard reads the in-memory model (`if ($itr->completed_at !== null ...)`), not under a
lock, and `bootstrap/app.php:50-54` schedules the command without `withoutOverlapping()`. Two
overlapping ticks can both enter the body.

`Lifecycle::canStart()` (`:60-71`) encodes the §1.1.7 one-at-a-time rule and has **no production
caller**: grepping `canStart(` across `app/` returns only its own definition. `start()` never consults
it, and the migration puts no unique index on `training_run_id` while the model declares `hasOne`.

**Change.**
1. Wrap each transition in `DB::transaction`.
2. Make the stamp conditional so a double entry cannot double-apply:
   `IndependentTrainingRun::whereKey($itr->id)->whereNull('completed_at')->whereNull('abandoned_at')->update([...])`,
   and only touch the run when the update returned 1.
3. `bootstrap/app.php`: add `->withoutOverlapping()` to the `independent-training:complete` entry.
4. State the one-at-a-time rule in the database: a unique index on `independent_training_runs.training_run_id`
   (new migration, working `down()`), because an index cannot be bypassed and `hasOne` already assumes it.

**Tests.** Force the second write to throw and assert both rows are unchanged (transaction
behaviour). Assert a second `start()` on a run that already carries an open overlay is refused, via
the index.

**Acceptance.**
```
grep -c 'DB::transaction' app/Services/IndependentTraining/Lifecycle.php   # 2
php artisan test --compact --filter=IndependentTraining
```

### S1.9 - The SP budget gate can be stepped past in two posts

**Measured.** `app/Http/Controllers/TrainingRunController.php:1232-1265` (`budgetRefusal()`) reads the
run's recorded SP from the latest turn entry, then sums only the acquisitions **in the current POST**:

```php
foreach ($request->validated()['skills'] as $entry) {
    if ($request->acquisitionFor($entry) !== SkillAcquisition::Acquired) { continue; }
    ... $spend += ...;
}
if ($spend <= $sp) { return null; }
```

Previously acquired skills are not in the sum. Hold 200 SP, post a 150 SP skill (150 <= 200, passes),
post another 150 SP skill (150 <= 200, passes): the run has now spent 300 against 200 recorded. The
guard exists precisely because, in the class's own words, "a write that trusts the client's arithmetic
is a gate the client can simply omit"; it reproduces that shape one layer down.

**Read-side inconsistency, same slice.** `app/Http/Controllers/Career/SkillsPlannerController.php`
prices the same skill two ways on one page: `purchasableSection()` sets
`hint_adjusted_cost = sp_cost` with the comment "no hint recorded on unacquired skills yet", while
`recommendedSection()` applies `HintDiscount::price()` from `run_skills.hint_level` for the same pool,
and its own comment says a hint is not a priority-only fact. A Trainer sees two prices for one skill.

**Change.**
1. `budgetRefusal()`: include already-acquired rows. `TrainingRun::skills()` is the `run_skills`
   belongsToMany (`app/Models/TrainingRun.php:346-348`), and `run_skills` carries `hint_level` per row
   (`:416` records that a hint never lives orphaned). So read the recorded acquired rows, price each
   through the same `HintDiscount::price()` the POST delta uses, then compare
   `recorded + delta` against `$sp`. Do not cache or persist the figure in this method; it is a
   read-and-compare gate.
2. `purchasableSection()`: apply the same `HintDiscount::price()` path `recommendedSection()` uses, or
   delete the divergent comment and print one price with a named reason. Two prices must not appear.

**Tests.** A run with 200 recorded SP: post 150 (passes), then post another 150 -> refused with the
existing message naming the total. And a page test asserting one skill's price is identical in the
Purchasable and Recommended panels.

**Acceptance.**
```
php artisan test --compact --filter=SkillsPlanner
php artisan test --compact --filter=RunSkill
```

### S1.10 - Cap arithmetic has a second home in a controller

**Measured.** `app/Http/Controllers/DatabaseController.php:191-208` computes
`'cap' => $baseCap + $bonus` straight from `config('scenarios.base_cap')` and
`$definition['cap_bonus']`, with **no clamp and without `ScenarioCaps`**, while the docblock beside it
says the config is the single source and the sibling
`app/Services/Career/ScenarioSectionBuilder.php:207-239` correctly routes through
`ScenarioCaps::scenarioCaps()`.

`app/Http/Controllers/Career/DeckSelectController.php:112-138` goes further and hardcodes ceiling
figures as prose for two scenario keys: `'unity_cup' => ["Unity Cup's Wit cap is 1800; the other four
stats cap at 1300."]` and `'our_grand_concert' => ['Speed cap is 1600. ...']`.

These agree with config today (measured: `unity_cup` Wit 1800, `our_grand_concert` Speed 1600). The
defect is the ceiling: a `cap_bonus` edit silently leaves the deck-builder hints and the Database
matrix printing the old number, and `AGENTS.md` §8 names `ScenarioCaps` the single owner of per-stat
ceiling arithmetic (`ADR-0015`).

**Change.** Route `DatabaseController::scenarios()` through `ScenarioCaps::caps()` (or
`scenarioCaps()`), and replace both hardcoded hint strings with values read from the same owner. If a
sentence needs the number inline, interpolate it from `ScenarioCaps`, do not retype it.

**Tests.** `tests/Feature/ScenarioCapParityTest.php` exists for config-vs-table parity; extend the
same idea to assert the Database matrix and the deck hints equal `ScenarioCaps::caps()`. A test that
fails when someone edits `cap_bonus` and forgets the prose.

**Acceptance.**
```
grep -rn 'base_cap' app | grep -v 'Services/ScenarioCaps.php'      # empty
php artisan test --compact --filter=ScenarioCapParity
```

### S1.11 - Verdict integrity in the review queue is unguarded

**Measured.** `app/Http/Requests/ResolveMatchCandidateRequest.php:26-33` accepts any
`CandidateStatus` value, including `Pending`. In `app/Actions/ResolveMatchCandidate.php:36-41`
`CandidateStatus::Pending => null`, then `:43` stamps the row and `:44` saves, so a `Pending` verdict
returns success, flashes "Candidate resolved.", and changes nothing.

`alias()` (`:66-79`) returns early when `$target === null`, but `handle()` stamps the row `Aliased`
regardless, so the candidate leaves the queue with no alias recorded. That is the only non-Reject path
available to the 304 suggestion-less rows.

`ReviewController::reopen()` (`:193-198`) flips the row back to Pending without undoing or recording
the catalog write a prior Confirm made, so Reopen then Confirm creates a second `umamusume` row and a
second provenance row.

**Change.**
1. Drop `Pending` from the accepted verdicts. Reopen is the door back to Pending.
2. Refuse `Aliased` when no target resolves, with a field error naming what is missing.
3. `reopen()` refuses a row whose prior verdict wrote a catalog row, or records that reversal
   explicitly. Do not silently allow a duplicate promotion.

**Tests.** `tests/Feature/ReviewQueueTest.php` (4 passed, 30 assertions at measurement) plus a case
per refusal. The duplicate-promotion case asserts `umamusume` count unchanged across
Confirm -> Reopen -> Confirm.

**Acceptance.**
```
php artisan test --compact --filter=Review
```

### S1.12 - Two screens disagree about a run with no scenario

**Measured.** `app/Http/Controllers/Career/RacePlannerController.php:131-143` refuses to lend a
no-scenario run a calendar, with a comment saying that would be "a fact about a scenario the Trainer
never chose".
`app/Http/Controllers/Career/CockpitController.php:1058-1066` (`obligationSlots()`) and
`app/Services/Career/TeamPanelBuilder.php:86-95` run the same `forScenario($run->scenarioKey())` query
with no `hasScenario()` guard, and `TrainingRun::scenarioKey()` (`:453-457`) resolves null to
`config('scenarios.baseline')`, which is `ura_finale`.

`ScenarioCaps::scenarioCaps()` takes the strict side too (measured: `:178-197` refuses a bonus,
describing it as "lending URA Finale's +200 to a run that never picked it").

So a no-scenario run's cockpit shows URA's seven mandatory races as its obligations and its career
goals panel, while its Race Planner says it has no calendar and its stat caps claim no bonus.

**Change.** Add the `hasScenario()` guard to `obligationSlots()` and to
`TeamPanelBuilder::careerGoalSections()`, returning an empty collection or a named absence, matching
`RacePlannerController` and `ScenarioCaps`. Do not change `scenarioKey()`'s fallback: other surfaces
depend on it resolving to the baseline for layout.

**Tests.** Create a run with `scenario = null`; assert the cockpit's obligation and goal lists are
empty (or carry the absence sentence) and the Race Planner's `empty` prop is non-null. Both read one
answer.

**Acceptance.**
```
php artisan test --compact --filter=Cockpit
php artisan test --compact --filter=CareerRacePlanner
```

### S1.13 - The goals panel places the run one turn behind the strip on the same screen

**Measured.** `app/Services/Career/TeamPanelBuilder.php:86` reads `$run->nextTurnToPlay()`, while
`app/Services/Career/RaceStripBuilder.php:84-94` and `RacePlannerController:98` read
`$run->decisionTurn()`. `RaceStripBuilder`'s own comment records that the direct read is "the right
answer only for a new-career run and one turn late for a snapshot", found on run #36 (imported at
index 67).

So on a snapshot run the goals panel marks a mandatory race `not_reached` while the race strip beside
it marks the same race as the turn being decided. `TrainingRun` exposes both methods separately
(`:736` `nextTurnToPlay()`, `:766` `decisionTurn()`).

**Change.** One call: `:86` becomes `$run->decisionTurn()`. It returns the same
`{year, turn, turn_index}` shape the surrounding code destructures. Keep the `hasScenario()` guard
added in S1.12.

**Tests.** A cockpit test on an imported run asserts the goal row's state and the strip's state agree
for the same race.

**Acceptance.**
```
grep -c 'nextTurnToPlay()' app/Services/Career/TeamPanelBuilder.php    # 0
php artisan test --compact --filter=Cockpit
```

### S1.14 - `updateTurn()` lets the turn number move without re-keying its events

**Measured.** `app/Http/Controllers/TrainingRunController.php:1131-1138` writes
`$request->validated()` wholesale, so `turn` is editable. `turn_events` is keyed on
`(training_run_id, turn)` with no `turn_entry_id`, which `storeTurn()` itself documents at `:968-976`
("a row at a number a deleted turn used inherits that turn's event").
`resources/js/pages/Career/TurnEdit.vue:62-76,175` renders the turn number input, so the renumber is
reachable from the UI.

Separately, `StoreTurnEntryRequest.php:97-105,162-188` validates `performance`, `stage`, `outcome`,
`penalty_kind` and `previewed`, which are not columns; they are discarded silently because
`preventSilentlyDiscardingAttributes` is not enabled anywhere.

**Change.** Exclude `turn` from the update path (a turn number is identity, not an attribute), or
carry the renumber through to `turn_events` in the same transaction. And either add those five keys as
columns (they have their own model payloads under `app/Models/TurnEvents/`) or stop validating keys
that cannot persist.

**Tests.** `tests/Feature/TurnEntryUpdateTest.php`: a PUT that changes `turn` either 422s or moves the
turn's events with it; a PUT carrying `performance` either persists it or is refused, never silently
dropped.

**Acceptance.**
```
php artisan test --compact --filter=TurnEntryUpdate
php artisan test --compact --filter=TurnEdit
```

### S1.15 - Scenario names are still compared literally where config flags exist

**Measured.** `app/Http/Controllers/Career/RaceDecisionController.php:278-306` picks the payout family
with `$run->scenarioKey() === 'trackblazer'`.
`app/Services/Career/TeamPanelBuilder.php:170-190` gates the Happy Meek module on `=== 'ura_finale'`
right beside a sibling branch that uses `composesPanel('team_rank_ladder')`.
`app/Services/Career/TrackblazerPanelBuilder.php:59-63,575-580,737-765` gates four methods on
`!== 'trackblazer'` although `TrainingRun::composesPanel('grade_objectives')` is the documented gate
(D-240, G-33).
`resources/js/pages/Career/Cockpit.vue:1105,1118` branches on `'trackblazer'` and `'grand_concert'`,
and the second is a **typo**: `config/scenarios.php` declares `our_grand_concert`, so that note is dead
code.

`AGENTS.md` §7 makes a hardcoded scenario name in a page a defect.

**Change.** Add the missing flags to `config/scenarios.php` (`race_payout_family`, `happy_meek`) and
read them by resolved key, as `composesPanel()` already does. Fix the `grand_concert` typo, and move
the two Cockpit sentences into config (`support_data_note` per entry) so the page knows no scenario
name.

**Tests.** A page test asserting the Our Grand Concert note renders; a unit test that a fifth scenario
declaring `grade_objectives` gets the Trackblazer panel treatment without a code change.

**Acceptance.**
```
grep -rn "scenarioKey() === '\|scenarioKey() !== '" app        # empty
grep -c "scenario === '" resources/js/pages/Career/Cockpit.vue # 0
```

### S1.16 - Two write routes and one read screen bypass the Form Request convention

**Measured.** `runs.destroy` reads `$request->boolean('confirmed')` off the base `Request`
(`TrainingRunController.php:880-891`); `runs.indep.abandon` takes no `Request` at all (`:1272-1281`);
`review.reopen` takes none (`ReviewController.php:193-199`). And
`app/Http/Controllers/Career/SnapshotController.php:140-150` calls
`Validator::make($submitted, StoreSnapshotRequest::fieldRules())->validate()` inside a controller
method, which is the Floor's "no inline `$request->validate()`" shape one layer down.

The Floor as written bans `$request->validate(` specifically, and a tree-wide grep for that token
returns 0 (measured in an earlier pass). These are the same defect expressed three other ways.

**Change.** `ConfirmRunDeletionRequest` (`confirmed` => `accepted`),
`AbandonIndependentTrainingRequest` (empty rules), and a `SnapshotReviewRequest` that exposes
`fieldRules()` through `rules()`. The abort-on-unread-draft branch stays in the controller.

**Tests.** Existing delete/snapshot/review tests pass unchanged; add one case per new Form Request.

**Acceptance.**
```
grep -rn 'Validator::make' app/Http/Controllers     # empty
php artisan test --compact --filter=SnapshotReview
```

### S1.17 - Hint tier "Lv Max" prices at full cost on the server, 40% off on the screen

**Reproduced.**
```
HintDiscount::price(130, 5)     -> 130
HintDiscount::price(130, 'max') -> 78
HintDiscount::price(130, 4)     -> 84
config('uma.skills.hint_discount') keys -> 1,2,3,4,max
```

The ladder is keyed `1,2,3,4,max`. The UI sends the integer `5` for the top tier
(`resources/js/components/career/SkillPlanRow.vue:148-153`, option value `5` labelled 40% off;
`resources/js/pages/Career/SkillsPlanner.vue:270-279` maps `5` to 40). `HintDiscount::discountPct()`
(`app/Services/HintDiscount.php:92-98`) looks the value up in the config, finds no `5` key, returns
null, and `price()` falls back to the undiscounted base. Both call sites cast to int before pricing
(`SkillsPlannerController.php:267`, `TrainingRunController.php:1253-1258`).

**Consequence, user-visible.** A Trainer records hint Lv Max, sees the Cost column and the Remaining
bar price the skill at 78 SP, commits, and the server refuses the plan quoting 130. The recommendation
ordering also treats that skill as unhinted, so a heavily hinted skill sorts behind a more expensive
one, which is the ordering `ADR-0035` Decision 5 exists to fix. This is the worst user-visible defect
in the audit and it is one line.

**Change.** Normalise inside `HintDiscount::discountPct()`: map int `5` to `'max'` before the config
lookup. Every reader goes through `price()`, so the write path, the planner payload and the budget
refusal all agree with no other edit.

**Second half, same defect.** `StoreRunSkillRequest` validates `hint_level` as
`['nullable', 'integer', 'between:1,5']` (`:75`), so `'max'` cannot be posted through the API at all,
while `tests/Feature/Career/BuildTargetHintLevelTest.php:67` uses the string form on the build-target
path. Pick one representation and enforce it at the boundary: accept `1..5` and map in
`discountPct()` (smaller diff), or accept `max` and widen the rule. Two vocabularies on one column is
how this recurred.

**Tests.** `tests/Feature/SkillHintLevelTest.php` (existing, `:61-96`): price at every tier 1-5 equals
the configured percentage, and the max tier is discounted.
`tests/Feature/RunSkillInventoryTest.php:141-215` (budget refusal): a max-tier skill's refusal quotes
the discounted figure.

**Acceptance.**
```
php artisan tinker --execute="echo App\Services\HintDiscount::price(130,5);"   # 78
php artisan test --compact --filter=SkillHintLevel
php artisan test --compact --filter=RunSkillInventory
```

**Order.** First in Phase 1, and before S1.9, because S1.9 changes the same pricing path.

### S1.18 - Correcting a turn leaves its stale Failure event behind

**Measured.** `app/Http/Controllers/TrainingRunController.php:1131-1138`:
```php
$turn->update($request->validated());
```
No transaction, no event work. Compare the two siblings: `storeTurn()` at `:968-976` deletes any
Failure event at the turn number before writing its own, `destroyTurn()` at `:1149-1156` deletes it
inside a transaction, both with the comment explaining that `turn_events` is keyed on
`(training_run_id, turn)` with no `turn_entry_id`, "so a row at a number a deleted turn used inherits
that turn's event".

`updateTurn()` is the third door and it does neither. And `resources/js/pages/Career/TurnEdit.vue:175`
renders the turn number as an editable input, so a correction can also renumber a turn, leaving the
event at a number that no longer belongs to it.
`TimelineController.php:191-193` renders events by turn number.

**Consequence.** The career record contradicts itself: the corrected row reads Success while a red
Failure chip prints beside it on the Timeline. This is the same class as S1.14 (renumber without
re-keying), and the two must be fixed together or the second one reintroduces the first.

**Change.** Wrap the body in `DB::transaction`, and mirror `storeTurn`'s block: delete the Failure
event at both the old and the new turn number, then rewrite one when the corrected outcome is a
failure. Extract the clear-and-write pair into one private helper called by `storeTurn`, `updateTurn`
and `destroyTurn`, so three doors cannot drift to two behaviours again.

**Tests.** `tests/Feature/TurnEntryUpdateTest.php`: a turn logged as a failure then corrected to
Success leaves no Failure event at that number. A correction that renumbers leaves none at either
number. `tests/Feature/TurnEditTest.php` covers the form path.

**Acceptance.**
```
grep -c 'DB::transaction' app/Http/Controllers/TrainingRunController.php   # 5 -> 6
php artisan test --compact --filter=TurnEntryUpdate
php artisan test --compact --filter=Timeline
```

### S1.19 - `syncSkills` is the only multi-write path in its controller with no transaction

**Measured.** `TrainingRunController` wraps five writes: `:234` (`store`), `:807` (`storeRace`), `:955`
(`storeTurn`), `:1149` (`destroyTurn`), `:1296` (`syncDeck`). `syncSkills` (`:1187-1215`) is the sixth
and has none: it loops the validated skills writing the `run_skills` pivot via `setSkillStatus()` and
`skill_levels` rows via `skillLevels()->updateOrCreate()`/`->delete()`, then redirects.

`PRD.md:341` (NFR-4) requires multi-write operations inside `DB::transaction`.

**Consequence.** A batch that fails partway (a constraint, a missing skill) leaves earlier rows written
while the controller returns an error page, so the run's skill inventory is half-applied with no
message saying which half. The docblock's "upserted per skill" is true per row, not per batch.

**Change.** Wrap the `foreach` in `DB::transaction`, matching the five siblings. The `budgetRefusal()`
call stays outside so a refusal still writes nothing.

**Tests.** Force a mid-batch failure (an id that passes validation but is filtered by
`availableOnGlobal()`) and assert no partial write.

**Acceptance.**
```
php artisan test --compact --filter=RunSkill
```

---

## Phase 2 - User-visible behaviour

### S2.1 - A completed transfer does not prevent banking the same career again

**Measured.** `app/Actions/CompleteTransferRequest.php:48-61` records the reference in
`veteran_label`, then `$veteran?->delete()` at `:61`. The `TrainingRun` row survives with
`status = Completed`. `app/Http/Controllers/Career/ResultController.php:439-453`
(`saveVeteranSection()`) decides availability from one fact only:

```php
$finished = $run->status === RunStatus::Completed;
'available' => $finished,
```

`RunStatus` has three cases, `Active`, `Completed`, `Retired` (`app/Enums/RunStatus.php`). Nothing in
this section consults the transfer. `resources/js/pages/Career/Result.vue:594-604` renders the
"Save Veteran" link from `props.save_veteran.available`; `:448` is the "already in the library"
message branch, which is keyed on `$run->veteran !== null`.

**Why it is a defect.** `docs/adr/0033-veteran-transfer-request.md:16-17, 30-38` promises permanent
removal: the Veteran leaves the library and the career is spent. Because `RecordVeteran::handle()`
(`app/Actions/RecordVeteran.php:37-38`) is `Veteran::updateOrCreate(['training_run_id' => $run->id], …)`,
re-banking recreates the row cleanly and the transfer's reward is kept alongside it. The absence the
deleted row left is indistinguishable from "never banked".

**Change.**
1. `saveVeteranSection()` gains a third state: `$run->transferRequest()->where('status', 'completed')->exists()`
   (or the relation the model already exposes). When true, `available => false` with a reason naming
   the transfer, and the copy must not promise a re-file.
2. Keep the door *visible* when it would be misleading to hide it: the repo's convention is a printed
   reason with the control disabled, not a vanished control (`ADR-0031` §"A gated row still renders").
3. Do not delete the run, and do not add a column unless the owner asks for one. The audit row
   already carries `veteran_label` and `completed_at`; that is enough to answer the question.

**Frontend.** `Result.vue` renders the new `reason` in the existing disabled branch at `:595-609`. No
new component. The reason string is display copy and goes through `lang/en/uma.php` if it names a
domain term.

**Tests.** `tests/Feature/CareerResultTest.php` (which pins the key set, per the docblock at `:435`)
gains: completed run + completed transfer -> `save_veteran.available === false` and the reason names
the transfer; a run that was never banked keeps `available === true`.
`tests/Feature/TransferRequestTest.php` gains the end-to-end: complete a transfer, then attempt
`POST /training-runs/{run}/veteran`, expect refusal and no new `veterans` row.

**Acceptance.**
```
php artisan test --compact --filter=CareerResult
php artisan test --compact --filter=TransferRequest
```
`RecordVeteran` is never reached for a transferred run: assert `veterans` count unchanged.

**OWNER DECISION.** Whether a transferred career should be permanently unbannkable, or re-bankable
with an explicit override. The ADR says "permanent removal"; read it with the owner before choosing
the copy, because the copy is the promise.

### S2.2 - The review queue has no way to dispose of 304 rows

**Measured.** `routes/web.php` declares exactly four review routes: `GET /review`,
`GET /review/{candidate}`, `POST /review/{candidate}`, `POST /review/{candidate}/reopen`.
`resources/js/pages/Review/Index.vue` renders no `<form>`, no checkbox and no select-all (measured:
no `checkbox`, `selectAll` or `bulk` token in the file).
`ReviewController.php:41` paginates at 25 with a deliberate ordering (suggestable rows first).

So the live backlog of 304 rows costs 304 page loads and 304 single resolutions, and after S1.1 the
only verdict that is safe for a race candidate is Reject. That is the queue's whole population.

**Change.**
1. **Filter before acting.** `ReviewController::index()` gains `entity` and `match_tier` filters
   (Form Request for the search, per `AGENTS.md` §7). Facet contract: reuse `App\Support\PageSize` and
   the `FilterContractTest` conventions named in `docs/adr/CURRENT.md:114`; refuse an unknown facet
   rather than ignoring it.
2. **Bulk Reject.** `POST /review/bulk` with a Form Request taking `ids[]` (bounded, e.g. max 100),
   routed through one `DB::transaction`, each row disposed through the same action the single path
   uses so the two cannot drift (`ResolveMatchCandidate`). Bulk **Confirm is deliberately absent**: a
   mass merge is the one operation that cannot be reviewed after the fact.
3. **UI.** `Review/Index.vue` gains a per-row checkbox, a select-all for the current page only (not
   the whole filtered set, which a Trainer cannot see), a count, and one Reject button whose disabled
   state and label follow the existing `BaseInput`/button conventions. Keyboard path and target sizes
   per `DESIGN.md`; no new component unless one of the 119 existing ones fits.

**Tests.** `tests/Feature/ReviewQueueTest.php`: bulk reject disposes N rows in one transaction, leaves
the rest Pending, is bounded at the cap, and refuses an empty list. A browser spec
(`tests/browser/review-bulk.spec.ts`) for the select-all scoping, only if the interaction cannot be
covered by a feature test; `AGENTS.md` §9 requires an end-to-end spec for every completed flow.

**Acceptance.**
```
php artisan test --compact --filter=Review
```
On the real dev DB: 304 Pending rows can be reduced to zero by entity filter plus bulk reject, and the
"Resolved today" tile at `ReviewController.php:61-75` reflects it.

### S2.3 - Telemetry contradicts NFR-1 and is enabled by default

**Measured.** `PRD.md:334` (NFR-1): "No account, no auth surface, **no telemetry**, no outbound calls
except declared fetch sources." `PRD.md:394` repeats it: "No telemetry, analytics or user data
leaves the" machine.

The dirty tree adds, uncommitted:
- `composer.json:14` `"initred/laravel-langfuse": "*"` (wildcard constraint), lock `v1.0.2`
- `config/langfuse.php:9` `'enabled' => env('LANGFUSE_ENABLED', true)`; **absent env means on**
- `config/latitude.php` with `'enabled' => env('LATITUDE_TRACING_ENABLED', false)`
- `bootstrap/app.php:98-101` registers `LangfuseServiceProvider`
- `routes/api.php:12` puts `LangfuseMiddleware` and `LatitudeTraceMiddleware` on all six `/api/v1`
  routes (confirmed by `php artisan route:list --json`: the middleware list for `api/v1/umamusume`
  carries both)
- `app/Http/Middleware/LatitudeTraceMiddleware.php:51-57` posts OTLP to `config('latitude.endpoint')`
  in `terminate()`

`LANGFUSE_ENABLED=false` appears in `.env.example` but an existing `.env` receives nothing on pull, so
the default wins. `tests/` contains no test for either middleware: measured, zero files matching
`(Latitude|Langfuse|Telemetry)` under `tests/`.

**Change, in order of preference.**

1. **Drop both packages** (preferred). Neither has an application caller: measured, `Langfuse` appears
   in exactly two files, `bootstrap/app.php` and `routes/api.php`, and nowhere under `app/`,
   `resources/`, `config/uma.php` or `tests/`. Remove the middleware from `routes/api.php`, the
   provider from `bootstrap/app.php`, delete `config/langfuse.php` and `config/latitude.php`, delete
   `app/Http/Middleware/LatitudeTraceMiddleware.php` and its test, and revert `composer.json` +
   `composer.lock`. This resolves S3.1's PHP floor as a side effect.
2. **If the owner wants telemetry**, it needs a PRD amendment first (`AGENTS.md` §3: PRD is product
   truth, and NFR-1 currently forbids it), a default of `false` in both config files, a pin instead of
   `*`, and an allowlist note beside the outbound host, because `cloud.langfuse.com` and
   `ingest.latitude.so` are today outside `config('uma.sources')` and the app is loopback-only by
   `PRD.md` §6.10.

**Tests, either way.** A case asserting `Http::assertNothingSent()` after `GET /api/v1/umamusume`
with the shipped config. That is the guard NFR-1 does not currently have.

**Acceptance.**
```
php artisan route:list --json | grep -c 'Latitude\|Langfuse'   # 0 after option 1
php artisan test --compact --filter=ApiV1
```

**OWNER DECISION, blocking.** This is the only slice in the plan that changes product behaviour against
a written non-goal. Nothing else in Phase 2 depends on it. Park it and finish the rest.

### S2.4 - The Sources screen reports "No fetch recorded" for 12 of 13 sources

**Measured.** `app/Http/Controllers/DatabaseController.php:437` groups `data_sources` by `source_key`
and looks up each `config('uma.sources')` key. The table holds **133 rows across exactly one
`source_key`**: `gametora-characters = 133`, against **13** declared sources. Every other source
resolves to null and renders "No fetch recorded" (`resources/js/pages/Database/Sources.vue:191`),
even immediately after a successful fetch, because the other writers stamp provenance inline on their
own tables (`race_catalog_slots.source_url`, `skills.source_url`, and so on) and never write
`data_sources`.

The roadmap already names the backfill as D-9 ("One `source_key` covers 14 datasets").

**Consequence.** The provenance surface lies in the direction that matters: it says a dataset was
never fetched when it was. It is the screen a Trainer uses to decide whether the catalog is current,
so it degrades trust in the one screen that exists to build it. The rows are not wrong, only
incomplete, which is why it needs a ruling rather than a patch.

**Change, cheapest honest fix without a migration.** Union the newest inline `fetched_at` per table
into `sources()` beside the `data_sources` lookup, so each source's date comes from wherever that
source actually writes. The full `data_sources` backfill stays D-9 and needs the owner's ruling.

**Tests.** A source that has written only inline provenance renders its date, not "No fetch recorded".
`DatabaseController` has existing coverage to extend rather than a new file.

**Acceptance.**
```
php artisan test --compact --filter=Database
```

**OWNER DECISION.** D-9 asks whether every engine fact should carry a `data_sources` row. Until that
is ruled, the union is the only honest reading available.

---

## Phase 3 - Documentation truth

None of these are progress; they are the reason a future session re-derives what this one measured.
Each is one line unless stated.

### S3.1 - Declared PHP floor is unbuildable

**Measured.** `composer.json:12` `"php": "^8.3"`. Locked `initred/laravel-langfuse v1.0.2` requires
`^8.5`. Eighteen `symfony/*` v8.1.x packages require `>=8.4.1` (`http-foundation`, `console`, `routing`,
`string`, `translation`, `process`, `finder`, `filesystem`, `event-dispatcher`, `error-handler`,
`var-dumper`, `http-kernel`, `mailer`, `mime`, `css-selector`, `uid`, `clock`, `yaml`). The only reason
this host works is PHP 8.5.8, and all three workflows pin `setup-php` to 8.5.

**Change.** `"php": "^8.5"` plus `composer update --lock` so the content hash matches. Update
`README.md:21` and `:34` (both state `>= 8.3`) and `README.md:22`, which states
`laravel/framework` **13.32.0** while the lock holds **v13.42.0** (`VERSION.md:15` already says 13.42.0
correctly).

**Acceptance.**
```
composer validate --no-check-publish
composer why-not php 8.3     # must name the same constraint the floor now states
```

**If S2.3 takes option 1**, the floor may stay `^8.3`: deleting langfuse removes the `^8.5` requirement
and the symfony `>=8.4.1` floor remains, so the floor becomes `^8.4`. Measure after the deletion rather
than guessing.

### S3.2 - `VERSION.md`'s measured block is stale on four of five rows

| Row | Document says | Measured now |
| --- | --- | --- |
| `:19` divergence | 8 behind / 38 ahead at `816e923e` | `0 0` at `fec658f8` |
| `:29` citation census | 808 dead against 708 | 528 GONE at HEAD |
| `:30` ADR checker | 2 findings (word-cap) | `0 finding(s)` |
| `:27` PHP baseline | 2467 passed / 2 failed | not re-run in this pass; mark inherited or re-run |

`:5` claims every figure was re-measured and every row names its command. Keep the promise by
re-running each row and stamping the HEAD sha beside it.

**Consequence worth stating in the commit:** the release-state section says the tag cannot be cut until
the divergence row reaches `0 0`. It already is `0 0`, so the document is blocking a shippable tag.

### S3.3 - Three documents describe `DocCitationParityTest` as red when it passes

**Measured.** `tests/Feature/DocCitationParityTest.php:44` `const DOC_CITATION_BASELINE = 708;`,
`:56` `expect((int) $m[1])->toBeLessThanOrEqual(DOC_CITATION_BASELINE);`. Census at HEAD is 528.
The assertion passes. `VERSION.md:29-33`, `EXECUTION-RECORD-2026-11-18.md:333` and `:351`, and
`VERSION.md:86` (Phase-1 criterion (d)) all record it as unsatisfied.

**Change.** Update the four sites to the current reading, mark criterion (d) satisfied at 528 <= 708
with the date and command, and stop conflating two subjects: 528 is the ref-based census, 530 the
working-tree one (`--working-tree`). Name which basis each figure uses.

### S3.4 - `ARCHITECTURE.md` §4 contradicts itself and the shipped API

**Measured.** `ARCHITECTURE.md:313-317` enumerates four endpoints (umamusume index/show,
training-runs index/show). `routes/api.php:13-18` ships six, adding `support-cards` index/show, covered
by `tests/Feature/ApiV1SupportCardTest.php`. Then `:329-332` says "Six endpoints is terminal … `skills`,
`support-cards`, and every other catalog table stay browser-only: adding a read-only index/show for
them would be surface the PRD does not ask for."

So the same section names support-cards as part of the terminal six and as a thing that must not be
added. `PRD.md:298` (FR-E E-1) says "catalog and runs", which does cover a catalog entity.

**Change.** Add the two support-card lines to the endpoint block, delete `support-cards` from the
forbidden list, and keep the "terminal at six" claim (it is still true).

**Acceptance.** A reviewer diffing `php artisan route:list --path=api` against §4 sees six for six.

### S3.5 - `docs/adr/CURRENT.md` says the Independent Training door does not exist; it ships

**Measured.** `docs/adr/CURRENT.md:158`: "**The mode has no Trainer-facing entry control** … the
Preflight switch and policy radios were cut at `ae16ec88`; `StartCareerRequest::independentConfig()` is
nullable and unreachable from any shipped control".

`resources/js/pages/Career/Preflight.vue:1061-1138` renders `<details data-independent-training>`, the
`independent` switch at `:1074-1075`, the policy select at `:1082-1090` and the priority-skill picker
at `:1101-1111`. `app/Http/Controllers/Career/PreflightController.php:166-174` supplies
`policies`/`skills`/`maxPrioritySkills`, and `:225-228` calls `(new Lifecycle)->start($run, $config)`.

Separately: `PRD.md` has **zero** occurrences of "Independent Training". An accepted ADR
(`docs/adr/0032-…`, status accepted 2026-11-18) authorises a mode with no PRD requirement.
`CURRENT.md:160` records that as owed.

**Change.** Dated erratum in `CURRENT.md` (append, never rewrite: `AGENTS.md` §11), and one of:
a PRD line under the career section, or an explicit non-goal entry in `PRD.md` §6. Owner's choice;
either closes the "owed" row.

**Acceptance.** `python tools/adr-check.py docs/adr` still reports `0 finding(s)` and the erratum
carries a date.

### S3.6 - `AGENTS.md` counts disagree with the tree and with each other

**Measured.** 62 pages, 119 components, 3 layouts (`find resources/js/{pages,components,layouts} -name '*.vue' | wc -l`).
`AGENTS.md:17` says "~119 components / ~62 pages" (correct). `AGENTS.md:137` says
"pages/ (66), components/ (119), layouts/ (2)" (two wrong). `AGENTS.md:528` records "verified counts
(119 components, 66 pages)" in the changelog (wrong, and it claims a verification that did not happen).

**Change.** Set both rows to 62 / 119 / 3, and replace the counts with the three `find` commands so the
figure is re-derivable rather than asserted.

### S3.7 - `AGENTS.md` §18's Inertia trap describes a file state that no longer exists

**Measured.** `AGENTS.md:490`: "`INERTIA_SSR_ENABLED` defaults true, `config/inertia.php` unpublished so
`ssr.timeout` unset". `config/inertia.php` **is** tracked, `:24` is
`'enabled' => (bool) env('INERTIA_SSR_ENABLED', false)`, and `:34` is
`'timeout' => env('INERTIA_SSR_TIMEOUT')`.

**Why it matters.** The trap is the documented defence against KI-76. It prescribes publishing the
config as the remedy (already published) and misstates the default (off), so a session may set
`INERTIA_SSR_TIMEOUT` believing SSR is on.

**Change.** Rewrite the bullet: file is published, `ssr.enabled` defaults false, timeout is wired to
`INERTIA_SSR_TIMEOUT`, `.env.example`'s explicit false stays as belt and braces. Delete the "publish
it" remedy.

### S3.8 - "No CI" is wrong, and the CI that exists runs no gate

**Measured.** `AGENTS.md:19`: "No CI; every gate in §9 runs locally and its output is the only
evidence." `AGENTS.md:419` and `CLAUDE.md:272` say the same. `.github/workflows/` tracks three
workflows (`nativephp-windows.yml`, `nativephp-windows-acceptance.yml`, `release.yml`) and `.gitignore`
carries `/.github/`, which is why the discrepancy survived.

Grepping all three workflow files for `phpstan|pint|pest|artisan test|gates.sh|typecheck|ui-framework|lore`
returns **nothing**. So the infrastructure claim is doubly wrong: gates exist as docs, CI exists as
build-only, and neither enforces the other.

**Change.** Correct the three doc lines to "CI exists but runs build, acceptance and release only; no
§9 gate runs there". Then add one gate job. See S4.2.

### S3.9 - `composer audit` resolution is fine; do not "fix" it

**Recorded so a future session does not chase it.** A delegated audit claimed `composer audit` was
unreachable on this host, that `tools/gates.sh:93-94` picks an MSYS path native `php` cannot open, and
that the skip message wrongly blames KI-4. Measured: `command -v composer` resolves to
`/c/Users/exatf/AppData/Local/ComposerSetup/bin/composer`; `composer --version` prints 2.10.2;
`composer audit --format=json` returned real `symfony/yaml` advisories; and the gates.sh block run
verbatim printed `REACHED: composer audit path`.

The only defensible part is cosmetic: the skip message names KI-4 (GNU make) for a composer problem.
Change the message if you like; do not change the resolution logic.

---

## Phase 4 - Release

### S4.1 - A tag publishes an installer no packaged test ever ran

**Measured.** `.github/workflows/release.yml:3-7` triggers on `push: tags: ['v*']` and builds its own
installer at `:82-115`, validates that an EXE exists and hashes it at `:117-142`, then publishes at
`:157-199` with `needs: build` and no other dependency.
`.github/workflows/nativephp-windows-acceptance.yml:3-6` triggers on
`workflow_run: workflows: ["NativePHP Windows Build"]` only. It therefore gates the *other* workflow's
artifact and never runs against the installer `release.yml` builds and publishes.

`tests/browser/nativephp-packaged.spec.ts` does exercise the real gates (four probes: no header 403,
wrong secret 403, correct header 200, correct cookie 200). Not defect: the `Cookie: ***` visible in the
file is the harness's secret redaction; `node --check` exits 0 and the raw bytes hold a template
literal.

**Change.** Add the acceptance job as a required predecessor of publication inside `release.yml`:
build -> run `nativephp-packaged.spec.ts` against the just-built installer -> publish only on success.
`needs:` becomes the chain rather than `build` alone.

**Acceptance.** A `workflow_dispatch` run shows the packaged spec executing against this run's
installer, and the release job is skipped when the spec fails. Do not cut a test tag or a test
Release: this repo's standing preference is real CI success or nothing.

### S4.2 - No CI job runs a gate

Add one job to `nativephp-windows.yml` (or a new `gate.yml`) on `push: branches: [master]` and
`pull_request`:
```yaml
- run: bash tools/gates.sh --fast
```
`--fast` deliberately skips the PHP suite so the job stays cheap; S0.1 makes the fast path's steps
honest first, which is why this slice sits after Phase 0 and cannot precede it.

Requirements the job must satisfy, both measured: `setup-php` extensions must include `sockets`
(`pestphp/pest-plugin-browser` requires it or `composer install` fails on a platform mismatch), and the
PHP version must match whatever S3.1 settles on.

**Acceptance.** A push to `master` shows the job green with the same output `gates.sh --fast` prints
locally, and a deliberately introduced lint failure reddens it.

### S4.3 - Cut `v3.0.0` (OWNER)

Not an agent act. The artifact the owner signs: an annotated tag on a commit reachable from
`origin/master`, with `VERSION.md`'s release and tag rows updated in the same change, after Phases 0-3
are green and S4.1/S4.2 are landed.

**Precondition, measured:** the divergence blocker named in `VERSION.md:67-71` is already cleared
(`0 0`). The remaining blocker is S3.2 recording it.

```
git tag -l 'v3*'                                   # must contain v3.0.0 after
git merge-base --is-ancestor v3.0.0 origin/master  # exit 0
bash tools/gates.sh                                # full, output recorded in the tag's commit body
```

### S4.4 - Two pre-tag obligations the gate only pretends to run

Neither reddens an ordinary slice (`AGENTS.md` §5 makes them tag-time). Both must actually produce a
reading before S4.3:

- `composer audit` (works here; S3.9)
- `npm audit --omit=dev` (needs S0.3 before its output means anything)

**Acceptance.** Both outputs quoted in the tag commit body, with the advisory IDs named, and no
reachable critical or high.

---

### S4.5 - The tree's own telemetry edits already fail Pint

**Measured.** `vendor/bin/pint --test --format agent routes/api.php bootstrap/app.php` returns `fail`:
`routes/api.php` needs `ordered_imports`; `bootstrap/app.php` needs `fully_qualified_strict_types`,
`unary_operator_spaces`, `not_operator_with_successor_space`, `ordered_imports`.

So the working tree fails C-3 today, on the two files the telemetry change touched. Anything built on
top of them inherits the red.

**Change.** If S2.3 takes option 1 (drop the packages), both files revert and the finding dissolves.
If the packages stay, run `vendor/bin/pint routes/api.php bootstrap/app.php` as part of S2.3 and
re-run `--test` to confirm.

**Acceptance.**
```
php vendor/bin/pint --test --format agent routes/api.php bootstrap/app.php
```

**Note.** This is not a separate defect so much as the proof that no gate ran on these edits.
An agent that ran §9's hand-off would have caught it, which is the case for S4.2.

## Phase 5 - Debt with a named ceiling, parked deliberately

These are not defects and must not be started as "small follow-ups". Each needs an owner ruling or a
measured trigger. Record the ceiling so the deferral cannot quietly become permanent.

| Item | Measured size | Ceiling and upgrade path |
| --- | --- | --- |
| `app/Models/TrainingRun.php` 1709 lines | 29 tracked code files exceed 800 lines; `TrainingRunController.php` 1517, `CockpitController.php` 1417, `TeamPanelBuilder.php` 1142, `SkillsPlannerController.php` 928 | No measured symptom. Upgrade when a slice needs to change three unrelated concerns in one file, or when a query-count test fails on the cockpit read path (`CockpitController.php:250-256` loads six relations then sorts `turnEntries` in memory three times per request). Split by job, never by line count. |
| `tools/ui-framework-check.mjs` reports app.css findings at wrong lines | Reported `app.css:315` for `--color-page: #1E1435`, which sits at `:728`; line 315 is `--color-on-mood: #1F1508`. Comment stripping replaces newlines, so `lineAt` counts lines in the stripped text; skew reaches ~630 lines. 77 of the 84 `literal-colour` hits are inside `html[data-theme='eva01']`, a documented theme override the span detector does not know about | Every app.css count is unverifiable by line. Upgrade: preserve newlines in `stripComments` (`m.replace(/[^\n]/g,' ')`) and add the eva01 selector to `tokenSpans`. Two lines, no rule semantics. Do it in the same commit that next needs to trust an app.css finding. |
| `data_sources` provenance backfill | 133 rows, 1 distinct `source_key`, covering 14 datasets | `AGENTS.md` §8 wants one row per fact. Recorded as roadmap D-9. Needs a ruling before a backfill action; do not invent source keys. |

---

## Dependency order

```
Phase 0  S0.1 -> S0.2 -> S0.3 -> S0.4 -> S0.5 -> S0.6 -> S0.7
                                                        |
Phase 1  S1.17 (one line, worst user-visible defect; S1.9 reads the same function)
         S1.5 (stops the leak; nothing else in Phase 1 depends on a populated queue)
         S1.4 (independent; gates S1.5's and S1.1's tests on a reproducible seed)
         S1.1 (needs S1.5: the schema and resolver assume no more race rows arrive)
         S1.2, S1.3 (independent of everything above)
         S1.10, S1.14, S1.15, S1.19 (independent one-file fixes)
         S1.9 (after S1.17: same pricing path)
         S1.14 -> S1.18 (same training-run controller, same event-key defect)
         S1.12 -> S1.13 (same file, same shape)
         S1.7 -> S1.8 (same class; S1.7's transaction move is the pattern S1.8 generalises)
         S1.6, S1.11, S1.16 (independent)
Phase 2  S2.1 (independent)
         S2.2 (needs S1.1 and S1.11: the safe-verdict rule defines what bulk reject may offer)
         S2.3 (OWNER; blocks nothing else)
Phase 3  S3.1 (needs S2.3's decision: option 1 changes the floor answer)
         S3.2 -> S3.3 (same commit; both are the same five rows)
         S3.4, S3.5, S3.6, S3.7, S3.8 (independent one-liners)
Phase 4  S4.1, S4.2 -> S4.3 (OWNER) -> S4.4
Phase 5  parked, no arrow
```

Rules across every arrow:

- An arrow is a verification checkpoint. A slice with no acceptance output stays open and is reported,
  not marked done.
- Phase 0 precedes everything because its four repairs decide whether any later green means anything.
- Owner-gated items (S2.3, S1.1's disposition question, S2.1's copy question, S4.3) park with the exact
  artifact to sign and never block an independent slice beside them.

---

## Per-slice hand-off, cheapest sufficient

Copy from `AGENTS.md` §9. Every slice, in order, stopping at the first rung that does not apply:

```bash
php artisan test --compact --filter=<SliceName>          # narrow first
php artisan test --compact                               # full suite at a checkpoint, not per edit
vendor/bin/pint --test --format agent <touched paths>    # never --dirty (KI-75)
vendor/bin/phpstan analyse --no-progress --memory-limit=1G
npm run typecheck                                        # if TS/Vue/Blade touched
node tools/ui-framework-check.mjs                        # if a Vue template touched
python tools/md_fence_check.py <touched md>              # if docs touched
php tools/lore.php && php tools/lore.php code            # every user-visible string
composer audit && npm audit --omit=dev                   # pre-tag only
```

Browser work is always session-private and one process at a time:

```bash
PLAYWRIGHT_PORT=<port> PLAYWRIGHT_SCRATCH_DB=$PWD/.scratch-uma/<session>/browser-<port>.sqlite \
  npx playwright test --workers=1
```

Preflight one trivial SQLite read before a broad sweep; if it takes more than a few seconds the box is
contended and the sweep is void (KI-74).

---

## Excluded as already complete, with the evidence

Stated so no future session re-implements these. Each was measured in this pass.

| Item | Evidence it is done |
| --- | --- |
| Independent Training entry door | `Career/Preflight.vue:1061-1138` renders it; `PreflightController.php:166-174, 225-228` wires `Lifecycle::start()`. Roadmap S3.1 recorded DONE. |
| Independent Training completion by scheduler | `bootstrap/app.php` schedules `independent-training:complete` every minute; `nativephp/electron/electron-plugin/src/index.ts:126,134` calls `startScheduler()`, so the packaged app runs `schedule:run`. `Lifecycle.php` has `start`/`canStart`/`complete`/`abandon`/`completeExpired`. |
| Run export CSV/JSON | `TrainingRunController.php:1467-1516`: closed whitelist (`abort_unless(in_array($format, ['csv','json'], true), 404)`), `fputcsv` with explicit args, no path built from input. Not exploitable. |
| Backup durability (NFR-5) | `app/Actions/BackupDatabase.php:46` UTC-stamped name, `:61` `VACUUM INTO` on its own PDO, `:24` refuses an existing target, `:57-61` escapes the composed path. |
| Engine never writes Trainer data | Grepped every write path under `app/Services/DataPipeline/` plus `app/Actions/Promote*` and `Store*` against `training_runs`, `turn_entries`, `turn_events`, `race_entries`, `shop_purchases`, `deck_slots`, `run_skills`, `veterans`, `transfer_requests`: 0 hits. |
| `is_manual` guard on promotion | `PromoteMatchedRecord.php:40-42` returns `skipped` for a manual row before any write, inside the transaction boundary. `StoreRaceCatalogSlots.php:40-41` and seven sibling `Store*` actions do the same. S1.2 is the one command that does not. |
| Form Requests at every write boundary | 33 classes under `app/Http/Requests/`; `$request->validate(` occurs **0** times across `app/`. |
| Per-race maiden gate columns dropped | `database/migrations/2026_10_27_120000_drop_per_race_maiden_gate_columns` plus `tests/Feature/Schema/MaidenGateColumnDroppedTest.php` pinning all three absences. The *predicate* is still wrong (S1.3). |
| G-61 ADR set contract | `python tools/adr-check.py docs/adr` returns `0 finding(s)`, and the step is in `tools/gates.sh`. |
| `support-deck.spec.ts` drives live controls | `RESIDUAL-VERIFICATION-2026-11-18.md` §refuted: 13 of 13 green against the shipped picker; selector literals resolve to `SupportSlot.vue:221` and `Builder.vue:443,455,494`. |
| `nativephp-packaged.spec.ts` `Cookie: ***` | Not broken JS. `node --check` exits 0; raw bytes hold a template literal; the `***` is the harness's secret redaction. |
| Gate script's composer block | Works. `composer audit --format=json` returned real advisories; the block printed `REACHED`. See S3.9. |

---

## Whole-plan acceptance

The plan is complete when all of these hold at one HEAD, in one recorded run.

```bash
# 0. tree
git --no-optional-locks status --porcelain -uall      # only intended changes
python tools/doc_census.py                            # <= 708, and can fail
python tools/check_untracked.py                       # clean, and can fail
python tools/audit_status_register.py KNOWN-ISSUES.md --index-lines 140   # exit 0
python tools/adr-check.py docs/adr                    # 0 findings
python tools/verify-backdrop.py                       # 6/6 ok
node tools/ui-framework-check.mjs                     # exit 0, card-in-card fatal
python tools/md_fence_check.py $(git diff --name-only HEAD '*.md')

# 1. behaviour
php artisan test --compact                            # full suite, output quoted
npm run typecheck
vendor/bin/pint --test --format agent <paths>
vendor/bin/phpstan analyse --no-progress --memory-limit=1G
php tools/lore.php && php tools/lore.php code

# 2. schema (scratch DB, never the dev file)
DB_DATABASE=.scratch-uma/<session>/acceptance.sqlite php artisan migrate:fresh --seed
php artisan uma:enrich-races --dry-run 2>/dev/null || true
php artisan migrate:status

# 3. release (owner)
bash tools/gates.sh
composer audit && npm audit --omit=dev
git tag -l 'v3*'
```

Functional acceptance, stated as what a Trainer can do that they could not before:

1. `php artisan uma:fetch` with no argument runs to completion, skipping the four enrichment keys by
   name, instead of aborting on the first name-less source.
2. The review queue holds zero race rows: a fetch files no candidate for a race, and the 304 existing
   ones are Rejected once.
3. Confirm on a non-trainee candidate answers 422 with a named reason, never 500, and writes nothing
   to `umamusume`; Confirm -> Reopen -> Confirm does not duplicate a catalog row.
4. Dispose of any remaining Pending candidates by filtering to one entity and rejecting in bulk.
5. A winless native career past turn 12 still shows the maiden lock on every graded race row, with the
   sentence that explains it.
6. `uma:enrich-races` leaves a hand-edited `race_catalog_row` untouched and says how many it skipped
   for that reason.
7. A fresh clone seeds race tracks, fan curves and rewards with no network.
8. A transferred career cannot be re-banked, and the screen says why.
9. A career cannot be started with `independent=1` and no policy: the form errors and no run exists.
10. Two Independent Training sessions cannot overlap on one run, and closing one either leaves the
    career Active or asks before ending it.
11. A run cannot exceed its recorded Skill Points across two successive skill posts.
12. A run with no scenario shows no obligations and no scenario goals on the cockpit, matching what
    its Race Planner and its stat caps already say.
13. A hint recorded at "Lv Max" is priced at the discounted figure everywhere: the Cost column, the
    Remaining bar, the recommendation ordering and the server refusal all quote one number.
14. Correcting a turn's outcome removes the stale Failure chip from the Timeline, and renumbering a
    turn does not hand its event to a different turn.
15. `bash tools/gates.sh --fast` on a tree with a new nested Card, a citation over the baseline, or a
    failing backdrop plate fails.

---

## What this plan does not do

- It does not touch `docs/roadmap/MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` or
  `docs/roadmap/UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md`; both stay the ordering record they are.
- It does not resolve the five open PRD questions (`PRD.md:479-502`), the owner decision register
  (`MASTER-IMPLEMENTATION-ROADMAP` §14), or the Phase-5 capability table. Those need rulings, not
  slices.
- It does not run the full Pest suite or any browser sweep. No figure in this file is a suite result;
  the PHP baseline row is marked inherited from `VERSION.md:27`.
- It does not treat the 24 untracked owner files at the repo root (the backdrop drafts, logo sources,
  `lighthouse.png`, `agent.json`) as anything but owner property. Nothing here deletes or stages them.
- Unmeasured and named as such: query counts on the cockpit and planner read paths at scale, packaged
  exe behaviour, GitHub Actions run history, and the advisory contents of either audit.
