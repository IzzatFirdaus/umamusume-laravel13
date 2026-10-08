# Known issues

**Pointer for the historical register, and still the live one. Its full text moved on 2026-10-03 to
`docs/research-scratch/AUDIT-AND-VERIFICATION.md`, section "KNOWN-ISSUES.md (defect register)":
2,655 lines covering KI-01 to KI-37, each entry naming the command or file that proves it and the
commit that closed it.** KI-38 through KI-56 sit in the same master, further down; this file carries
KI-57 onward.

**Status (2026-10-06, at `ab53861`):** this file carries KI-57, KI-58, KI-59 and KI-60, and all four are
OPEN. KI-60's dev-database half was remediated on 2026-10-06 and stays open for its prevention half; the
entry records which is which. The count is stated as composition rather than a total, because the
totals that used to live here went stale the moment the history moved.

**Status correction (2026-10-07, D8 hand-off):** the composition above is superseded, and the dated
claim is left standing rather than rewritten. This file now carries KI-57 through **KI-63**. KI-61
(D7's red landing, filed with the wizard's rerun fix) was appended without this line being updated.
KI-62 and KI-63 were filed by the D8 hand-off; KI-62 also applied its fix in the working tree. All are
OPEN, and each names its own closure conditions. `master` remains unpushed (`O-1`), so none can close
yet.

## This file is still the append target

`AGENTS.md` and every slice record write into `KNOWN-ISSUES.md`, so this file was a poor
consolidation candidate and only its history moved. Keep appending new entries here, in the format
the embedded snapshot uses:

1. `### KI-nn — title`, with `nn` continuing from the highest existing number, never reusing one.
2. A Status line saying what is open, closed, or accepted-as-known.
3. The command or file that proves it, not just the symptom.
4. On closure, the commit hash, and what the closure explicitly does not cover.

When a register pass changes the counts, update the `**Status (...)**` line at the top of this file.
The embedded snapshot is a dated record and is not edited to follow; it states 35 filed, 24 closed,
11 open as of 2026-09-30, and this file is where the current number lives. Two numbers disagreeing
across a dated snapshot and a live register is the intended arrangement, not a defect to reconcile.

Tracked files cite `KNOWN-ISSUES.md` by name, including `PRD.md`,
`tests/Feature/DocSchemaDriftTest.php` and `tests/Feature/CharacterCardParserTest.php`. Do not
delete it, and do not renumber existing entries: the renumbering history (KI-16 left as a hole,
KI-30 and KI-31 renumbered from KI-16 and KI-17) is what made the old numbers cheap to stop using.

### KI-57 The screenshot manifest's Race result row names a frame that does not exist, because its date ellipsis is wrong - FILED 2026-10-03 (Phase A closure pass, screenshot pipeline), OPEN

`docs/research-scratch/DESIGN-CORPUS.md` section `## SCREENSHOT-MANIFEST.md`, the Race result/live
row of the Screen-type coverage table (line 4207 at filing time), lists its representatives as
"`2026-07-18 000919`, `235511`". The ellipsis on the second frame inherits `2026-07-18` from the
first, but the frame on disk is `Screenshot 2026-07-17 235511.png`. Taken literally the manifest
names `Screenshot 2026-07-18 235511.png`, which is in no directory, in no cluster, and in no
signature record.

Reproducer: `ls docs/game-screenshots/ | grep 235511` returns exactly one name,
`Screenshot 2026-07-17 235511.png`. `Screenshot 2026-07-18 235511.png` appears nowhere in
`docs/game-screenshots/`, `docs/design-research/_scratch/clusters.json` or
`docs/design-research/_scratch/signatures.json`.

Remedy: append a dated erratum beneath the manifest row stating the second representative is
`Screenshot 2026-07-17 235511.png`, and do not edit the historical line in place. First found by
the Phase A inventory pass on 2026-10-03, which initially propagated the phantom frame into
`research-scratch/screenshots-inventory.csv` and corrected it there; the CSV row now cites the
real frame.

### KI-58 `tools/gate.py`'s hex allowlist lost its document leg silently when the design master was folded, and the guard for that condition cannot fire - FILED 2026-10-03 (Phase A closure pass, from the priors pass finding), OPEN

`tools/gate.py:94` reads `docs/design-research/DESIGN.md` as leg one of `load_token_hexes()`. The
re-baseline folded that document into `docs/research-scratch/DESIGN-CORPUS.md` and deleted it, and
`tools/gate.py:95-96` handles a missing document by returning an empty set with no report. The
guard at `tools/gate.py:120-121` was written to catch exactly this and cannot fire, because leg
two, `docs/design-research/_scratch/tokens.json`, alone yields 176 ramp values, far over the
40-value threshold the guard checks. The enforced allowlist (`tools/gate.py:229-232`) is therefore
`tokens.json`'s ramps plus the three hardcoded values at `tools/gate.py:116-118`, unchanged since
commit `e0e043c`.

