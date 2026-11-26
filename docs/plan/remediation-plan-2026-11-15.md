# Remediation plan - audit findings of 2026-11-15 (transfer 500s, schema drift, form 500s, state wipes)
<!-- PLAN-CONTRACT
plan: remediation-plan-2026-11-15
status: dropped
slices: —
superseded-by: —
amended-by: —
open: —
-->

Status: proposal, awaiting owner approval. No `app/`, `database/`, or `resources/` file has been
modified. This document is the only deliverable of this pass.

Tree state at planning time: HEAD `2ae2de54` (peer commits landed during the pass: `9c99aeaa`,
`ebe5b3fe`; none touches transfer, export, or the run-form paths this plan covers). Working tree
carries 9 peer-modified files and 6 untracked paths, none of them in this plan's fence. All
measurements below ran read-only against copies of `database/database.sqlite` under
`.scratch-uma/audit-2026-11-15/`; the dev database was not written.

Evidence labels: **measured** = probe output recorded in this pass (`.scratch-uma/audit-2026-11-15/*.php`);
**read** = source/vendor/file read only.

---

## 0. Dispatch premises, measured

The dispatch that ordered this plan carries premises. Each is measured here before the plan is
built on it, per the standing rule that a brief's numbers describe the tree its author last saw.

| Premise in the dispatch | Measured reality |
| --- | --- |
| "dev DB currently cascade-deleted rows missing" (data repair needed) | **False.** `transfer_requests` holds 0 rows in the dev DB, and a failed `CompleteTransferRequest` rolls back the whole transaction: the transfer row stays `offered`, the veteran stays alive (probe6/probe2). There is no data repair to do; the defect is schema-and-code only. |
| "Update the [original] migration to use a partial unique index" (Slice 1.2) | **Deviate.** The original `2026_11_14_023609` migration is already recorded **Ran** on the dev DB. Editing a landed migration is not how this repo corrects a schema; the corrective migration must converge both shapes. See Decision A. |
| "SQLite does not support altering foreign keys directly ... confirm whether the plan requires table rebuild (`Schema::table` or raw SQL)" | **Measured: Laravel's SQLite grammar rebuilds the table itself.** A single `Schema::table` closure combining `dropIndex` + `dropForeign` + `change()->nullable()` + re-add `foreign()->nullOnDelete()` produced the correct end DDL (rebuilt table, `on delete set null on update cascade`). No manual 12-step rebuild and no raw `ALTER TABLE` emulation is needed; **raw SQL is required for the partial index itself** (see Section 1). |
| "`prepareForValidation()` posts `mode` default on `runs.store` only" | Confirmed as the defect site: `StoreTrainingRunRequest.php:166` merges unconditionally, and the same class serves `runs.update` (probe.php section 3: mode flips `snapshot` -> `new_career` on a settings save). |
| "transfer pager navigates to the library" | Confirmed (probe4): `veteransIndexUrl` = `/veterans`, `goToPage()` calls `router.get(props.veteransIndexUrl, ...)`. |

Two further measured facts the dispatch did not carry, and which reshaped the slices:

- **The 500 now also hits the scenario-switch path.** A Trackblazer run that recorded
  `shop_resets_in` and is switched to a shop-less scenario via `/settings` throws the model's
  `InvalidArgumentException` (probe12a: `Scenario [ura_finale] has no shop...`). The scenario
  form's payload carries no `shop_resets_in` key at all, so no request-level refusal can fire on
  that path; the fix needs a write-path cleanup, not only a validation rule (Section 3b).
- **`current_objective_index` on the same switch is refused, not a 500** (probe13, corrected
  scenario key): the settings scenario form re-posts the stored period, so the existing closure
  fires a field error. That is the designed behaviour and stays; only `shop_resets_in` has no
  such path.

---

## 1. Corrective migration: `transfer_requests` (Slice 1a)

### 1.1 Measured DDL, both shapes

Dev DB (`sqlite_master`, probe schema-probe.php):

