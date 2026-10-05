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
#6E6459` (`app.css:48`). Their prior values `#FF9A2C`, `#0088E0` and `#7A7067` remain recoverable at
`git show e0e043c:docs/design-research/DESIGN.md`.

Fix options, in priority order: repoint `tools/gate.py:94` at `resources/css/app.css` and read the
`@theme static` block plus the `html[data-theme='dark']` block directly, so the allowlist tracks
the shipped theme; or regenerate `tokens.json` with `tokens.py` against the current `app.css` so
the anchor is refreshed. Not fixed here; this entry files the finding. First recorded in
`docs/research-scratch/DESIGN-CORPUS.md` section "scratch-priors.md" §2.1 (2026-10-03, priors pass;
the priors file moved there on 2026-10-03 when root `research-scratch/` was emptied).

### KI-59 The catalog detail browser case asserts a portrait absence a populated mirror cannot produce, and its sibling guard makes the pair mutually exclusive - FILED 2026-10-05 (Task B1 closure pass, from the Playwright run), CORRECTED 2026-10-05, OPEN

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