Reproducer: three hexes that `resources/css/app.css` declares as current token values are in
neither `tokens.json` nor the hardcoded three, so an artifact using any of them fails G-4:
`--color-up: #B45309` (`app.css:168`), `--color-down: #0667B0` (`app.css:169`), `--color-ink-muted:

\#6E6459`(`app.css:48`). Their prior values`#FF9A2C`,`#0088E0` and `#7A7067` remain recoverable at

`git show e0e043c:docs/design-research/DESIGN.md`.

Fix options, in priority order: repoint `tools/gate.py:94` at `resources/css/app.css` and read the
`@theme static` block plus the `html[data-theme='dark']` block directly, so the allowlist tracks
the shipped theme; or regenerate `tokens.json` with `tokens.py` against the current `app.css` so
the anchor is refreshed. Not fixed here; this entry files the finding. First recorded in
`docs/research-scratch/DESIGN-CORPUS.md` section "scratch-priors.md" §2.1 (2026-10-03, priors pass;
the priors file moved there on 2026-10-03 when root `research-scratch/` was emptied).

## KI-59 The catalog detail browser case asserts a portrait absence a populated mirror cannot produce, and its sibling guard makes the pair mutually exclusive - FILED 2026-10-05 (Task B1 closure pass, from the Playwright run), CORRECTED 2026-10-05, OPEN

`tests/browser/catalog-detail.spec.ts:140-152` asserts unconditionally that the catalog detail Identity
section holds no `<img>`, while its sibling at `:113` asserts the frame is present but only once it is
already visible. Exactly one of the two can pass on a given host: `:140` needs the mirror to hold no
file, `:113` needs it to hold one. The spec's own contract at `:107-112` says each case "asserts only
when that state is present, the same `:36` rarity-chip precedent the catalog index uses", and `:113`
follows it while `:140` does not.

On this host the mirror holds 665 portraits under
`storage/app/private/artwork/characters/portrait/`, which is why `:146` fails `toHaveCount(0)` with
`Received: 1`, resolving to one element across 33 polls. The app is behaving as documented:
`ArtworkMirror::url()` (`app/Services/DataPipeline/ArtworkMirror.php:67-72`) probes the disk and
returns the `artwork.show` route only when the file is present, and `CatalogIndexPortraitTest` pins the
other branch server-side, asserting `artworkURL` is `null` when the portrait is not mirrored
(`tests/Feature/CatalogIndexPortraitTest.php:44-54`). The absence state is therefore real and
covered, but unreachable from a browser: with every cataloged `card_id` mirrored, no page on a
populated host can show it. That is a coverage gap and a fixture-dependence, not a rendering defect.

Fix options, in priority order: give `:140` a fixture whose portrait the mirror provably lacks, because
an unmirrored card is exactly the case `DESIGN.md` §4.7 exists to describe; or guard it like `:113` so
both cases assert only when reachable and the suite stops depending on whether this host has run
`uma:fetch-art`. Not fixed here; this entry files the finding. Separately, `AGENTS.md` §8 still
describes the `ADR-0021` display half as unbuilt, which no longer matches
`resources/js/components/ArtworkSlot.vue`; that is a documentation defect of the same pass, not part of
this entry's mechanism.

**Correction, 2026-10-05, the same day as filing.** The original text named the app and claimed
"`Catalog/Show.vue:128` passes `:url="trainee.artworkURL"` to `ArtworkSlot` unconditionally, and
`ArtworkSlot.vue:80-85` renders an `<img>` whenever `url` is truthy, with no check that the mirrored
file exists", concluding "the prop comes from the row, not from the disk". That was wrong and is
withdrawn: `url()` does check the disk, so a host that has not run `uma:fetch-art` would emit no URL
at all and render no frame. The mechanism was misread because the `card_portrait` kind resolves to the
nested `characters/portrait/trainee/<bucket>/` tree, and a shallow directory listing without recursion
reported zero files where 665 exist. The `DESIGN.md` §4.7 rule holds and the app is not at fault.

**Host-state note, 2026-10-06.** The sentence above asserts "this host" holds 665 mirrored files. Measured
on this worktree on 2026-10-06, `storage/app/private/artwork/` **did not exist**, and
`php artisan uma:fetch-art --dry-run` reported `card_portrait: 106 ids, already on disk 0` and
`support_thumb: 559 ids, already on disk 0`. So the populated-mirror premise of this entry does not
describe this tree, and every slot on every screen was rendering the null branch. Two readings fit the
evidence: the 665 belonged to a peer worktree that has since been pruned (each tree carries its own
gitignored `storage/`), or the count conflated the two kinds, since 106 + 559 = 665 exactly while only
the 106 live under `characters/portrait/`. Which one it was is not established here, and this note does
not withdraw the entry's mechanism: with a populated mirror the `:140` case still cannot pass. The
mirror was filled on 2026-10-06 by `uma:fetch-art`, so the entry is now reproducible in this tree again.

### KI-60 Four committed migrations have never been applied to `database/database.sqlite`, so the landing page and `/legacy` return 500 while the whole suite passes green - FILED 2026-10-06 (from the owner's `GET /` error report), OPEN

