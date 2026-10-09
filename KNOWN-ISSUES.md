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
claim is left standing rather than rewritten. This file now carries KI-57 through **KI-64**. KI-61
(D7's red landing, filed with the wizard's rerun fix) was appended without this line being updated.
KI-62 and KI-63 were filed by the D8 hand-off, KI-62 applying its fix in the working tree; KI-64 was
filed by the D10 hand-off, which found it by trying to use the import it needed. All are OPEN, and each
names its own closure conditions. `master` remains unpushed (`O-1`), so none can close yet.

**Status correction (2026-10-07, D14a hand-off):** the composition above is superseded again, and the dated
claim is left standing rather than rewritten. This file now carries KI-57 through **KI-67**. KI-65 and KI-66
were filed by the D9 hand-off; KI-67 (the withdrawn `race_instances` pin, which stops any refresh of the
career catalogue) by this one. `KI-59` carries `##` rather than `###`, which is why a heading count reads
ten while the numbering reads sixty-seven. Status is per entry in its own heading: KI-62, KI-65 and KI-66 are
FIXED IN TREE, NOT CLOSED, and the rest are OPEN.

**Status correction (2026-10-08, documentation-sync pass):** the composition above is superseded again, and
the dated claim is left standing rather than rewritten. This file now carries KI-57 through **KI-74**.
Entries added since the D14a read: KI-68 (D12's inline validation, OPEN — the tree still proves it: see the
entry's own note below), KI-69 (the fixture-data contract), KI-70 (four shared files, process hazard) and
KI-71 (career files only in the working tree) filed by the D13 session, KI-72 (the tag filter) and a second
KI-70 (E1's 44px sweep, **CLOSED 2026-10-07**) filed on the D16 follow-up, a second KI-71 (the Grand
Concert `grand_concert` key mismatch, OPEN) filed by the E5 hand-off, and KI-73 and KI-74 (a leaked
`:8127` server and a contended-worktree gate) filed 2026-10-08. Per earlier entries in this file, timing
entries with the same number (two KI-70, two KI-71) are reported rather than renumbered, per the register's
convention. `master` remains unpushed (`O-1`), so closure of any FIXED IN TREE entry stays held.

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

### KI-58 `tools/gate.py`'s hex allowlist lost its document leg silently when the design master was folded, and the guard for that condition cannot fire - FILED 2026-10-03 (Phase A closure pass, from the priors pass finding), CLOSED 2026-10-09 at `7d8b62a`, note below

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

*Closed 2026-10-09, on the owner's instruction, by `7d8b62a` (2026-10-08, "read the design gate token map
from the shipped theme"), which is fix option 1 above implemented as written: `load_token_hexes()` now
reads `resources/css/app.css` and both theme blocks, so the allowlist cannot go stale against the theme and
the document leg it lost is gone. Proven on this tree: the entry's three reproducer hexes — `#B45309`,
`#0667B0` and `#6E6459`, each a current `app.css` token value — are legal now, where this entry filed them
as failing. The text above is preserved as written. **What closure does not cover:** the gate still exits
1, now for the mirror reason, and that is a separate defect filed as KI-83 rather than a continuation of
this one. The 40-value threshold the entry's second paragraph says cannot fire is still unable to fire, for
a better reason than before — the shipped theme yields 92 hexes — so a future theme with fewer tokens would
still pass the guard silently.*

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

### KI-64 The CSV import's Confirm step always fails validation in a browser, because `confirmImport()` posts the run's columns and never the file body - FILED 2026-10-07 (D10 hand-off), OPEN

**Symptom, observed.** Driving `/training-runs/import` through a browser — preview a twelve-row CSV,
then press the confirm button — leaves the page on `/training-runs/import` with no run created and no
visible error, and the confirm button holding focus. The URL never changes.

**Cause, measured.** The two halves of the write disagree about the payload.

`resources/js/pages/Runs/Import.vue:112`:

```js
function confirmImport(): void {
    router.post('/training-runs/import', props.preview?.run ?? {});
}
```

`props.preview.run` is composed by `TrainingRunController::importPreview()` from
`$request->only(['umamusume_id', 'scenario', 'status', 'notes'])` — the run's own columns. The CSV
body and the parsed rows are sibling props (`csv`, `preview.turns`), and neither is sent.

`app/Http/Requests/ImportHistoricalRunRequest::rules()` requires both:

```php
$rules['csv'] = ['required', 'string'];
$rules['turns'] = ['required', 'array', 'min:1', 'max:'.self::MAX_ROWS];
```

So the POST is refused at the boundary, the refusal redirects back to the same URL, and because the
confirm form renders no error block of its own the Trainer sees nothing happen.

**Why no test caught it, and why the register is not the cover.** `tests/browser/run-import.spec.ts` says
so in its own header: "Nothing here completes a commit: the confirm step is reached and read, never posted".
That sentence is the spec *documenting* the gap, not covering it: a two-step flow whose second step is never
posted is a spec hiding a defect rather than testing a flow, and it has been green while import could not
complete. The Pest tests drive `importStore` directly with a correct payload, so they never exercise the
component. The browser path is the only one that is broken and the only one with no coverage, and the
failure is **silent at both ends**: the refusal redirects back to the same URL, and the confirm form renders
no error block, so neither the Trainer nor a test that only reads the page sees a validation message.

**Impact.** Import is one of the two ways a run enters this tool, and its second step cannot complete
in the browser at all. `PRD` FR-C-3 and `SCR-RUN-004/005` both describe a working two-step import, and
`SCREEN_SPEC.md` records the screen as implemented.

**Closure.** This entry is open pending:

1. A fix that sends the body with the run: `router.post('/training-runs/import', { ...props.preview.run,
   csv: props.csv, turns: props.preview.turns })`, with `csv` declared as a page prop if it is not
   already. The Pest half already passes, so the fix is component-side only.
2. A browser case that actually posts the confirm step and asserts the run exists, which is the case
   `run-import.spec.ts` deliberately does not have. Without it the fix is unproven.
3. A decision on what the confirm form shows when the write is refused. Today it shows nothing, which
   is the second half of this defect: a refusal that renders no message is indistinguishable from a
   button that does nothing.

The D10 slice did not fix it. Its browser spec needed a run with turns logged, the import would have
been one write instead of eleven, and the defect was found by trying to use it; the spec logs its
turns through the run screen's own form instead, and this entry records what was left.

**Escalated, 2026-10-07.** Two rulings are owed and neither is D10's to make. The component fix is
Laravel Dev's, in `Import.vue`, and it is a small one. The register ruling is QA / Reviewer's: whether a
browser spec that reaches a write and never posts it may continue to be counted as coverage of that flow.
Today `SCR-RUN-004/005` is recorded as implemented on the strength of a spec that does not exercise the
step this defect lives in, and that is the part a reader should not infer.

### KI-65 The D5/D6 wizard halves landed red at `0006b17` and carried a private copy of the parent-name resolution - FILED 2026-10-07 (D9 hand-off, from the plan's §4.1 queue), FIXED IN TREE, NOT CLOSED

**Symptom, as recorded.** `frontend-development-plan.md` §4.1 item 1 and the §15 changelog row for
2026-10-06 record that the step-4 and step-5 wizard halves landed at `0006b17` with three failing tests
and three PHPStan errors, no browser spec, and no `SCR-CAR-*` rows, and that no `KNOWN-ISSUES.md` entry
was filed for it at the time. This entry is that entry.

**Cause, measured today.** The item records the cause as the step-4 draft write never producing the
parent identity the run path holds: a draft has no `training_runs` row, so `inheritance_parent_a_id` /
`_b_id` cannot be written, and the wizard has to name each parent through the draft's `legacy_parents`
library picks. Measured on this tree, the landed fix is exactly that reuse: `git diff 0006b17 HEAD --
app/Http/Controllers/Career/LegacySelectController.php` shows the controller's own private
`parentNames()` (22 lines, a second implementation of the same query) deleted and the call replaced by
`AncestryGraph::parentNames(SetupDraft::legacyParents())`, the shared owner `LegacyController` already
uses for the builder and the compare surface.

**Closure, on this tree.** Measured today, against the current code rather than by re-running the bad
commit:

1. `vendor/bin/pest tests/Feature/CareerLegacyDeckStepsTest.php` - **12 passed (248 assertions)**,
   including `it leaves the run-scoped ancestry and deck writes exactly as they were` and
   `it shapes the six nodes the same way for the draft and for a run`.
2. `tests/browser/career-legacy-deck-steps.spec.ts` exists and is **9 passed** on the scratch harness
   (port 8137, `PLAYWRIGHT_BASE_URL`), which is the browser gate whose absence the item names.
3. `SCREEN_SPEC.md` §2 holds both rows: `SCR-CAR-008` (Legacy Select, wizard step 4) and `SCR-CAR-009`
   (Support Deck Select, wizard step 5), each `Implemented 2026-10-06`.
4. PHPStan level 6 reads `[OK] No errors` on this tree (KI-65's second half).

**What closure does not cover.** The three failing tests and three PHPStan errors were recorded from the
plan's status read, not reproduced at `0006b17`: running that commit's suite would need a checkout with
its own `vendor/`, and the fix has since replaced the code they pointed at. The entry closes the process
gap the plan names (a red landing with no register entry), not a live defect: no failing test or
analysis error attributable to D5/D6 remains on this tree as of 2026-10-07.

### KI-66 The wizard's deck step bound `:is-friend` against a snake_case prop, so the "Friend slot" chip never rendered on step 5 - FILED 2026-10-07 (D9 hand-off, browser pass), FIXED IN TREE, NOT CLOSED

**Symptom, observed.** `tests/browser/career-legacy-deck-steps.spec.ts:174` failed: `#deck-slot-6`
carried `Slot 6 · Friends` and no `Friend slot` chip, while the run-scoped deck builder rendered the chip
correctly. The DOM showed the binding falling through: `<li ... is-friend="true" class="...">` - the
attribute present as a plain HTML attribute, which is what Vue does with an attribute that matches no
declared prop.

**Cause, measured.** `SupportSlot.vue:48` declares the prop as `is_friend` (snake_case, matching the page
payload key the server sends). `Career/DeckSelect.vue:234` bound it as `:is-friend="slot.is_friend"`.
Vue camelizes a kebab-case attribute to `isFriend`, which does not match `is_friend`, so the declared prop
stayed `undefined` and `v-if="is_friend"` never rendered the chip. The other caller,
`pages/Support/Builder.vue:270`, spreads `v-bind="slot"` and therefore passes the snake_case key intact -
which is why the same component rendered the chip on the run-scoped builder and not on the wizard step.

**Fix.** One line, in the caller: `:is_friend="slot.is_friend"`. The prop name was deliberately not
renamed to `isFriend`: `v-bind="slot"` on the other caller passes the payload key, so a rename would have
required touching the server-side payload shape to keep that caller working, for a name the component
shares with its own payload contract.

**Verification.** `career-legacy-deck-steps.spec.ts` 9 passed after a rebuild (`public/build` serves the
component, so a source edit alone does not reach the browser). `support-deck.spec.ts:67` already asserted
the same chip on the run-scoped builder and was green before and after, which is what made the wizard the
odd screen rather than the component the odd component.

### KI-67 The race catalogue's pinned GameTora document has been withdrawn, so `uma:fetch` can no longer refresh the career calendar at all - FILED 2026-10-07 (D14a hand-off), OPEN

**Status: OPEN.** `config/uma.php:141` pins `gametora-race-catalog` at
`https://gametora.com/data/umamusume/race_instances.294424fc.json`. That path now answers **HTTP 404 with a
`text/html` body**. The publisher's live manifest names `race_instances` at `8993fc1b`. The source declares
no `manifest` block, so `SourceFetcher::resolveUrl()` takes the `! is_array($manifest)` branch at `:151` and
requests the pin directly: every `php artisan uma:fetch` of this source fails from here on, and nothing
about the failure is visible in the app.

This is the case `docs/research-scratch/AUDIT-AND-VERIFICATION.md` predicted in the `ADR-0011` scope note:
"`race_instances` is pinned at `294424fc`, which matches the manifest right now and will therefore go stale
silently at its next republish, the same way the characters pin already did." It is a sharper form of KI-24:
there the withdrawn document kept answering `200` with stale content, here it answers `404`.

**Proving commands** (both read 2026-10-07, no proxy):

```bash
curl -sI -H 'User-Agent: Mozilla/5.0' -H 'Accept: application/json' \
  https://gametora.com/data/umamusume/race_instances.294424fc.json   # HTTP/1.1 404 Not Found
curl -s -H 'User-Agent: Mozilla/5.0' -H 'Accept: application/json' \
  https://gametora.com/data/manifests/umamusume.json | grep race_instances   # "race_instances":"8993fc1b"
```

**What still works, and why that makes this easy to miss.** The offline path is unaffected:
`'seed_file' => 'race_instances.json'` (`config/uma.php:146`, landed `8b17703`) fills 410 `race_catalog_slots`
rows from the committed body, and `database/database.sqlite` holds those 410 rows with `fetched_at`
2026-10-01. `migrate:fresh --seed` on a fresh worktree therefore looks healthy and the calendar renders; the
only thing that breaks is a refresh nobody can currently complete. That is the state the scenario-scoping gap
is waiting behind: `SCREEN_SPEC.md` records that 406 of 410 rows carry no `scenario_key`, and a fetch that
cannot reach its document cannot add it.

**Second question for the same owner, deliberately not claimed here.** The same manifest read gives
`characters` = `e0aa6d43` and `character-cards` = `bc892aba`, neither of them the `e9e9ee6d` named in the
`gametora-characters` entry's `seed_file`. Whether that is the same drift, a different document, or the two
grains of one document is the Data Engineer's reading under escalation 5, and this entry does not assert it.

**Closure.** The fix `ADR-0011` already approved for `gametora-skills`: give the entry a `manifest` block
(`url`, `base`, `key`) so the hash resolves per run, with the pinned `url` staying as the documented fallback.
Closure does not cover: the robots and rate-limit note a source change owes `AGENTS.md` §11, the
`characters`/`character-cards` question above, the fact that a resolved hash changes which rows land (so the
410-row figure quoted in `SCREEN_SPEC.md` and in `RaceCalendar.vue`'s comment moves with it), or any of the
scenario-scoping work the refresh unblocks.

### KI-68 D12's Inheritance write validates inline in the controller, which is a Floor breach and leaves `StoreTurnEventRequest` as a second, disagreeing owner of the same boundary - FILED 2026-10-07 (D14a session, on the owner's D12 close-out), OPEN

**Status: OPEN.** Found while answering the D12 close-out review, not by this slice's own change.

**The defect.** `app/Http/Controllers/Career/InheritanceEventController.php:83` runs

```php
$validated = request()->validate([ 'turn' => [...], 'source_name' => [...], ... ]);
```

`AGENTS.md` §5's Floor lists "no inline `$request->validate()`" beside "no business logic in a
controller", and §7 requires a Form Request for every write. This is the only occurrence of the pattern
in `app/Http/Controllers/Career/`: `StoreTurnEventRequest`, `StoreRaceEntryRequest`,
`StoreBuildTargetRequest`, `StoreActionBatchRequest` and the rest of the career writes all use a
Form Request, so the breach is local and the house pattern is intact to copy.

**Proving command.**

```bash
grep -rn 'request()->validate(\|\$request->validate(' app/Http/Controllers/Career/
# one hit: InheritanceEventController.php:83
```

**The second half, which is why this is more than style.** `StoreTurnEventRequest:39` restricts
`event_type` to `self::SOURCES` (the four choice cases: Character, Support Card, Group, Scenario) and
its message at `:56` names exactly those four. D12 writes a `TurnEventType::Inheritance` row from the
controller instead, so the two paths now disagree about what a turn event may be: the Form Request
would refuse the value the controller writes. `SCREEN_SPEC.md:1905` records D11's ruling that the enum
must **not** be widened to satisfy a displayed source ("the write rejects it … recorded here rather than
resolved by widening the enum"), and no owner ruling names the `Inheritance` case: the only record is
`SCREEN_SPEC.md:2357`'s change-log line, which states the fact rather than authorizing it.
`TurnEventType::Failure` is the in-repo precedent for a non-choice, machine-written event type
(`TrainingRunController.php:1750`, documented at `SCREEN_SPEC.md:350`), so the case itself is
defensible; what is missing is the ruling, and an agent reading only the enum will take it as
precedent for the next case. `turn_events.event_type` is a plain `string(30)`
(`database/migrations/*create_turn_events*`), so no schema change is needed either way.

**Closure.** Move the write to a Form Request (its own, or an inheritance branch of
`StoreTurnEventRequest` decided by whoever owns that request), and have the owner either rule the
`Inheritance` case in or record why `Failure`'s precedent covers it. Closure does not cover: whether
D12's seven feature cases still pass once the boundary moves (`tests/Feature/CareerInheritanceEventTest.php`
is untracked and was written against the inline path), the `StoreTurnEventRequest::SOURCES` vocabulary
itself, or any of the D11 screen's behaviour.

**Attribution note.** D12 was built by another session in this shared worktree; this entry is filed by
the D14a session because the close-out review asked for the enum's authorization and the search turned
up the breach. The D12 author owns the fix.

### KI-69 The browser suite has no fixture-data contract: five cases assert an empty-state surface, seven need a populated Veteran library and one reads the artwork mirror off the disk - FILED 2026-10-07 (D13 session, on the owner's D11 close-out review), OPEN

**Status: OPEN.** Filed while answering the D11 review's question "is the suite flaky, or did something
regress after those closures". The answer is neither: every one of the fourteen failures is
**deterministic**, and each is a harness precondition rather than a product defect. Nothing here is
flaky across runs on a fixed harness.

**The shared cause.** `playwright.config.ts:7` hardcodes `const port = 8127`, and `webServer.command`
is `php artisan serve --port=8127` with `reuseExistingServer: true`. That server takes its database from
`.env`, which is `DB_DATABASE=database/database.sqlite`: the **shared dev file**. Plan §4.1 item 7 states
the opposite requirement, that "the browser suite still runs against a scratch database on port 8137 with
`PLAYWRIGHT_BASE_URL`: it asserts an empty runs and veterans table". The config and the plan disagree, and
an unqualified `npm run test:browser` follows the config. The D11 full-suite reading was taken that way:
its own log line names `http://127.0.0.1:8127/training-runs/150`, and `database/database.sqlite` holds
five Active runs (`id` 7, 52, 107, 136, 216) with `sqlite_sequence` at 216.

**Class 1, empty-state assertions (5 of the 14).** Each of these depends on a surface that only renders
when the runs table holds no rows: `runs.spec.ts:41` and `career-legacy-deck-steps.spec.ts:268` assert
`getByText('No runs yet')`, `dashboard.spec.ts:65` asserts `getByText('No active career. Start a new
training run.')`, and `dashboard.spec.ts:163` asserts the `Start a new training run` link.
Proven both directions on a scratch DB at `.scratch-uma/d11-iso.sqlite` (`migrate --seed`, served on the
session's own port 8141 with `DB_DATABASE` and `PLAYWRIGHT_BASE_URL` set):

```bash
# wiped scratch: PASS
PLAYWRIGHT_BASE_URL=http://127.0.0.1:8141 npx playwright test runs --grep "empty"                 # 2 passed
PLAYWRIGHT_BASE_URL=http://127.0.0.1:8141 npx playwright test dashboard --grep "empty career"     # 1 passed
PLAYWRIGHT_BASE_URL=http://127.0.0.1:8141 npx playwright test career-legacy-deck-steps            # 9 passed (2.8m)
# shared dev file, five runs: the same four cases FAIL with `getByText('No runs yet') … element(s) not found`
```

`dashboard.spec.ts:105` is the same class for a different reason: at `:120` it calls `boundingBox()` on
`getByRole('link', { name: 'Start a new training run' })`, and a dashboard that holds runs replaces that
empty-state call to action with career content, so the locator never resolves and the case times out at the
180s budget.

**This also voids §4.1 item 4's closure as a durable claim.** That closure records the empty-table defect
being cleared because "the scratch database holding a stale run from a crashed spec … was deleted from
that database". Deleting one row is a one-time manual reset, not a fix; the premise returns as soon as any
spec crashes or any other session writes. Item 4 is closed for that pass, not closed forward. The closure's
other fix is uncommitted too: `git status --porcelain tests/browser/career-legacy-deck-steps.spec.ts` reads
`M`, and the diff is the scoped `The seven support types…` assertion replacing the 54-element
`getByText('Wit')`. So `HEAD` at `55be0cd` still holds the selector the closure says was fixed
(`git show HEAD:tests/browser/career-legacy-deck-steps.spec.ts | grep -c "getByText('Wit')"` reads 1), and a
fresh worktree checking out `HEAD` reproduces that failure as well.

**Class 2, the artwork mirror (1 of the 14).** `support-cards.spec.ts:171` asserts
`#support-card-results img` has count 0, i.e. that the mirror holds no file. `storage/app/private/artwork`
on this tree holds **665 PNG files**, so the precondition is false on disk and the case fails on a wiped
database too (proven above: `1 failed` with the scratch runs table empty). This is not a new ruling to
make. Plan §4.1 item 5 already ruled exactly this class for `catalog-detail.spec.ts` — "was asserting the
disk rather than the screen; it now asserts the shape of whichever state the tree is in, the same ruling
`support-deck.spec.ts` recorded for the picker's thumbnails" — and `support-cards.spec.ts` is the third
instance that the per-file fix missed.

**Class 3, the fixture needs data no code path creates (7 of the 14).**
`tests/browser/career-inheritance-event.spec.ts` is **untracked**, as is the whole of D12:
`git status --porcelain` reads `??` for `app/Http/Controllers/Career/InheritanceEventController.php`,
`resources/js/pages/Career/InheritanceEvent.vue`, `tests/Feature/CareerInheritanceEventTest.php` and the
spec (KI-68 records the same status for the feature test). Every case except `:234` calls
`openInheritanceEvent()`, which at `:80` does
`select('select[name="legacies.0.legacy_id"]', {label: 'Symboli Rudolf'})`. That select is seeded from
`LegacyController::roster()` (`:308`), which reads the **`veterans` table**. Two measured facts:

- `veterans` is empty on every database in reach: `database/database.sqlite` 0 rows,
  `.scratch-uma/browser.sqlite` 0, and a fresh `migrate --seed` 0. No seeder touches it
  (`grep -ril veteran database/seeders/` returns nothing) and `app/Actions/RecordVeteran.php` is referenced
  only in docblocks, never called. So the roster picker renders no options, and Playwright waits out its
  180s budget on `did not find some options`. Re-running the whole spec on the fresh seeded scratch
  database reads **7 failed, 1 passed (22.9m)**, and all seven stack traces end at
  `career-inheritance-event.spec.ts:80:63` — the same seven names and the same failure point the D11
  full-suite run recorded. The one green case is `:234`, the only case that never calls the fixture.
- Inserting four Completed runs with matching `veterans` rows moves the failure off `:80` and onto `:81`,
  `input[name="legacies.0.rank"]`, which the shipped `Builder.vue` **never renders**: its inputs carry
  `v-model` and `:id="rank-${parent.slot}"` with no `name` attribute at all. The only `name` the page
  emits is `legacies.${index}.legacy_id`, plus `affinity`. `ancestors.N` and `sparks.N.kind` are missing
  the same way. The spec was written against a form contract the component does not have.

**So the plan holds two contradictory requirements at once.** Item 7 says the suite needs an *empty*
veterans table; this fixture needs veterans with specific names. No single scratch database satisfies
both, which is the actual defect: there is no fixture-data contract for `veterans`, and no committed
path that populates it.

**Nothing here is flaky.** Each class above was measured twice on the same harness and landed in the same
place both times, on the same database state.

**Class 4, the trainee step loses its flash (1 of the 14).** `career-trainee-select.spec.ts:88` **also
reproduces on a wiped scratch database**, so it is not the empty-table class. It fails at `:104` on
`getByText('Trainee set: Admire Vega.')`. The three candidate causes were tested in order and two are
cleared:

1. *Redirect shape or the shared `flash` prop.* **Cleared.** `TraineeSelectController::store()` does
   `redirect()->route('career.trainee')->with('status', "Trainee set: {$trainee->name}.")` and
   `HandleInertiaRequests:49` shares `'flash' => ['status' => fn () => $request->session()->get('status')]`.
   A feature-level probe on the same middleware chain passed both
   `assertSessionHas('status', 'Trainee set: Admire Vega.')` and
   `assertInertia(...->where('flash.status', 'Trainee set: Admire Vega.'))`. The server side is correct.
2. *`preserveState` or `only` skipping shared props.* **Cleared.** `choose()` at `TraineeSelect.vue:172`
   passes only `preserveScroll` plus `onSuccess`/`onFinish`; neither `preserveState` nor `only` appears in the
   write. The filter form's `router.get(..., {preserveState: true})` at `:144` is not on this path: `</form>`
   closes at `:292`, well before the card list at `:321` and the button at `:375`, so Enter on the button
   cannot submit that form. Measured directly: a mouse click and a keyboard Enter fail identically, so the
   keyboard path is not the defect.
3. *The layout remount eats the one-shot flash.* **Partly supported, and the mechanism is now narrower.**
   A request trace on the live page shows exactly two requests after the write:
   `PUT /career/setup/trainee [X-Inertia=true]` answered **303**, then
   `GET /career/setup/trainee [X-Inertia=-]`. The follow-up carries no `X-Inertia` header at all, i.e. it is
   a **top-level document navigation**, not the Inertia XHR visit the client normally makes. The page that
   renders from it has no flash element (`main p.mb-4` contents `[]`), while the draft state it does render
   (`Stored choice: Admire Vega`) proves the write landed.

**Still open, stated as the live hypothesis rather than as unknown:** why does this write's 303 resolve to a
document reload where the wizard's other writes do not? `LegacySelectController:124` and
`DeckSelectController:117` flash the same way, on the same `SetupLayout`/`AppLayout` pair, and their text
renders (`career-legacy-deck-steps.spec.ts:268` asserts `Legacy recorded.` and `Deck saved.` and is 9 passed
in isolation). The one structural difference measured so far is that `store()` redirects to the URL the SPA is
already on, so the reload re-enters the same route; the other two are being tested as the discriminator. Who
ever picks this up should start there, and the reproduction is four lines of scratch spec, not a theory.

**Closure.** Name the harness in `playwright.config.ts` instead of leaving it in prose: point the suite at
a scratch database it creates and wipes itself, so class 1 cannot recur when a peer writes to the dev
file. Apply item 5's existing disk-state ruling to `support-cards.spec.ts:171` (class 2). For class 3,
the D12 author either seeds the Veterans the fixture picks (a committed seeder or an in-spec data setup)
and corrects the selectors to the `:id` contract `Builder.vue` actually renders, or the spec stops driving
the Builder and reaches `legacy_selection` the way the run screen does. Class 4 needs its own investigation
from whoever owns the wizard's trainee step.

Closure does not cover: whether `RecordVeteran` should gain a caller (that is D16's open half, and a product
decision rather than a test fix); whether class 4 is a page-side or controller-side fix; and any product
behaviour of the Legacy Lab, the runs list or the dashboard, none of which this pass found broken.

**Attribution note.** None of these fourteen is a D11 or D13 defect. Filed by the D13 session because the
owner's D11 close-out review asked whether the queue was flakiness or regression; the D12 author owns
class 3, and the harness owner owns the config change.

**Dated re-read 2026-10-08 (documentation-sync pass): one of the "closure does not cover" clauses is now
false, and the entry stays OPEN for its reported classes.** The clause "whether `RecordVeteran` should
gain a caller (that is D16's open half...)" is superseded: D16 landed `SaveVeteranController` as
`RecordVeteran`'s first caller, wired at `routes/web.php:237-240` (the `runs.veteran` / `runs.veteran.store`
pair), so the write path the class-3 fixture needs now exists. That resolves the *populate-the-library*
half; it does **not** resolve the entry's reported defects, each still measured on this tree:
`playwright.config.ts:7` still hardcodes `port = 8127` with no `DB_DATABASE`, so an unqualified
`npm run test:browser` still points at the shared dev file (class 1 and the harness half, open);
`support-cards.spec.ts:171` still asserts the artwork-mirror-empty condition (class 2, open);
`resources/js/pages/Legacy/Builder.vue` still emits no `name="legacies.0.rank"` (or ancestors/sparks)
attribute while `tests/browser/career-inheritance-event.spec.ts:72` still drives one, so the class-3
selector contract mismatch stands (measured by grep on 2026-10-08); and class 4's investigation is
unattributed. The entry's status line stays OPEN; this note records that one of its premises changed, not
that the defect it names closed._

### KI-70 Four shared files are being edited by concurrent sessions in one working tree, and one committed screen's route exists only in the working copy - FILED 2026-10-07 (D13 session, on the owner's instruction), OPEN

**Status: OPEN.** This is a process hazard, not a code defect. It is filed because the next editor can
silently destroy another session's in-flight work, and one instance has already happened: the owner reports
that a concurrent session edited `app/Http/Controllers/Career/CockpitController.php` underneath the D11 pass
and reverted its event note. The re-application survived; this pass did not observe the revert itself and
reports the measured state below rather than the incident.

**Measured state, 2026-10-07.** `git diff --stat` against the working tree:

| File | Uncommitted churn |
| --- | --- |
| `SCREEN_SPEC.md` | 426 lines |
| `app/Http/Controllers/Career/CockpitController.php` | 222 lines |
| `routes/web.php` | 44 insertions unstaged, plus 1 deletion staged |

The owner's review cited 228 changed lines for `SCREEN_SPEC.md`; it measures 426 now, so the file grew while
this session worked. Treat any number quoted for these four paths as stale until re-read.

**Two concrete instances, both at 07:19:43 on 2026-10-07.** A concurrent session wrote a batch of files in
one second, and two of them broke another session's work:

- `config/scenarios.php` was caught mid-write by a `php artisan test` run, which aborted with
  `strict_types declaration must be the very first statement in the script`. The file lints clean and returns
  its array now, so the reading was torn, not wrong — but 15 of that run's 15 failures were
  `LegacySearchRequest.php:44` `array_keys(): Argument #1 ($array) must be of type array, null given`, i.e.
  five unrelated suites reported a configuration that no longer existed. A gate result taken during that
  window is not evidence about the tree; re-run before believing a count.
- `resources/js/pages/Preferences/Edit.vue:81` — **CLOSED 2026-10-07, no KI owed.** The stray brace stood in the working tree and failed `npm run build` for every session in this tree, because Vite builds the whole page glob: a screen none of them is touching makes the bundle for all of them, and that in turn blocked the browser gate for any slice, since `public/build/manifest.json` could not be regenerated to include a new page. It is a D18 file, not a peer's, and D18 closed it. **Blocked, then unblocked; final state verified by rebuild + browser spec.** Four reports gave four incompatible stories for one character; the authorship of the intermediate version is not knowable from the current tree and is not recorded. The plain facts that matter: the file now compiles, the build succeeds (955 modules), and the tablist renders in `scenario-panel.spec.ts`'s output. The entry is closed rather than re-litigated.

The second is the expensive shape of the hazard: it is not a lost edit but a shared gate held hostage by one
unsaved file. Closure does not extend to fixing another session's in-flight file; the owner's standing rule
on this is that a peer's mid-edit work is left alone and named, as the CockpitController analysis error was
in the D13 hand-off.

**The sharpest instance is `routes/web.php`.** The `runs.races.decision` route — D10's Race Decision screen,
recorded in plan §8 as `Landed` at `55be0cd` — is present in the **working copy only**:

```text
55be0cd: use App\Http\Controllers\Career\RaceDecisionController;   (import, no route: 1 occurrence)
index  : (the import deleted by a staged change from another session: 0 occurrences)
worktree: use …; + Route::get('/training-runs/{run}/races', [RaceDecisionController::class, 'show'])  (2)
```

So `git show 55be0cd:routes/web.php` registers no route for a screen the plan calls landed, and a working-tree
reset of this file takes the route with it. The staged deletion of the import is a second session's edit
pointing the other way; neither is wrong on its own, and the pair is why this needs a rule rather than a fix.

**Instruction to the next editor of these four paths.** Re-read the file immediately before writing, and
re-read it again after any `git add`, `git stash` or commit you did not make: `git diff --stat` and
`git diff --cached --name-only` are two commands and they are the whole check. Commit with an explicit
pathspec (`git commit -m … -- <path>`) so a peer's staged entry stays staged and untouched — verified working
on this tree at `b5d69be`, where the two index entries survived the commit. Never `git add -A`, never
`git stash pop`, and never treat a clean `git status` as evidence that a shared path is yours to rewrite.

**Proving command.**

```bash
git diff --stat -- SCREEN_SPEC.md app/Http/Controllers/Career/CockpitController.php routes/web.php
git diff --cached --name-only
php -r '$h=shell_exec("git show 55be0cd:routes/web.php");echo substr_count($h,"RaceDecision");'   # 1, no route
```

**Closure.** No commit is possible here without an owner decision on sequencing, because four sessions share
one working tree and `master` is the branch of record with no CI to catch a partial landing. Closure needs
either per-slice worktrees (the `.worktrees/` sibling directory this project already uses) or a written rule
naming who owns each of these four paths at a given time. Closure does not cover: the correctness of any
concurrent session's edits, the staged entries themselves, or whether `55be0cd` should be amended (it should
not; a follow-up commit is the only safe shape here).

### KI-71 Twenty-seven career-phase files, including whole slices the plan marks Landed, exist only in the working tree - FILED 2026-10-07 (D13 session, on the owner's instruction), OPEN

**Status: OPEN.** A `git checkout`, a `git clean`, or any session resetting the working tree deletes these
slices outright: there is no commit to restore them from, and no CI, remote branch or bundle holds a copy.
Filed because the KI-69 attribution depends on D12's untracked state, and because a reader of plan §8
currently cannot tell which "Landed" rows are in `master` and which are in a directory.

**Census.** `git status --porcelain` names 49 untracked paths on this tree, **27** of them career-phase code
(filtered on `Career`/`career` plus `RunRaceStripTest.php`; this session's own
`docs/research-scratch/D13-SKILLS-PLANNER-2026-10-07.md` is untracked too and is not in the 27). The
per-slice shape:

| Slice | Plan §8 status | Untracked files |
| --- | --- | --- |
| D11 Event Decision | Landed | `Career/EventDecisionController.php`, `Career/EventDecision.vue`, `career/EventCard.vue`, `CareerEventDecisionTest.php`, `career-event-decision.spec.ts` |
| D12 Inheritance Event | Landed, "All gates passed" | `Career/InheritanceEventController.php`, `Career/InheritanceEvent.vue`, `CareerInheritanceEventTest.php`, `career-inheritance-event.spec.ts` |
| D13 Skills Planner | built this session | `Career/SkillsPlannerController.php`, `Career/SkillsPlanner.vue`, `career/SkillPlanRow.vue`, `CareerSkillsPlannerTest.php`, `career-skills-planner.spec.ts` |
| D14a Race Strip | Landed | `career/RunRaceStrip.vue`, `RunRaceStripTest.php`, `career-race-strip.spec.ts` |
| D10 Race Decision | Landed at `55be0cd` | its browser spec `career-race-decision.spec.ts` is untracked, though its controller is committed |
| not numbered in §8 | — | `Career/ResultController.php`, `Career/Result.vue`, `CareerResultTest.php`, `Career/TimelineController.php`, `Career/Timeline.vue`, `career/CareerTimeline.vue`, `CareerTimelineTest.php`, `career-result.spec.ts`, `career-timeline.spec.ts` |

The registry rows are untracked too: `SCR-CAR-014`, `SCR-CAR-015` and `SCR-CAR-016` are in the working
`SCREEN_SPEC.md` and **absent from `55be0cd`** — `git show 55be0cd:SCREEN_SPEC.md` contains none of the three.
`routes/web.php` shows the same split in the opposite direction, per KI-70.

**What this does to the plan's own claims.** For these slices "Landed, all gates passed" is a statement about
an uncommitted directory. It also dissolves one KI-69 reading without further argument: §8 records D12's
browser spec green while that spec is unrunnable on the tree as seeded, and there is no commit at which it
could have been either.

**Closure.** Commit each slice as its own change with its tests, in slice order, with the owner fixing the
sequence because four sessions share the tree (KI-70). The verification cost is small and the loss cost is
total: a green suite does not protect a file `git` has never seen. Closure does not cover *which* session
commits which slice; whether D12's spec should land before its KI-68 boundary fix and its KI-69 class-3
fixture defect do (recommended: not); or the `veterans` fixture ruling now open as plan §4.1 item 10.

**Attribution note.** D13's five files are this session's and are named here rather than committed: the owner
authorized exactly one commit in this follow-up, `b5d69be` for the D5/D6 selector fix, and a slice commit is
not implied by a file edit.

### KI-72 The library's tag filter is case-sensitive, so a hand-typed tag is unfindable by its capitalised suggestion - FILED 2026-10-07 (D16 follow-up, on the owner's ruling), OPEN

**Status: OPEN.** `StoreVeteranRequest::prepareForValidation()` de-duplicates tags by `mb_strtolower`
(`app/Http/Requests/StoreVeteranRequest.php:131`) and stores the first spelling exactly as typed, so a
Trainer who types `speed` keeps `speed`. The library's tag filter answers the `Speed` suggestion with
`whereJsonContains('tags', 'Speed')` (`app/Actions/ListVeterans.php:61`), and SQLite compares JSON string
elements case-sensitively. The stored row and the chip then disagree silently: the Veteran exists, the
filter says it does not, and nothing errors. The suggestion chip is the library's discovery path, so the
miss lands on exactly the free-text half of the field the Save Veteran screen exists to offer.

**Proving file.** `ListVeterans.php:60-62` (one `whereJsonContains` per tag, case-sensitive) against
`StoreVeteranRequest.php:99-141` (case-insensitive de-duplication, verbatim storage). The D16 review
reproduced it end to end: a career filed with the tag `speed` returns no row for the filter value `Speed`
and returns the row for `speed`.

**Closure.** A normalized companion column (the stored tags keep the Trainer's spelling; the filter matches
a lowercase twin) is a schema change under §11's migration package, not a UI edit, which is why this is
filed rather than folded into D16. Closure does not cover the filter's displayed wording, nor the
`veterans` fixture ruling that KI-69 class 1 and plan §4.1 item 10 hold open, which any browser-level
regression test for this defect needs first.

---

## KI-70 — E1's 44px-floor sweep fails on a cockpit control, not the scenario panel

**Filed 2026-10-07, by the D18 close-out. CLOSED 2026-10-07 by the E1/E2 pass (closure below). Owner: the E1 session.**

`tests/browser/scenario-panel.spec.ts:153` ("keeps the 44px floor across the cockpit and reflows at 320
px") fails on **control 11 at 32px**:

```text
Error: control 11 is not sized to the 44px contract
Expected: >= 44
Received:    32
```

**Not D18's defect, and not the scenario panel's.** The test sweeps `main a, main button` across the
whole Unity Cup cockpit page, so its index counts every control on the page, not only the ones the
scenario panel draws. The error-context snapshot shows the failing page is the cockpit after a
run-delete, and control 11 is an element on the cockpit — D18's slice touches `Preferences/Edit.vue`,
`PreferenceController.php`, `AppLayout.vue` and the preferences specs, none of which is the cockpit.

The panel itself renders correctly in all seven passing cases, including the 320px reflow check (the
overflow assertion at line 175 passes — no sideways scroll). The 44px failure is a cockpit control
that predates E1 and sits outside every Phase D slice's scope.

**Proving file.** `test-results/scenario-panel-keeps-the-4-31c62-ckpit-and-reflows-at-320-px-chromium/error-context.md`,
page snapshot ref `f2e3`, showing the cockpit shell with control 11 measuring 32px.

**Owner.** The E1 session, when its panel row is next opened. The cockpit control is not in E1's
remit either, so the fix is either a `min-h-11` hoist onto the offending element (the same pattern the
`Go to the run record screen` link uses) or a narrowing of the sweep to the panel's own controls — the
E1 session chooses. Not filed against D18, and not closed by D18's close-out.

**Closed 2026-10-07 by the E1/E2 pass, first remedy of the two the entry names.** The offending control
is identified: `resources/js/pages/Career/Cockpit.vue:327`, the `Career Timeline` door the D14 pass added
as an inline link inside a `text-xs` sentence, carrying `font-medium text-ink-strong underline` and no
`min-h-11`, so it measured 32px. It was hoisted out of the sentence to the standalone
`mt-2 inline-flex min-h-11 items-center` pattern its sibling `Go to the run record screen` link already
used, which is the option the entry prescribes; the sweep was not narrowed.

**Proving command.** A Playwright probe over `main a, main button` on the Unity Cup cockpit reported
`total controls: 13 | under 44px: 0`, and the link as `{"h":44,"cls":"mt-2 inline-flex min-h-11 items-center
text-xs font-medium text-ink-strong under…"}`. `scenario-panel.spec.ts` then passed its 44px case: 8 passed
alone, 18 passed with `ura-panel.spec.ts` together.

**What closure does not cover.** The fix ships in this slice's commit, so a checkout before it still fails
the sweep. It does not cover the second, unrelated 44px finding this pass made in E2's own run: a control
measuring 0px inside a collapsed `<details>` on the URA cockpit, which is not a target-size violation (a
control that is not placed cannot be hit) and which `ura-panel.spec.ts` now handles by measuring visible
controls only, with a floor on the measured count so the skip cannot become a way to pass on nothing. It
also does not resolve the **numbering collision**: this KI-70 and the process hazard filed above it as
"Four shared files are being edited by concurrent sessions" share a number. The register says never
renumber an existing entry, and renumbering this one would break the inbound `KI-70` anchors in the plan
and in this file, so the collision is reported for the owner to settle rather than fixed by inference.

### KI-71 The Grand Concert final is tagged with a scenario key no run carries - FILED 2026-10-07 (E5 hand-off), FIXED IN TREE 2026-10-08, NOT CLOSED

**Status: FIXED IN TREE, NOT CLOSED.** Not caused by E5 and not fixed by it. It is filed here because E5 reads
the same career calendar D10 reads, and reading it whole is what made the mismatch visible. The fix below is
uncommitted, so a checkout of `HEAD` still carries the defect.

`app/Services/DataPipeline/Parsers/GametoraRaceCatalogParser.php` maps the export's `final_live` source key
to `grand_concert`. `config/scenarios.php` keys the same scenario `our_grand_concert`, and that is the key
`ScenarioSelectController` stores on the run. `RaceCatalogSlot::scopeForScenario($run->scenarioKey())`
matches `scenario_key IS NULL` plus the run's own key, so the row tagged `grand_concert` is matched by no
run at all.

**Effect.** The fourth Global scenario's own final is the one `is_mandatory` row tagged `grand_concert`
(`URA Finals Final (Grand Live)`). It therefore never appears on a Grand Concert run's calendar, on
`SCR-CAR-013` (Race Decision), on `SCR-CAR-023` (the planner this hand-off landed) or in the Cockpit's
mandatory list. The other three scenarios are unaffected: their finals are tagged with the key their config
entry carries. The planner's mandatory group and deadline region are consequently short one obligation for
that scenario, and the planner says nothing about it, because it reads the calendar and the calendar has no
such row.

**Proving commands** (both read 2026-10-07):

```bash
php -r '$d=new PDO("sqlite:database/database.sqlite"); foreach ($d->query("select scenario_key, title from race_catalog_slots where scenario_key is not null") as $r) echo $r["scenario_key"]." | ".$r["title"]."\n";'
# grand_concert | Twinkle Star Climax  (and unity_cup, ura_finale, trackblazer; no our_grand_concert row)
grep -n "final_live" app/Services/DataPipeline/Parsers/GametoraRaceCatalogParser.php
# 'final_live' => 'grand_concert',
```

**Closure owed.** Either the parser's map or the config key has to move, and which one is an owner call: the
config key is the one the wizard stores and every scenario-aware lookup reads, while the parser's value is
what the fetched source publishes. Either way the fix needs a reparse (`php artisan uma:reparse <source>`)
to retag the stored row, so it is filed rather than changed by inference.

**What closure would not cover.** `race_catalog_slots` holds no `our_grand_concert` row today, so any screen
that reads a scenario's own races stays one row short until that reparse runs. And the register's existing
numbering collision (two entries numbered KI-70, recorded in the entry above) is untouched by this one.

**Fixed in tree 2026-10-08 (Grand Concert Slice 2), parser side.** The two candidates were not equally
weighted once the const was read whole: `GLOBAL_FINALS_BY_SLOT`'s other three values are `ura_finale`,
`unity_cup` and `trackblazer`, and all three are *the app's* keys, identical to the keys `config/scenarios.php`
declares. The const's contract is therefore the app scenario key, `grand_concert` is the single value that
breaks it, and the config key is the one the wizard stores and every surface reads, so the parser moved:
`'final_live' => 'our_grand_concert'`.

**A reparse alone would not have retagged the row, and this is the part worth carrying forward.**
`StoreRaceCatalogSlots::find()` includes `scenario_key` in the row's identity (`:72`, the null-coalescing
grain the unique index is built on), so changing the emitted key makes the row *unmatchable* rather than
updatable. Running `php artisan uma:reparse gametora-race-catalog` accordingly reported `409 updated, 1
created` and left the old row in place beside the new one:

```text
409 | grand_concert     | URA Finals Final (Grand Live)   <- orphan, reachable by no run
411 | our_grand_concert | URA Finals Final (Grand Live)   <- the correct row
```

The orphan was removed on the same day: it was engine-owned (`is_manual = 0`), referenced by no
`race_entries` row (`count(*) where race_catalog_slot_id = 409` was 0), tagged with a key no config declares,
and reproducible from the snapshot. It was dumped to `.scratch-uma/ki71-orphan-row-409.json` before the
single-row delete, which reported `rows_deleted=1`.

**Proving commands** (2026-10-08, after the fix, the reparse and the cleanup):

```bash
grep -n "final_live" app/Services/DataPipeline/Parsers/GametoraRaceCatalogParser.php
# 'final_live' => 'our_grand_concert',
# scopeForScenario's own predicate, run against the dev file:
#   select ... where (scenario_key is null or scenario_key = 'our_grand_concert') and title like '%Grand Live%'
# 411 | URA Finals Final (Grand Live) | mand=1 | y4      -> grand_live_rows_reachable=1
# rows tagged grand_concert: 0 ; tagged keys are now exactly the four config declares, one each
```

**Regression test.** `tests/Feature/GametoraRaceCatalogParserTest.php` gains the invariant beside the
existing value pin: every scenario key the parser emits on a final slot must be a key
`config('scenarios.scenarios')` declares, asserted over all four slots. The value pin alone would have
passed a fifth scenario's wrong key; the invariant is what makes an undeclared tag a failure, which is the
property that made `grand_concert` a defect rather than a spelling.

**What the fix does not cover.** The identity semantics above are unchanged: a future rename of any
scenario key re-creates this exact orphan, and nothing in the pipeline removes the stale row, so the class
outlives this instance. Nothing here re-runs the parser against a fresh fetch either, so a later export that
renames the slot id would need the same two steps. And the numbering collisions recorded above are still
open.

### KI-73 A leaked `php artisan serve` holding :8127 takes the default browser entry point down for every session in the worktree - FILED 2026-10-08 (E6 follow-up 2, on the owner's instruction to diagnose rather than work around), OPEN

**Status: OPEN.** Not caused by E6 and not fixed by it. E6 found it while trying to make `npm run
test:browser` work with no override, and the reason it is filed instead of fixed is in "Closure owed".

`playwright.config.ts:7` pins one port (`const port = 8127`), `:36` starts the dev server on it, `:38`
sets `reuseExistingServer: true`, `:37` polls `url: baseURL` for readiness and `:39` allows 60s. The
combination means **one `php -S` on :8127 is the browser gate for the whole worktree**, and every session
shares it.

The failure measured on 2026-10-08: a `php artisan serve` left running by a concurrent session held the
port, and its `DB_DATABASE` pointed at `storage/app/private/planner-scratch.sqlite`, a scratch file that
had since been deleted. Every request therefore threw
`Illuminate\Database\SQLiteDatabaseDoesNotExistException` at `app/Http/Controllers/DashboardController.php:93`
(the home route's `TrainingRun::where('status', RunStatus::Active)->first()`) and answered **HTTP 500 in
4.7s**. Playwright never accepted that listener as ready and never replaced it, so `npm run test:browser`
spent its 60s polling a 500 and reported the web server as unable to start. Freeing the port made the same
command work immediately: it bound :8127 itself (new PID 11544) and ran "251 tests using 1 worker", with
the first eight cases passing at 4.6-14.3s each. So the defect is the shared fixed port plus
`reuseExistingServer`, not the pages.

Two things follow that are worth stating separately, because each one burned an hour.

**A server that dies between turns is worse than one that never starts.** The scratch-server recipe the
worktree has been using backgrounds `php artisan serve` inside one Bash call. When the call ends the
process is reaped, but when it does *not* end cleanly the listener survives with the caller's environment
still baked into it, and that is exactly the 500-ing holder above. The port then reads as "in use by
something" to every later session.

**`kill <pid>` from Git Bash cannot reap a Windows pid, and the usual compound check cannot fail.** The
first attempt this pass was `kill 1472 && ... || echo "port_freed"`: Git Bash reported
`kill: (1472) - No such process`, the `||` arm ran anyway and printed `port_freed`, and the port was still
listening afterwards. `taskkill //PID 1472 //F` returned `SUCCESS` and `netstat` then showed no LISTENING
socket on :8127. **How to apply:** reap with `taskkill`, and prove it with
`netstat -ano | grep ":8127.*LISTENING"` as a command of its own, never as the tail of a chain whose
fallback echoes success.

**A leak does not follow every run.** Checked on 2026-10-08 after the sweep below finished: the server that
run's own `webServer` started (PID 11544) was **not** left holding the port. `taskkill //PID 11544 //F`
returned `ERROR: The process "11544" not found` (exit 128) and `netstat -ano | grep ":8127"` returned no
line at all (exit 1), with a positive control for that check earlier in the same pass, when the identical
command did list PID 1472 as LISTENING. So Playwright reaped the server it started, and the hazard this
entry names is narrower than "every browser run leaks a holder": it is a **session that dies without
teardown**, leaving a listener whose environment outlives its scratch database. That is still enough to
take the default entry point down for every later session, which is why the entry stands, but a reader
should not add a post-sweep reap to their own hand-off as if it were the fix.

**Proving commands** (all run 2026-10-08):

```bash
netstat -ano | grep ":8127"                      # LISTENING 1472
powershell -NoProfile -Command "Get-CimInstance Win32_Process -Filter 'ProcessId=1472' |
  Select-Object ProcessId,CreationDate,CommandLine | Format-List"
# php.exe -S 127.0.0.1:8127 D:\Projects\umamusume-laravel13\vendor\...\resources\server.php
# CreationDate 7/10/2026 10:53:00 PM
curl -s -o /dev/null -w "%{http_code} %{time_total}\n" http://127.0.0.1:8127/   # 500 4.727490s
ls storage/app/private/*.sqlite                   # No such file or directory
taskkill //PID 1472 //F                           # SUCCESS
npm run test:browser                              # Running 251 tests using 1 worker
```

**Closure owed.** Three candidate remedies, none of them E6's to choose, because all three change the
instrument every other session reads: a per-session port (read from an env var so `reuseExistingServer`
can no longer be poisoned by a neighbour), a readiness check that fails fast on a 5xx instead of polling it
for 60s, or a worktree-wide convention that a scratch server is always reaped in the call that starts it.
The first is the only one that survives two sessions on one box. This is a tooling-owner call under
`GOVERNANCE.md` §GATE-REGISTRY, and a scenario slice editing the shared `playwright.config.ts` mid-slice
would move the gate for concurrent sessions without their knowing, which is the thing this entry exists to
prevent.

**What closure would not cover.** Closing this does not explain the collapse the sweep went on to have. The
unoverridden run finished at **19 passed, 52 failed, 180 did not run** of 251 in 1.2h (exit 1). Of the 52,
39 are `worker process exited unexpectedly (code=3221225794)` (0xC0000142, a Chromium worker that could not
initialise), 18 are `Test timeout of 180000ms exceeded` at `page.goto`/`page.waitForURL`, and one is
`browserContext.newPage: Target crashed`. Two independent measurements say the host, not the pages, was the
limit: a single indexed `PDO` read of `training_runs` on the dev file took **over 120s** to return, and the
process table at the same moment showed a peer `php` at 510s CPU alongside four long-lived agent `node`
processes at 12,000-17,000s CPU. A single-process `php -S` serves one document at a time under that load,
which is the contention `playwright.config.ts:19-28` already documents when it explains why its own timeout
is 180s. **This sweep is therefore void as a product signal**, and no case in it is proven either way: the
two E6 cases it reached, `grand-concert-panel.spec.ts:48` and `scenario-panel.spec.ts:58`, are both reported
at `(0ms)`, meaning the worker was already dead and they never executed. Establishing E6's browser gate
needs the run repeated on a quiet box, or against a scratch database copy on a private port per the recipe
the worktree already uses, and the result recorded here rather than inferred.

### KI-74 A gate run on a contended worktree is void as a signal, and a red sweep under load reads as a product regression - FILED 2026-10-08 (E6 follow-up 3), OPEN

**Status: OPEN.** This is the class, not one run. It invalidated two different gates in one night on this
worktree, and each time the first reading was "the product is broken".

`AGENTS.md` §1 describes the shape of the problem: this is a local-only, single-Trainer tool with **no CI**,
so a command's output is the only evidence that exists. Everything in §9's hand-off therefore runs on the
same box that every concurrent session is running on, against the same `database/database.sqlite`, served by
a single-process `php -S`. `playwright.config.ts:19-28` already documents the consequence and was edited to
180s because of it ("a document queued behind another session's page (or behind `php artisan test`, which is
CPU-bound here) costs what that request costs - 11-16s each, observed at `:8141`"). What it does not say is
that once the box passes a certain load the instrument stops measuring anything at all.

**Measured 2026-10-08, during the unoverridden `npm run test:browser` sweep:**

| Signal | Reading |
| --- | --- |
| Suite outcome | 19 passed, 52 failed, **180 did not run** of 251, 1.2h, exit 1 |
| Failure family 1 | 39 `worker process exited unexpectedly (code=3221225794)` (0xC0000142, worker could not initialise) |
| Failure family 2 | 18 `Test timeout of 180000ms exceeded` at `page.goto` / `page.waitForURL` |
| Failure family 3 | 1 `browserContext.newPage: Target crashed` |
| A single indexed `PDO` read of `training_runs` | **over 120s** to return (exit 0 when it finished) |
| `cat` of a small file | exceeded a 120s tool timeout |
| `Glob` over `node_modules` | exceeded a 20s ripgrep timeout |
| `laravel.log` mtime | unchanged through the timeouts, so the stalled requests were never exceptions |
| Peer load at the same moment | `php` PID 10360 at 510s CPU; four agent `node` processes at 12,000-17,000s CPU |

The suite's own two E6 cases (`grand-concert-panel.spec.ts:48`, `scenario-panel.spec.ts:58`) are reported at
`(0ms)`, which is what a test that never executed looks like. **A sweep like this proves nothing about the
pages it touched**, in either direction, and must not be recorded as a regression list.

**Rule to apply.** Before starting a tree-level browser sweep, take the cheap measurement first: time one
trivial SQLite read (`php -r` on `select id from training_runs limit 1`). If it exceeds a few seconds the box
is contended and the sweep is void before it starts; either wait for a quiet box or run the slice's own specs
against a scratch database copy on a private port, and say which of the two the evidence is. If a sweep has
already come back red under load, report it **void with the counts and the contention readings attached**,
not as a defect list - and do not re-run it repeatedly hoping for a different result, which costs an hour per
attempt and was the mistake made here.

**Proving commands** (all 2026-10-08):

```bash
php -r '$d=new PDO("sqlite:database/database.sqlite"); foreach($d->query("select id from training_runs order by id desc limit 5") as $r) echo $r["id"]."\n";'
# returned 2 rows, after exceeding a 120s timeout
powershell -NoProfile -Command "Get-Process php,node | Select-Object Id,StartTime,CPU | Format-Table -AutoSize"
grep -c "worker process exited unexpectedly" <sweep log>   # 39
grep -c "Test timeout of 180000ms exceeded" <sweep log>     # 18
```

**Owner.** QA / Reviewer for the bar (this is a gate-validity rule, so it belongs beside `GOVERNANCE.md`
§CONSTRAINTS.md and the hand-off sequence in `AGENTS.md` §9), and the tooling owner for anything that makes
the box's load visible in advance. It is not a slice's to fix.

**Closure owed.** A written gate-validity rule: which runs count as evidence, and what a void run must report
with it. The alternative is a contention pre-flight check baked into `npm run test:browser` that refuses to
start a 251-test sweep while a trivial read is stalling. Either one is small; neither is E6's to choose, and
the second edits the shared script every session uses, which is the same objection KI-73 records.

**What closure would not cover.** Closing this does not answer what the 180 unreached cases and the 18 goto
timeouts would say on a quiet box; they still need one clean sweep, and until then the tree-level browser
gate is simply **not run**, which is a reportable state under `AGENTS.md` §15 rather than a failure. It also
does not cover KI-73, which is a different defect on the same port: a surviving listener with a dead database
path. A quiet box with PID-style leak on :8127 still cannot start.

### KI-80 The browser suite's scratch database is one shared repo path, so two sessions in a worktree cannot both run the suite and the second one's setup deletes the first one's data - FILED 2026-10-08 (E6 Grand Concert audit slice), OPEN

**Status: OPEN.** Found while re-taking the Grand Concert browser evidence on a quiet box; not caused by that
slice and not fixed by it. Filed as KI-75 and renumbered to KI-80 within the hour: a concurrent session's
"Database reference views hand-off" filed a different KI-75 while this entry was being written, and the
register's rule is never to renumber an existing entry, so this one moved instead. That is the third
numbering collision this register has recorded in two days, after the two KI-70s and the two KI-71s.

`tests/browser/global-setup.ts:27` fixes `SCRATCH_DATABASE` to `database/browser-scratch.sqlite`, and `:32-34`
deletes that file plus its `-wal` and `-shm` siblings with `rmSync(..., { force: true })` before re-creating
and re-seeding it. The path is repo-wide, exactly as the port in KI-73 is, so the suite is **single-instance
per worktree** and nothing in the file says so.

Measured 2026-10-08, with the port free and no `PLAYWRIGHT_BASE_URL` set:

```text
npx playwright test tests/browser/grand-concert-panel.spec.ts tests/browser/scenario-panel.spec.ts
Error: EPERM, Permission denied: \\?\D:\Projects\umamusume-laravel13\database\browser-scratch.sqlite
   at global-setup.ts:33
exit=1
```

`database/browser-scratch.sqlite` was present at 2,220,032 bytes with an mtime from that evening; three peer
`php` processes had been alive since earlier that afternoon and none held a port in the 8127-8149 range, so
the handle belonged to a concurrent session's run rather than to the browser server.

**Two failure modes, and only the first one is visible.** The observed `EPERM` merely stops the second
session from starting. The unobserved one is worse: the delete is guarded only by what Windows permits, so
where the handle does allow the unlink, session B removes the database session A is actively serving and A's
remaining cases read an empty schema mid-run. That is the exact class KI-69 was filed to end, re-introduced
between sessions instead of between specs, and it would surface as a red sweep that looks like a product
regression (which is KI-74's misreading).

The observation is not that the peer did anything wrong. KI-69 correctly made the suite own its database
rather than read `.env`'s shared dev file, and correctly did not consider two sessions on one checkout.

**Proving commands** (2026-10-08):

```bash
npx playwright test tests/browser/grand-concert-panel.spec.ts    # EPERM at global-setup.ts:33, exit 1
ls -la database/browser-scratch.sqlite*                          # 2220032 bytes, mtime that evening
netstat -ano | grep LISTENING | grep -E ":(8127|8128|8129|813[0-9]|814[0-9])"   # no rows
powershell -NoProfile -Command "Get-Process php,node | Select-Object Id,StartTime,CPU | Sort-Object StartTime"
```

**Closure owed.** A per-session scratch path, keyed on the same identity a per-session port would use, so the
file is as private as the port is not. That is the same tooling-owner call KI-73 records and the two should
be settled as one question, because they are one design assumption: the worktree's browser instrument assumes
one session and names no owner for that assumption.

**What closure would not cover.** KI-74's contention class, which voids a suite that starts cleanly. And the
Grand Concert browser evidence this slice could not re-take: with the file held, the slice's browser gate
rests on the earlier accepted run rather than a fresh one, which is recorded in the slice report rather than
implied away here.

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

### KI-76 With a Vite dev server hot, every Inertia render POSTs to the SSR endpoint with no timeout, so a page blocks 30s and answers 500 - FILED 2026-10-08 (F1 residual, on the owner's instruction), OPEN

**Status: OPEN.** Not caused by F1, not fixed by it, and not fixed in the slice that filed it. The remedy is
shared configuration that every session reads, which is the same objection KI-73 records against a slice
editing the shared harness. Filed with the owner's instruction: "File it; leave it."

**The mechanism, from the files.** The app publishes no `config/inertia.php` (measured: `ls config/inertia.php`
-> no such file), and no service provider touches SSR (`grep -n "ssr" app/Providers bootstrap/app.php
resources/views/app.blade.php` -> no match), so the package's own defaults govern:
`vendor/inertiajs/inertia-laravel/config/inertia.php:24` `'enabled' => (bool) env('INERTIA_SSR_ENABLED', true)`,
`:30` `'url' => env('INERTIA_SSR_URL', 'http://127.0.0.1:13714')`, `:32` `'hot_url' => env('INERTIA_SSR_HOT_URL')`
(null), and **`:34` `'timeout' => env('INERTIA_SSR_TIMEOUT')` -> null**. `HttpGateway::pendingRequest()`
(`vendor/inertiajs/inertia-laravel/src/Ssr/HttpGateway.php:120-132`) applies a timeout **only when that config
value is truthy**, so the SSR request carries none.

`HttpGateway::dispatch()` has two guards before it sends: `:51-53` returns early when SSR is disabled, and
`:55-57` skips the send when the bundle is missing — **but only when Vite is not hot**. While any session runs
`npm run dev`, `public/hot` exists, `Vite::isRunningHot()` is true, the bundle check is bypassed, and the
target becomes the hot origin. `public/hot` on this worktree read `http://[::1]:5174` at 06:11 on 2026-10-08.

**Measured on the live hot origin, read-only, nothing in the tree touched:**

```text
curl -s -o /dev/null -w "%{http_code} %{time_total}s" --max-time 20 "http://[::1]:5174/__inertia_ssr"
-> code=000 time=20.037s   (curl exit 28, operation timeout)
```

The dev server accepts the connection and never answers that path. A Laravel render that posts to it has no
timeout to give up on, so the request sits until the CLI server's `max_execution_time=30` kills the script
mid-render: **HTTP 500**, and on a single-process `php -S` every document behind it queues.

**The discriminating pair** (same `VACUUM INTO` scratch database, `SESSION_DRIVER=file`, differing only in the
env the harness was started with, 2026-10-08):

| Harness | `/` | `/training-runs` |
| --- | --- | --- |
| SSR at the package default (enabled), port 8186 | **500 after 33.47s** | **500 after 30.39s** |
| `INERTIA_SSR_ENABLED=false`, port 8185 | 200 after 1.97s | 200 after 2.45s |

The fatal names the path: `PHP Fatal error: Maximum execution time of 30 seconds exceeded in
vendor/guzzlehttp/guzzle/src/Handler/CurlHandler.php`, frames `#21 .../Ssr/SsrState.php(43):
Inertia\Ssr\HttpGateway->dispatch()` and `#22 storage/framework/views/<root view>.php(18):
SsrState->dispatch()`.

**What this cost, and what it did not.** Measured cost: any case that opens `/` or `/training-runs` on an
SSR-enabled harness gets a 500 instead of a page, which is the Dashboard and the Careers list, two of the three
surfaces F1 asserts on. What is **not** attributable to it: the F1 hand-off's two voided browser passes (8 of 20
tests, then 4 of 16, every failure at `page.goto`) were re-tested with `INERTIA_SSR_ENABLED=false` and failed
the same way, while the same harness answered `curl` on `/` in 0.80s and on `/career/setup/scenario` in 1.48s.
A wedged server does not do that. Those passes were client-side starvation - 25 `chrome`, 13 `node` and 32
`php` processes on the box at the same moment - which is KI-74's defect, not this one. The two hazards
compound: this entry costs 30s and a 500 per affected document, KI-74 costs a whole sweep its meaning.

A previous slice's workaround was to move `public/hot` aside for the length of its run, which cannot be a
standing rule because the file belongs to whichever session is running Vite, and removing it from under that
session is exactly the cross-session interference KI-70 and KI-75 record.

**One correction to a reading made in the same pass.** `/` and `/training-runs` first looked route-specific: on
port 8186 those two hung and the two following documents, the Cockpit and the scenario step, returned in about
2s. Re-probing the same shape later showed `/` fail at 2.0s with a curl error, then answer 200 in 1.86s, and
`/training-runs` 200 in 3.28s. The accurate statement is **intermittent per request**, dependent on whether the
hot origin is up-and-unresponsive at that moment; I did not attribute what made the two later requests fast,
and the entry does not claim to.

**Config-plumbing control, because that was the other hypothesis.** There is no cached config
(`bootstrap/cache/config.php` absent), and booting with the variable unset prints
`ssr.enabled=true url=http://127.0.0.1:13714` while booting with `INERTIA_SSR_ENABLED=false` prints `false`.
The flag reaches the application, so the defect is the missing timeout on a target that does not answer, not
the env wiring.

**Owner.** Laravel Dev or the tooling owner for the configuration (it is not a screen and not a slice's), and
QA / Reviewer for the consequence to the §9 hand-off sequence, which is where "a claim is the command output"
is written.

**Closure owed.** Two remedies, and the choice is the owner's: (a) set a real SSR timeout, either by publishing
`config/inertia.php` or by `INERTIA_SSR_TIMEOUT=3` in `.env.example` and the local `.env`, so a dead endpoint
degrades to a client-side render in seconds instead of a 500 in thirty; or (b) turn SSR off by default for this
application, with the reason written down: the tool is local-only on loopback with no hosting or deploy path
(`AGENTS.md` §1, §12), it has no crawler and no first-paint requirement, and `resources/views/app.blade.php`
renders the Inertia root for the browser anyway. (b) edits the env file every session reads, which is the KI-73
objection, so whichever lands should land as an owner ruling rather than as a slice's convenience.

**What closure would not cover.** It does not make a contended-box sweep valid, which is KI-74's problem, and it
does not recover the two voided passes: the 18 migrated specs named in the F1 hand-off still need one run on a
harness that answers. It also does not decide who may remove `public/hot`; that file remains whichever session
started Vite's.

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

### KI-78 `uma:backup` changed mechanism, destination handling and timestamp zone on 2026-10-08, and two documents still state the old one - FILED 2026-10-08 (D18b hand-off, on the owner's instruction), OPEN

**Status: OPEN.** Not a defect report. The command works, the new mechanism is the safer one, and the
change is uncommitted in the working tree as this is written. This entry is the record the change owed a
reader who relied on the old semantics, because all three of the differences below are observable from
outside the class that was edited.

**The three changes.** HEAD's `app/Console/Commands/UmaBackup.php` (last touched `f0f508c`, 2026-09-27)
versus the working-tree file, which now delegates to `app/Actions/BackupDatabase.php`.

| Aspect | Before, at HEAD | After, in the working tree | What a reader relied on |
| --- | --- | --- | --- |
| Mechanism | `DB::statement('PRAGMA wal_checkpoint(TRUNCATE);')` on the application connection, then PHP `copy()` | `VACUUM INTO` on a PDO connection of its own | The old form **mutated the source database**: a TRUNCATE checkpoint folds the write-ahead log into the main file and empties the `-wal`. Anyone who ran `uma:backup` and then looked at `database/database.sqlite-wal` saw it truncated, and the new form leaves it alone |
| Existing destination | `copy()` overwrote it silently and returned SUCCESS | `RuntimeException`, "A file already exists at ... and a snapshot never overwrites one." | A script that re-ran the same destination path to refresh a snapshot now fails instead of replacing it |
| File name timestamp | `now()->format('Ymd-His')`, the machine's local zone | `now()->utc()->format('Ymd-His')` | Two names in different zones sort differently against a wall clock. `docs/research-scratch/SLICE-RECORDS.md:588-590` already prints both readings side by side (`uma-backup-20260929-153038` against 09-29 23:30 local, an eight-hour offset), so the stored names were UTC-shaped while the code said local. This brings the code in line with what the record already shows |

**Why the mechanism moved.** `VACUUM INTO` runs inside a read transaction of its own, so the target is a
consistent snapshot of a database that may be mid-write. Checkpoint-then-copy is not: under WAL, a `copy()`
can read a main file whose newest frames are still sitting in the log, which is precisely the failure NFR-5
exists to prevent. The Action also opens its own connection rather than borrowing the caller's, because
SQLite refuses a `VACUUM` inside an open transaction outright.

**Proving commands (2026-10-08).**

```bash
git show HEAD:app/Console/Commands/UmaBackup.php    # lines 32-47: PRAGMA wal_checkpoint(TRUNCATE), then copy()
git show HEAD:app/Console/Commands/UmaBackup.php | grep -n "now()->format"   # 35, local time
cat app/Actions/BackupDatabase.php                  # 46: now()->utc(), 53-55: refuse existing, 61: VACUUM INTO
grep -n "uma:backup" PRD.md ARCHITECTURE-ESSENTIALS.md   # the two stale lines, below
```

**The two documents that now read wrong.** `PRD.md:178` states NFR-5 as "`uma:backup` produces a consistent
single-file copy (WAL checkpoint, then copy)", and `ARCHITECTURE-ESSENTIALS.md:74` lists "uma:backup (WAL
checkpoint + file copy)". Both name the retired mechanism as the requirement. Per `AGENTS.md` §2 the code
wins and the rule is stale, so each needs a dated note rather than a silent rewrite (§11). Not edited in
this slice: the PRD's NFR text is product truth and the correction is the Architect's and the Docs Writer's
call, not the slice's.

**Owner.** Docs Writer for the two dated notes; Architect for whether NFR-5's wording moves from mechanism
to property (a consistent single-file snapshot) and leaves SQLite's choice of mechanism to
`ARCHITECTURE.md`; Laravel Dev for the command itself.

**Closure owed.** The two dated notes above, plus one line stating that snapshot names are UTC, so the next
reader treats the `SLICE-RECORDS.md` eight-hour offset as the rule rather than as an unexplained oddity.

**What closure would not cover.** It does not restore the truncated `-wal` side effect for anyone who
wanted it; that behavior was incidental, not the contract. It does not decide whether the Settings screen's
Backup action and `uma:backup` should keep sharing one implementation, which is what `BackupDatabase` now
does. And nothing here lands the change: `uma:backup`'s old semantics remain HEAD's until the slice is
committed.

### KI-79 Eleven pages set their loading state from Inertia's `start` event, which a `Link` hover prefetch also fires, and an interrupted request never fires `finish`, so a page can announce a load that already ended and never clear it - FILED 2026-10-08 (NFR hardening pass, from the attached performance task), FIXED IN TREE, NOT CLOSED

**Status: FIXED IN TREE, NOT CLOSED.** The eleven pages that carried the block now call one composable,
`resources/js/composables/useVisitState.ts`, which ignores a prefetch visit and owns the four listeners
once. Nothing is committed.

**The mechanism, from the installed library.** `@inertiajs/core` 2.3.28. `Request::send()`
(`node_modules/@inertiajs/core/dist/index.esm.js:2283`) calls `fireStartEvent(...)` **before** it looks at
`prefetch`, so a hover prefetch dispatches the same document `inertia:start` a real visit does. `Request::finish()`
(`:2327-2333`) returns early when `wasCancelledAtAll()` — `cancelled || interrupted` (`:1772-1774`) — so a
cancelled request fires `start` and then **nothing**. `Router::visit()` calls `syncRequestStream.interruptInFlight()`
(`:2554`) on every non-async visit, and that stream is `{maxConcurrent: 1, interruptible: true}` (`:2415-2418`),
so a second navigation always interrupts the first; `cancelInFlight({prefetch: false})` (`:2552`) cancels in-flight
prefetches when the destination changes.

**What that produced.** Each of eleven pages carried its own copy of a four-listener block that set
`visiting = true` on `start` and cleared it only on `finish` / `exception` / `invalid`. `AppLayout.vue` prefetches
eight of its ten destinations on hover (`:prefetch="item.prefetches ? 'hover' : false"`, 75 ms delay). Two
consequences, both matching the task's reported symptoms: the page the Trainer is already on announced
"Loading…" every time the pointer crossed the sidebar; and a prefetch superseded by the next one was cancelled
without a `finish`, so the flag was never cleared and the page stayed in that state. A real visit that is
interrupted self-heals on the interrupting visit's `finish`, which is why the defect read as intermittent.

**Proving commands.**

```bash
grep -n "fireStartEvent\|wasCancelledAtAll\|interruptInFlight" node_modules/@inertiajs/core/dist/index.esm.js
grep -rn "router\.on(" resources/js/pages          # 11 files before, 0 after
grep -n "prefetch" node_modules/@inertiajs/core/dist/index.esm.js | head -1   # hoverDelay: 75
```

**The regression test.** `tests/browser/navigation-determinism.spec.ts` holds the prefetch response open with
`page.route`, hovers a nav link, and asserts no `role="status"` appears; a second case interrupts one held
prefetch with another. Both fail on the pre-fix tree and pass on the fixed one. `useVisitState.ts` also returns
`false` from `exception`/`invalid`, which `SaveVeteran.vue` did not do before, so that page now suppresses
Inertia's default error modal the way the other ten already did.

**Owner.** Laravel Dev / frontend owner for the composable; QA / Reviewer for whether the eleven-page
duplication should have been collapsed in the same change.

**Closure owed.** A commit. Nothing else: the behaviour is the standard the other ten pages already implemented.

**What closure would not cover.** It does not address `start` firing for a prefetch at all, which is Inertia's
behaviour and not this app's to change; it does not make `cancel` a usable event — the type map declares
`cancel` (`types/types.d.ts:204-208`) but 2.3.28 never fires it, so a listener keyed on it would be a check that
cannot fail; and it does not touch KI-76, which is a server-side defect with its own remedy choice.

### KI-81 The Legacy Lab's parent pick never reached the payload, so choosing a parent saved a null pick and the Inheritance Event rendered its empty state - FILED 2026-10-09 (number reserved at `docs/proposals/provenance-and-absence-contracts.md:441`), FIXED 2026-10-09 at `45e7a26`

**Status: FIXED IN TREE, component-tested, not browser-gated.** The number was reserved rather than
skipped when KI-82 was filed, because this pass owned the defect and could not write a closure commit
before it existed. It now has one.

`AncestryNode.vue` bound a native `<select>` with `v-model` on an internal ref and emitted nothing, while
`Builder.vue:242` read `form.legacies[index].legacy_id`. A Trainer chose a parent, the label showed the
choice, and the save dropped it: `legacy_selection` kept a null pick, `/inheritance` rendered the empty
state, and no line on screen said the choice had been lost. The component's own docblock already
documented a `modelValue` prop that no call site supplied and no emit declared, so the contract existed
in prose and not in code. Proved by `tests/browser/ancestry-node-picker.spec.ts`, which mounts the real
SFC and pins the three things that can regress separately: the emit on click, the emit on a keyboard
arrow, and a prop write reflected back into the select.

**What closure does not cover.** The fix is a component-level proof, not an end-to-end one:
`tests/browser/career-inheritance-event.spec.ts` still cannot pass on a shared harness, because KI-80's
single scratch database and KI-69's missing Veterans fixture are both open, so the full
choose-a-parent-then-visit-`/inheritance` path is asserted at the payload layer only. The `change`-event
choice (a native `<select>` fires `change`, never `update:model-value`) is not covered by any PHP test,
and this entry does not claim the Legacy Lab was browser-verified after the fix.

### KI-82 Our Grand Concert's finale catalogue row is titled "URA Finals Final (Grand Live)", a borrowed URA name plus GameTora's rendering, where the `[Global]` launch notice prints "Grand Concert" - FILED 2026-10-09 (Slice 19 finale state foundation), OPEN

**Status: OPEN.** Found while establishing the finale's read-side foundation in Slice 19; not caused
by that slice and not fixed by it. It is a second, independent defect on the **same row** that KI-71
addressed: KI-71 corrected that row's `scenario_key` from `grand_concert` to `our_grand_concert` at
`480b711`, which made the row belong to the scenario, and this entry records that the row's **title**
still does not. Filed as KI-82 rather than KI-81 because KI-81 is already reserved in
`docs/proposals/provenance-and-absence-contracts.md:441` for the Legacy Lab picker defect, which that
package owns; the gap is deliberate and this entry does not claim 81.

The row's `title` is the dataset's `name_en`, mapped verbatim by
`app/Services/DataPipeline/Parsers/GametoraRaceCatalogParser.php:173`
(`'title' => (string) ($details['name_en'] ?? '')`). For the slot id `final_live` that value is
`"URA Finals Final (Grand Live)"`, read from the committed body:

```text
grep -o '"id": *"final_live".\{0,400\}' database/seeders/data/race_instances.json
  ... "name_en": "URA Finals Final (Grand Live)",
      "name_jp": "URAファイナルズ決勝 (グランドライブ)", ...
```

Two claims in that string are wrong for `[Global]`. **"URA Finals"** is the first scenario's finale
name, pasted onto the fourth; the four finals share slot-shape, not name. **"Grand Live"** is
GameTora's rendering of the JP word グランドライブ, and `docs/UMAMUSUME_REFERENCE.md` §7 row 52 records
that the `[Global]` event is printed by Cygames' notice 905 as **Grand Concert** — the same class of
divergence row 48 caught on "Mental" against **Composure**. Neither half of the title is a measured
`[Global]` client string, and `docs/scenarios/07-grand-concert.md:34-36` already warns that three
labels circulate for this scenario and to join on the dataset order, never on a name string.

**Severity is latent, and that is stated rather than smoothed over.** Nothing renders this title
today: the row reaches a Trainer only through `CockpitController::careerGoalSections()`, which is
gated by `$panels['career_goals']['on']` (gate G-33), and Our Grand Concert composes no panel. Fixing
the key in KI-71 without fixing the title, or switching the panel on later, makes the defect visible
without any further code change.

**Closure owed.** A parser-or-catalogue decision at the owner level, not a slice fix: either the
dataset's `name_en` is accepted as this tool's race label and the divergence is documented beside it,
or the scenario-scoped finals get their `[Global]` names from a source this repository treats as
authoritative. Because `config/scenarios.php` is the only place a scenario name may enter the layout
path (D-240), a display override cannot live in a component, and because `race_catalog_slots` is
engine-owned with a promotion path that skips `is_manual` rows, a hand edit is not a fix. Closure
therefore names a commit plus the decision it records.

**What closure would not cover.** It does not settle whether "Grand Concert" is the *client's* race-card
string rather than the notice's sentence — no `[Global]` capture of the finale screen exists, and the
corpus still records 0 frames for this scenario (`docs/research-scratch/DESIGN-CORPUS.md:4261`). It does
not touch the other three scenario finals, whose `name_en` values may carry the same class of borrow and
were not read for this entry. It does not reopen the Songs, Lessons, Live Bonus or Promo Concert gates,
which §7-19, §7-21, §7-22 and §7-23 leave blocked. And it does not make the finale *state* uncertain:
that foundation landed at `2c7bb3e`.

### KI-83 Three tracked design prototypes render four raw hexes that are not shipped theme tokens, and two of the four are the values KI-58's folded document leg once carried - FILED 2026-10-09 (provenance/absence contract pass, on the owner's §11 dispensation), FIXED IN TREE 2026-10-09, NOT CLOSED

**Status: fixed in tree, not closed.** Found while dispositioning the `python tools/gate.py` failure that
KI-58 left behind. This is the **mirror** of KI-58, not a continuation of it: KI-58 was the allowlist losing
a leg and therefore rejecting colours the theme *did* declare; here the theme no longer declares the colours
the artefacts use. The gate exits 1 with ten findings across three **tracked** files, clean in the tree and
committed together at `e0e043c` (2026-09-27): `docs/design-research/prototypes/screen-a-scenario-v10.html`
(two occurrences of `#7A7067`), `screen-c-event-v1-inline.html` and `screen-c-event-v2-preview-column.html`
(`#7A7067`, `#106F9F`, `#C5A558`, `#FDF7EF` once each). Reproduce with `python tools/gate.py`.

Provenance of each value, because it decides the repair. `#7A7067` was `--color-ink-muted` **before** the
re-baseline and is the same token's prior value, recoverable at
`git show e0e043c:docs/design-research/DESIGN.md`; `#106F9F` is the light half of the artefacts' own
`--cyan-800`, and their dark half is `#4FC3F7`, which is exactly the shipped dark `--color-sp-ink` — so both
are stale readings of tokens that still exist. `#C5A558` (the checked-choice border beside `--pick`) and
`#FDF7EF` (the highlight end of that choice's gradient) have **no** antecedent in any theme block: they
appear only in the gitignored `docs/design-research/_scratch/analysis/tokens/tokens.json`, and neither is a
shipped token at any date.

**The repair, applied in tree.** Each hex was replaced at its declaration or use site with the current
value of the token that owns its role: `#7A7067`→`#6E6459` (`--color-ink-muted`, light),
`#106F9F`→`#0E7490` (`--color-sp-ink`, light), `#C5A558`→`#7A5C10` (`--color-pick-line`, light),
`#FDF7EF`→`#FFFFFF` (`--color-raised`). Replacement counts were verified per file and sum to the ten
findings exactly (2 + 4 + 4). **Two of the four are judgement, not mapping, and the owner should see them
before this closes:** the pick's border moves from a mid gold to the token's dark amber `#7A5C10`, and the
gradient's cream highlight becomes a cool white. Both are legible in a research artefact that never ships,
which is why they were resolved rather than left failing; the alternative is `10b`, re-scoping the gate,
which the owner refused on 2026-10-09 because the gate was correctly reporting.

**What closing this will not cover.** It does not make the prototypes consistent with the shipped theme in
any deeper sense — they remain standalone research artefacts with their own `:root` blocks, and per
`AGENTS.md` §11 they are protected from hand edits, so this repair stands only because the owner dispensed
with that fence in writing on 2026-10-09. It does not restore KI-58's dead guard: `gate.py` still warns
only below 40 allowlisted hexes and the shipped theme yields 92, so a thin future theme still passes
silently. It does not touch the two other register items this pass found in the same file set: `gate.py:6`
cited a `docs/design-research/CONSTRAINTS.md` that no longer exists (repointed to
`docs/research-scratch/DESIGN-CORPUS.md` in the same working pass), and `README.md:306` still describes
KI-58 as an open defect. Closure is the commit that lands these three files plus the gate exit 0 it
produces; it does not cover the other artefacts under `superseded/`, which the gate does not scan.

### KI-84 A concurrent session staged the deletion of ten feature-test files, three of which are Our Grand Concert's own absence guards, with no owner approval on record - FILED 2026-10-09 (Slice 20 pickup, on the owner's instruction), OPEN

**Status: OPEN.** Found while staging the cockpit baseline-strip pickup, which could not be
committed without either sweeping these entries under an unrelated message or mutating another
session's staged state. The owner ruled the index entries unstaged on 2026-10-09 so the pickup could
land; **ruling the index entries unstaged is not a ruling on the deletions**, and no session owns a
decision here until the owner gives one.

Ten paths, all present in `HEAD` at filing time and therefore recoverable with `git restore --
<path>`:

```text
tests/Feature/FlashBannerTokensTest.php          tests/Feature/RaceCatalogPickerTest.php
tests/Feature/FrontendComponentLibraryTest.php   tests/Feature/ResourceStripOnRunDetailTest.php
tests/Feature/GoalPanelsOnRunDetailTest.php      tests/Feature/ResourceStripTest.php
tests/Feature/GradePointMeterTest.php            tests/Feature/RunDeckTest.php
tests/Feature/MoodPillTest.php                   tests/Feature/RunGoalsPanelTest.php
```

Two facts make this more than a housekeeping collision.

**Three of the ten are this scenario's guards, not dead weight.**
`docs/scenarios/07-grand-concert.md` ("Matrix mapping") names `ResourceStripTest`,
`RaceCalendarTest`, `GradePointMeterTest`, `GuidedStepScenarioVariationTest` and
`GoalPanelsOnRunDetailTest` as the tests that *assert the absence* of a resource chip, a Hype gauge
and a live marker for Our Grand Concert, and `AGENTS.md` §18 repeats the same sentence. Deleting
`ResourceStripTest`, `GoalPanelsOnRunDetailTest` and `GradePointMeterTest` removes the assertion that
Phase B, C and D surfaces are unrendered, which is the only thing keeping the blocked gates honest
while no `[Global]` capture exists.

**The deletions are in the working tree, not only the index.** Measured at filing: all ten are gone
from disk, so `git reset HEAD -- <paths>` leaves them as unstaged worktree deletions rather than
restoring the files. Any later blanket add (`git add -A`, `git commit -a`) by any session commits
their loss. That is why this entry names all ten paths instead of describing them.

The bar's clause is explicit: `AGENTS.md` §5, "no deleted or skipped test without owner approval and
a stated reason", and §14, "Deleting or skipping a test needs owner approval and a stated reason in
the commit." No such reason is recorded anywhere in the tree for these ten.

**Closure owed.** An owner ruling on the deletions themselves, filed by the session that made them
or superseded by a commit that states the reason per file. Closure of this entry is not closure of
the question of whether the three Grand Concert guards have a replacement; if they were folded into
another suite, that suite must be named, and until then the absence of a chip, a gauge and a live
marker is asserted by nothing.

**What closure would not cover.** It does not restore any file: restoration is the deleting
session's or the owner's act, not a register edit. It does not rule on the other seven files'
relevance, only that removing them needs a stated reason. It does not touch the Slice 20 pickup,
which landed independently of this collision, nor the KI-82 label defect on the same scenario's
finale row.

**Landed 2026-10-09 (erratum; the text above is left exactly as filed).** A later commit by the same
concurrent session, `91494a1 feat(career): migrate legacy run controls to 2.0 surfaces and redirect
/training-runs/{run}`, committed the staged deletions. The set is not ten. Two instruments, both run at
`91494a1`:

```text
git show 91494a1 --diff-filter=D --name-only --format="" -- tests | wc -l        # 24
git show 91494a1 --numstat --format="" -- tests | grep -F -f <those 24 paths> | awk '{d+=$2} END{print d}'
                                                                                  # 5274
```

Nine of the ten paths named above are among the 24. The tenth, `tests/Feature/RunGoalsPanelTest.php`,
survived that commit and is still in `HEAD` (`git cat-file -e HEAD:tests/Feature/RunGoalsPanelTest.php`
exits 0), but it is deleted in the working tree and unstaged, so `git status` reads ` D` for it and one
blanket add makes it the 25th. None of the 24 has come back: `git ls-files --error-unmatch` over the list
returns nothing at `HEAD`, and `tests/Feature` went from 165 files at `91494a1^` to 149. The same commit
also removed 34 lines from `tests/Feature/FinaleReportingTest.php` and 102 from
`tests/Feature/ScenarioPanelTest.php`; both files are still tracked.

The landing does not close this entry, because what was missing was not staging but approval. `AGENTS.md`
§5 and §14 want an owner approval and a stated reason per deletion; `91494a1`'s message describes a
legacy-controls migration and states no reason for removing `RaceCalendarTest`, `RunDeckTest` or
`GuidedTurnOnRunViewTest`. And the consequence named above has arrived: the three Grand Concert absence
guards (`ResourceStripTest`, `GoalPanelsOnRunDetailTest`, `GradePointMeterTest`) are gone from the
branch, so nothing on `master` any longer asserts that the Phase B, C and D surfaces stay unrendered
while those gates are blocked for want of a `[Global]` capture. Restoration remains the owner's or the
deleting session's act; this entry records the state, and KI-86 carries the code defect the same commit
caused.

### KI-85 Commit 822d97b carries a concurrent session's ported test case alongside the finale pickup, an attribution boundary rather than a defect - FILED 2026-10-09 (Slice 20 pickup, on the owner's ruling), FIXED IN TREE

**Status: FIXED IN TREE - the note is the fix.** No history rewrite was attempted and none is owed:
rewriting `master` to correct attribution would be a worse defect than the attribution error, and the
only clean-looking removal is a deletion of a passing test, which `AGENTS.md` §5 forbids without owner
approval. The record is the remedy, and this entry is it.

The fact: `822d97b feat(scenario): surface the finale state in the cockpit baseline strip` changed six
paths. Five contain only the slice's own work. The sixth, `tests/Feature/ScenarioPanelTest.php`, shows
103 added lines where the pickup's change is 3: the remaining hundred are another session's uncommitted
test case, `it('turns on exactly the panels the matrix declares, and renders no panel data where it turns
one off')`, ported from `GuidedStepScenarioCompositionTest`.

The mechanism is named so it is not repeated. The two controllers in that commit were staged as
`HEAD + own-hunks-only` blobs and verified with `php -l` and `vendor/bin/pint --test`. The other four
paths were hashed from the working tree on the belief that a file last seen clean is still clean. A
concurrent session had edited `ScenarioPanelTest.php` in the interval. The tooling was right and the
assumption was the defect, so the rule now applied without exception is that **every** staged path gets
its own blob, not only the paths suspected of being dirty.

What is not in question: the ported case passes and the work it tests is intact. `vendor/bin/pest` over
`FinaleReportingTest`, `ScenarioPanelTest` and `GrandConcertPanelTest` was green on the committed tree at
19 tests / 410 assertions before and after this entry. Functionally the commit is sound; only the
authorship line is wrong, and no credit commit is filed under this session's name, because that would
recreate the same problem pointing the other way.

**Closure owed.** None. This entry exists to make the boundary visible to the next reader of the log,
who would otherwise attribute the ported case to the finale slice. If the owner prefers the attribution
carried in the code's own history, the route is the deleting session restating the case in a commit of
theirs, not an edit here.

**What closure would not cover.** It does not change `822d97b`; the commit is left exactly as landed. It
does not rule on KI-84's ten deleted test files, which remain the deleting session's and the owner's
question. It does not cover the other four paths in that commit, which were the slice's own work.

### KI-86 A blanket add deleted the finale reader that HEAD's own controllers and advisor still call, so a checkout of master fatals on the cockpit and the result screen - FILED 2026-10-09 (Slice 21 landing), FIXED IN TREE 2026-10-09 AT d7d6ed5, NOT CLOSED

**Status: FIXED IN TREE at `d7d6ed5`, NOT CLOSED.** The missing class is back and `HEAD` is
self-consistent again. The entry stays open on two counts: `master` is unpushed (O-1), so a
`FIXED IN TREE` flag is not a release; and the index state that deleted the file is still live, so the
next session that commits from the shared index removes the class a third time.

**The defect, measured at `ad8dc77`** — three commits after `91494a1` deleted the file, and the point by
which the concurrent commits had swept in the Slice 19, 20 and 21 code that names the class. Six
references, no definition:

```text
$ git show ad8dc77:app/Services/Scenario/FinaleReader.php
fatal: path 'app/Services/Scenario/FinaleReader.php' exists on disk, but not in 'ad8dc77'

$ git grep -n "FinaleReader" ad8dc77 -- app
app/Http/Controllers/Career/CockpitController.php:17:use App\Services\Scenario\FinaleReader;
app/Http/Controllers/Career/CockpitController.php:677:            'finale_state' => FinaleReader::forRun($run),
app/Http/Controllers/Career/ResultController.php:15:use App\Services\Scenario\FinaleReader;
app/Http/Controllers/Career/ResultController.php:72:            'finale' => FinaleReader::forRun($run),
app/Services/Advisor/TrainerAdvisor.php:11:use App\Services\Scenario\FinaleReader;
app/Services/Advisor/TrainerAdvisor.php:114:        $finale = FinaleReader::forRun($run);

$ git grep -n -E "class FinaleReader|function forRun" ad8dc77 -- app
ad8dc77:app/Services/ScenarioCaps.php:94:    public static function forRun(?TrainingRun $run): array
```

The third command is what makes this a fatal and not a style complaint: nothing at `ad8dc77` defines the
class or the `forRun` those three sites invoke. `ScenarioCaps::forRun` is another class and does not
answer the call, so PHP resolves the name at the call site and raises
`Error: Class "App\Services\Scenario\FinaleReader" not found`. Two of the three sites sit on a page load
(`CockpitController.php:677` builds `scenario.finale_state`, `ResultController.php:72` builds
`scenario.finale`); the third is on the advisor's path and is reached from `TrainerAdvisor.php:91`, where
`$this->finaleContext($run)` sits inside the `Advice` constructor's own argument list, so it runs on
every `advise()` call that carries a run rather than only near a finale — and both controllers carry one.
This has not been measured by booting a second checkout; it follows from six references and no
definition, and it is stated as that rather than as a captured stack trace.

**Why it stayed invisible on this tree.** The deletion took the path out of git and left the bytes on
disk, so the autoloader here finds the class, the routes answer, and
`php artisan test --compact --filter "(ScenarioAdvisorFinale|FinaleReporting|TrainerAdvisor|CareerCockpit)"`
reads 59 passed / 632 assertions, exit 0, both before and after the restore. `git status` prints two
lines for that one path:

```text
D  app/Services/Scenario/FinaleReader.php
?? app/Services/Scenario/FinaleReader.php
```

`D ` because the path is absent from the index while present in `HEAD`, `??` because a file the index
does not know about is on disk. A green suite, a working page and a branch that cannot be checked out are
all true at once, and only the last is the defect in `master`.

**The repair, at `d7d6ed5`.** One path added, its blob compared to `822d97b`'s version by content before
hashing (`BLOB-EQUALS-822d97b`, 3,183 bytes), then `php -l` and `vendor/bin/pint --test` clean,
`npm run typecheck` exit 0. It is not a reimplementation and not a revert of the deleting session's work:
it hands back the class their own commit left three callers pointing at. The alternatives all rewrite
code this session does not own — moving the read into `TrainingRun`, or removing the two controller
blocks and the advisor's `finaleContext()`. And no replacement had been landed either: `91494a1`'s
message claims "TrainingRun exposes gradeObjectives/hasScenario/finale helpers", while
`git grep -n -i "finale" 91494a1 -- app/Models/TrainingRun.php` returns one comment line at `:498` and
`git grep -n -E "function [a-zA-Z]*[Ff]inale" 91494a1 -- app/Models/TrainingRun.php` returns nothing.

**Closure owed.** Either the owner rules the deletion intended, in which case the six references above go
away in a commit that says so, or the deleting session restages the path so the shared index stops
carrying a countermanding deletion. Neither is this session's act: the first is escalation 1, the second
is another session's index state, and `AGENTS.md` §11 forbids mutating it here.

**What closure would not cover.** It does not restore the 24 feature-test files in KI-84's erratum; a
deleted class and deleted suites are separate losses with separate owners, and this commit adds one path
only. It does not settle what the finale row should be *titled* (KI-82, still OPEN), nor whether the
reader belongs in `app/Services/Scenario/` at all rather than on `TrainingRun` — an Architect call, not a
Slice 21 one. And because the blob is unchanged, nothing about the reader's *logic* was re-examined here:
any defect inside it travelled with the restore untouched.