```sql
CREATE TABLE "transfer_requests" ("id" integer primary key autoincrement not null,
  "veteran_id" integer not null, "status" varchar not null default 'offered',
  "reward" text, "completed_at" datetime, "withdrawn_at" datetime,
  "created_at" datetime, "updated_at" datetime,
  foreign key("veteran_id") references "veterans"("id") on delete cascade)
CREATE UNIQUE INDEX "one_offered_per_veteran" on "transfer_requests" ("veteran_id")
```

Migration file `2026_11_14_023609` (what every fresh/test database builds, probe2 section a):

```sql
CREATE TABLE "transfer_requests" (... "veteran_id" integer, ...,
  foreign key("veteran_id") references "veterans"("id")
  on delete set null on update cascade)
CREATE UNIQUE INDEX "one_per_veteran_per_status" on "transfer_requests" ("veteran_id", "status")
```

The dev index name `one_offered_per_veteran` exists in no file (`grep -rn one_offered_per_veteran
database app tests`: zero matches outside the binary database). The dev DB was built from an older
draft revision of the same migration.

Faults in each shape, measured:

- Dev shape: `on delete cascade` deletes the audit row when the veteran is deleted, so
  `CompleteTransferRequest`'s `$transfer->fresh()` returns `null` -> `TypeError` 500 (probe2,
  probe6). `veteran_id NOT NULL` blocks `nullOnDelete`'s whole point.
- Fresh shape: full unique `(veteran_id, status)` refuses the second `withdrawn` row for one
  veteran, so offer -> withdraw -> offer -> **withdraw** throws
  `UNIQUE constraint failed: transfer_requests.veteran_id, transfer_requests.status` (probe5).

### 1.2 The two SQLite facts that decide the recipe

1. **Laravel's fluent index builder silently drops the `WHERE` clause on SQLite.** `$table->unique('veteran_id')->where('status', 'offered')` is accepted, but the emitted DDL is
   `CREATE UNIQUE INDEX ... ("veteran_id")` with no `WHERE` (probe planfix.php section a, with
   `compileUnique` in `vendor/.../Grammars/SQLiteGrammar.php:398` confirmed to emit no WHERE).
   A column-only partial index then forbids offer+withdrawn coexistence entirely. **The partial
   index must be created with raw SQL.**
2. **`Schema::table` handles the FK alter on SQLite by rebuilding the table.** Measured working
   primitives on a dev-shaped copy: `dropIndex('one_offered_per_veteran')`,
   `dropForeign(['veteran_id'])`, `(unsignedBigInteger('veteran_id')->nullable()->change())`,
   `foreign('veteran_id')->references('id')->on('veterans')->nullOnDelete()->cascadeOnUpdate()`.
   End DDL carried `on delete set null on update cascade` (probe11, probe18). `Schema::hasIndex`
   exists and works for existence checks (probe18). `CREATE UNIQUE INDEX IF NOT EXISTS ... WHERE
   status = 'offered'` is idempotent (probe18, run twice).

### 1.3 Recipe

New file `database/migrations/2026_11_15_HHMMSS_fix_transfer_requests_table.php`, matching the
repo's `declare(strict_types=1)` + docblock conventions. Its docblock records, in this order: the
drift (two shapes, which one each environment carries), why the original migration is not edited
(Decision A), and why the partial index is raw (fact 1 above).

`up()`, each step guarded so it is a no-op on a database already in the target shape:

1. `Schema::hasIndex('transfer_requests', 'one_per_veteran_per_status')` -> `dropIndex`. Removes
   the fresh-shape full unique.
2. `Schema::hasIndex('transfer_requests', 'one_offered_per_veteran')` -> `dropIndex`. Removes the
   dev-shape full unique (same name the partial index will reuse).
3. If `veteran_id` is not nullable (`Schema::getColumns` -> `nullable` key, field set measured in
   probe18), run the single `Schema::table` closure: `dropForeign(['veteran_id'])`,
   `unsignedBigInteger('veteran_id')->nullable()->change()`,
   `foreign('veteran_id')->references('id')->on('veterans')->nullOnDelete()->cascadeOnUpdate()`.
   On the fresh shape the FK is already `nullOnDelete` and the column already nullable; the guard
   skips the closure, so no gratuitous rebuild runs in tests.