**Symptom, observed.** `GET http://127.0.0.1:8000/` raises
`Illuminate\Database\QueryException: SQLSTATE[HY000]: General error: 1 no such table: veterans
(Connection: sqlite, Database: database/database.sqlite, SQL: select count(*) as "aggregate" from
"veterans")`. The aggregate comes from `ListVeterans::handle()` at `app/Actions/ListVeterans.php:63`,
the `paginate()` call, reached from the route `home`.

**Cause, measured.** `php artisan migrate:status` on the dev file reports four **Pending** migrations,
all of them committed: `2026_10_02_193559_add_condition_groups_to_skills_table` and
`2026_10_02_193601_add_awakening_event_evo_to_character_cards_table` (both from `e1de4ce`), plus
`2026_10_05_120000_create_veterans_table` and
`2026_10_05_180000_add_build_target_to_training_runs_table` (both from `dbf390f`). The dev database
carries 41 rows in `migrations`; the tree ships 45. `php artisan db:table veterans` answers
`Table [veterans] doesn't exist.`

**Scope, wider than the report names.** This is not a defect in the dashboard. The dashboard path that
raised it is a peer's **uncommitted** in-progress slice (`DashboardController.php`, `Dashboard.vue`,
`types.ts`, `DashboardTest.php`, `config/scenarios.php`), and nothing here touched it. But committed
`master` already reads the same table at `app/Http/Controllers/LegacyController.php:106`
(`Veteran::query()->count()` on `/legacy`), and `build_target` and `condition_groups` are read by
committed code too. Every route touching those four tables is broken on this host's dev database
regardless of the working tree.

**Why no gate caught it.** `phpunit.xml:64` forces `DB_DATABASE=:memory:`, so every test rebuilds its
schema from the migration files and cannot observe the dev file's drift. The suite is green at five
digits and the application returns 500 on its own landing page; those two facts are not in tension,
they are the same fact seen from a database the check does not read. `composer docs`, Pint, PHPStan and
`npm run typecheck` are likewise schema-blind. There is no check that runs `migrate:status` against the
dev file.

**Proof of the mechanism, run without writing to the dev file.** The dev database was copied with
`VACUUM INTO` (never `cp`: it is in WAL mode, and the main file alone held ~0.2% of the content when
last measured), `php artisan migrate` was run against the copy, and the result checked there: all four
applied cleanly, `veterans` exists with its unique index on `training_run_id`, all six new columns
exist, and all 29 pre-existing tables kept identical row counts (only `migrations` moved, 41 to 45).
The migrated copy was then served on a spare port and `GET /` and `GET /legacy` both returned 200.
`database/database.sqlite` was not modified by this pass.

**Two consequences the fix has to cover, not just the 500.**

1. `migrate` alone leaves the two 2026-10-02 columns **empty**: on this host `skills` holds 1,910 rows
   and `character_cards` 106, and `condition_groups`, `skills_awakening`, `skills_event` and
   `skills_evo` are NULL on all of them, because those columns are filled by the parsers, not by the
   migration. Reading them after a bare `migrate` renders the very mechanics absence `KI-33` and
   `KI-59` describe. The population step is `php artisan uma:reparse <source>` (`AGENTS.md` §11, and
   `KI-27` for why a short-circuited `uma:fetch` will not do it).
2. The plan tracks slices by whether their code landed, and
   `docs/proposals/frontend-development-plan.md` §5 records C3 as `Landed` while the table it names had
   never been created on the only database anyone browses. "Landed" there means merged and tested; it
   carries no leg for "applied to the dev database", which is the leg that broke.

**How it stayed unfixed for four days, stated plainly because the reason is the finding.** Every
dispatch since 2026-10-02 fenced shared-database writes and required `DB_DATABASE=<scratch path>` for
each migrate, and `AGENTS.md` §11 routes schema verification to a scratch database. That instruction is
correct for *verification* and was read as forbidding *application*, so the migrations landed with no
permitted path onto the dev file and nobody filed the gap. This entry does not resolve that: applying
the four migrations to `database/database.sqlite` is an additive, per-migration-reversible write to the
Trainer's own data, and it is the owner's call, not an agent's.

**Fix, in order.** (a) `php artisan migrate` against the dev file; (b) `php artisan uma:reparse` for the
skills and character-card sources so the new columns hold data; (c) give the plan's status column an
applied-to-dev leg, or add `migrate:status` to the hand-off sequence in `AGENTS.md` §9, so a Pending
migration in front of a committed read path fails a gate instead of a page.

**Remediation applied 2026-10-06, by owner ruling after this entry was filed. (a) and (b) are done; (c)
is not, and closure is held on `O-1`.**

- `php artisan uma:backup` first, and the backup was opened and counted *before* any write:
  `storage/app/backups/uma-backup-20261005-195923.sqlite` holds 30 tables, 6 runs, 1,910 skills and 41
  `migrations` rows. The name is UTC; the local date is 2026-10-06.
- `php artisan migrate` applied all four (10.55 / 8.16 / 3.31 / 2.84 ms). `migrate:status` now reports
  **0 Pending**.
- `uma:reparse gametora-skills` → 1,910 updated, 0 created, 0 skipped, 0 to review.
  `uma:reparse gametora-character-cards` → 106 updated, 0 created, 1 skipped, 0 to review. Both replayed
  from the `2026-09-29` snapshots, zero network, as `AGENTS.md` §11 requires.
- Post-state, measured on the dev file rather than asserted: `condition_groups` non-null on **1,910 of
  1,910** skills, and `skills_awakening` / `skills_event` / `skills_evo` on **106 of 106** cards.
  `veterans` holds 0 rows and `build_target` is NULL on all 6 runs, which are correct empties rather
  than gaps: nothing has been recorded to the library and no target has been entered.
- **No row count moved anywhere.** Against the same-day backup: `umamusume` 67, `training_runs` 6,
  `turn_entries` 1, `character_cards` 106, `skills` 1,910, `race_catalog_slots` 410, `support_cards` 559,
  all identical.
- `GET /` and `GET /legacy` return **200** on the live server on 8000. The dashboard document carries the
  `activeCareer`, `recentVeterans`, `quickActions` and `dataStatus` props and the string
  `no such table` is absent from it.

**Two things this pass suspected and then cleared, recorded because both look like defects.**

1. `data_sources` stayed at 67 rows while 2,016 rows were rewritten, which reads as a breach of the
   Floor's "no fact stored without provenance". It is not: at these grains provenance travels **on the
   row**, and `source_url`, `snapshot_path` and `fetched_at` are non-null on all 1,910 skills and all
   106 cards. Rows holding content with no provenance number **0**.
2. The row's `fetched_at` then moved to the moment of the reparse while `data_sources.fetched_at` stayed
   at the real fetch date, so two clocks disagree. The copy separates them correctly: the skill, card
   and form panels print `read <fetched_at>` and the `data_sources` list prints `fetched <...>`
   (`resources/js/pages/Skills/Show.vue:184`, `resources/js/components/catalog/FormDetail.vue:41`,
   `resources/js/pages/Catalog/Show.vue:389`). Nothing claims a fetch that did not happen.

**One number this pass could not explain, and did not paper over.** The cards reparse reported
`1 skipped (manual or unresolved)` while `character_cards` holds **zero** rows with `is_manual = 1` and
**zero** rows with a NULL `umamusume_id`, and its row count did not change. The counter's meaning lives
in `PipelineRunner`, which this pass did not read, so the skip is recorded as unattributed rather than
assigned to a cause. A future pass should read that counter before treating the message as evidence.

**What stays open.** Half (c): `docs/proposals/frontend-development-plan.md` still records a slice as
`Landed` with no leg for `applied to the dev database`, and no gate runs `migrate:status` against the dev
file, so the next migration can sit Pending in exactly this way again. Landing (c) means editing
`AGENTS.md` §9 and the plan's status table, both of which have been fenced against agent edits in every
dispatch since 2026-10-02, so it is the owner's to apply. Closure of this entry is additionally held on
**O-1**: register discipline closes an entry only when the fix is on `origin/master`, and `master` is
unpushed. Everything above is local.

**Correction, 2026-10-06, the same day.** The owner lifted the `AGENTS.md` fence and asked for
corrections, so the §9 half of (c) **landed at `e505f72`**: `migrate:status` on the dev database joins the
Schema row of the §9 change-class table, the hand-off sequence carries a paragraph stating that
`phpunit.xml:64` forces `DB_DATABASE=:memory:` and therefore that a green suite proves nothing about
`database/database.sqlite`, and §18 gains the matching trap. The paragraph above stands as the record of
what was fenced when this entry was filed; what is now false in it is "no gate runs `migrate:status`".
Still open: the plan's `Landed` leg, and this entry's closure, which waits on **O-1**.

### KI-61 The D7 wizard halves landed red at `0006b17` on the owner's instruction, because no browser-spec gate existed for the Preflight screen at the time of commit - FILED 2026-10-06 (D7 hand-off verification phase), OPEN

**Symptom, observed.** After the Preflight controller, StartCareerRequest and the four-step setup
wizard routes were committed, `tests/browser/career-preflight.spec.ts:95` (the case titled `Start
Career creates the run and lands on it`) was observed returning HTTP 500 when Playwright drove the
Inertia PUT against an empty body. The server logged:

`ValueError: "" is not a valid backing value for enum App\Enums\RunStatus`

thrown from `app/Http/Requests/Career/StartCareerRequest.php:113` inside `runAttributes()`. The same
code path tested green via Pest `put()`/`putJson()` for an identical payload, so the failure was
visible only from a real browser Inertia client posting JSON Content-Type.

**Cause, measured.** The compound root cause has two independent layers, both triggered only when the
outer request arrives as `Content-Type: application/json` (the shape Inertia's `useForm({}).put(url)`
produces):