4. Raw statement:
   `create unique index if not exists one_offered_per_veteran on transfer_requests (veteran_id) where status = 'offered'`.

Data repair step: **none needed, and the migration carries no repair.** Measured: zero rows exist
in the dev table, and the failure mode that would have produced orphans (cascade on complete)
rolled back before commit. The docblock states this with the measurement, so a later reader does
not add a phantom repair.

`down()`: drops the partial index; restores the full unique `(veteran_id, status)`; restores the
NOT NULL cascade FK **only when no row holds `veteran_id = NULL`**, otherwise throws with a named
message ("audit rows of deleted Veterans cannot satisfy a NOT NULL veteran_id; clear them or keep
the corrective migration"). This keeps the repo's working-`down()` rule honest: it works on every
shape it can restore, and refuses loudly rather than silently deleting audit rows. The
`one_per_veteran_per_status` re-add can itself fail if any veteran holds two `withdrawn` rows;
that failure is the correct one (the pre-fix shape cannot represent that state) and is noted in
the docblock. *(Caveat: the two refusal cases in `down()` are inferred from the constraint
definitions; execution will prove them on the scratch copy before the migration is committed.
This is the one part of the plan not yet measured.)*

### 1.4 Verification (execution phase)

1. On a scratch copy of the dev DB: apply, dump `sqlite_master` for the table, run the lifecycle
   (Section 6 cases) against it, then `migrate:rollback --step=1` and dump again.
2. `php artisan migrate` on the dev database itself, after `php artisan uma:backup`. The dev DB
   is the one environment where the drift exists, so its application is part of the fix, not an
   afterthought.
3. `php artisan migrate:status` clean, quoted in the hand-off.

---

## 2. `CompleteTransferRequest` refactor (Slice 1b)

### 2.1 The `fresh()` removal

`app/Actions/CompleteTransferRequest.php:47` returns `$transfer->fresh()`. With `nullOnDelete`
the row survives (measured on the corrected shape, probe12 section b: complete returns
`status=completed`, `veteran_id=NULL`, veteran deleted). The change is:

- Return `$transfer` directly instead of `$transfer->fresh()`. The instance already holds the
  written values from `$transfer->update([...])` (line 38). This makes the action correct on both
  schemas instead of only the corrected one, which is the defense the audit asked for.

Exact diff shape (one line plus the docblock sentence that claims the mechanism):

```php
// before
return $transfer->fresh();
// after
return $transfer; // the row it just wrote; a fresh() here couples the action to a schema shape
```

### 2.2 `veteran_label` audit stamp (recommended, deferrable)

The comment at line 44 currently claims "the transfer_request keeps veteran_id for audit via
nullOnDelete"; measurement refutes the readable half: `veteran_id` is nulled (probe12), and the
board falls back to the literal `Removed Veteran` (`TransferRequests.vue:72`). The audit row
keeps the reward and the timestamps and loses who was transferred.

Options weighed:

- **A. New nullable `veteran_label` column** stamped at completion (`VeteranRow::label($veteran->trainingRun)`
  before the delete). Cost: one more column in the same corrective migration; the board reads it
  when `veteran` is null. Honest, queryable, one line on each side.
- **B. Stuff a key into `reward` JSON.** Zero schema cost, but `reward` is a typed amount bag
  (`{star_pieces?, monies?, support_points?}`) that the board sums and prints; a label inside it
  is the kind of overloaded column this repo's DTO discipline exists to prevent.
- **C. Leave as is, correct only the comment.** The audit trail names nobody.

Recommendation: **A**, folded into the same corrective migration (the table is already being
altered; a second migration for one nullable string is churn). If the owner prefers to keep the
corrective migration minimal, **C** is the honest minimum and this section becomes a filed KI
instead. The board change is `veteranDisplay()`: prefer `transfer.veteran_label` over the literal
fallback. The comment at line 44 is rewritten either way.

---

## 3. `shop_resets_in` bounds (Slice 2)

### 3.1 Where the bound lives (measured)

- `config/scenarios.php`: only `trackblazer` declares `shop` (`{"rotation_turns":6,
  "max_copies_per_item":5, "locked_until_debut":true}`, line 792). `ura_finale`, `unity_cup`,
  `our_grand_concert` have no `shop` key. `baseline` is `ura_finale`.
- The model guard `TrainingRun::booted()` (`app/Models/TrainingRun.php:146-178`) reads
  `config('scenarios.scenarios.'.$run->scenarioKey().'.shop.rotation_turns')`, throws when the
  key is absent ("has no shop") or when the value is outside `0..rotation`. It is the sole owner
  of the bound today, and it throws - which is the 500.
- `StoreTrainingRunRequest.php:139` is `['nullable','integer','min:0']`: no upper bound, no
  scenario condition. The request-side pattern to copy exists two rules above it:
  `current_objective_index` (lines 117-134) refuses a value when the run's scenario does not
  compose the panel.

### 3.2 Exact rule change (create path)

In `StoreTrainingRunRequest::rules()`, replace the bare rule with a scenario-conditioned pair,
mirroring the `current_objective_index` closure verbatim in shape:

```php
'shop_resets_in' => is_int($rotation = self::rotationFor($scenario))
    ? ['nullable', 'integer', 'min:0', 'max:'.$rotation]
    : ['nullable', 'integer', 'min:0', function (string $attribute, mixed $value, Closure $fail): void {
        if ($value === null) {
            return;
        }
        if (! self::rotationComposes($scenario)) {
            $fail('This scenario has no shop, so there is no rotation countdown to report.');
        }
    }],
```

where `$scenario` is read the way the sibling closure reads it:
`$this->input('scenario', $run instanceof TrainingRun ? $run->scenario : null)`, and
`rotationFor()` is a private helper that reads `config('scenarios.scenarios.'.$scenario.'.shop.rotation_turns')`
and returns it only when it is an int. Both the rule and the model guard then read the same config
key; the request refuses first, the guard stays as the belt for non-request writers (dispatch hard
rule 2: no guard removed). Message entry `'shop_resets_in.max' => 'The shop rotation is :max turns
in this scenario.'` joins `messages()`.

Note the alias: the request's `prepareForValidation()` normalizes `''` to nothing for other keys;
`shop_resets_in` arriving as `''` is already `nullable`-safe because `ConvertEmptyStringsToNull`
runs in the HTTP kernel. No change needed there.

### 3b. Scenario-switch cleanup (update path)

Measured 500 (probe12a): Trackblazer run holding `shop_resets_in = 3`, switched to `ura_finale`
via the `/settings` scenario form (payload carries no shop key) -> model guard throws. There is
no field on any surface that can clear the column before the switch, so the write path must do it.

Exact change in `TrainingRunController::update()` (after `$request->validated()`), before
`$run->update(...)`:

```php
$validated = $request->validated();
if (array_key_exists('scenario', $validated) && $validated['scenario'] !== $run->scenario
    && ! ScenarioRotation::composes($validated['scenario'])) {
    $validated['shop_resets_in'] = null; // belongs to the scenario being left
}
$run->update($validated);
```

`current_objective_index` keeps its designed refusal (the settings form re-posts the stored
period, so the existing closure fires a field error - probe13; that is a message, not a 500, and
the user has a field to act on). Decision C in Section 7 records this asymmetry for the owner.

---

## 4. Mode retention on update (Slice 3a)

Measured (probe.php section 3): `PUT /training-runs/{run}` with the settings payload
(`umamusume_id`, `status`, `scenario`, `current_objective_index`) produced
`mode before=snapshot after=new_career`, because `StoreTrainingRunRequest::prepareForValidation()`
line 166 merges `mode => input('mode', RunMode::NewCareer)` unconditionally and
`update()` writes `validated()` whole. `RunSettings.vue` and `TeamEdit.vue` post no `mode`, so
every settings or team save wipes the classification.

Exact change: guard the merge by route.

```php
// before
$this->merge(['mode' => $this->input('mode', RunMode::NewCareer->value)]);
// after
if ($this->routeIs('runs.store')) {
    $this->merge(['mode' => $this->input('mode', RunMode::NewCareer->value)]);
}
```

Consequences checked: `runs.store` keeps its default (RunModeTest's create cases unchanged);
`runs.update` no longer carries `mode`, so the column is untouched (the rule stays `nullable`
`Rule::enum`); `ImportHistoricalRunRequest` extends this class, and its model write omits `mode`
entirely, so `TrainingRun::create()` falls back to the model's own `$attributes` default
`new_career` - the import path's observable behaviour is unchanged. `CreateSnapshotRun` sets
`mode` explicitly and does not use this request.

---

## 5. CSV quoting and the transfer pager (Slices 3b + 4)

### 5.1 Export quoting

Measured (probe14 section b, probe17): `fputcsv($h, $row, ',', '"', '')` on the existing fixture
row emits byte-identical output to today's `implode(',')` for the header and for
`1,480,300,355,210,95,240,,74,GOOD,41250` (the exact row `FrontendAuditFixesTest.php:60` asserts),
quotes `Good, energetic` as `"Good, energetic"` and `He said "go"` as `"He said ""go"""`, and
emits LF only. The real parser re-reads all three through `ImportHistoricalRunRequest::parse()`
with the values intact (probe17). The existing assertions in `TrainingRunTest.php:145` (header
prefix) and `FrontendAuditFixesTest.php:60` (exact second line) therefore stay green by
measurement, not by hope.

Exact change in `TrainingRunController::export()` (line 1482-1488): build the CSV through a
`php://temp` stream with `fputcsv($h, $row, ',', '"', '')` - the empty escape argument matches
the parser's own explicit `str_getcsv(..., '', ...)` (ADR-0017 records why). Whole-row pass, no
manual `str_replace`. Content-Type and Content-Disposition headers unchanged.

ADR/registry trail (required, since the ADR's contract explicitly carries this limit):
- `docs/adr/0017-historical-run-import.md`: the contract block's `open:` field currently names the
  comma limit; it and the "one limit it accepts" section get a `## Ledger` entry (newest first,
  dated) recording the fix and that the export, not the parser, changed. The original sentences
  stay verbatim per the erratum convention; the `open:` field moves to `—`.
- Regenerate the ADR index with `python3 tools/adr-index.py docs/adr --write` (command read from
  `docs/adr/README.md:55`); `CURRENT.md` regenerates in the same pass if the tool covers it.

### 5.2 Pager

Exact change: `TransferRequestController::index()` adds `'transfersUrl' => route('veterans.transfers')`
to the Inertia props (one line, beside the existing `veteransIndexUrl`); `TransferRequests.vue`
`goToPage()` (line 121) uses `props.transfersUrl`; the `Props` interface gains the field; the
"Back to Veterans" anchor (line 168) keeps `veteransIndexUrl`. No route change.

---

## 6. Test coverage matrix

New/updated cases, exact names and files. Every case is written RED-first where the fix is a
behaviour change (the harness builds fresh in-memory SQLite from the migration files, so the
corrective migration is exercised by every case below).

`tests/Feature/TransferRequestTest.php` (exists; 13 passing today):
1. NEW `it('walks offer, withdraw, offer, withdraw for one Veteran')` - the cycle the full
   `(veteran_id, status)` unique refused. Red before Slice 1a, green after. (fix 2)
2. NEW `it('creates a partial unique index on status offered, not a full unique index')` -
   reads `sqlite_master` (route measured in probe16: `select sql from sqlite_master where
   tbl_name='transfer_requests' and type='index'`) and asserts the index SQL contains
   `where status = 'offered'`. This is the **drift-trap test**: it fails if either the full unique
   returns OR the fluent `->where()` form silently drops its WHERE clause (probe planfix fact 1).
3. NEW `it('completes a transfer, deletes the Veteran, and keeps the audit row')` - extends the
   existing complete case with the row-survival assertion and `veteran_id` nulling. (fix 1)
4. UPDATE `it('completes a transfer: marks completed, deletes Veteran, records reward')` (line
   107) only if Slice 1b adds the column; otherwise unchanged.
5. NEW (only if Decision B adopts the label) `it('stamps the transferred Veteran label on the audit row before deleting it')`.
6. NEW `it('sends the transfers board its own url for paging')` - `assertInertia` on
   `transfersUrl` = `/veterans/transfers`. (fix 6)

`tests/Feature/RunCreateSurfaceTest.php` (exists):
7. NEW `it('refuses shop_resets_in on a scenario with no shop instead of throwing')` - POST
   `scenario=ura_finale`, `shop_resets_in=3`, assert `assertSessionHasErrors('shop_resets_in')`
   and `assertRedirect`. Red today (500 via `InvalidArgumentException`). (fix 3)
8. NEW `it('refuses a shop countdown beyond the scenario rotation')` - `scenario=trackblazer`,
   `shop_resets_in=7`, assert session error on the field. Red today (500). 
9. NEW `it('accepts a shop countdown within the scenario rotation')` - `trackblazer`, `5`,
   `assertSessionHasNoErrors` - the non-vacuity pair for 7/8.

`tests/Feature/RunSettingsTest.php` (exists; grows the update-path cases):
10. NEW `it('retains snapshot mode and stored position when the settings route updates the run')` -
    factory snapshot run (`mode=snapshot`, `career_position` set), PUT `runs.update` with the
    settings base payload, assert both survive. Red today (mode flips). (fix 4)
11. NEW `it('clears the shop countdown when the scenario changes to one that has no shop')` -
    Trackblazer run holding `shop_resets_in=3` -> PUT `scenario=ura_finale` -> 302, no errors,
    column null. Red today (500, probe12a). (fix 3b)

`tests/Feature/RunModeTest.php` (exists): unchanged create-path cases stay as the counterproof
that `runs.store` still defaults `new_career`.

`tests/Feature/HistoricalRunImportTest.php` (exists):
12. UPDATE `it('rejects a condition carrying a comma rather than shifting every later column')`
    (line 292) IN PLACE - never deleted - into
    `it('round-trips a run whose condition carries a comma through export and import')`: export a
    run whose condition holds a comma, feed it to `runs.import.store`, assert the value returns
    intact. A dated comment records that the old case pinned the export's limitation, that
    ADR-0017's ledger entry records the fix, and why the assertion flipped. (fix 7)
13. The existing byte-level round-trip case (line 93-125) already covers the quote-free path and
    must stay byte-identical; measured, `fputcsv` keeps its fixture row unchanged.

Known gaps this matrix does not close, stated rather than implied:
- The dev-DB drift itself cannot be reproduced inside the suite (the harness builds only the
  fresh shape). The drift is covered by the schema-shape case (2) plus the execution-phase
  verification of Section 1.4 against a copy of the dev file; the KI entry (Section 7) carries
  the rest.
- No browser spec covers the transfer board; adding one is out of this plan's fence (the board's
  page-level spec would be a separate slice if the owner wants it).

---

## 7. Execution order, commits, gates, decision points

### Order (each slice: RED case first where applicable -> change -> green -> gates)

1. **Slice 1 - transfers schema + code.** Corrective migration (Section 1), `CompleteTransferRequest`
   (Section 2), optional label (Decision B), tests 1-5, KI entry (below). Verify on a dev-DB copy,
   then apply to the dev DB after `php artisan uma:backup`; `migrate:status` quoted.
   Commit message (dispatch's, kept verbatim):
   `fix(transfers): resolve dev schema drift, 500 on complete, and 500 on double withdraw`
2. **Slice 2 - form 500s.** Sections 3 and 3b, tests 7-9 and 11. Commit (dispatch's, verbatim):
   `fix(runs): gracefully validate shop_resets_in instead of throwing 500`
3. **Slice 3 - state and navigation.** Section 4 (test 10) and Section 5.2 (test 6). Commit
   (dispatch's, verbatim): `fix(runs): retain snapshot mode on update; fix transfer pager route`
4. **Slice 4 - CSV.** Section 5.1, test 12, ADR-0017 ledger entry, regenerated index. Commit
   (dispatch's, verbatim): `fix(exports): quote CSV cells to allow commas in condition text`
5. **Docs trail.** KI entry, `KNOWN-ISSUES.md` (see below), ADR per Decision D. May ride Slice 1's
   commit or land as its own `docs(...)` commit; owner's call.

Registry obligations discovered while planning, to be settled in execution rather than silently:

- **No ADR owns the transfer feature.** `grep transfer PRD.md ARCHITECTURE*.md docs/adr/CURRENT.md`:
  zero matches; the feature landed in `6bbca89b` (2026-11-14) with a schema migration and no
  decision record. AGENTS.md §11 requires an ADR for a schema change. This plan's corrective
  migration is itself a schema change, so the minimum trail is: one new ADR
  (`docs/adr/0031-...`) covering the partial-index + nullOnDelete decision, an
  `ARCHITECTURE-ESSENTIALS.md` digest row for `transfer_requests` (the table is absent from the
  digest entirely), and an index regeneration. Decision D governs whether that lands with Slice 1
  or is filed for the owner first.
- **KI entry** for the drift itself (dev DB built from an unlanded draft revision; suite blind to
  it), appended to `KNOWN-ISSUES.md` in the four-part form continuing from the highest number
  (KI-160 observed as the current maximum; the seeder may have landed newer during execution -
  re-check before filing). The entry's proving command is the `sqlite_master` diff pair.

### Pre-commit gates per slice (this repo's hand-off, cheapest sufficient level)

- `vendor/bin/pest tests/Feature/TransferRequestTest.php` etc. for the slice's own files
  (measured, a single file ran in ~32s), then at the end of each slice the related files.
- `vendor/bin/pint --test --format agent <changed paths>` (never `--dirty`).
- `vendor/bin/phpstan analyse --no-progress --memory-limit=1G <changed paths>`.
- `npm run typecheck` and `npm run build` on any slice touching `.vue` (Slices 3 and 4's board
  change), because the browser/board paths read `public/build`.
- Level 3 after the last slice: full `php artisan test --compact` plus the hand-off sequence
  (`composer lore` / lore-code with rulings, `migrate:status` clean on the dev DB). The full suite
  runs in the background to a session-private log (`.scratch-uma/<session>/`), and per the
  standing rule a red sweep is classified before anything re-runs.
- Working-tree discipline: the ten peer-modified/untracked paths measured at planning time are
  not touched, and each commit names its own pathspecs explicitly.

### Decision points for the owner (each present, with a default)

- **A. Corrective-only, or edit the original migration too?** Default: corrective-only (Section
  1.3). Editing `2026_11_14_023609` after it has run on the dev DB would rewrite history the dev
  database has already consumed; the corrective migration converges both shapes instead, and its
  docblock records why.
- **B. Stamp `veteran_label` now, or file it?** Default: stamp it (Section 2.2 option A), folded
  into the corrective migration. The alternative (defer + KI) is legitimate and cheaper; the
  audit row then keeps naming nobody.
- **C. Should a scenario switch also auto-clear `current_objective_index`?** Default: no - it has
  a working refusal with a field error today (probe13), and only `shop_resets_in` has no path at
  all. The asymmetry is deliberate and recorded here.
- **D. ADR + digest with Slice 1, or filed first?** Default: land them with Slice 1, since §11
  makes them part of the schema change. If the owner prefers, Slice 1 can land code-only and the
  docs trail becomes an immediate follow-up - but then the slice is knowingly short of the bar,
  and the record must say so.

### What this plan does not do

- Does not write any code; no `app/`, `database/`, `resources/`, or `tests/` file has changed.
- Does not run the full suite, PHPStan, Pint, or the browser suite (audit-scope reads only; the
  measured runs are the single transfer test file, 13 passed, and the probes above).
- Does not apply anything to the dev database; that happens in execution (Section 1.4).