1. **Laravel framework alias layer.** Symfony's `Request::createFromBase()` writes
   `$this->request = $this->json` (shared object reference) for every JSON Content-Type request.
   Because `FormRequest::createFrom($parent)` shallow-copies every ParameterBag by reference rather
   than deep-copying them, a child `FormRequest` produced by `createFrom` shares the parent's
   `request` / `json` bag exactly. Any `replace()` on the child's input therefore mutates the
   parent's input through the same pointer.

2. **The wizard's rerun pattern.** `StartCareerRequest::withValidator()` re-runs each of the three
   child step FormRequests (`StoreDraftDeckRequest`, `StoreDraftLegacyRequest`,
   `StoreDraftBuildTargetRequest`) against its own slice of the composed SetupDraft — calling
   `createFrom` followed by `$child->replace($slice)` exactly once per slice. Each slice has one
   or two keys and replaces the whole shared bag, so after the third child (the deck slice with a
   single `deck` key holding position-filtered slots), every key the parent's own
   `prepareForValidation()` had merged in (`status`, `umamusume_id`, `scenario`, `build_target`,
   `legacy_selection`, `legacy_parents`) was overwritten to absent. The next line of parent code
   that reads `$this->input('status')` received `""` (the ConvertEmptyStringsToNull middleware did
   not run a second time on the mutated bag), and `RunStatus::from("")` raised the ValueError.

Verification evidence: a new Pest regression test titled `it survives all three child reruns with every
merged key intact on an Inertia JSON PUT` was added to `tests/Feature/CareerPreflightTest.php` (lines
308–353), which fires `putJson(route('career.preflight.store'), [], ['X-Inertia' => 'true'])` and
asserts all fourteen merged-key properties (status=Active, scenario, trainee id, build_target purpose +
distance + Speed=800, legacy_selection affinity + legacies count=2, both inheritance_parent_*_ids
resolved, 6 deck slots). The test failed red against the pre-fix rerun body and passed green after
rebuilding the child independently.

**Scope, wider than the screen.** The `createFrom + replace($slice)` anti-pattern is latent in any
Form Request re-validation scheme that runs against a JSON outer request, not just this wizard. Any
future code that composes a child FormRequest from a parent with `createFrom` and then calls
`replace`, `merge` or `set` on the child's input will quietly destroy the parent's own input on the
JSON Content-Type code path, while passing identical tests written in the form-encoded path. The
correct pattern (this entry's closure) is always `FormRequest::create()` with fresh bags rather than
`createFrom`, combined with stripping JSON Content-Type from the child's `$server` so the child
resolves its input source from the populated form ParameterBag and not the empty JSON one.

**Why no gate caught it.** The browser-spec gate for `career-preflight.spec.ts` did not exist in the
handoff at the moment the wizard code landed at `0006b17`; the file was written by the peer's
concurrent session and landed a short time after the wizard commit. Feature tests (`tests/Feature/`)
used `put()` (form-encoded) or internal request routing that preserved separate ParameterBag
instances because they did not go through `Request::createFromBase()` with JSON Content-Type set —
the path the Laravel `call()` implementation takes sets up a custom Request object that bypasses the
`json = request` alias, so the sharing never manifested in the suite. 1,326+ tests passed green
with the crash sitting on the browser path alone.

**Fix, applied without committing (local worktree, awaiting owner approval of the series).**

1. `StartCareerRequest::rerun()` was rewritten to build the child via
   `FormRequest::create($this->fullUrl(), $this->method(), $slice, cookies, files, $server)` where
   `$server` is `$this->server->all()` filtered to strip `CONTENT_TYPE`, `HTTP_CONTENT_TYPE`,
   `HTTP_ACCEPT` and `HTTP_X_INERTIA` keys. This guarantees freshly-allocated ParameterBags for
   every source (query, request, json, attributes) so the child's input mutation never touches the
   parent.
2. Session is reattached (`setLaravelSession` guarded by `hasSession()`), container and redirector
   are copied, and Content-Type is explicitly forced to `application/x-www-form-urlencoded` as a
   defense-in-depth so any sub-path reading headers rather than `isJson()` still sees form.
3. Stray `logger()->info('preflight.probe', …)` debug probe left in `prepareForValidation()` was
   removed, and two unused imports (`Symfony\Component\HttpFoundation\ParameterBag`,
   `Symfony\Component\HttpFoundation\InputBag`) were cleaned.
4. The regression test described above was appended to `CareerPreflightTest.php`.

Post-fix evidence on this worktree (all local — nothing pushed):

- `tests/Feature/CareerPreflightTest.php`: 11 passed, 171 assertions (was 9/153 before the new case
  and the ParameterBag fix).
- `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`: [OK] No errors.
- `vendor/bin/pint --dirty --format agent`: passed.
- `npm run typecheck` (tsc --noEmit): clean.
- `php artisan migrate:status` against `database/database.sqlite`: 44/44 Ran, 0 Pending.
- `composer audit`: one HIGH advisory in `league/commonmark` (GFM Table quadratic DoS,
  `PKSA-m4t9-vsgq-8khn`), pre-existing on this tree, not reachable from a local-only Trainer tool
  with no untrusted Markdown parse path.
- Browser runs against `career-preflight.spec.ts` and `career-build-target.spec.ts`: listed in the
  hand-off checklist, not yet re-run after the fix as of this filing (the Playwright sandbox was
  intermittent in this dispatch; the closure below covers them).

**Closure.** This entry is open pending four things:

1. Owner approval of the commit series (the rerun() fix, the regression test, the import and probe
   cleanup) so the worktree state lands on `master` and can be pushed — the original `0006b17`
   commit that landed red is history, and its fix is still sitting in `git diff` only.
2. `PLAYWRIGHT_BASE_URL=http://127.0.0.1:8141 npx playwright test career-preflight.spec.ts` run
   four times against the built-bundle scratch server and producing 4/4 green, confirming that the
   red Playwright case at line 95 is resolved on the exact code path that reproduced it.
3. The owed D4 browser evidence run `npx playwright test career-build-target.spec.ts` against the
   same base URL, filed alongside §5.4 of the D7 hand-off.
4. `composer lore` and `composer lore-code` re-runs against the final tree, with any new hits
   carrying a one-line context ruling and no forbidden-lexicon hits on display copy.

Once (1) through (4) are done and the work passes a fresh hand-off run of
`php artisan test --compact`, the entry can move to CORRECTED with the closing commit. Closure is
additionally held on **O-1**: register discipline closes an entry only when the fix is on
`origin/master`, and `master` is unpushed.

### KI-62 `ProvenanceBadge.vue` derived its glyph and word once at setup, so a prop that changed in place would print the previous state's word beside the new figure - FILED 2026-10-07 (D8 hand-off), FIXED IN TREE, NOT CLOSED

**Symptom, latent.** `resources/js/components/ProvenanceBadge.vue` read its two render values into
plain consts:

```js
const copy = BADGES[props.state];
const name = props.title ? `${copy.word}: ${props.title}` : copy.word;
```

A `<script setup>` body runs **once per component instance**. Inertia patches a page in place, so a
component whose props change underneath it keeps its instance and its setup does not re-run: `copy`
and `name` stay frozen at the first render's `state`. A screen that re-rendered the badge with a
changed `state` would print the old word (and old glyph) beside the new figure, with no error and no
console warning. Nothing calls it that way today, which is why this is filed as latent rather than as
an observed defect.

**Cause, measured.** The same class was reproduced live, in the same slice, one component over.
`resources/js/components/career/RecommendationCard.vue` derived its band the same way. The D8 browser
case `records Energy through the cockpit correction and then marks one action`
(`tests/browser/career-cockpit.spec.ts`) records Energy through the page's own correction, Inertia
patches the Cockpit in place, and the card kept printing `Recommendation unavailable because Energy
has not been entered.` while the reason lines and the `Go to …` button beside it, read straight from
`props`, updated correctly. That asymmetry — direct `props.x` reads updating and a setup-time `const`
not — is the defect, and it is what makes the badge's case a certainty rather than a suspicion.

**Fix, in tree.** `ProvenanceBadge.vue` now derives both values with `computed`, so they track the
prop:

```js
const copy = computed(() => BADGES[props.state]);
const name = computed(() => (props.title ? `${copy.value.word}: ${props.title}` : copy.value.word));
```

A comment in the file names the Cockpit as the case that proved it, so the next reader does not
"simplify" it back. `RecommendationCard.vue` carries the same fix and the browser case above is its
regression proof.

**Closure.** This entry is open pending:

1. The commit that carries the two `computed` derivations, so the fix is on `master`. Nothing is
   pushed (`O-1`), and register discipline closes an entry only when the fix is on `origin/master`.
2. A regression proof for the badge **itself**, which does not exist yet: no test renders
   `ProvenanceBadge` with a `state` that changes in place. The Cockpit case proves the class, not this
   component. The likely first caller is the Career Timeline (`SCREEN-018`, D14), which re-renders a
   run's history; that slice owes the case. Until then this entry's own evidence is inspection plus
   the sibling reproduction, and the entry says so rather than claiming a green test it does not have.
3. `npm run typecheck` and `npm run build` on the final tree, since the component's props contract is
   unchanged but its two bindings are now refs (both were re-run green on 2026-10-07 with the fix in
   tree; they are listed here as the gate the closing commit re-runs).

### KI-63 Inertia's bundled NProgress bar carries `role="bar"`, an invalid ARIA role, so a page-wide axe scan fails at WCAG 2.2 A 4.1.2 whenever a visit is in flight - FILED 2026-10-07 (D8 hand-off), OPEN

**Symptom, observed.** The D8 Cockpit's axe case failed with one critical `aria-roles` violation:

```json
{
  "id": "aria-roles", "impact": "critical", "tags": ["cat.aria", "wcag2a", "wcag412"],
  "nodes": [{ "html": "<div class=\"bar\" role=\"bar\" style=\"transform: translate3d(0%, 0px, 0px); transition: 200ms linear;\"><div class=\"peg\"></div></div>",
              "target": [".bar"],
              "none": [{ "id": "invalidrole", "data": ["bar"], "message": "Role must be one of the valid ARIA roles: bar" }] }]
}
```

The same scan passed on an earlier run, which is the tell: the node is **transient**.

**Cause, measured.** The node is not this repository's markup. It is NProgress, which Inertia ships
and drives for every visit. Its default configuration is in the built bundle
(`public/build/assets/spa-*.js`) and reads:

```js
{ positionUsing: 'translate3d', speed: 200, trickle: true,
  barSelector: '[role="bar"]', spinnerSelector: '[role="spinner"]', parent: 'body',
  template: ['<div class="bar" role="bar">', '<div class="peg"></div>', '</div>', …].join('') }
```

NProgress appends that template to `<body>`, as a **sibling** of the app root `#app`, and removes it
when the visit settles. `role="bar"` is not in the WAI-ARIA role list, so axe reports it as a critical
`aria-roles` failure under 4.1.2 Name, Role, Value for as long as the bar is mounted. Every Inertia
screen in this app is exposed to it, not the Cockpit alone: `tests/browser/accessibility.spec.ts`'s
two page-wide scans have the same exposure and pass only because a settled `goto` usually leaves no
visit in flight.

**Impact, scoped.** A conformance claim of "axe A + AA clean on each screen" is not true while a visit
is in flight on any screen, and the failure is timing-dependent, so it is exactly the kind of
violation that disappears from one run and returns in the next. Nothing here is a Trainer-visible
defect: the bar draws a progress indicator and carries no text or control.

**Workaround, in tree.** `tests/browser/career-cockpit.spec.ts` scopes both of its scans with
`buildAxe(page).include('#app')`, which excludes the framework's chrome and asserts this application's
own tree. That is scoping, not suppression: the excluded node is a sibling of `#app` and belongs to
Inertia, and the reason is written in the spec so a reader does not read the narrowing as a fix. The
shared `tests/utils/accessibility.ts` builder was left unchanged, because its other consumer
(`accessibility.spec.ts`) is another slice's file.

**Closure.** This entry is open pending a decision, and it is the owner's rather than a slice's:

1. **Disable Inertia's progress bar** (`createInertiaApp({ progress: false })` in `resources/js/spa.ts`)
   and re-scan page-wide. Cheapest, and the app already carries its own loading affordance: every page
   that starts a visit renders a `role="status"` line (`Dashboard.vue`, `Cockpit.vue`), so NProgress
   is a second, unlabelled indicator. The cost is losing the bar as a visual affordance on screens
   that have not added their own status line yet.
2. **Keep the bar and narrow every scan** to `#app`, with the exclusion written down. No code change,
   and the conformance claim is restated as "the application tree" rather than "the document".
3. **Keep the bar and file it as an accepted exception** in the gate registry, naming 4.1.2 and the
   third-party origin.
4. Whichever is chosen, `tests/utils/accessibility.ts` is the one place a default scope belongs, so
   that the two page-wide scans in `accessibility.spec.ts` stop being timing-dependent.

### KI-75 `pint --dirty` reformats another session's uncommitted file in the shared worktree - FILED 2026-10-08 (Database reference views hand-off, on the owner's instruction), OPEN

**What happens.** `AGENTS.md` §9 puts `vendor/bin/pint --dirty --format agent` in the hand-off sequence.
`--dirty` collects every file that differs from `HEAD`, which in this worktree is not the same set as the
slice's own files, because several sessions work in one tree (KI-70, KI-71). Measured 2026-10-08: a slice
that touched no controller ran the gate and Pint rewrote
`app/Http/Controllers/PreferenceController.php` with `no_unused_imports`, a file carrying 138 lines of
another session's uncommitted D18b work. The reported result was
`{"result":"fixed","files":[{"path":"app\Http\Controllers\PreferenceController.php","fixers":["no_unused_imports"]}]}`.
Nothing in the output says the file was not the caller's.

**Why it matters.** The bar's own definition of done says "no unrelated file changed" (`AGENTS.md` §15). A
mandatory gate guarantees that clause is broken in a shared tree, so the honest hand-off must report a
change it did not author, and the only repair, `git checkout -- <file>`, would delete the other session's
work. The agent is left choosing between a false report and a destructive revert, which is the shape KI-70
was filed for.

**Proving command.**

```bash
vendor/bin/pint --dirty --format agent          # names a file the run's own slice never opened
git diff --stat -- app/Http/Controllers/PreferenceController.php   # 138 insertions, two authors
```

**Owner.** QA / Reviewer for the hand-off wording in `AGENTS.md` §9, and the tooling owner for the command.
It is not a slice's to fix, and this slice did not fix it.

**Closure owed.** One of the two remedies, plus the §9 row rewritten to name it: (a) the gate runs on the
slice's own explicit paths (`vendor/bin/pint app/Http/Controllers/DatabaseController.php config/reference.php`),
which is what E3 did and what makes the "no unrelated file" clause satisfiable; or (b) a `composer fmt`
alias that reads its pathspec from the file list of the change being handed off. Closure is whichever lands
with the §9 row that cites it.

**What closure would not cover.** It does not undo the line already sitting in `PreferenceController.php`:
that edit stays, and the session owning the file will carry Pint's change inside its own commit. It also
does not touch the condition underneath, several sessions' uncommitted work in one working tree; any
future gate in the sequence that writes rather than reads inherits the same hazard the moment it is given a
`--fix` mode, and `phpstan` escapes it only because it never rewrites.

### KI-77 The working-tree `routes/web.php` imports seven controllers that `HEAD` does not carry, so no single slice can commit the file - FILED 2026-10-08 (Database reference views hand-off, on the owner's instruction), OPEN

**What happens.** KI-70 records a route registration pointing at a class that exists only in the working
copy. The same defect now sits one line earlier in the same file: in the *import statement*.
`git show HEAD:routes/web.php` carries ten `App\Http\Controllers\Career` imports and no
`DatabaseController` at all; every one of those ten resolves to a tracked file. The working-tree version
of the same tracked file adds seven imports whose files are untracked. The file is one object with eight
authors' lines in it, and committing it as it stands records a `HEAD` that names classes `HEAD` does not
contain.

**The seven, with the slice each belongs to** (each row is the controller's own docblock, not an
inference): D11 `EventDecisionController` (`SCR-CAR-014`), D12 `InheritanceEventController`
(`SCR-CAR-015`), D13 `SkillsPlannerController`, D14 `TimelineController` (`SCR-CAR-017`), D15
`ResultController` (`SCR-CAR-018`), E5 `RacePlannerController` (`SCR-CAR-023`), and D17
`DatabaseController` (`SCREEN-023`). Six of the seven belong to sessions other than the one filing this.

**Consequence, stated precisely.** A commit of the file as it stands boots, and then cannot serve those
URLs: dispatch resolves the action to a class that is not in the checked-out tree, and the request dies
with `Class "App\Http\Controllers\...Controller" not found`. `php artisan route:list` cannot resolve the
action either. That half of this entry is reasoned from the framework's action-resolution path, not
measured here, because measuring it means committing the file whole; the file-state half above is measured
and reproducible.

**Proving commands (2026-10-08).**

```bash
git show HEAD:routes/web.php > /tmp/head-routes.php
grep -cE '^use App\Http\Controllers\Career' /tmp/head-routes.php   # 10, and 0 DatabaseController
git ls-files app/Http/Controllers > /tmp/tracked.txt                   # 25 files
# every `use App\...` line in the working routes/web.php mapped to app/...php and tested against that list
# -> 7 imports have no tracked file: the seven named above
```

**Why it matters.** `AGENTS.md` §14 makes `master` the branch of record with no CI to catch a partial
landing, and §15 requires that a committed change be self-consistent. This file cannot satisfy either
while the six other slices stay untracked: the first session that commits `routes/web.php` wholesale ships
dead routes under a green-looking diff.

**Number note, and it is not incidental.** This entry was drafted against KI-76 and found the number taken
on arrival: another session filed the Vite-SSR hot-file finding as KI-76 between the two runs, so this one
sits at KI-77. The register already carries two KI-70 headings and two KI-71 headings, recorded in KI-70's
and KI-71's own text. Nothing in the tree checks for a duplicate KI number, which is why three collisions
now exist; `§11`'s "never renumber an existing entry" rule has no instrument behind it, and in a shared
worktree two sessions can pick the same next number within minutes.

**Owner.** Architect, for the per-slice worktree discipline KI-70 and KI-71 already ask for; QA / Reviewer
for the hand-off gate in `AGENTS.md` §9 that would catch it.

**Closure owed.** Three things, and any one of them is small: (a) `php artisan route:list` joins the
hand-off sequence, since it fails on a missing action class immediately and costs one line in §9; (b) the
worktree discipline of KI-70 applied to the tracked files, so a slice's route lines and its controllers
move together; (c) a rule naming `routes/web.php` as not-committable-whole while any import in it resolves
to an untracked file, with the audit above as its check. Closure is whichever lands, plus the §9 or
`GOVERNANCE.md` row that cites it.

**What closure would not cover.** It does not land the seven controllers and does not retire KI-71, the
condition underneath: whole slices existing only in the working tree. Adding `route:list` to the sequence
makes the failure visible sooner; it does not make a partial commit safe. This slice did not fix any of
it: no route-only patch was built. This slice's three routes can land only on HEAD's version of the file,
and the hub they belong to needs six more of D17's routes plus its `databaseIndex` methods on three tracked
catalogue controllers and six uncommitted page components, so the patch widened past the slice before it
was written. That widening is this entry's finding, not its fix.

