# Known issues

**Pointer for the historical register, and still the live one. Its full text moved on 2026-10-03 to
`docs/research-scratch/AUDIT-AND-VERIFICATION.md`, section "KNOWN-ISSUES.md (defect register)":
2,655 lines covering KI-01 to KI-37, each entry naming the command or file that proves it and the
commit that closed it.** KI-38 through KI-56 sit in the same master, further down; this file carries
KI-57 onward.

**Where the older numbers live.** `KI-1` through `KI-56` are in
`docs/research-scratch/AUDIT-AND-VERIFICATION.md` ("KNOWN-ISSUES.md (defect register)"), not in this
file; this file carries `KI-57` onward. Three of the older entries are still OPEN and appear in no
status block above: `KI-24` (a stale source hash answers 200 with stale content), `KI-25` (the turn
log's horizontal scroll at phone width) and `KI-27` (`uma:fetch` reports "unchanged since last
snapshot" against a database it never wrote to). `KI-4` is RESOLVED. The master is authoritative for
those entries' current status; this line names them, it does not restate them.

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

**Status correction (2026-10-09, register pass):** KI-64, KI-71, KI-81, KI-85 and KI-88 are CLOSED
2026-10-09 on this tree; the dated claims above are left standing rather than rewritten. KI-64's fix is at
`851ed51` (the confirm step now posts the file body); KI-71's is `b494d6f` (all census files are tracked);
KI-81's is `45e7a26` (component-tested parent pick restored); KI-85's is the record itself (closure owed:
none, per the entry); KI-88's is `48e4f22` (submit controls and fixture re-anchors). Still OPEN: KI-60
(held on O-1 and the plan's `Landed` leg), KI-61 (held on O-1 and browser test re-runs), KI-63 (owner
decision owed), KI-69 (classes 1, 3, 4 remain), KI-70 (process hazard), KI-72 (FIXED but not yet on
origin/master), KI-74 (process hazard), KI-82 (title unchanged), KI-84 (owner ruling on deletions owed),
KI-87 (two queries not reconciled, label partially fixed).

**Status correction (2026-10-12, register pass):** the composition above is superseded again, and every
dated claim above is left standing rather than rewritten. This file now carries **KI-57 through KI-99**
(read from each entry's own heading and status line, not from the chain above; the two duplicate numbers
KI-70 and KI-71 are counted as the register reports them). Per each entry's own text:

- **CLOSED:** KI-57, KI-58, KI-62, KI-64, KI-65, KI-66, KI-67, KI-68, KI-71 (both the census entry and the
  Grand Concert scenario-key entry), KI-72, KI-73, KI-75, KI-76, KI-77, KI-78, KI-79, KI-80, KI-81, KI-83,
  KI-85, KI-86, KI-88, KI-89, KI-90, KI-92, KI-93, KI-96, KI-97. Two of these (KI-83, KI-86) carry a
  heading that still reads "NOT CLOSED" while their own body records the closure with a commit
  (`a3f0795` and `d7d6ed5` respectively); the body is later and dated, so the entry is classified CLOSED
  here and the heading/body divergence is named rather than silently resolved.
- **FIXED IN TREE / NOT CLOSED:** KI-98 (the `doc_census.py` resolver, fixed in the working tree, not
  landed), KI-99 (the Legacy builder's rank drop and the per-ancestor pairing, fixed in the working tree
  and standing in master's chain via the peer repair commit `6717c7c` after `ff7332c` was orphaned by the
  staging race; not landed). Both are held on **O-1** like the other unlanded fixes: register discipline
  closes an entry only when the fix is on `origin/master`, and `master` is unpushed.
- **OPEN:** KI-59, KI-60, KI-61, KI-63, KI-69, KI-70 (the first of the two — the shared-files process
  hazard; the second KI-70, E1's 44px sweep, is CLOSED above), KI-74, KI-82, KI-84, KI-87, KI-91, KI-94,
  KI-95. Each names its own closure condition; none is closed here.

No entry is renumbered. KI-59 is OPEN and is named here although it is absent from the earlier chain: its
own heading reads "CORRECTED 2026-10-05, OPEN" and it is not in any closure list above.

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

**Status correction (2026-10-19, Phase 8 documentation pass):** the composition above is superseded
again, and every dated claim above is left standing rather than rewritten. This file now carries 56
KI headings, KI-57 through KI-106, of which six numbers appear twice. Three facts are recorded here
because they are cross-references rather than new defects.

- **Six numbers carry two entries each:** KI-70, KI-71, KI-101, KI-102, KI-103 and KI-104. Each has
  one `##` entry filed 2026-10-16 and one `###` entry filed 2026-10-19. Per the register's
  convention (the 2026-10-08 block above) timing entries with the same number are reported rather
  than renumbered, and no existing entry is renumbered here. A bare citation of one of those four
  2026-10-19 numbers therefore resolves to two entries, and a reader needs the filing date to pick
  one. `PRD.md` NFR-20 now cites the 2026-10-19 entries by date and by heading.
- **The 2026-10-19 KI-101 calls the refresh case "a required Phase 1 gate per the 2026-10-18 gating
  decision". The 2026-10-18 decision names four required gates, and the refresh case is not one of
  them.** `PRD.md` NFR-20 now says so in the same terms, and this block is the register half of that
  correction. Whether the refresh case joins the required set is the owner's call, and it is not
  settled by either document.
- **NFR-20 now carries an explicit numbering**, and the register's item numbers follow it: item 3 is
  the refresh case, item 6 is background work, item 7 is loading and error state termination. The
  2026-10-19 entries KI-101, KI-102 and KI-103 already used those numbers, so no entry's text changed
  to make this true.
**Status correction (2026-10-20, Phase 8.8 close-out):** the composition above is superseded again, and
every dated claim above is left standing rather than rewritten. This file now carries 57 KI headings,
KI-57 through KI-107, of which six numbers still appear twice (KI-70, KI-71, KI-101, KI-102, KI-103 and
KI-104). KI-107 was filed by the Phase 8.8 close-out for two dead references inside the peer-owned
`CLAUDE.md`. It is the first entry in this file whose defect sits in a file this batch did not touch, so
it is recorded rather than fixed, and the fix is the peer's to make. No entry was renumbered.

### KI-57 The screenshot manifest's Race result row names a frame that does not exist, because its date ellipsis is wrong - FILED 2026-10-03 (Phase A closure pass, screenshot pipeline), CLOSED 2026-10-08

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

**Closed 2026-10-08.** The erratum is appended at `docs/research-scratch/DESIGN-CORPUS.md:4284-4289`
stating the second representative is `Screenshot 2026-07-17 235511.png`, and a second correction at
`:4291-4295` covers the Screen inventory's `S9` row. Both are dated and point at the real frame.

### KI-58 `tools/gate.py`'s hex allowlist lost its document leg silently when the design master was folded, and the guard for that condition cannot fire - FILED 2026-10-03 (Phase A closure pass, from the priors pass finding), CLOSED 2026-10-09 at `7d8b62a`, note below

`tools/gate.py:94` reads `DESIGN.md` as leg one of `load_token_hexes()`. The
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
   `docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md` §5 records C3 as `Landed` while the table it names had
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

**What stays open.** Half (c): `docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md` still records a slice as
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

### KI-62 `ProvenanceBadge.vue` derived its glyph and word once at setup, so a prop that changed in place would print the previous state's word beside the new figure - FILED 2026-10-07 (D8 hand-off), CLOSED 2026-10-09

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

**Closed 2026-10-09.** All three conditions are on `HEAD`. The two `computed` derivations are committed
in the tree (parent of `da7df2b`); the badge's own regression test — `tests/browser/provenance-badge.spec.ts`
— landed at `da7df2b` ("test(a11y): pin the ProvenanceBadge in-place update (KI-62 item 2)"); and
typecheck + build were green at filing time. What closure does not cover: the Career Timeline (SCREEN-018,
D14) remains the likely first live caller, and whether D14 adds further coverage is that slice's question.

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

### KI-64 The CSV import's Confirm step always fails validation in a browser, because `confirmImport()` posts the run's columns and never the file body - FILED 2026-10-07 (D10 hand-off), CLOSED 2026-10-09

**Closed 2026-10-09.** The fix is in the tree at `851ed51` ("fix(import): post the file body on commit and show the refusal (KI-64)"): `Import.vue`'s `confirmImport()` now posts `csv` and `turns` alongside the run columns, `ImportHistoricalRunRequest` validates the full payload, and `run-import.spec.ts` gains an end-to-end case that previews a two-row sheet, presses confirm, and asserts the redirect lands on the written run. All three closure conditions from this entry's own section are met. What closure does not cover: the end-to-end commit case writes a run, so it belongs on the scratch-database harness; the Pest suite still proves the write against a per-test database, and the browser case is tree-pinned rather than part of a full shared-harness run.

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

### KI-65 The D5/D6 wizard halves landed red at `0006b17` and carried a private copy of the parent-name resolution - FILED 2026-10-07 (D9 hand-off, from the plan's §4.1 queue), CLOSED 2026-10-09

**Symptom, as recorded.** `docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md` §4.1 item 1 and the §15 changelog row for
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

**Closed 2026-10-09.** All four closure conditions are on `HEAD`:
`CareerLegacyDeckStepsTest.php` exists and is green, `career-legacy-deck-steps.spec.ts` exists (9 passed
on the scratch harness), `SCREEN_SPEC.md` §2 holds `SCR-CAR-008` and `SCR-CAR-009` both marked
`Implemented 2026-10-06`, and PHPStan level 6 reads `[OK] No errors`. What closure does not cover: the
process gap itself (a red landing with no register entry) is closed by this entry's existence, not by
D5/D6's own record, and the four failing tests at `0006b17` were never reproduced in a separate checkout.

### KI-66 The wizard's deck step bound `:is-friend` against a snake_case prop, so the "Friend slot" chip never rendered on step 5 - FILED 2026-10-07 (D9 hand-off, browser pass), CLOSED 2026-10-09

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

**Closed 2026-10-09.** The one-line fix `:is_friend="slot.is_friend"` is on `HEAD` at
`resources/js/pages/Career/DeckSelect.vue:247`, verified by the 9 passing browser cases and the
unaffected `support-deck.spec.ts:67` before and after. What closure does not cover: the deliberate
decision not to rename the prop to `isFriend`, which would have required touching the server-side payload
shape for the `v-bind="slot"` caller on `Builder.vue:270`.

### KI-67 The race catalogue's pinned GameTora document has been withdrawn, so `uma:fetch` can no longer refresh the career calendar at all - FILED 2026-10-07 (D14a hand-off), CLOSED 2026-10-09

**Status: CLOSED 2026-10-09.** `config/uma.php:141` pinned `gametora-race-catalog` at
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

**Closed 2026-10-09.** The `manifest` block is on `HEAD` at `config/uma.php:156-160`, matching the
`ADR-0011` pattern already used for `gametora-skills`, and the fix landed at `6984b54`
("fix(fetch): resolve the race catalog URL through the publisher manifest (KI-67)"). `SourceFetcher::resolveUrl()`
now reads the manifest per run and only falls back to the pinned hash, so the stale-pin failure is over. What closure
does not cover: the robots and rate-limit note, the `characters`/`character-cards` question, the 410-row figure
that moves with a resolved hash, and the scenario-scoping work the refresh unblocks.

### KI-68 D12's Inheritance write validates inline in the controller, which is a Floor breach and leaves `StoreTurnEventRequest` as a second, disagreeing owner of the same boundary - FILED 2026-10-07 (D14a session, on the owner's D12 close-out), CLOSED 2026-10-09

**Status: CLOSED 2026-10-09.** Found while answering the D12 close-out review, not by this slice's own change.

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

**Closed 2026-10-09.** Both halves are fixed on `HEAD`: the write was moved to
`StoreInheritanceEventRequest` (`app/Http/Requests/StoreInheritanceEventRequest.php`), replacing the inline
`request()->validate()` at `InheritanceEventController.php:83`, and the docblock explicitly records that
`event_type` is set to `TurnEventType::Inheritance` by the controller itself, following `Failure`'s precedent
as a non-choice, machine-written case. `StoreInheritanceEventRequest` is now tracked at `HEAD`
(`git ls-files --error-unmatch` exits 0), and `grep -rn 'request()->validate(\|\$request->validate('
app/Http/Controllers/Career/` returns zero hits. What closure does not cover: the seven D12 feature cases
needing re-verification against the Form Request boundary, and whether `CareerInheritanceEventTest.php`
should be committed.

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
Proven both directions on a scratch DB at `.scratch-uma/cited/d11-iso.sqlite` (`migrate --seed`, served on the
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

**Class 2 closed in tree at `d36fd12` ("test(browser): stop asserting the artwork mirror is empty (KI-69 third instance)").**
Following the `catalog-detail.spec.ts` ruling at plan §4.1 item 5, `support-cards.spec.ts:172` now asserts
the shape of whichever state the tree is in: `toHaveCount(0)` on `img[src=""]` holds unconditionally (an empty
`src` is never valid per `DESIGN.md` §4.7), and the frame-shaped assertions branch on `framed.count()`
exactly as the sibling case does. KI-69 stays OPEN for its classes 1, 3 and 4; only this class is closed.

**Class 3, the fixture needs data no code path creates (7 of the 14).**
`tests/browser/career-inheritance-event.spec.ts` is **untracked**, as is the whole of D12:
`git status --porcelain` reads `??` for `app/Http/Controllers/Career/InheritanceEventController.php`,
`resources/js/pages/Career/InheritanceEvent.vue`, `tests/Feature/CareerInheritanceEventTest.php` and the
spec (KI-68 records the same status for the feature test). Every case except `:234` calls
`openInheritanceEvent()`, which at `:80` does
`select('select[name="legacies.0.legacy_id"]', {label: 'Symboli Rudolf'})`. That select is seeded from
`LegacyController::roster()` (`:308`), which reads the **`veterans` table**. Two measured facts:

- `veterans` is empty on every database in reach: `database/database.sqlite` 0 rows,
  `.scratch-uma/cited/browser.sqlite` 0, and a fresh `migrate --seed` 0. No seeder touches it
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

### KI-71 Twenty-seven career-phase files, including whole slices the plan marks Landed, exist only in the working tree - FILED 2026-10-07 (D13 session, on the owner's instruction), CLOSED 2026-10-09

**Closed 2026-10-09.** All twenty-seven census files named in this entry are now tracked in `HEAD`: the D11–D16 slices landed at `b494d6f` ("feat(career): land slices D5-D16, the career nav shell and the load-speed fixes"), committing the controllers, pages, components and tests listed in the census table. `git ls-files --error-unmatch` resolves all thirty named paths across the ten controller files, sixteen Vue/SFC files and seventeen test/spec files. `git status --short` on this working tree shows no staged or unstaged deletions of any of them, and the only untracked files are three new cockpit test files (`CockpitRunIdentityTest.php`, `CockpitTeamPanelTest.php`, `CockpitUnityCupCountersTest.php`) that postdate this entry. What closure does not cover: KI-70's process-hazard caveat still applies to any future concurrent edits, and KI-69's fixture-contract defect still blocks a full shared-harness browser run of the slices these files test.

**Status: OPEN.** A `git checkout`, a `git clean`, or any session resetting the working tree deletes these
slices outright: there is no commit to restore them from, and no CI, remote branch or bundle holds a copy.
Filed because the KI-69 attribution depends on D12's untracked state, and because a reader of plan §8
currently cannot tell which "Landed" rows are in `master` and which are in a directory.

**Census.** `git status --porcelain` names 49 untracked paths on this tree, **27** of them career-phase code
(filtered on `Career`/`career` plus `RunRaceStripTest.php`; this session's own
that file is untracked too and is not in the 27). The
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

### KI-72 The library's tag filter is case-sensitive, so a hand-typed tag is unfindable by its capitalised suggestion - FILED 2026-10-07 (D16 follow-up, on the owner's ruling), CLOSED 2026-10-09

**Closed 2026-10-09.** The fix is in the tree: `database/migrations/2026_10_08_180000_add_tags_normalized_to_veterans_table.php` adds the folded column and backfills it, `Veteran` folds `tags` into `tags_normalized` in a `saving` hook so no writer can desync it, and `ListVeterans.php:63` filters with `whereJsonContains('tags_normalized', mb_strtolower($tag))`. `VeteranLibraryTest.php:163` pins the case-insensitivity across `Speed` / `speed` / `FRONT RUNNER` / `front runner`. What closure does not cover: `ListVeterans.php:19-20`'s docblock still says the tags are "searched through `tags`", which the code no longer does; that prose is stale and left for a docs pass.

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

**Proving file.** the cockpit page snapshot ref `f2e3`,
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

### KI-71 The Grand Concert final is tagged with a scenario key no run carries - FILED 2026-10-07 (E5 hand-off), CLOSED 2026-10-09

**Status: CLOSED 2026-10-09.** Not caused by E5 and not fixed by it. It is filed here because E5 reads
the same career calendar D10 reads, and reading it whole is what made the mismatch visible. The fix below
was uncommitted at filing but is now on `HEAD` at `480b711`.

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
and reproducible from the snapshot. It was dumped to `.scratch-uma/cited/ki71-orphan-row-409.json` before the
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

**Closed 2026-10-09.** The fix is on `HEAD` at `480b711` (parser map corrected to
`'final_live' => 'our_grand_concert'`), the reparse ran (409 updated, 1 created), the orphan row 409 was
dumped and deleted, and `tests/Feature/GametoraRaceCatalogParserTest.php` carries the invariant test.
Proving commands pass on the dev file. What closure does not cover: the numbering collision (two KI-71
entries) and the class's future re-creation on any scenario key rename.

### KI-73 A leaked `php artisan serve` holding :8127 takes the default browser entry point down for every session in the worktree - FILED 2026-10-08 (E6 follow-up 2, on the owner's instruction to diagnose rather than work around), CLOSED 2026-10-09

**Closed 2026-10-09.** The port is per session: `playwright.config.ts:12` reads `PLAYWRIGHT_PORT` (default 8127), and the harness's own webserver command resolves the same port, so a leaked server on the default no longer takes every other session's entry point down. What closure does not cover: a leaked server on a session's own port is still possible; the isolation is per session, not a reaper.

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

### KI-80 The browser suite's scratch database is one shared repo path, so two sessions in a worktree cannot both run the suite and the second one's setup deletes the first one's data - FILED 2026-10-08 (E6 Grand Concert audit slice), CLOSED 2026-10-09

**Closed 2026-10-09.** The scratch database is per invocation: `tests/browser/global-setup.ts` resolves `PLAYWRIGHT_SCRATCH_DB` when set and otherwise builds `database/browser-scratch-<pid>-<ms>.sqlite`, so two sessions in one worktree cannot collide and the second cannot delete the first's data. What closure does not cover: the default path still sits under `database/`, and no `.lock` file is used, because the pid-and-ms suffix already makes the path unique per invocation.

**Status: FIXED IN TREE, NOT CLOSED.** Found while re-taking the Grand Concert browser evidence on a quiet box; not caused by that
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

**Fixed in tree at `4996877` ("test(browser): give every Playwright invocation its own scratch database (KI-80)").
`scratchDatabasePath()` now returns `database/browser-scratch-${process.pid}-${Date.now()}.sqlite` by default,
and `PLAYWRIGHT_SCRATCH_DB` names the path when set. `removeScratchDatabase()` deletes only the resolved path
plus its `-wal`/`-shm` siblings, never a glob. The `/database/*.sqlite*` `.gitignore` entry covers the new
pattern. `tests/browser/scratch-isolation.spec.ts` verifies the path carries this process's pid and that the
sweep deletes only the owned file. The register closes only when the fix is on `origin/master`, and `master`
is unpushed (`O-1`), so the entry stays held.**

### KI-75 `pint --dirty` reformats another session's uncommitted file in the shared worktree - FILED 2026-10-08 (Database reference views hand-off, on the owner's instruction), CLOSED 2026-10-09

**Closed 2026-10-09.** `AGENTS.md` §9 carries the rule, "**Never `pint --dirty` in this tree (KI-75).**", with the explicit-path replacement `pint --test --format agent <paths>` and the controller-file incident recorded. What closure does not cover: nothing enforces it mechanically; it is a written rule an agent reads.

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

### KI-76 With a Vite dev server hot, every Inertia render POSTs to the SSR endpoint with no timeout, so a page blocks 30s and answers 500 - FILED 2026-10-08 (F1 residual, on the owner's instruction), CLOSED 2026-10-09

**Closed 2026-10-09.** `.env.example:74` sets `INERTIA_SSR_ENABLED=false` with the reason, and `AGENTS.md` §18 carries the trap. What closure does not cover: `config/inertia.php` stays unpublished, so a local `.env` that omits the key inherits the package default; the fix is the documented env var, not a published timeout.

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

### KI-77 The working-tree `routes/web.php` imports seven controllers that `HEAD` does not carry, so no single slice can commit the file - FILED 2026-10-08 (Database reference views hand-off, on the owner's instruction), CLOSED 2026-10-09

**Closed 2026-10-09.** All seven controllers are tracked at `HEAD`: `app/Http/Controllers/Career/{EventDecision,InheritanceEvent,SkillsPlanner,Timeline,Result,RacePlanner}Controller.php` and `app/Http/Controllers/DatabaseController.php` each answer `git ls-files --error-unmatch`. `routes/web.php`'s imports therefore resolve against the checked-out tree, and a commit of the file records a tree that can serve its own URLs. What closure does not cover: KI-70's route-registration half is a different line in the same file and stays its own entry.

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

### KI-78 `uma:backup` changed mechanism, destination handling and timestamp zone on 2026-10-08, and two documents still state the old one - FILED 2026-10-08 (D18b hand-off, on the owner's instruction), CLOSED 2026-10-09

**Status: CLOSED 2026-10-09.** Not a defect report, and the closure owed below is paid: `PRD.md:180` and `ARCHITECTURE-ESSENTIALS.md:76` each carry a dated 2026-10-09 erratum naming `VACUUM INTO` and stating the UTC snapshot naming. The command works, the new mechanism is the safer one, and the
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

### KI-79 Eleven pages set their loading state from Inertia's `start` event, which a `Link` hover prefetch also fires, and an interrupted request never fires `finish`, so a page can announce a load that already ended and never clear it - FILED 2026-10-08 (NFR hardening pass, from the attached performance task), CLOSED 2026-10-09

**Status: CLOSED 2026-10-09.** The eleven pages that carried the block call one composable,
`resources/js/composables/useVisitState.ts`, which ignores a prefetch visit and owns the four listeners
once. The proving command now reads clean: `grep -rn "router\.on(" resources/js` returns only the four
listeners inside `useVisitState.ts` and nothing under `resources/js/pages`. What closure does not cover:
the eleven-page duplication the Owner line asks about is a review question rather than a defect, and the
browser regression spec named below was not re-run by this closing pass.

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

### KI-81 The Legacy Lab's parent pick never reached the payload, so choosing a parent saved a null pick and the Inheritance Event rendered its empty state - FILED 2026-10-09 (number reserved at `docs/research-scratch/PLANS-AND-BRIEFS.md` §"provenance-and-absence-contracts.md", approval table; was source line 441 on 2026-10-09), CLOSED 2026-10-09

**Closed 2026-10-09.** The fix is on `HEAD` at `45e7a26` ("fix(legacy): return the parent pick through the controlled select the builder reads"), which replaced the internal ref `v-model` with a `modelValue` prop emit on `AncestryNode.vue` so the parent pick reaches `Builder.vue:242`. `tests/Feature/AncestryNodePickerTest.php` (and the SFC-level `tests/browser/ancestry-node-picker.spec.ts`) pin the emit on click, the emit on a keyboard arrow, and the prop write reflected back into the select; `vendor/bin/pest --filter=AncestryNodePicker` reads green on this tree. What closure does not cover: the full choose-a-parent-then-visit-`/inheritance` path is still blocked by KI-69's fixture contract and KI-80's scratch-database sharing, so the Legacy Lab is component-tested and tree-pinned rather than browser-gated end to end.

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
`docs/research-scratch/PLANS-AND-BRIEFS.md` §"provenance-and-absence-contracts.md", approval table, for the Legacy Lab picker defect, which that
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

**Status: CLOSED 2026-10-09.** Found while dispositioning the `python tools/gate.py` failure that
KI-58 left behind. This is the **mirror** of KI-58, not a continuation of it: KI-58 was the allowlist losing
a leg and therefore rejecting colours the theme *did* declare; here the theme no longer declares the colours
the artefacts use. The gate exited 1 with ten findings across three tracked files, clean in the tree and
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
cited a `docs/research-scratch/DESIGN-CORPUS.md` that no longer exists (repointed to
`docs/research-scratch/DESIGN-CORPUS.md` in the same working pass), and `README.md:306` still describes
KI-58 as an open defect. Closure is the commit that lands these three files plus the gate exit 0 it
produces; it does not cover the other artefacts under `superseded/`, which the gate does not scan.

**Closed 2026-10-09.** The repair is on `HEAD` at `a3f0795` ("fix(gates): point the design gate at the corpus master and align three prototypes to shipped tokens"), where the four hexes were replaced at their declaration or use sites with the current value of the token that owns their role. `python tools/gate.py` now exits 0 with `GATE PASS: 3 prototype(s), all machine-checkable gates green`. What closure does not cover is unchanged from the paragraph above: the prototypes remain standalone artefacts, KI-58's dead guard is not restored, and the `README.md:306` stale reference to KI-58 stays.

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

**Disposition recorded 2026-10-09 (the text above and the first erratum are left exactly as written).** The
owner approval this entry said was missing is on record: `docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md`, its
F2 close-out dated 2026-10-09, states "All five rulings were approved as recommended (R-1 approve
retirement; R-2 move assertions and retire the flow-only remainder; R-3 split per assertion)." The 24 files
`91494a1` deleted split exactly across the two approved groups, and each disposition is executed, so the
Floor's approval requirement is met and no file is restored:

- **R-1, retired (9)** — legacy-only assertions; the flip removes the component or Blade asset and there is
  no 2.0 successor: `FlashBannerTokensTest`, `FrontendComponentLibraryTest`, `GoalPanelsOnRunDetailTest`,
  `GradePointMeterTest`, `MoodPillTest`, `ResourceStripTest`, `ResourceStripOnRunDetailTest`, `RunDeckTest`,
  `RaceCatalogPickerTest`.
- **R-2, moved (15)** — the assertion lives in the 2.0 owner and the original file is superseded:
  `GuidedFirstTurnTest`, `GuidedTurnValidationTest`, `GuidedTurnOnRunViewTest` and `RunViewErrorEnvelopeTest`
  to `CareerCockpitTest`; `GuidedStepScenarioCompositionTest` and `GuidedStepScenarioVariationTest` to
  `ScenarioPanelTest` (both cases carry `Ported from ...` docblocks); `RaceCalendarTest` and
  `RaceCalendarYearTabsTest` to `RunRaceStripTest`; `RaceEntryCirclesTest`, `RaceEntryTurnLinkTest` and
  `FreeRaceCalendarCellTest` to `CareerRaceDecisionTest`; `RunSkillPickerTest` and `RunSkillRowLabelsTest` to
  `CareerSkillsPlannerTest`; `RunViewFrameTest` and `RunViewNoScriptTest` retired with the flow (the Cockpit
  reproduces neither the pinned-region frame nor a no-script state).

Verification, run on this tree: restoring all 24 from `91494a1~1` and running them gives **174 failed / 97
passed**, every failure a `302` from the `runs.show` redirect or a missing `Runs/Show.vue`. No file is
green, so no restoration is owed; the deletions are the authorized retirement the close-out records.

The three Grand Concert guards this entry named are R-1 retirements, and their absence claim did not go
unowned: `GrandConcertPanelTest` asserts the named `scenario.panel_absence` string on the Cockpit payload,
and `ScenarioPanelTest` asserts the widget, recommendation and finale absences. The absence of the Phase B,
C and D surfaces is asserted at the panel level, which is the live owner after the run-detail screen's
retirement.

**Closure owed.** None for the file question: the approval is on record and the moves are landed. What this
correction does not do is decide the two orphan components the retirement left (`RaceCalendar.vue`,
`RacePanel.vue`, and `StatBand.vue`/`GradeBadge.vue`), which the F2 close-out names as still unclaimed.

### KI-85 Commit 822d97b carries a concurrent session's ported test case alongside the finale pickup, an attribution boundary rather than a defect - FILED 2026-10-09 (Slice 20 pickup, on the owner's ruling), CLOSED 2026-10-09

**Closed 2026-10-09.** This entry exists to make the boundary visible, as its own closure section states ("Closure owed. None."). The record is the remedy: no history rewrite was attempted, no test was deleted, and the ported case passes on the committed tree at 19 tests / 410 assertions (`vendor/bin/pest --filter=FinaleReportingTest` on this tree). The rule this entry established — every staged path gets its own blob, not only the paths suspected of being dirty — is now the working practice, and `822d97b` is the commit where it was recorded. What closure does not cover: it does not change `822d97b`; the commit is left exactly as landed. It does not rule on KI-84's ten deleted test files, which remain the deleting session's and the owner's question.

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

**Status: CLOSED 2026-10-09.** The missing class was restored at `d7d6ed5` and `HEAD` is
self-consistent again. The two counts this entry named against closure are resolved on this tree: the index
is no longer carrying a countermanding deletion, and the worktree shows the path as a clean tracked file.

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

**Closed 2026-10-09.** The restore commit is on `HEAD` at `d7d6ed5` ("fix(career): restore the finale reader HEAD's own call sites still name"), and the index is clean: `git ls-files --stage app/Services/Scenario/FinaleReader.php` returns hash `d94231a4`, stage 0, with no `D ` status line. The file is tracked in `HEAD`, the autoloader resolves the class, and `php artisan test --compact --filter "(ScenarioAdvisorFinale|FinaleReporting|TrainerAdvisor|CareerCockpit)"` reads 59 passed / 632 assertions, exit 0. What closure does not cover is unchanged from the paragraph above: the 24 deleted test files, the KI-82 title defect, and the reader's own logic are untouched.

### KI-87 The cockpit reads its finale through two queries that select different rows, and prints one scenario's client noun on all four - FILED 2026-10-09 (Slice 22 strip/reader agreement), OPEN

**Status: OPEN.** Found by writing the agreement test this slice owes, not by reading the code for a
disagreement. Both halves are pinned green in `tests/Feature/CockpitFinaleStripTest.php` at `31d4af1`,
which is why they are filed as defects rather than asserted as intent: a passing test records what the
code does, and here what it does is two surfaces of one page answering about different races.

**Half one: the two queries do not select the same rows.** The strip asks the model's own scope, and the
scope is shared-inclusive:

```text
app/Models/RaceCatalogSlot.php:130  public function scopeForScenario(Builder $query, string $scenarioKey): Builder
app/Models/RaceCatalogSlot.php:133      $q->whereNull('scenario_key')->orWhere('scenario_key', $scenarioKey);

app/Services/Scenario/FinaleReader.php:44   ->where('scenario_key', $run->scenarioKey())
```

Measured on the dev catalogue through the app's own model on 2026-10-09, `race_catalog_slots` where
`year = RaceCatalogSlot::YEAR_FINALE` holds six mandatory rows: two with `scenario_key` NULL (`URA Finals
Qualifier`, `URA Finals Semifinal`) and one per scenario (`Twinkle Star Climax` for `trackblazer`, `URA
Finals Final (Aoharu)` for `unity_cup`, `URA Finals Final (Grand Live)` for `our_grand_concert`, `URA
Finals Final (URA)` for `ura_finale`). These are engine-owned catalogue rows, written by
`app/Actions/StoreRaceCatalogSlots.php` through `PipelineRunner`, and no seeder holds them: the URA corpus
at `database/seeders/data/ura-races.json` fills `scenario_slots` through `ScenarioSlotSeeder.php`, which
is a different table. So the shared rows are the pipeline's own answer about the career calendar, and the
strip lists them as obligations still to come while the reader never sees them. One run can complete the
shared Qualifier and be told, on the same screen, that its finale has no outcome. That is the case the
third test pins.

Which reading is right is not this slice's call. `ADR-0015` makes the point that where a validator and a
renderer read the same number, both must call the same owner with the same argument; here two renderers
read the same *row set* with different arguments, and the question "is the finale one keyed row, or the
Finale year's whole block" has no answer in the repository's documents. Nothing today invents it.

**Half two: one label for four scenarios.** `FinaleReader.php:69` returns `'label' => 'Grand Concert'`
whatever the run's scenario is, and that value is what three surfaces print: the baseline row
(`ScenarioPanel.vue:266`), the advisor's proximity line (`RecommendationCard.vue`) and the Career Result
block. A Unity Cup career therefore reads "Finale: Grand Concert" and "The Grand Concert is 4 turns
away" while its own catalogue row is titled "URA Finals Final (Aoharu)", and a `trackblazer` career reads
the same noun over a row called "Twinkle Star Climax". The line's own comment at `:27-30` says the value
is hardcoded until KI-82 closes and that the upgrade path is `$slot->title`; what that note does not
cover is that the hardcoding is scenario-blind, so on the day KI-82 closes three other scenarios are
still wearing Our Grand Concert's name. Displayed copy is the Lore Guardian's C-4 territory (`AGENTS.md`
§5, escalation 3), and the fix is a per-row label or a per-scenario one, neither of which is a decision
to take inside a slice.

`tests/Feature/CockpitFinaleStripTest.php` asserts the word on a `unity_cup` run with a comment marking
it as a defect to move rather than a value to keep, so the day either half closes the test trips and
points at this entry.

**Closure owed.** One ruling on the row set (does the finale mean the keyed row or the Finale year's
mandatory rows, and which of the two queries becomes the owner both surfaces call?) and one on the label
(per-scenario noun, catalogue title, or no noun at all). The naming half of the same collision, the
`scenario.finale` versus `scenario.finale_state` key pair, is not waiting on a ruling: it is renamed in
tree by Slice 23 and is not part of this entry's closure.

**What closure would not cover.** It does not settle KI-82, which is about the stored *title* of the
Grand Concert row and stays OPEN beside this. It does not restore the coverage the deleted suites held
(KI-84). And closing the two questions here would not make the cockpit's two regions agree in *timing*:
an exhausted grid legitimately reads as reached to the reader and as no-turn-to-decide to the strip, and
that case is pinned as correct rather than as a defect.

### KI-88 The cockpit's status form renders no submit control, and thirty fixture waits still point at the record screen the cutover redirected - FILED 2026-10-09 (Tier B browser gate, on the owner's remaining-work prompt), CLOSED 2026-10-09

**Closed 2026-10-09.** Both halves are fixed in the tree and on `HEAD`. Half one (`fix(career): give the
cockpit's three header edits a submit control (KI-88)`) at `71488cb` adds a submit button per form —
"Change status", "Change scenario", "Report period" — matching the file's existing `min-h-11` button
treatment. Half two (`test(browser): re-anchor the create-redirect waits to the cockpit (KI-88)`) at
`5e1cdf2` re-points every `waitForURL` wait that still expected the pre-cutover `/training-runs/{id}`
shape to `/training-runs/{id}/cockpit$`, and routes the eight fixtures that created turns through the
deleted run screen onto `tests/utils/record-turns.ts` via `runs.turns.store`. Verified: `grep -rn
"waitForURL.*training-runs" tests/browser/*.spec.ts` shows 29 hits, all anchored to `/cockpit` or
the conditional `/(\/cockpit)?$/` pattern except three that intentionally wait for `/\/training-runs$/`
(the empty-state list page). `Cockpit.vue` carries `type="submit"` on all three header forms. What closure
does not cover: the browser suite still cannot complete a full finished career on a shared harness until
KI-88's own class-1 empty-table defect (KI-69) and KI-82's missing title are resolved, so the submit
controls are component-tested and tree-pinned rather than browser-gated end to end.

**Status: OPEN, with two independent halves.** (Dated record, retained.) Found by running the browser gates that KI-80 had held
`routes/web.php:116` makes `runs.show` a redirect (`TrainingRunController::redirect()` →
`runs.cockpit`), and the cockpit's own header forms carry no submit control:
`resources/js/pages/Career/Cockpit.vue` opens `<form @submit.prevent="saveStatus">` at `:310`,
`saveScenario` at `:325` and `savePeriod` at `:343`, and each closes (`:320`, `:335`, `:359`) holding only
its `<select>` and its `Saved.` confirmation line — no `button`, no `[type="submit"]`. `saveStatus` is
defined at `:236` and reachable only through a submit event. Measured, not read: a throwaway Playwright
probe created a run, opened `/training-runs/{id}/cockpit`, located the form by `#status`, and resolved
`form.locator('button, [type="submit"]')` to **0 elements across 32 retries**; the log is
`.scratch-uma/tierB-probe.log`. `resources/js/pages/Runs/Show.vue`, which owned the equivalent forms, no
longer exists on disk, so there is no second surface holding the control. The write route is intact
(`PUT /training-runs/{run}`, `runs.update`, `UpdateTrainingRunRequest`), and the `<p>Saved.</p>` at `:319`
proves the authors expected the request to fire. **What is not yet measured** is whether Chromium's
implicit submission still fires these forms from the keyboard: each form holds exactly one field that
blocks implicit submission, so Enter-on-select may submit where a pointer has nothing to press. The probe
stopped at the missing-control assertion, so the entry claims the rendered absence, not a proven
can't-save-it, and the 44px and axe cases cannot answer it either because a control that is not in the
a11y tree is not scanned as one.

**Half two, suite debt: 30 fixture waits still expect the pre-cutover address.** A scan of
`tests/browser/*.spec.ts` for `waitForURL(/…training-runs…\d+$/)` finds **30 matches across 22 files**;
only `career-inheritance-event.spec.ts` has been adapted, at `:87` and `:291`, to
`/\/training-runs\/\d+(\/cockpit)?$/` with its comment at `:91-92` explaining that the create redirect is
`runs.show` at some refs and `runs.cockpit` where the cutover has landed. Every unadapted fixture fails
inside its own setup, before any assertion of the screen it means to test:
`career-save-veteran.spec.ts:54` (5 failed, `.scratch-uma/tierB-save-veteran.log`),
`veteran-compare.spec.ts:53` (1 passed and 3 failed, `.scratch-uma/tierB-veteran-compare.log`),
`career-cockpit.spec.ts:237`, `career-race-strip.spec.ts:42`, and eighteen other files.
`career-training-detail.spec.ts` fails differently — six cases time out waiting for a
`Record this training` button at its `:108`, a spec the concurrent session still holds open at 26 added
and 23 deleted lines, so its result describes that session's in-flight tree rather than any commit and is
not counted as evidence here.

**Reproduce.** `PLAYWRIGHT_SCRATCH_DB=database/browser-scratch-x.sqlite PLAYWRIGHT_PORT=8181 npx
playwright test tests/browser/career-save-veteran.spec.ts` fails at `:54`; the probe recipe is in
`.scratch-uma/tierB-probe.log`. Half one is checkable without Playwright too: `grep -n "saveStatus" -A8
resources/js/pages/Career/Cockpit.vue` shows the form open, the select, a `Saved.` paragraph and a close,
with nothing that submits.

**What closing this will not cover.** Adding the control is a design decision the cutover owns: a Save
button per header form, or autosave on `change` with the `Saved.` line as the only confirmation, and the
latter changes what the 44px sweep and the axe scan measure on a page that currently carries three such
forms (status, scenario, grade-point period). It does not make the 30 waits safe to blanket-edit: at
least `run-detail.spec.ts` (four of the 30) asserts the record screen's own markup, which is deleted, so
that spec is obsolete-by-cutover rather than mis-anchored, and each of the 22 files needs a judgement
about whether its subject still exists. It does not restore the browser evidence for the surfaces whose
specs fail in setup — until half one lands, no spec can reach a finished career, which is also what
KI-82's and KI-87's cockpit claims still lack. And it does not touch KI-84's staged deletions or KI-69's
class-3 fixture contract, both separate.

### KI-89 `FinaleVocabularyTest.php`'s docblock cites `docs/proposals/Our-Grand-Concert-plan.md` §9 E8, a section no document in the tree defines - FILED 2026-10-09 (Grand Concert closing pass), CLOSED 2026-10-09

**Status: CLOSED 2026-10-09.** A documentation defect, small and real: a test's own docblock pointed a reader at a section
that did not exist, so the citation could not be followed and the provenance the test stated for itself could not
be checked.

**What the citation says.** `tests/Feature/FinaleVocabularyTest.php:16` opens the file with *"Slice 23, the
finale's payload vocabulary (`docs/proposals/Our-Grand-Concert-plan.md` §9 E8)."*

**Why it cannot resolve, in two independent directions.**

- **The named file carries no §9.** `docs/proposals/Our-Grand-Concert-plan.md`, landed at `5f3f6e0`, is structured as
  `# Phase A` through `# Phase F` with `## Slice 1` through `## Slice 24` beneath them, plus four unnumbered
  top-level sections (`# Global Out-of-Scope Rules`, `# Repository Safety Constraints`, `# Slice Completion
  Contract`, `# Current Status`). There is no `## 9`, so there is no §9 for an E8 to sit in.
- **No document under `docs/` defines an `E8` at all.** The E-numbered slice ids live in
  `docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md` and run **E1 through E6**; `SCREEN_SPEC.md` cites them in that
  same form (`plan §9 E4` at `:2827`). A grep for the word `E8` across `docs/` returns zero hits.

So the reference is not imprecise, it is dangling twice over: the section is absent from the file named, and
the section id is absent from the repository.

**The two corrections, neither taken here.**

1. **Point at a section that exists.** If the intended subject is the slice plan governing this work, the
   pointer belongs in `docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md`'s own §9 slice table — but that table has
   no E8 row either, so this correction cannot be written until its author names which slice the test actually
   belongs to.
2. **Rewrite the comment to name the shipped source.** The test's real provenance is the code it exercises:
   `app/Services/Scenario/FinaleReader.php`, the one reader behind `finale_state` on both the Cockpit
   (`CockpitController.php:678`) and the Career Result (`ResultController.php:72`), together with the plan's
   Slice 19 and Slice 20 bodies at `docs/Our-Grand-Concert-plan.md:786` and `:811`, which record the
   determination those slices reached. This correction is available today and needs no ruling.

**Reproduce.** `grep -n "E8" tests/Feature/FinaleVocabularyTest.php` returns line 16;
`grep -rn "\bE8\b" docs/` returns nothing; `grep -n "^## 9" docs/Our-Grand-Concert-plan.md` returns nothing.

**Closed 2026-10-09.** Correction #2 from the entry is applied: the docblock at
`tests/Feature/FinaleVocabularyTest.php:15-19` now cites `app/Services/Scenario/FinaleReader.php` (the one
reader behind `finale_state` on the Cockpit at `CockpitController.php:678` and `finale` on the Career Result
at `ResultController.php:72`) plus the plan's Slice 19 and Slice 20 bodies at `docs/Our-Grand-Concert-plan.md:786`
and `:811`. `FinaleVocabularyTest` passes 5/5 with 54 assertions. What closure does not cover: the two
`SCREEN_SPEC.md` gaps named below (`SCR-CAR-018`'s pre-rename payload keys and the missing
`SCR-CAR-024` state entry), both routed to the author of the rename, and the question of whether this test's
subject is a plan slice at all.

**What closure would not cover.** Correcting the comment does not land the test's subject, and it does not
touch the two `SCREEN_SPEC.md` gaps the same rename left behind: `SCR-CAR-018`'s payload list at `:2515` still
names the pre-rename `finale` and `finale_absence` keys, and the cockpit's `finale_state` row has no
`SCR-CAR-024` state entry. Both are routed to the author of the rename. Nor does it settle whether this test's
subject is a plan slice at all, which is that author's to state.

### KI-90 The recommendation card rendered nothing whenever the finale context was present, because one component declared its prop snake_case and the other bound it kebab-case - FILED 2026-10-10 (2A.6 browser verification pass), CLOSED 2026-10-10

**Status: CLOSED — fixed at `7a344a4`.** Found during the 2A.6 browser verification pass, counted
pre-existing on `HEAD` (not caused by that slice), and fixed in its own dedicated commit.

`resources/js/components/AdvisorRail.vue` bound `:finale-context` to `RecommendationCard.vue`, whose
prop was declared `finale_context` (snake_case). Vue's `camelize` converts hyphens only, so the
kebab-case attribute resolved to `finaleContext`, which never matched the declared key: the prop
arrived undefined through `attrs`, the card's `!== null` guard passed, and its template
dereferenced `undefined.label`, throwing during render. Both advisor states failed at once — the
refusal line (`career-cockpit.spec.ts:119`) and the ranked deficit line (`:263`) both asserted copy
the card drew, and the error-context DOM at failure time showed the Advisor region reduced to its
heading and standing prose.

Introduced by `ad8dc77` (2026-10-09, the cockpit cutover series), before 2A.6's commit `5eba0d0`.

**Fix.** `7a344a4` — both components renamed the prop to camelCase (`finaleContext`), so the
kebab-case binding camelizes to the declared key.

**Proving command.** `PLAYWRIGHT_PORT=8177 npx playwright test tests/browser/career-cockpit.spec.ts tests/browser/career-cockpit-forms.spec.ts` → 18 passed (6.0m) at `7a344a4`, both previously failing
cases green.

**Closure coverage.** `RecommendationCard` is imported only by `AdvisorRail`, and `AdvisorRail` only
by `Cockpit.vue`, so the browser run covers the whole blast radius.

### KI-91 The Skills planner has no link into it: the surface that carried the only entry point was retired and nothing replaced it - FILED 2026-10-10 (R2-07 copy sweep), OPEN

**Status: OPEN.** Found while retargeting the instructions that named the retired run record screen. Not
caused by that sweep, and not fixed by it.

`routes/web.php:158` declares `runs.skills.planner` and `SkillsPlannerController::show()` renders it, and
the screen works: `CareerSkillsPlannerTest` (19 cases) and `SkillSpendCoverageTest` (4 cases) exercise its
props, its write and its refusal states. Nothing in `app/` or `resources/` generates a link to it. The one
entry point the shipped tool had was the 2.0.0 record screen's header (`SCREEN_SPEC.md` SCR-CAR-016 still
carried that sentence until `380350e`), and `F2` retired that screen behind a redirect while giving the
Skills planner no successor door: the Cockpit's action grid is documented as deliberately not growing an
entry for it (`SCREEN_SPEC.md`, SCR-CAR-016: "that grid is the turn's decisions, and skills are
build-level").

A Trainer who is told "skills are marked on the Skills planner" — which `ResultController.php:176` now
says, accurately — has no way to get there from inside the application.

**Proving command.** `git grep -n "runs\.skills\.planner" -- app resources routes` → one hit, the route
declaration itself; no `route('runs.skills.planner')` call exists in any controller, component or layout.
Cross-check: `git grep -ln "skills_planner_url" -- app resources` → no output, so no screen carries the URL
as a prop either.

**What remains open.** A one-link affordance, whose owner is the Architect: the natural door is the
Cockpit's build-target region or the career bar, not the action grid, and the decision belongs with
`ADR-0020` §1 rather than with a copy pass. Until then the route is reachable only by URL.

### KI-92 Three Cockpit 44px sweeps size every control the DOM holds, so a collapsed disclosure's button fails them as a zero-height target - FILED 2026-10-10 (slice 11 browser verification), CLOSED 2026-10-10 (slice 14, `dc8ad7b`)

**Status: CLOSED.** Filed OPEN from the slice-11 browser run; closed by the sweep fix in `dc8ad7b`, which the
**Closure** section at the end of this entry records. The finding below stands as it was filed.

Found while verifying the scenario strip's heading rename in `tests/browser/scenario-panel.spec.ts`.
Not caused by that rename, and not fixed by it: the change adds no interactive element, so the sweep's target list
is the same list it had before.

The case `keeps the 44px floor across the cockpit and reflows at 320 px` sizes `page.locator('main a, main button')`
by index and requires each box to reach 44px (`scenario-panel.spec.ts:168-174`). The run-delete door is a
two-step disclosure: its `<summary>` reads "Delete this career" and its `<button type="submit">` sits inside the
`<details>` that opens it (`resources/js/pages/Career/Cockpit.vue:661-684`, the button carrying `min-h-11`). While
the disclosure is closed that button is in the DOM with no box, so `boundingBox()` answers null, the sweep reads
height 0, and the case fails on `control 17`. The failing index is not a guess: the accessibility listing captured
at the failure names 17 interactive controls inside `main` before the door (the career bar's two, the header's
status and scenario pickers, the two download links, the first-turn link, the seven action-grid links, the race
strip's Cockpit door, then the Timeline and Result doors), and the door's own summary is listed as a generic
disclosure rather than a button.

The dates say which change broke it. The sweep was written by `6a74f85` (2026-10-08). The delete door arrived with
the surface cutover in `91494a1` (2026-10-09, "migrate legacy run controls to 2.0 surfaces"), which put a
`<details>`-held submit button on the page the sweep reads. Nothing has re-run this file's sweep between then and
now, so the failure surfaced a day late rather than at landing.

The application is not the defect and must not be changed for it: the confirm button is a 44px target as soon as a
Trainer can see it, and hiding a destructive confirmation behind a disclosure is the behavior `F2` ruled. The
instrument is the defect, and this repository already settled the shape of the fix. `ura-panel.spec.ts:206-223`
walks the same selector, `continue`s on a control that `isVisible()` rejects, says why in the comment above the
skip, and then guards the skip so it cannot become a way to pass on nothing
(`expect(measured, 'no control was visible to measure').toBeGreaterThan(8)`). Two other specs already filter the
same way for the same reason: `career-race-planner.spec.ts:197` keeps `control.placed`, `preferences.spec.ts:241`
keeps `height > 0`.

**Proving command.** `PLAYWRIGHT_PORT=8255 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/cited/browser-8255.sqlite npx playwright test
scenario-panel.spec.ts` -> 7 passed, 1 failed, the failure reading `control 17 is not sized to the 44px contract`,
expected `>= 44`, received `0`. Reproduced three times in one session, at two different line numbers of the same
file, which rules out a host-load flake.

**What closure does not cover.** The same unfiltered shape is in three more Cockpit sweeps:
`grand-concert-panel.spec.ts:112`, `trackblazer-panel.spec.ts:226` and `unity-cup-panel.spec.ts:192`. Only
`scenario-panel.spec.ts` was run here, so those three are listed by the shape of their code, not by a measured
failure; a closure that edits one file leaves the other three. `database.spec.ts:273` shares the selector and was
not run either: its three views hold no `<details>` of their own (`resources/js/pages/Database/` answers none), but
whether a component they mount carries one is unverified. Whichever remedy is taken, the `ura-panel` guard belongs
with it: a sweep that skips unplaced controls has to keep asserting that it measured something.

**Closure.** `dc8ad7b` (2026-10-10, slice 14). The rule moved to `tests/utils/tap-targets.ts`, next to `buildAxe`,
exporting `expectTapTargets(targets, minimumMeasured)`: it measures every control `isVisible()` accepts, skips the
rest with the reason in the module's own docblock, and asserts how many it measured so the skip cannot become a way
to pass on nothing. Five specs now call it and lost their own copy of the loop — the three named above plus
`ura-panel.spec.ts`, which had already hand-rolled the same skip and keeps its `> 8` guard as `minimumMeasured = 9`.
`career-cockpit-forms.spec.ts` gained the case that keeps the skip honest: it opens the run-delete door and asserts
the floor on what the disclosure reveals, so a control the page-wide sweep now skips is still measured where it
becomes a target. No floor was removed or lowered; a visible control under 44px still fails.

Evidence: the six affected specs together 53 passed (25.9m) on `:8262` with its own scratch database, including
every case this entry predicted would fail (`scenario-panel.spec.ts:162`, `grand-concert-panel.spec.ts:107`,
`trackblazer-panel.spec.ts:226`, `unity-cup-panel.spec.ts:189`) and the converted `ura-panel.spec.ts:200`. Ad-hoc
`tsc --noEmit` over the helper and all nine specs: exit 0, because `npm run typecheck` cannot see `tests/` at all
(see the coverage gap below).

**What closure does not cover.** Three sweeps keep the latent shape and are only signposted, not converted, because
their pages hold no `<details>` today: `database.spec.ts:273`, `career-build-target.spec.ts:170` and
`career-trainee-select.spec.ts:174`. Each now carries a one-line comment naming the helper, so the next author to
put a disclosure on one of those pages is told what to do. The second remedy this entry listed — opening every
disclosure before measuring, which is what `career-training-detail.spec.ts:326` does — is not what was chosen: the
page-wide sweeps skip instead, and the opened-disclosure coverage lives in the one positive case rather than in
every sweep.

### KI-93 No npm script type-checks `tests/`, so every browser-spec commit has reported a typecheck gate that could not see its own change - FILED 2026-10-10 (slice 14), CLOSED 2026-10-10 (slice 15, `b558a97`)

**Status: CLOSED.** Filed OPEN from slice 14, whose own typecheck had to be run by hand for exactly this
reason; closed by widening the config in `b558a97`. The finding below stands as it was filed.

`tsconfig.json:15` reads `"include": ["resources/js/**/*.ts"]`, and `npm run typecheck` is `tsc --noEmit` against
that config (`package.json`). Everything under `tests/` is therefore outside the gate: the 48 spec files in
`tests/browser/` (counted at this writing) and four shared modules in `tests/utils/` (`accessibility.ts`,
`delete-run.ts`, `record-turns.ts`, `tap-targets.ts`). Playwright transpiles those files at run time, so a type
error there surfaces as a failing spec
or not at all if the file is never exercised — and a commit that only changes a spec reports a green `typecheck`
gate that never read the file it changed. That is every browser-spec commit in this repository's history, including
the ones this register lists as verified.

**Proving command.** `npm run typecheck` exits 0 while its config excludes `tests/`; the same compiler run against
one of those files with the config set aside is the instrument that actually reads them:
`npx tsc --noEmit --ignoreConfig --strict --target es2022 --module esnext --moduleResolution bundler --skipLibCheck
--lib es2022,dom,dom.iterable tests/utils/tap-targets.ts tests/browser/scenario-panel.spec.ts` — exit 0, and the
`--ignoreConfig` flag is required because the root config is present. Note `--types node` fails here
(`Cannot find type definition file for 'node'`), so a checked-in tests config would have to settle how the specs'
`process` usage resolves.

**What closure needs.** Either widen the root `include` to `tests/**/*.ts` with the libs and types those files need,
or add a second script (`typecheck:tests`) so the two scopes stay legible. Not fixed in slice 14: `tsconfig.json` is
off-limits for this session by the brief's own do-not-touch list, and changing what `npm run typecheck` covers
changes the meaning of a gate every future change reports against, which is the owner's call rather than a
side effect of a test fix.

**Closure.** `b558a97` (2026-10-10, slice 15), owner-authorised. `tsconfig.json`'s `include` gained
`tests/browser/**/*.ts` and `tests/utils/**/*.ts`, and `types` gained `"node"`. That needed `@types/node`
installed as a dev dependency: the specs and `global-setup.ts` use `child_process`, `fs`, `path`, `url` and
`process`, and the package was absent from both `node_modules/@types` and the lockfile, so it was not available
transitively. Widening surfaced **25 errors across 8 files**, all fixed in the same commit: 16 were the missing
Node types (15 TS2591 + 1 TS2503 `NodeJS`); 5 were `window.__setModel` / `window.__setBadgeState`, now declared
once on `Window` by `tests/browser/fixtures/fixture-globals.d.ts`; 2 were the two fixture modules importing `.vue`
files, now covered by `tests/browser/fixtures/shims-vue.d.ts`; 2 were
`PerformanceEntry.transferSize` / `decodedBodySize` in `screen-speed.spec.ts`, narrowed by an
`entry is PerformanceResourceTiming` predicate. `npm run typecheck` then reports exit 0 with zero errors, and the
`"node"` types did not disturb `resources/js` — the risk of widening one config rather than adding a second.

**What closure does not cover.** The `.vue` shim is deliberately loose, because a `.d.ts` cannot know a
component's real prop signature without `vue-tsc`; a prop typo inside a fixture is caught by the browser run and
not by the typecheck. Closing that would mean `vue-tsc`, a dependency decision of its own. The gate now covers
`tests/browser/**/*.ts` and `tests/utils/**/*.ts` only, and it is a TypeScript gate: `tests/Feature/**` is PHP and
is outside PHPStan as well, whose `phpstan.neon` analyses `app` alone, so any TypeScript added elsewhere under
`tests/` would be outside every static gate.

### KI-94 `npm audit --omit=dev` reports one high in source-map-js, which predates this session - FILED 2026-10-10 (slice 15 dependency change), OPEN

**Status: OPEN.** Noticed while running the audit that AGENTS.md §5 requires after a dependency change. Pre-existing
and unrelated to the change that surfaced it.

`npm audit --omit=dev` exits 1 with one high: `source-map-js` 1.0.0–1.2.1,
GHSA-68fv-2mgg-jv7q, "allows event-loop denial of service through indexed source-map section offsets". It is a
production-tree entry pulled by `@tailwindcss/vite` → `@tailwindcss/node`, by `vite` → `postcss`, and by Vue's
`@vue/compiler-dom`/`@vue/compiler-sfc`. Assessment: build-time only — this application never parses a source map
at runtime, and the input would be a dependency's own map rather than anything a Trainer supplies, so it is not
reachable from the app's surface. The `--omit=dev` reading is red because Vite and Tailwind are production
dependencies here (the assets are built in the same tree), not because the vulnerability is in shipped runtime code.

**Proving command.** `npm audit --omit=dev` → exit 1, 1 high. `git show HEAD:package-lock.json | grep -c
source-map-js` → 6, the same 6 the working lockfile carries, so slice 15's `@types/node` install did not introduce
or move it.

**What closure needs.** `npm audit fix` (which advances `source-map-js` inside the lockfile) as a dependency change
with its own approval, plus a judgement on whether any production dependency consumes untrusted source maps at
build time. Not fixed in slice 15: the authorised change was `@types/node`, and moving a transitive dependency
because an audit happened to be run is a different decision.

### KI-95 The cockpit cutover deleted the record screen's import provenance line, so an imported run's header no longer names the pasted body - FILED 2026-10-10 (G2-FD4 close-out browser gate), OPEN

**Status: OPEN.** Pre-existing, introduced by `b30906a` (2026-10-09, "chore: land peer's cockpit cutover as one
coherent unit"), not by the slice whose gate surfaced it. Found while classifying the ten failures of a full
browser run; `run-import.spec.ts:124` is the one case that fails on it.

`SCREEN_SPEC.md:331` lists the run header's parts, including "import provenance line when `imported_at` is set", and
`:347` fixes its wording, `Imported {date} from {import_source}`. The value is still stored:
`ImportHistoricalRunRequest.php:221` returns `'pasted CSV ('.substr(md5($csv), 0, 8).')'`. What is gone is the
renderer. That line lived in `resources/js/pages/Runs/Show.vue`, and `b30906a` deleted the file.
`resources/js/components/career/CareerHeader.vue`, which is the run header on the Cockpit that replaced it, renders
the trainee, the scenario, year, month, turn and the resource strip, and no provenance line. Grepping the front end
for the value it would print returns nothing: `imported_display` appears nowhere under `resources/js`, and
`import_source` appears in no `.vue` file. `SCREEN_SPEC.md:324-347` still describes the record screen as a live
screen, though `routes/web.php:132` now makes `runs.show` a redirect to the Cockpit.

The consequence is narrow and real: an imported run opens on a header that does not say where it came from, and
`run-import.spec.ts:140` (added with KI-64's closure at `851ed51`, 2026-10-08) fails on the assertion. KI-64's own
closure condition, "the run's own provenance line names the pasted body", no longer holds on this tree. KI-88
recorded the cutover's other fallout (the status form's missing submit control, and thirty fixture waits pointing at
the pre-cutover address) and closed on the waits; it did not name this line.

**Proving commands.** `grep -rn "imported_display\|import_source" resources/js` returns no matches. `git log
--oneline --diff-filter=D -- resources/js/pages/Runs/Show.vue` reads `b30906a`. `PLAYWRIGHT_PORT=8231
PLAYWRIGHT_SCRATCH_DB=.scratch-uma/ki95.sqlite npx playwright test tests/browser/run-import.spec.ts --grep "commits
the previewed file"` reads 1 failed, at `:140`, while `:137`'s redirect wait and `:138`'s heading both pass, so the
write lands and only the provenance line is missing.

**Closure owed.** A commit, by whoever owns the run surface (F1/F2's redirect and write-owner work, or the owner of
the Cockpit header). Either the live header renders `Imported {date} from {import_source}` again when `imported_at`
is set, or `SCREEN_SPEC.md`'s record-screen section is retired with the line and `run-import.spec.ts:140` is
corrected to what the screen prints. What closure would not cover: it does not audit the record-screen section's
other requirements for the same loss; it does not touch KI-88's half two; and it does not change `b30906a`.

**Dated note 2026-10-13 (continuation pass, Slice B.3).** `b910c2d` prints the same `sourceLabel()` string on the
import preview, so the value the run will store is checkable *before* the write, and the browser case now reads it
there (the relocation is the second arm the closure paragraph above names). The entry stays OPEN: the written run's
header still prints no provenance line, which is what this entry is about, and no migration, route or write path
moved.

### KI-96 The wizard's ancestry case filled a `<select>` and read the rank as one field, so `career-legacy-deck-steps.spec.ts` was red for two days with nobody re-running it - FILED 2026-10-11 (Round 2 walk, W2), CLOSED 2026-10-11

**Status: CLOSED.** Filed and closed by one change, so this entry quotes no closure hash: the commit that adds
this line is the commit that lands the repair, and a commit cannot carry its own hash. The finding stands as it
was filed.

Two of the case's locators went stale on 2026-10-09, eighteen minutes apart, and only the first was ever seen.

- `5876f64` (17:54, "ancestor letter rank alongside star count") split the node's rank into the letter the
  Trainer read and the star count beside it, so `AncestryNode.vue:101-103` prints `Rank not recorded, 4★`. The
  case asserted `getByText('Rank 4')`.
- `6fa0d01` (18:12, "typed spark targets with category-conditional validation") replaced the free-text
  `Applies to` input in `Career/LegacySelect.vue` with a category `<select>` and a separate `Target`
  `<select>`, and `Speed` moved from a typed value to an option of the second. The case still called
  `getByLabel('Applies to').fill('Speed')`.

The second break hides the first because it happens earlier in the same test, which is why the Round 2 register
attributed the whole red to `6fa0d01` and stopped there. The application is not the defect: `AncestorRankTest`
pins `rank_label` to the string the node prints, and `StoreLegacySelectionRequest::assertTargetMatchesCategory()`
accepts `Stat` with `Speed`, so both halves of the write the case drives are intended behavior. The instrument
was stale, and the file went unre-run for two days.

The repair took a second sitting because the label `Stars` is not unique on this screen. `getByLabel` matches a
case-insensitive substring, so it also resolves inside each parent's `Own rank (stars)` label: two parents plus
the one Spark row is three elements. The ARIA snapshot Playwright writes at the failure
(`test-results/*/error-context.md`) settled it against the live DOM rather than against a guess, and records that
`Applies to` and `Target` each resolve to one control while `Stars` resolves to one only with `{ exact: true }`.

**Proving command.** Before: `PLAYWRIGHT_PORT=8241 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/phaseA.sqlite npx
playwright test tests/browser/career-legacy-deck-steps.spec.ts --grep "records the entered ancestry"` -> 1
failed, `locator.fill: Error: Element is not an <input>, <textarea> or [contenteditable] element` at `:117`.
After the first repair the same case failed onward at `:134` with `getByText('Rank 4')` finding no element, then
the whole file read `10 passed` at `PLAYWRIGHT_PORT=8243 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/phaseA3.sqlite npx
playwright test tests/browser/career-legacy-deck-steps.spec.ts`. `npm run typecheck` exits 0. The two sibling
specs were run to measure the divergence claim instead of inferring it from the code shape: `PLAYWRIGHT_PORT=8244
PLAYWRIGHT_SCRATCH_DB=.scratch-uma/phaseA4.sqlite npx playwright test tests/browser/legacy.spec.ts
tests/browser/career-inheritance-event.spec.ts` -> 16 passed (7.8 m), including the builder case that types
`Speed` into a free-text `Applies to` and the seven inheritance-event cases that read it as a `textbox`.

**What closure does not cover.** Three things. The two Legacy editors now diverge, and this entry did not
reconcile them: the wizard's step 4 offers category and target selects, while `Legacy/Builder.vue:363-366` and
the Inheritance Event screen still take a free-text target, so `legacy.spec.ts:234` and
`career-inheritance-event.spec.ts:118` keep their `fill()` and are correct rather than merely unfailing. Because
`category` is `nullable` in the rules and the payload stores the target string verbatim (`ADR-0010`), the
divergence is a product observation and not a defect this closure owns. Second, nothing in the tree re-runs a
browser spec when a page changes a control's type: there is no CI (`AGENTS.md` §1), and the feature tests for the
same screen stayed green across both commits, so only a deliberate browser pass could have caught this on the
day. Third, the shared worktree still carries this file's peer edit (KI-95) uncommitted, so the working tree and
this entry's commit differ by that one block by design.

### KI-97 The Cockpit's Spirit Bursts rail is hard-wired to null, so a recorded counter pair prints as an absence on every Unity Cup run - FILED 2026-10-11 (Phase C.1 investigation), CLOSED 2026-10-11

**Status: OPEN.** The root cause is proven by reading the display path end to end. It is not fixed here for one
reason named below, and the reason is the file's ownership, not the diagnosis.

Found while chasing a contradiction on the run the Phase C walk created. Run 10 holds `spirit_bursts_count = 6`
and `extreme_bursts_count = 5`: the team editor re-reads both after a reload, the scenario panel prints `Spirit
Bursts 6 / 5 Extreme`, and `CockpitUnityCupCountersTest.php:47` pins `combined_bursts` at `11`. On the same page
the header rail prints `SPIRIT BURSTS` with the value `N/A` and the caption `not yet recorded`. A value the
Trainer entered is rendered as an absence, which is the direction `AGENTS.md` §5 and `DESIGN.md` §4.2 forbid:
`N/A` belongs to what was never recorded.

The cause is one line. `ResourceStrip.vue:43` maps the `spirit_bursts` widget to the value key `bursts`, and
`CockpitController.php:316` (at `29f5fc1`) sets `'bursts' => null` inside the header's `values` array
unconditionally. The widget is composed for Unity Cup, so the box always renders.

**Why this is a defect and not a ruled absence**, three independent ways:

1. The rail's own caption names the quantity it wants. `ResourceStrip.vue:75` sets `sub = 'normal plus Extreme'`
   when a value is present, and `combined_bursts` is the already-tested producer of exactly that figure. The
   design question the Phase C register flagged (6 or 11) is answered by the shipped copy, not by a ruling.
2. The comment licensing the null states its own release condition: the values "stay absent until a column or a
   payload holds them (D-220)". Both halves are now satisfied. `2026_10_10_160000_add_unity_cup_counters_to_training_runs`
   created `spirit_bursts_count` and `extreme_bursts_count`, and the team payload carries the combined figure. The
   condition was met and the line was never revisited.
3. Nothing pins the null. `CareerCockpitTest.php:233` asserts only that Unity Cup composes
   `['team_rank', 'spirit_bursts']` - membership, not value. No test reads `header.values.bursts` at any commit.

**Scoped narrowly, because the two sibling nulls are correct.** `pragma_table_info('training_runs')` has no
`grade` and no `coin` column, so `grade_points => null` and `shop_coins => null` satisfy the comment honestly.
Only `bursts` has backing storage. This entry is about that one line, not about the whole array.

**The fix, and why it was not landed here.** `'bursts' => $team['combined_bursts'] ?? null` in the same `values`
array, where `$team` is already in scope two lines above (the adjacent `team_rank` reads `$team['rank']`), plus
one assertion in `CockpitUnityCupCountersTest` that `header.values.bursts` is `11` for the recorded pair and
still null when either counter is absent. Blocked by the repository's standing rule that a file carrying another
session's uncommitted edits is not this change's to splice: `CockpitController.php` holds a peer's +467 lines in
this worktree, and that working copy still reads `'bursts' => null` at `:317`, so the in-flight Trackblazer slice
is not fixing it either. The test was not landed alone, because a paired fix is one commit or nothing.

**Proving commands.** `git show 29f5fc1:app/Http/Controllers/Career/CockpitController.php | grep -n "'bursts' =>"`
-> `316: 'bursts' => null`, and the same grep against the working file -> `317: 'bursts' => null`.
`git show 29f5fc1:resources/js/components/ResourceStrip.vue | sed -n '43,44p;72,76p'` -> the `bursts` key mapping
and the `normal plus Extreme` caption. `git grep -n "bursts" 29f5fc1 -- tests` -> the panel assertions and
nothing for the header. Live: run 10 on the Phase C snapshot printed `SPIRIT BURSTS N/A` while
`select spirit_bursts_count, extreme_bursts_count from training_runs where id=10` returned `6, 5`.

**Closure owed.** Whoever owns `CockpitController.php` next lands the one-line value and the header assertion
together. What closure does not cover: it does not decide whether `TEAM RANK` on the same rail is correct (that
reads `$team['rank']` and was observed as `N/A` on a run that never reported a rank, which looks right and was
not tested here); it does not add `combined_bursts` to any other surface; and it does not revisit the two sibling
nulls, which this entry records as correct.

**Closure, 2026-10-11. CLOSED by the change that adds this block, so no closure hash is quoted inside its own
commit.** The gate this entry named opened when the peer landed `820a61e`; `CockpitController.php` was clean, and
the two hunks in the fix's diff stayed separate from the shop work that arrived behind it.

The fix reuses the panel's own figure rather than recomputing it, which keeps one owner for one number and is
what the entry's own reasoning pointed at: `'bursts' => $team['combined_bursts'] ?? null`. `null` survives on the
end of the expression for the case the counters test already pins - a run that stated one tally and not the
other gets no combined total, and the rail must keep printing the named absence there rather than inventing a
half count.

**Evidence.** RED first: the new feature case failed at its own assertion with `Tests: 1 failed, 5 passed`, the
failure line being `->where('header.values.bursts', 11)`. After the one-line change, the file reads
`6 passed (103 assertions)`, exit 0, and the neighbouring `CareerCockpitTest` reads `36 passed (427 assertions)`,
exit 0. Rendered evidence, because the defect was a rendered thing: `tests/browser/career-cockpit-forms.spec.ts`
already writes 6 and 5 through the team editor, so the assertion went there - the box that owns the label must
contain `11`, and it passed (`1 passed`, exit 0, on a session-private port and scratch database). The check was
then proved to be a check: restoring `'bursts' => null` made `toContainText('11')` fail at that line while
`toHaveCount(1)` still passed, so the locator is right and the assertion is not vacuous. The defect was put back
and taken out again; the final diff carries the fix.

**Gates run for the closure:** `pint --test` on both explicit paths -> passed; `phpstan analyse
--no-progress --memory-limit=1G` -> `[OK] No errors`, exit 0; `composer lore` and `composer lore-code` -> exit 0,
with no hit attributable to these lines. **Not run, and not claimed:** the full suite. `php artisan test
--compact` was refused twice by the session's safety layer, so the broad run is unexecuted and the change rests
on the two feature files and the browser case above. Nothing was weakened, skipped or deleted to get here.

**What closure does not cover** stands as filed, plus one addition: it does not re-verify the sibling
`grade_points` and `shop_coins` boxes against any column that may be added later, and it does not prove the rail
renders correctly for a run whose counters were written through paths other than the Cockpit team editor.

### KI-98 `doc_census.py`'s resolver counted live citations as dead, so `DocCitationParityTest` was red at HEAD on a tree that had no citation rot - FILED 2026-10-12 (Round 3 final regression sweep), FIXED IN TREE, NOT LANDED

**Status: FIXED IN TREE, NOT LANDED.** Found while classifying the two failures of the Level 3 sweep that followed
the Round 3 slice; neither was a regression from that slice. The ratchet bar is untouched: `DOC_CITATION_BASELINE`
still reads 708 and its pinning case still asserts 708.

The sweep ran `php artisan test --compact` at `f6f393f` and `DocCitationParityTest` failed with `784 is equal to 708
or is less than 708`. Per `AGENTS.md` §9 the failure was attributed before anything was re-run, per file and per
target, and the rise was not in the corpus's prose. Two resolver gaps in `tools/doc_census.py` counted citations to
tracked, present files as dead:

1. A ref containing a slash was tried only as written and under `docs/`, never beside the citing file. So
   `jev-inspect/docs/DESIGN.md` inside `jev-inspect/CHANGELOG.md` counted dead while the tracked `jev-inspect/docs/DESIGN.md`
   sat next to it - Markdown's own relative-link rule, unimplemented.
2. A bare name was tried in three directories (`docs/`, `docs/research-scratch/`, beside the citing file), so the
   corpus's shorthand broke for every docs area added after those three were chosen. `AGENTS.md:54` telling a
   reader to open `code-style.md` counted dead, and so did `docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md`, `docs/research-scratch/DESIGN-CORPUS.md` section `## design-2.0.md`,
   `docs/research-scratch/PLANS-AND-BRIEFS.md` section `## screen-spec-2.0.md`, the `docs/scenarios/` guides, the `docs/audits/` rounds and the `screenshot-notes/` files -
   all tracked, all present. Gap 2 was already acknowledged once in the function's own docstring for
   `GOVERNANCE.md`; the same class had simply grown past the three directories.

**Evidence that this is a false positive and not a re-ruled record.** Every one of the 127 hits the two rules
remove names a tracked markdown file that exists at the natural relative path, and all 27 distinct bare names the
widened rule resolves match exactly one tracked file, so nothing resolved by accident. The genuinely absent targets
stay dead where they must: \`docs/research-scratch/DESIGN-CORPUS.md\` is still counted 61 times, RAW-FINDINGS.md,
SKILLS-GAPS.md, `docs/research-scratch/GOVERNANCE.md` and `docs/research-scratch/GOVERNANCE.md` all still count, because no tracked markdown
carries those names. Measured with the corrected resolver: 556 at `ee93b38` (the commit that set the bar, whose own
measurement therefore carried ~152 false positives), 642 at `f6f393f`, 657 in this worktree before this entry was
written. This entry's own lines name 8 absent targets as evidence, records the gate tolerates by design, so the
register now reads 665. On the pre-fix
resolver the same `f6f393f` tree reads 735. The gate was never over the bar for a real reason; the crossing commit
was not pinned, the per-commit sweep did not complete before this diagnosis made it moot.

**The fix.** Both rules in `dead_links()`: a slash-bearing ref is tried beside its citing file, and a bare name
resolves when a tracked markdown file of that name exists anywhere. `ponytail:` the bare-name rule keys on the
name, not the path, so a deleted docs/foo/README.md would read live via root README.md; upgrade path is to
apply the fallback only where the basename is unique in the tracked set.

**Proving commands.** `python tools/doc_census_selftest.py` -> exit 0, and the same against `git show
HEAD:tools/doc_census.py` copied to `tools/.doc_census_head.py` -> `AssertionError: a live bare-name citation counts
dead`, so the check fails when the resolver regresses rather than passing by not looking. `python
tools/doc_census.py` -> `dead markdown links: 665 (642 GONE, 23 UNTRACKED)`, and
`vendor/bin/pest tests/Feature/DocCitationParityTest.php` -> 2 passed, exit 0.

**What closure does not cover.** It does not lower or raise the 708 bar, and it does not make the remaining 636
GONE lines correct - most are dated provenance inside masters naming sources they absorbed, tolerated by the gate's
own header, and repointing them is still Task 5 of the `PROCESS-PLANS.md` section named PLAN-DOC-SYNC-2026-10-02. It does not resolve the
21 UNTRACKED lines, whose remedy is committing the masters they name (docs/Trainer-Desk-2.0-G-Prompts.md,
`docs/research-scratch/TRAINING-RUN-IMPORTS.md`), and those files are another session's in-flight work in this
shared worktree. It does not re-measure `AGENTS.md`'s §3 documentation map against the new resolver, and it leaves
`composer docs`' other sections (master line counts, inbound citation counts) exactly as they were.


**Erratum 2026-10-19 (Phase 8 documentation pass):** the status banner above reads FIXED IN TREE,
NOT LANDED, and that is now out of date. The resolver fix this entry describes landed in `c9fcc51`
(2026-10-12, "fix(docs): close the citation resolver's two false-positive classes"), which is in the
tree. The two false-positive classes are closed. What the entry leaves open is the UNTRACKED class it
names below - commit the target, or withdraw the pointer - and that class is counted by the gate by
design, not excluded: the gate compares the whole dead-citation count against the 708 baseline, and
GONE and UNTRACKED are two labels on the same total. The reconciliation of the three reported gate
counts is the ledger's Section 39.
### KI-99 The Legacy Lab builder's confirm dropped every recorded rank letter and grandparent detail on a routine save, and the write boundary could pair an empty grandparent's Spark list against the other grandparent's Sparks - FILED 2026-10-13 (remaining-screens UI audit fix pass, Slice 1.1), FIXED IN TREE, NOT LANDED

**Status: FIXED IN TREE, NOT LANDED.** Found by the remaining-screens UI audit (`docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" finding F-E1) and fixed in the same pass. Held on **O-1** with the other unlanded fixes: master is unpushed, and register discipline closes an entry only when the fix is on `origin/master`.

Two defects, one column. The builder (`Legacy/Builder.vue`) was a narrower second copy of the wizard's ancestry editor: it never passed `rank_label` to `AncestryNode`, so a run whose `legacy_selection` carried a recorded letter rank printed "Rank not recorded" (`AncestryNode.vue:77`'s default), and its form re-posted only `legacy_id`, `rank`, `is_guest`, ancestor names and the parent's own Sparks. `StoreLegacySelectionRequest::payload()` maps every unposted key to null and `LegacyController::update()` replaces the `legacy_selection` column whole, so **confirming from this screen silently dropped `rank_letter`, `ancestors_rank_letters`, `ancestors_rank` and `ancestors_sparks`** - the same run rendered `Rank B+` from the wizard's step and `Rank not recorded` from the Lab, and a save erased what the second reading named. The wizard's own screen was not the source: it posts every key.

**The second defect is a data-corruption path, not a display one, and it predated the builder.** `payload()` paired the per-ancestor lists with `array_values()` over `validated()`'s output. Laravel assembles nested wildcard arrays leaf by leaf, and the probe (`StoreLegacySelectionRequest` run through `validateResolved()` with `ancestors_sparks = [[spark], []]`) returned `{"1":[],"0":[{...}]}`: the key-to-value mapping is correct but the iteration order is not, so `array_values()` re-indexed the empty list into ancestor A1's slot and stored grandparent A2's absence against A1's Sparks. Any writer posting one populated and one empty grandparent list - which is the wizard's own shape whenever a Trainer reads a Spark for one grandparent only - could store the pair misaligned. The letters and star counts went through the same `array_values()` pattern.

**The fix** (`ff7332c`; SHA corrected 2026-10-13, same day: that commit holds the fix's exact tree but was orphaned by the same shared-index race that mislabelled `44d956c`, and the fix's content stands in master's history through the peer repair commit `6717c7c`, all four blobs blob-identical): the builder declares, seeds and posts all four keys, passes `rank_label` through, and carries the wizard's per-ancestor rank and Spark editors (category-filtered targets, so the `assertTargetMatchesCategory` boundary check runs on this screen too); and `payload()` pairs all three per-ancestor lists **by key** instead of by iteration order, which covers the wizard and the `StartCareerRequest` re-run through `StoreDraftLegacyRequest` (both extend the one request).

**Proving commands.** `php artisan test --compact --filter="(LegacyLabPageTest|AncestorRankTest|GrandparentRanksTest)"` -> 25 passed (278 assertions), including a new full-shape round trip ("keeps the letter ranks and grandparent details a builder-shaped save carries") that fails on both the pre-fix builder payload and the pre-fix `array_values()` pairing; `php artisan test --compact tests/Feature/CareerLegacyDeckStepsTest.php tests/Feature/CareerInheritanceEventTest.php` -> 29 passed (600 assertions). `vendor/bin/pint --test --format agent` on the four paths: clean. `composer lore`: exit 0, no hit naming a changed file.

**What closure does not cover.** It does not restore anything: a `legacy_selection` column already narrowed by an earlier builder confirm lost its rank keys for good, and nothing in the tree or the backups distinguishes an erased record from a never-recorded one, so repair is out of scope here. It does not exhaust `validated()`'s ordering behaviour beyond the three lists this request pairs - any future wildcard-in-wildcard list paired positionally should go by key - and it does not re-run `tests/browser/legacy.spec.ts` (the builder's new ancestor editors are worded so the spec's pinned locators still resolve to the same controls, but the browser pass that proves it on a rendered page is owed to the slice's Level 2). It does not touch `LEGACY`-adjacent read surfaces that print the stored shape.

## KI-100 Twenty-two stored career objectives state a requirement this tool cannot read, and the display says so rather than guessing the client's words - FILED 2026-10-13 (trainee_goals slice 4, on the owner's ruling 4a), OPEN

**Status: OPEN.** Found while building the ladder (`ADR-0026`, PRD FR-A-10), and disclosed on the surface
from the first commit: an unread row prints its race and its deadline and names its own requirement as
undecoded. The owner's ruling 4a chose that over an inference and asked for the register entry, on the
grounds that a gap disclosed only in a widget is a gap the repository does not track.

**The defect is a coverage gap in the source's codes, not a wrong number.** Two classes, measured on a seeded
scratch database from the committed body `database/seeders/data/ura-objectives.ead4bbfe.json`:

- **17 stored rows** are race objectives whose condition is `cond_type 1` (a placing is required) with
  `cond_value 2`. Across the whole document the same class is 31 rows, plus one at `cond_value 4`. Our
  captures attest three figures only - `1` → "Place 1st", `3` → "Place top 3", `5` → "Place top 5" - and
  the client form for a figure of 2 or 4 is nowhere in this repository.
- **5 stored rows** carry `cond_type 2` (Oguri Cap, Agnes Digital, Tamamo Cross, Smart Falcon,
  Matikanetannhauser), a condition kind no capture or guide line in the corpus explains; 10 such rows exist
  in the document, plus one at `cond_type 12`. `TraineeGoalType::Undecoded` is the row's state, and
  `TraineeGoal::requirementPhrase()` returns null for it.

**Why nothing was inferred.** `AGENTS.md` §5 ("Never invent a client string") and D-20 bind the display path.
"Place top 2" is the pattern's obvious next term, and the whole reason §5 exists is that this corpus has been
burned by plausible completions - the Unity Cup burst has two published names, the fan ladder has two
captured phrasings ("Earn 3000 fans" against a second-hand "Have at least 5000 fans"), and `va_en` was read as
a dub cast for a month. A wrong client string on 17 rows is worse than an opaque row, because the opaque row
tells the Trainer to go and read the screen.

**Proving commands.** `php artisan test --compact --filter=GametoraUraObjectivesParserTest` -> the case
"keeps an unread condition as a row rather than inventing what it asks for" pins 11 undecoded rows across the
document's 1,701 objectives and their condition types. On a seeded scratch file
(`DB_DATABASE=.scratch-uma/<session>/tgoals.sqlite php artisan migrate:fresh --seed`),
`select goal_type, required_placement, count(*) from trainee_goals where goal_type='Undecoded' or
(goal_type='Placement' and required_placement not in (1,3,5)) group by 1,2` -> 17 rows at placement 2 and 5
undecoded rows. Absence of a capture: `git grep -i -e "Place top 2" -e "Place 2nd" -e "Place top 4" -- docs`
-> no hits.

**What closure does not cover.** Closure needs either a `[Global]` client capture of one of these 22 objectives
- a goal banner reading the figure-2 form, which would settle the whole class by the same
Tier-B-needs-A-or-S rule that confirmed the other three - or a written owner ruling to print an inferred
string. It does not cover the *semantics* of `cond_type 2` either way: a capture of the sentence is not an
explanation of the condition, and a row whose kind is unread stays `Undecoded` even once its wording is known.
This entry files neither the "Place 1st" versus "Place top 1" variant nor the "Earn N fans" versus "Have at
least N fans" variant; both are wording conflicts inside a class already decoded and are recorded in
`SCREEN_SPEC.md` §7 by the documentation slice that this instruction excludes.

## KI-101 The browser harness serves `public/build` without a freshness check, so a Vue change that crashes on first render can pass the browser gate - FILED 2026-10-16 (scenario-field-metadata wiring close-out), OPEN

**Symptom.** `tests/browser/global-setup.ts` boots its server against whatever bundle `public/build`
currently holds and never compares it against the sources. A Vue change that lands after the last
`npm run build` is invisible to the whole browser suite: the specs exercise the old bundle and report
green (or report failures that read as product defects) while the changed code has never run in a browser.

**Evidence.** The cockpit-wt merge (`53e491a`, 2026-10-16 01:44) landed
`register('spirit_bursts', SpiritBurstsPanel)` in `resources/js/components/scenario/ScenarioPanel.vue`
against `spirit_bursts` — a widget key, not a panel flag — so the panel-shaped component received the
widget shape and its template read `props.scenario.alerts` off undefined. Every Unity Cup Cockpit render
crashed with `TypeError: Cannot read properties of undefined (reading 'alerts')`. The crash was invisible
to every session's browser gate until the scenario-field-metadata wiring pass (2026-10-16) rebuilt
`public/build`, whose first Cockpit walk produced the stack recorded in that pass's report, §8. Feature
tests could not see it: they assert payloads, not renders.

**Proving commands.** With the stale bundle in place and the registration present,
`PLAYWRIGHT_PORT=8141 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-8141.sqlite npm run test:browser -- tests/browser/career-cockpit-forms.spec.ts -g "without a runtime error"`
passes against the old bundle and fails after a rebuild — the bundle, not the tree, decides the outcome.
`ls -la public/build/manifest.json` next to `git log -1 --format=%ci -- resources/js/` states the
staleness directly: a manifest older than the newest source file is the condition.

**Smallest fix.** Have `tests/browser/global-setup.ts` refuse to run when the bundle is stale rather than
rebuild unconditionally: compare `public/build/manifest.json`'s mtime against the newest mtime under
`resources/js/**`, and abort with a one-line message ("public/build is older than resources/js; run
npm run build") before any server boots. A rebuild inside the harness would serialise every concurrent
session behind one Vite build, which is the KI-70 hazard the nothing-that-writes rule exists for; a
`--fresh-build` opt-in flag is the second option and costs every session the build's wall time, while the
refusal costs only the session that actually needed the rebuild.

**Who it impacts.** Every session running the browser suite on this shared tree after any Vue change: the
gate reports the stale bundle's behaviour rather than the working tree's, and a first-render crash lands
silently.

**What closure does not cover.** Closure is the staleness check landing in `tests/browser/global-setup.ts`
(a protected path, so an owner-approved edit) and the first green post-check run of the cockpit specs. It
does not cover the KI-70 tension beyond what the refusal shape already answers, and it does not
retroactively gate any run made before the check lands — including every green browser line recorded
against a stale bundle in this file's earlier entries.

## KI-102 `.env.example` cannot be parsed by the repo's own dotenv reader and ships MySQL defaults into a SQLite-only project, so a fresh clone's first boot dies twice - FILED 2026-10-16 (close-out pass, on a prior pass's measurements), OPEN

**Symptom.** A fresh clone follows the standard bring-up — copy `.env.example` to `.env`, then
`composer install` — and the install dies in `package:discover`, because the first artisan boot parses the
copied `.env` and the parse throws. A fresh worktree that gets past that dies again at
`migrate --seed`, on DNS resolution for a database host that does not exist.

**Cause.** Two independent defects in `.env.example`, a file nothing in this repository loads and every
fresh clone copies:

1. Line 1 is `APP_NAME=Trainer Desk` — an unquoted value containing a space. The repository's own dotenv
   parser (vlucas/phpdotenv, shipped in `vendor/`) rejects it.
2. Lines 25-30 declare `DB_CONNECTION=mysql`, `DB_HOST=mysql`, `DB_PORT=3306`, `DB_DATABASE=laravel`,
   `DB_PASSWORD=password` — a MySQL/Sail default set, in a project that is SQLite-only by contract
   (`AGENTS.md` §1) and whose own comment above the block ("Local fallback database (used when
   Docker/Sail is unavailable). Under Sail, DB_CONNECTION=mysql") states the relationship backwards.

**Evidence.** The parse failure reproduces in one line against the vendor copy of the parser, no app boot
needed: `php -r 'require "vendor/autoload.php"; Dotenv\Dotenv::createImmutable(getcwd(), ".env.example")->load();'`
→ `Dotenv\Exception\InvalidFileException: Failed to parse dotenv file. Encountered unexpected whitespace at [Trainer Desk].`
The DNS consequence was measured in a fresh worktree bring-up (a prior pass, recorded in this file's
session notes as `getaddrinfo for mysql`): `migrate --seed` under the copied defaults cannot resolve the
host named `mysql`. The file contents themselves are the config-level evidence for both halves.

**Fix options.** Three, in ascending scope: (a) quote the value — `APP_NAME="Trainer Desk"`; (b) replace
the MySQL default block with the SQLite triple this project actually runs
(`DB_CONNECTION=sqlite`, `DB_DATABASE=database/database.sqlite`, the rest removed), keeping any Sail note
as a comment; (c) split the file into `.env.example` (SQLite, parseable) and `.env.mysql.example` for
anyone who genuinely runs Sail. **Recommendation: (b) alone** — it fixes both the parse surface (nothing
to quote wrongly) and the DNS death, and (c) manufactures a second file for a deployment path
(`AGENTS.md` §12: no hosting, no public deploy) this repository does not have. Not implemented here; the
file is one owner edit and the choice of shape is the owner's.

**Who it impacts.** Every fresh clone, every fresh worktree, every CI bring-up — the first two commands
of the standard setup both die before any of this repository's own code runs.

**What closure does not cover.** Closure is the owner's chosen shape landing in `.env.example` and one
clean `cp .env.example .env && composer install` on a machine with no MySQL host. It does not cover the
Sail/MariaDB question for contributors who run one anyway (that is what option (c) would have named),
and it does not cover `.env.testing` or `phpunit.xml`, which are separate files with their own
agreements (`AGENTS.md` §18).


## KI-103 `SetupDraft::saved()` reaches six wizard pages and none of them declares the prop, so the saved half of the step bar is dead code and two written rules about it disagree - FILED 2026-10-16 (findings capture pass), OPEN

**Symptom.** The layout that draws the wizard's step bar declares an optional `saved` prop and ORs it
with `visited` when deciding whether a step reads as done. Six pages bind it, and not one of the six
declares it in its own `defineProps`, so under `<script setup>` typed props the read is `undefined` and
the `saved` disjunct can never be true. The marking still looks correct, because `visited` alone drives
it — which is exactly why nobody noticed: the feature is dead, not visibly broken. The disagreement is
the real finding, because two records describe opposite rules for the same pixels.

**Evidence.** The consumer declares and uses the prop: `resources/js/layouts/SetupLayout.vue:28` is
`defineProps<{ step: number; visited?: number[]; saved?: number[] }>()` and `:61` computes
`props.visited?.includes(n) === true || props.saved?.includes(n) === true`.

Six pages bind the read, and a declaration search across all of `resources/js` for `saved:` or `saved?:`
returns one hit in a page prop block and it is not any of these six (`SaveVeteran.vue:70`, an unrelated
`{ tags, notes }` shape):

```
resources/js/pages/Career/ScenarioSelect.vue:113   :saved="props.saved"
resources/js/pages/Career/TraineeSelect.vue:222    :saved="props.saved"
resources/js/pages/Career/BuildTarget.vue:302      :saved="props.saved"
resources/js/pages/Career/LegacySelect.vue:235     :saved="props.saved"
resources/js/pages/Career/DeckSelect.vue:229       :saved="props.saved"
resources/js/pages/Career/Preflight.vue:152        :saved="props.saved"
```

Step 1's own declaration block, immediately above its read, names four props and no fifth, and the
controller does send the value (`ScenarioSelectController.php:51`, `'saved' => SetupDraft::saved()`):

```ts
const props = defineProps<{
    scenarios: ScenarioCard[];
    visited: number[];
    selected: string | null;
    ruleset: { value: string | null; title: string };
}>();
```

`:visited="props.visited"` on the same line is declared and therefore live, which is what makes the
asymmetry the finding rather than a styling choice. And the two rules that disagree about what the
marking should mean are `SetupDraft::saved()`'s own docblock, "Only steps with actual saved data show
'done'" (slice 4.1), against the owner ruling of 2026-10-13 recorded in `SetupDraft::visited()`,
"'completed' means visited, not written to the draft". The dead prop means the ruling wins by accident
rather than by decision.

**Why no gate catches it.** `npm run typecheck` is `tsc --noEmit`, which does not type-check SFC
templates; it exited 0 in the worktree pass with this line present, and it would exit 0 either way. The
feature test asserts the payload (`saved` reaches the page) and the browser spec asserts rendered step
marking for *navigation*, not for the saved set, so neither end is covered. This is the same blind spot
KI-93 recorded for `tests/`, now demonstrated on a prop.

**Smallest fix.** The owner picks one of two, and both are one decision rather than six patches.
(a) Make the path live: add `saved: number[]` to each of the six pages' `defineProps`, six one-line
diffs, and then rule which of the two written semantics governs, because the `saved` disjunct would start
changing pixels that the 2026-10-13 ruling says are decided by `visited`. (b) Delete the dead path: drop
`saved` from `SetupLayout`'s props and its use at `:61`, drop the six bindings, and retire
`SetupDraft::saved()` plus the controllers' `saved` payload — the lazier fix, and the one that agrees
with the standing ruling, which the accident of the missing declaration currently enforces anyway. Take
(b) only after checking `saved()`'s other callers and the tests that pin it, which this pass did not
enumerate. Not applied here: all six files are wizard pages, `ScenarioSelect.vue` is peer-modified in the
shared tree, and the deselect patch still has to be rebased onto that same file.

**Proving command.** `npx vue-tsc --noEmit` on the tree (a tool this repo does not currently install —
adding it is a dependency decision, not a fix) would flag the undeclared read; until then,
`grep -c saved resources/js/pages/Career/ScenarioSelect.vue` returning `1` next to a
`SetupLayout` that declares a `saved` prop is the check.

## KI-104 The Cockpit nested two `Edit team state` disclosures under one label, so the team-editor browser cases hit a strict-mode collision; the fix is written but uncommitted - FILED 2026-10-16 (findings capture pass), FIXED IN TREE, NOT LANDED

Measured 2026-10-16 05:18, from `cockpit-unity-cup-no-crash.png` and `git show HEAD:`.

**Status: fixed uncommitted, so still live at HEAD.** `git show
HEAD:resources/js/components/career/UnityCupEditor.vue` carries `<details>` at 78, the summary text
`Edit team state` at 80 and the closing tag at 168; `Cockpit.vue:757-772` wraps the same component in its
own `<details data-region="team-state">` with the same summary. Two disclosures, one label, nested. The
05:03 screenshot shows it: an outer `▸ Edit team state` box containing an inner `▸ Edit team state` box.

The uncommitted diff (file mtime 05:11:29) deletes the inner `details`/`summary` pair, makes
`<form>` the component root and adds the comment at 78-82 explaining that the disclosure belongs to the
caller. That is the right fix and it is already written, so **no other pass should re-implement it**; the
outstanding risk is only that it stays uncommitted.

**Proving command.** `git diff HEAD -- resources/js/components/career/UnityCupEditor.vue` (6+/7−) and
`git show HEAD:resources/js/components/career/UnityCupEditor.vue | sed -n '78,80p;168p'`.

**What closure does not cover.** The two `career-cockpit-forms.spec.ts` strict-mode cases at 161 and 225 were
not re-run here: the fix is uncommitted, `public/build` is shared and stale-by-KI-101, and rebuilding it
while a peer works is barred by KI-70. A pass that lands this diff should re-run
`PLAYWRIGHT_PORT=<own port> PLAYWRIGHT_SCRATCH_DB=<own path> npm run test:browser --
tests/browser/career-cockpit-forms.spec.ts` after a build on a quiet tree, and `full-career-journey.spec.ts:144`
addresses the same label and belongs in that sweep.

## KI-105 — Workflow guardrails added: session-private artifacts, database readiness, safe staging, failure classification, pre-flight (W0-guardrails) — FILED 2026-10-15, OPEN

Status: OPEN

Proves with: AGENTS.md amended with the new subsection under §9 Testing and validation; tools/gate-preflight.php added (reads only, exits non-zero if >2000ms). Pre-flight probe returns 0 on quiet box and intended to exit non-zero under contention (KI-74). Amendment cites KI-60, KI-70, KI-73, KI-74, KI-75, KI-80, KI-84 explicitly.

Closure conditions (must all hold):
- AGENTS.md contains the guardrails subsection with the cited KIs.
- tools/gate-preflight.php exists, is executable/readable, reads only, and exits non-zero above documented threshold (quiet run exit 0 with ms quoted; contention run exit != 0).
- Owner decision on O-1: settle whether origin/master is closure bar, or name integration branch and redefine closure against it (not resolved by this slice).
- Owner decision on browser harness isolation: whether playwright.config.ts absorbs port/scratch DB defaults, or AGENTS.md states as convention (not resolved by this slice).

What closure does not cover: landing KI-98, KI-99; cross-surface domain defects (KI-82, KI-87, KI-91, KI-95); creating active-issues dashboard and register validator; changing playwright.config.ts; resolving O-1. This slice is documentation-and-tooling only.

## KI-106 `1e84b0c` deleted the root stubs and left the citation ratchet red at HEAD, with 270 dead citations and `AGENTS.md` §3 still naming the files - FILED 2026-10-17 (Phase 0 tree-ownership pass), OPEN

**Status: OPEN. Landed red, so every Level 3 run from `1e84b0c` forward carries it.**

**Symptom.** `DocCitationParityTest` holds dead markdown citations at or below the `DOC_CITATION_BASELINE`
of 708 (`tests/Feature/DocCitationParityTest.php:56`). `1e84b0c` ("docs(reorg): fold root stubs and spent
plans into the corpora") deleted `CONSTRAINTS.md` and `PLAN.md` and left every citation that routes through
those names pointing at nothing: `tools/doc_census.py` now reports **980** dead links, and the two most-cited
targets are the deleted files themselves (161 `CONSTRAINTS.md`, 56 `docs/research-scratch/DESIGN-CORPUS.md`,
48 `DESIGN.md`, 45 `PLAN.md`, 35 `docs/research-scratch/GOVERNANCE.md`, 29 `docs/research-scratch/GOVERNANCE.md`,
27 RAW-FINDINGS.md, 23 `docs/proposals/Our-Grand-Concert-plan.md`). The same commit left `AGENTS.md` §3's
documentation map describing `CONSTRAINTS.md` as "a pointer to `docs/research-scratch/GOVERNANCE.md`" and
`PLAN.md` as "a pointer to `docs/research-scratch/PROCESS-PLANS.md`" - rows for files that no longer exist,
in the file that tells an agent where the quality bar lives.

**Evidence.** Measured on `5bc7e0f`, working tree clean. The census's own top-target list is what attributes
it: the eight most-cited dead targets are precisely the names `1e84b0c` removed, and they account for 424
citations between them, against a ratchet overshoot of 272. The 980 reading was taken with `AGENTS.md`
restored to `HEAD` mid-pass, so the pass's own doc edits are not what moved it past the bar.

**Proving commands.**

```text
php artisan test --compact tests/Feature/DocCitationParityTest.php   # 1 failed: 980 vs 708
python tools/doc_census.py | grep -A9 "most-cited dead targets"
git show --stat 1e84b0c | grep -E "CONSTRAINTS|PLAN.md"              # 21 and 19 deletions
```

**Two other reds found in the same sweep, pre-existing and unrelated to each other** (recorded here so a
later pass does not re-attribute them to the current work): `DashboardTest.php:54` expects 3 `quickActions`
and `DashboardController::quickActions()` has returned 4 since `04bb402`; `CatalogRosterTreeTest.php:234`
pins the catalog route at 6 queries and the shell now costs 7. Both reproduce alone, so neither is
order-dependent. `vendor/bin/phpstan analyse` reports 3 findings, all in `CockpitController.php` lines that
no commit in this range authored (`MONTHS` unused at 120, two `nullsafe.neverNull` at 291) and the plan's
Phase 6.1 owns them. `vendor/bin/pint --test` flags 5 files, none of them in the `1e84b0c..5bc7e0f` set.

**Closure.** Repoint or withdraw the 270 citations, then re-measure and lower the baseline in the same
commit, with `AGENTS.md` §3's two rows corrected in the pass that does it. **What closure does not cover:**
the `DashboardTest` and `CatalogRosterTreeTest` assertions above, which are separate defects that only share
this entry's discovery pass; the PHPStan findings owned by Phase 6.1; the fence-integrity count the census
also reports (397 invalid closing fences across 13 files), which `tools/md_fence_check.py` owns.

### KI-101 Refresh case counts two navigations for one reload, and the test source cannot say whether the second is a real load - FILED 2026-10-19 (doc-only batch, Decision 2), OPEN, BLOCKED ON OWNER TRACE

**Status: OPEN, blocked on owner trace classification.** NFR-20 item 3 ("a refresh does not loop") is a required Phase 1 gate per the 2026-10-18 gating decision in `PRD.md` NFR-20, and its case is red: `tests/browser/navigation-determinism.spec.ts:75` ("refreshing a route renders it in a single navigation") expects exactly one main-frame `framenavigated` event across one `page.reload()` and received **2** (reproduced 2026-10-19, chromium, KI-69 scratch database, `npx playwright test tests/browser/navigation-determinism.spec.ts --grep "refreshing a route"` -> 1 failed). The counter is the ambiguity: Playwright fires `framenavigated` for full-document navigations **and** for same-document (history) address changes, so N = 2 is not classifiable from the test source alone. Two dispositions: (a) **test-side** — one full reload plus one same-document address change; the fix is to count `performance.getEntriesByType("navigation")` entries with `type` `navigate` or `reload` instead of `framenavigated`; or (b) **app-side** — the reload plus a second full navigation; the fix is to trace the redundant load. Route `/training-runs` is a direct GET (`routes/web.php:125`, no redirect) and the app shell carries no Inertia version-mismatch reload, so neither disposition is proven. A browser trace of one refresh that records each event's navigation type closes the ambiguity; the classification lands here and resolution follows (a) or (b).

**Two corrections to the dispatch text.** (1) The dispatch names the file `tests/browser/refresh-navigation.spec.ts`; no such file exists at HEAD or in history, and the case lives in `tests/browser/navigation-determinism.spec.ts`. (2) The other three cases in the same file (hover prefetch, interrupted prefetch, settled navigation) pass in the same run.

**What closure does not cover.** This entry records the state of a Phase 1 gate; it does not satisfy the gate. Decision 2 closes only when the trace classification lands.

### KI-102 NFR-20 item 5 (no page request triggers background work) has no test - FILED 2026-10-19 (doc-only batch, Decision 3), ACKNOWLEDGED GAP

**Status: ACKNOWLEDGED GAP.** The 2026-10-18 gating decision in `PRD.md` NFR-20 acknowledges items 5 and 6 as known gaps; this entry is the register half of that acknowledgement, so Decision 3's "documented in PRD" and Decision 10's "recorded in KNOWN-ISSUES.md with status" point at the same pair of facts. Item 5 — a page request must not trigger fetch, snapshot, parse, normalize, match, artwork mirroring, import, or other long-running background work — has no automated termination test. Fix path: a browser case that loads a route and asserts no background command is spawned and nothing is written. Deferred to the next test-touching dispatch.

### KI-103 NFR-20 item 6 (loading/error state termination) has no test - FILED 2026-10-19 (doc-only batch, Decision 3), ACKNOWLEDGED GAP

**Status: ACKNOWLEDGED GAP.** Same acknowledgement as KI-102. Item 6 — loading and error states terminate; a failed navigation clears its loading state — has no automated test for the failed-request case. The `navigation-determinism` cases cover prefetch stranding and settled-navigation state, not a request that fails. Fix path: a browser case that fails a held route and asserts the loading state clears and no stranded status text remains. Deferred to the next test-touching dispatch.

### KI-104 ADR-0027 is proposed, not accepted: turn/decision logging has no binding decision - FILED 2026-10-19 (doc-only batch, Decision 6), PROPOSED

**Status: PROPOSED, not accepted.** Decision 6 filed `docs/adr/0027-turn-and-decision-logging.md`; a proposed ADR is not binding. The ADR is code-only: it describes the turn/decision logging design and its implementation is deferred. Closure: owner acceptance flips the ADR's status, and this entry then records the acceptance commit.


### KI-107 Peer CLAUDE.md carries two dead references to files that do not exist - FILED 2026-10-20 (Phase 8.8 close-out), OPEN, PEER-OWNED

**Status: OPEN, peer-owned.** `CLAUDE.md` line 12 cites CONSTRAINTS.md inside backticks, and line 248
cites CLAUDE.local.md inside backticks. Neither target exists: neither file is on disk, and neither is
tracked by git. CONSTRAINTS.md was folded into `docs/research-scratch/GOVERNANCE.md` in `1e84b0c`, and
CLAUDE.local.md is a user-level file this repository has never had.

**Proving command.** `grep -n CONSTRAINTS CLAUDE.md` reports lines 10, 12, 21 and 248. Only lines 12
and 248 are backticked paths, and so the only two the citation census counts. Lines 10 and 21 name
CONSTRAINTS.md as a section title inside a quoted string, not as a path, so the census does not count
them.

**Fix.** The peer resolves both the next time they touch `CLAUDE.md`: repoint the line 12 citation to
`docs/research-scratch/GOVERNANCE.md`, which is where the quality bar now lives, and drop or reword the
line 248 sentence, which is an instruction not to create the file. Phase 8 did not fix either, because
`CLAUDE.md` is peer-dirty and outside the batch's scope.

**What closure does not cover.** This entry records two dead citations in a file this batch did not
touch. It does not move the citation ratchet, which is Task 5 of the documentation-sync plan, and it is
not the cause of any gate failure: the count sits under the baseline for other reasons.

**Correction (2026-10-20, after the peer's commit `fbaa288`):** this entry was filed against the peer's
uncommitted rewrite of `CLAUDE.md`, and that rewrite landed after it was filed. Two facts in it are now
stale. The second residual moved from line 248 to line 247, and `CLAUDE.md` is no longer dirty in the
working tree, because the rewrite is now committed. Both residuals survive that rewrite: the census
reports GONE CLAUDE.md:12 -> CONSTRAINTS.md and GONE CLAUDE.md:247 -> CLAUDE.local.md. The entry stays
OPEN and the fix is still owed by the peer, who owns the file. The dispatch ledger's Section 41 carries
the pre-rewrite line number.

### KI-108 `docs/research-scratch/SLICE-RECORDS.md` section `## execution-ledger-2026-10-17.md` contains NUL bytes in the working tree - FILED 2026-10-20 (citation-fix batch close-out), OPEN, PEER-OWNED

**Status: OPEN, peer-owned.** `docs/research-scratch/SLICE-RECORDS.md` section `## execution-ledger-2026-10-17.md` carries approximately 6571
NUL bytes in the working tree. All committed versions (HEAD, bd9e2e5, 4db04fd) are clean UTF-8; the NULs
exist only in the peer's uncommitted edits (git status: ` M`).

**Origin:** peer session's in-flight work. Not introduced by `bd9e2e5` — that commit is clean at its own
tree. The file was first written by `bd9e2e5` ("docs(ledger): land 3.7; record primitive-contract
findings", 89 insertions) and is tracked.

**Gate impact:** `doc_census.py` reads the ledger with `errors="replace"`, so NULs are silently replaced —
the file contributes to the census count but its NUL content is not visible to either gate. `lore.php`
uses `git grep`, which treats the file as text and can read it, but NUL-containing lines may not match
grep patterns as expected.

**Fix:** peer re-encodes as UTF-8 without BOM and commits. No agent action while the file is peer-dirty.

**Ref:** Prior dispatch Step 5; current dispatch Step 1g.

**Correction (2026-10-20, Phase 8.8 residual close-out):** two facts in this entry are wrong as filed, and
both are load-bearing.

First, the committed versions are not clean UTF-8. The blob at HEAD is UTF-16LE with a byte order mark - the
first bytes are FF FE 23 00 20 00 45 00 - 528,880 bytes carrying 270,501 NUL bytes. The same is true of
4db04fd, 33d0017, f278cd6, 6b625ed and every earlier commit, back to a1db210, which is the first commit that
touched this path. The encoding is not one commit's accident; it has been the file's stored form since the
file was created.

Second, the first commit to write this path is a1db210, not bd9e2e5.

The consequence is larger than the entry states. tools/doc_census.py reads the file with encoding="utf-8" and
errors="replace", so in a clean checkout the UTF-16LE ledger yields no readable citation at all: the census
sees 0 of the 48 distinct backticked .md references it actually carries, and 7 of those 48 are dead -
code-style.md, eloquent.md, frontend-fidelity-2026-10-13.md,
`docs/research-scratch/PROCESS-PLANS.md` section `## implementation-backlog-2026-10-17.md`,
testing-standards.md, ui-ux-application-context-brief.md, and the
.scratch-uma/dispatch-2026-10-17/implementation-backlog.md that the peer's own working-tree edit already
unbackticks. The gate therefore reports a count that is a lower bound in a second, independent way, and the
peer's planned re-encode will expose those 7 the moment it lands, raising the count by about 7 against a 708
bar.

The fix is unchanged: the peer re-encodes as UTF-8 without a BOM and commits. Repointing those 7 before the
re-encode is cheaper than after it.

**Correction (2026-10-20, later the same day, Phase 8.8 residual close-out):** the correction filed above is
wrong on its measurements, and is withdrawn on those points. It was written from a PowerShell redirection,
which in Windows PowerShell 5.1 transcodes to UTF-16LE and writes a byte order mark; the "UTF-16LE with
BOM", "528,880 bytes" and "270,501 NUL bytes" were artifacts of that redirection, not properties of the file.
Re-measured byte-exactly through git cat-file, the blob at HEAD is 263,999 bytes, starts 23 20 45 78,
carries no byte order mark, and holds 6,571 NUL bytes.

The NULs are real, and they are committed. They enter at ac9b257 ("docs(ledger): close decision 1 and 3.2/3.3,
record Step 0 audits"), which is an ancestor of origin/master and therefore already pushed, and they are
present in every commit since, including every commit of this batch. They are absent from a1db210, which is
the first commit to write this path - the one point the correction above got right, and the reason this
entry's own "first written by bd9e2e5" is also wrong.

The census consequence is the opposite of what the correction above claims. tools/doc_census.py reads the file
with encoding utf-8 and errors replace, and a NUL is a valid codepoint, so the file decodes and the census
sees 47 of the 47 distinct backticked .md references the ledger carries. Six of them are dead -
code-style.md, eloquent.md, frontend-fidelity-2026-10-13.md,
`docs/research-scratch/PROCESS-PLANS.md` section `## implementation-backlog-2026-10-17.md`,
testing-standards.md and ui-ux-application-context-brief.md - and all six are counted. The gate is not
under-reporting on this file, and the count is not a lower bound because of the encoding. A byte-exact scan
of all 167 tracked .md files at HEAD finds no byte order mark in any of them and NUL bytes in this one file
only, so the exposure is contained to the ledger.

What the corruption does cost is the file's own integrity: it is not clean UTF-8, tools/lore.php reads with
git grep and can be unreliable over embedded NULs, and any tool that rewrites the file risks dropping or
duplicating content. The fix is a re-encode commit. No agent has made one, and the file is peer-dirty.

One further correction to the record. In this repository the author and committer identity is the configured
IzzatFirdaus <extremerazr@gmail.com> for every commit, this agent's and every other session's, so the commit
metadata carries no attribution signal at all. Attribution has to come from the session transcript, never
from the commit metadata.
### KI-109 `analysis_output.txt` and `base_veteran.vue` are UTF-16LE with BOM and NUL - FILED 2026-10-20 (citation-fix batch close-out), OPEN, OWNER DECISION REQUIRED

**Status: OPEN.** Both files are UTF-16LE with byte order mark (first bytes `FF FE`) and contain NUL
bytes. git treats both as binary: `git diff --stat` and `git grep` return no matches for either.

**Gate impact:** `lore.php` uses `git grep`, so both files are silently skipped. `doc_census.py` only
scans `.md`, so neither is read by the census. Neither file is a citation target today; the impact is
future — any citation that points into either file will be counted as a dead link because git cannot
see the content.

**Fix path:** (i) if the files are diagnostic output with no ongoing value: `git rm --cached` + add to
`.gitignore`; (ii) if they are real source: re-encode as UTF-8 and check them in; (iii) if unknown
origin: hold until owner identifies. Recommended: (i) — both names suggest transient analysis artifacts.

**Ref:** Prior dispatch Step 4; current dispatch Step 1e and 1f.

**Correction (2026-10-20, dated erratum, append-only):** the "owner or orphaned"
question is resolved per file. `analysis_output.txt` (276 bytes, first bytes
`FF FE 54 00`, 137 NULs) was added by `098abab` — the agent-lineage D-268
re-home — so it is a **tracked agent artifact**, not scratch; the entry's
recommendation to `git rm --cached` it is wrong. `base_veteran.vue` (43,302
bytes, first bytes `FF FE 3C 00`, 21,646 NULs) was added by `b30906a` (the
peer's cockpit cutover), so it is peer-owned; disposition is the peer's call.
Neither file is currently read by either gate — the risk is future, when a
citation resolves into either file. No `git rm --cached` without owner review.
See §42 in `docs/research-scratch/SLICE-RECORDS.md` section `## execution-ledger-2026-10-17.md` for byte-exact
evidence.

### KI-110 `doc_census.py` reads working tree, not committed ref - FILED 2026-10-20 (citation-fix batch close-out), ACCEPTED LIMITATION

**Status: ACCEPTED LIMITATION (documented).** `doc_census.py` reads files from disk, not from git. Its
count includes uncommitted edits (peer sessions' in-flight changes) and any file present on disk but
not yet `git add`ed. Different working trees at the same HEAD produce different counts.

**Gate relationship:** the Pest gate (`DocCitationParity`) reads the same census. The gate is
authoritative only when the working tree equals the committed tree (i.e. no peer-dirty state). The
current count of 648 measures a working tree where the ledger is NUL-corrupted by peer edits — neither
this number nor the prior dispatch's 657 reflects a clean committed state.

**Documented at:** `AGENTS.md` line 190 (tools/ inventory), `README.md` lines 192-198
("The census count is host-local; the gate number is the committed one.").

**Fix path (future code dispatch):** read from a git ref instead of disk. Deferred — code change, out of
doc scope.

### KI-111 Markdown closing fences carry info strings, so blocks that should end do not and the prose after them renders as code - FILED 2026-10-20 (fence-class close-out), OPEN

**Status: OPEN, reduced to two files.** A closing fence must be bare. CommonMark 0.31 section 4.5 says the
line that ends a block is the run of backticks and nothing else, so a fence carrying an info string is
always an opening. Where one sat where a closer belonged, the block never closed, and every line after it
rendered as code until the next bare fence. The reader saw something the file did not say.

**Proving command.** `python tools/md_fence_check.py` -> `6 invalid closing fence(s) in 167 file(s)`, exit 1.
167 is the number of tracked markdown files scanned. 22 of them carried findings before this pass; 2 still
do. The exit code counts invalid closers only: an unclosed fence is reported for the reader but is not a
failure, because a block running to the end of a file is legal CommonMark.

**What was fixed.** 20 files were brought to zero findings, all by the same mechanical change: the info
string was removed from each mis-tagged closing fence and the fence left bare. No other line changed, no
content was deleted or merged, and no line was re-wrapped. The counts were CATALOG-ROSTER-WORKSTREAM.md 129,
SLICE-RECORDS.md 85, DESIGN-CORPUS.md 79, design-2.0.md 61, PROCESS-PLANS.md 57, UX-DELIVERABLES.md 49,
PLANS-AND-BRIEFS.md 23, screen-spec-2.0.md 17, AUDIT-AND-VERIFICATION.md 13, SKILLS-MECHANICS.md 12,
RACE-AND-SLICE-RESEARCH.md 11, REFERENCE-GUIDE-BUILD-CHAIN.md 6, ARCHITECTURE.md 4, GOVERNANCE.md 4,
CHARACTERS-SOURCE.md 3, SKILL_AUTOMATION.md 2, DESIGN-AND-MECHANICS-REQUESTS.md 2, README.md 1,
REORGANIZATION_PLAN.md 1, Rice_Shower_Unity-Cup.md 1.

**What is left.** `docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md` holds 6 invalid closers and 4 unclosed
blocks. `docs/UMAMUSUME_REFERENCE.md` holds 6 unclosed blocks and no invalid closer. Both carry another
session's uncommitted work, so neither was touched. Both are reachable by the same mechanical rule: 3
closers in `UMAMUSUME_REFERENCE.md` (lines 133, 2683, 2691) and 2 in `docs/research-scratch/PROCESS-PLANS.md` section `## frontend-development-plan.md` (lines
576, 921) were simulated against the real checker, and each reaches zero findings with no other change.

**Fix path (future doc dispatch, once the peer work in those two files is landed).** Strip the info string
from each closing fence and leave the fence bare. The class is entirely mechanical: no file needed a closer
added, and no tagged line turned out to be an opener rather than a closer. Re-run the checker on the file
until it reports zero findings for it, then tree-wide until it exits 0.

### KI-112 The skill detail shipped `ADR-0011` on screen, so the copy gate that reads every template for a repository identifier was red at HEAD - FILED 2026-10-22 (copy-gate close-out), CLOSED 2026-10-22

**Status: CLOSED** by `6ca8d9a` ("fix(skills): keep the ADR citation out of the skill detail copy").

**Proving command.** Before: `php artisan test --compact --filter="UiHygiene"` -> 1 failed, 53 passed (597
assertions), with `resources/js/pages/Skills/Show.vue:118` and `:160` reported as "carries an ADR citation".
The file is at HEAD state, so the failure belongs to a committed change and not to a peer edit. After:
the same filter -> 4 passed (4 assertions), exit 0. `php artisan test --compact --filter="Skill"` -> 134
passed (2474 assertions), exit 0. `npm run typecheck` exits 0. `npm run lint:ui` -> `ui-lint: clean`.
`npm run build` -> exit 0, 1072 modules transformed. The browser proof, on a session-private pair
(`PLAYWRIGHT_PORT=8163 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/ui-hygiene-fix/browser-8163.sqlite`):
`npx playwright test tests/browser/skills.spec.ts` -> 10 passed (1.7m), including the two cases whose
assertions changed.

**How it happened.** `aaf2fe69` (2026-10-18, "chore(copy): remove the eight residual developer identifiers")
removed `ADR-0011` and `G-SK-20` from this page's shipped copy on purpose, and the gate
`tests/Feature/UiHygieneTest.php` was already in force. `c61ef815` (2026-10-20) put `(ADR-0011)` back into
the disclosure summary and the absence paragraph, and its own message says why: `skills.spec.ts` asserted
the citation was on screen and neither mechanics branch had named it since the drop. So the browser spec and
the copy gate were made to disagree, and the browser spec won by two days. The two cannot both hold: the
guard reads every `.vue` template for `/\bADR-\d+/` and `AGENTS.md` §5 says a repository identifier never
reaches a Trainer.

**The repair.** The ruling's substance rides on the summary in words a Trainer can read, so the fact is
still on the summary and not only inside the fold, which is what the browser spec was written to hold; the
citation moved to the template comment, where the guard's own docblock says a maintainer trace belongs. The
three spec assertions were re-pointed at the substance rather than deleted, and both cases still require the
absence to be stated on the summary.

**What closure does not cover.** Three things. First, the spec's comment said "both mechanics branches", but
only the branch that has conditions is exercised: the `v-else` absence paragraph has no browser case, and
this entry did not add one, so that branch is covered by the guard alone. Second, `AGENTS.md` §5 also says
"No em dashes in shipped copy", and 47 `.vue` files under `resources/js` carry one, this summary's own
`Raw engine data — the source's own expressions` among them; the only gate for that rule reads `.blade.php`
files, which the Vue port retired, so nothing enforces it. This closure did not touch the em dash, because
the tree-wide divergence is a separate decision and not a defect this entry owns. Third, `G-SK-20` is the
identifier the guard's goal pattern does not match (`/\bG-\d+\b/` does not match `G-SK-20`), so a future
copy pass could reintroduce it and the gate would stay green.

**Correction (2026-10-22, anti-slop audit pass; the text above is left as filed, except that the quotation in
the second item now reads the em dash the source actually carried).** The second item under "What closure
does not cover" is wrong on all three of its claims, and one of them was load-bearing.

First, the count. "47 `.vue` files under `resources/js` carry one" counted an em dash anywhere in a `.vue`
file, comments and script blocks included, which is not what the rule governs. The rule governs rendered
copy. Measured against the rendered copy only, two lines in two files carried a dash: this page's summary
and `Database/Scenarios.vue:142`.

Second, "the only gate for that rule reads `.blade.php` files, which the Vue port retired, so nothing
enforces it" is wrong. `tests/Feature/RenderedCopyHygieneTest.php` reads both trees
(`shippedCopySources()`, `resources/views` and `resources/js`), strips the comment forms a view can hide
prose in, and fails on any em or en dash that would reach a Trainer. The rule was enforced. The gate was
red: the full suite on 2026-10-22 read 1 failed, 1948 passed, and the failure was this one, on both lines
named above.

Third, "This closure did not touch the em dash" was true of `6ca8d9a` and is no longer true of the tree.
The audit pass that corrected this entry also fixed both lines, in `KI-113`, and the gate now reads green.
The deferral was a misreading of the gate, not a decision to defer: the sweep was two lines, not a
tree-wide divergence.

**KI-113** carries the dash defect itself, with the two root causes and the closure.

### KI-113 The R-02 em dash gate was red at HEAD on two rendered lines, so the copy rule the owner ruled on in 2026-09-28 was not enforced for two days - FILED 2026-10-22 (anti-slop audit pass), CLOSED 2026-10-22

**Status: CLOSED** by `fafd613` ("fix(copy): remove the em dash from the two rendered lines R-02 bans").

**Proving command.** Before: `php artisan test --compact` -> 1 failed, 1948 passed (31769 assertions) in
511.46s, the failure being `RenderedCopyHygieneTest.php:202` with
`resources/js/pages/Database/Scenarios.vue` at line 142 and `resources/js/pages/Skills/Show.vue` at line
108. The two lines are the whole finding: the gate strips the comment forms a view can hide prose in and
reports the files that still carry a dash in rendered copy. After the fix:
`php artisan test --compact --filter="RenderedCopyHygiene|UiHygiene"` -> 12 passed (42 assertions), exit 0.
The full suite, after the whole audit pass landed: `php artisan test --compact` -> **1949 passed (31769
assertions)**, exit 0, 683.00s.
`npm run typecheck` exit 0. `npm run lint:ui` -> `ui-lint: clean`. `npm run build` -> exit 0. The browser
proof, on a session-private pair (`PLAYWRIGHT_PORT=8163 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/antislop-audit/browser-8163.sqlite`):
`npx playwright test tests/browser/skills.spec.ts tests/browser/database.spec.ts` -> 36 passed (2.3m),
including the scenario matrix case whose assertion changed.

**How it happened.** Two separate commits, and neither was the copy pass that introduced the rule.
`c61ef815` (2026-10-20) wrote the skill detail's disclosure summary as
`Raw engine data — the source's own expressions and codes, not client labels`, and its own message shows
the dash was in the string from the first version of that line. `41a1ff2e` (2026-10-20) added the panel
state column to `Database/Scenarios.vue` with a tick for "on" and a dash for "off", each with the word
beside it for a screen reader. The gate was in force throughout (`RenderedCopyHygieneTest` reads
`resources/views` and `resources/js` and has since the Inertia port), so the tree was red from the moment
the first dash landed and nobody ran the suite.

**The repair.** The summary reads with a colon. The panel state cell prints the word and drops the glyph
pair: a tick beside the word was decoration, the dash is banned, and the word alone is the state, which is
the pattern `StatusBadge` already uses. The browser case now asserts the word is **visible** where it
asserted the word was **attached** and the glyph was a tick or a dash, so the case is stronger than the one
it replaces.

**What closure does not cover.** Three things. First, the em dash in the two lines was the whole rendered
copy finding, but the rule is wider than the gate: the gate reads `.blade.php` and `.vue` under
`resources/views` and `resources/js`, and the shipped vocabulary in `lang/en/uma.php` and any string a
controller hands to a view is outside it, so a dash there would not be caught. Second, the gate reports the
files and the lines, not the class of the dash, so a dash that is a glyph rather than prose is caught the
same way as one in a sentence; this entry removed both because the rule has no exemption for glyphs, and a
future case may need one, which is a ruling for the owner rather than a defect to file. Third, the two
defects in this pair of entries shared a shape, and the shape is not closed: a browser case can assert a
thing that a copy gate forbids, and nothing in the tree resolves the conflict automatically. `skills.spec.ts`
asserted an `ADR-0011` citation on screen against the identifier gate (KI-112) and `database.spec.ts`
asserted a tick-or-dash glyph against the dash gate (here). Both were resolved by re-pointing the browser
case at the substance, and both needed a person to notice.

### KI-114 Two browser files still drive the deck picker the wizard used to have, so eleven cases are red against a picker that no longer exists - FILED 2026-10-22 (anti-slop audit pass)

**Status: OPEN.** Not fixed here, and the reason is in "What this entry does not do" below.

**Symptom, observed.** `npx playwright test tests/browser/career-legacy-deck-steps.spec.ts
tests/browser/support-deck.spec.ts` on a session-private pair (`PLAYWRIGHT_PORT=8172
PLAYWRIGHT_SCRATCH_DB=.scratch-uma/antislop-audit/browser-8172.sqlite`) -> **10 passed, 11 failed** in 20.8
minutes. The eleven, by cause:

1. `career-legacy-deck-steps.spec.ts:212` reads `getByText(/Grandparent A1:\s*Daiwa Scarlet\s*Rank UG/)` on
   `/career/setup/preflight`. The page renders no ancestor summary any more: the `ancestors` prop is still
   declared at `pages/Career/Preflight.vue:70` and nothing in the template reads it, and the words
   "Grandparent" are gone from the frontend.
2. `career-legacy-deck-steps.spec.ts:229` and `support-deck.spec.ts:56` read the slot's absence as
   `Not equipped`. The slot now prints `Empty.` (`components/support/SupportSlot.vue:115`).
3. `career-legacy-deck-steps.spec.ts:267` and `:343` and `support-deck.spec.ts:113`, `:148`, `:250` drive the
   picker through controls that are gone: `#deck-pick` (a `<select>`), `getByRole('button', { name: /^Equip to
   Slot N/ })`, `Equip owned to Slot 1`, `The picker writes to Slot 1.`, `Deck saved.`, `select[name="rarity"]`,
   `select[name="type"]`, `select[name="query"]`, and `#deck-picker-results > li`. The picker is now a
   `<dialog>` (`components/support/CardPicker.vue`) listing cards, each with an `Equip` button, filtered by a
   type select and a name input; the slot's own button is `Choose a card in Slot N` when the slot is empty and
   `Replace the card in Slot N` when it is not.
4. `career-legacy-deck-steps.spec.ts:306` and `:327` and `support-deck.spec.ts:91` size and reflow the same
   controls, so they fail on the same absent elements.

**Cause, measured.** `5f2bfde4` (2026-10-19, "feat(wizard): step 5 uses per-slot pickers and segmented
ownership") replaced the picker's `<select>` with the per-slot dialog and renamed the slot's empty state and
button labels. `6717c7c8` (2026-10-13, "feat(cockpit): anchor sections on-page nav") is the commit that
carries the preflight page's ancestor summary. Neither commit updated the two browser files, and neither landed
a register entry, so the red was invisible: no gate reads these files, and the full PHP suite cannot see a
browser case.

**What was fixed here, and what was not.** The product defect underneath the first six step-4 cases was fixed
and is recorded as `9eb7bb6`: `bbc1814f` had removed the "No Sparks recorded." paragraph and the Spark chance
disclosure from `components/legacy/AncestryNode.vue` while leaving the prop, the import, the comment and the
compare surface in place, and the six step-4 cases are green on the current tree. One assertion in the file was
re-pointed at the shipped copy (the absent name is scoped by its own `title` now, because the pick control's
"Not chosen" option carries the same words). What is left is the picker migration itself.

**What this entry does not do.** It does not migrate the two files, and the reason is that the migration is a
rewrite of the interaction model rather than a string swap: nine distinct controls the cases drive are gone and
their replacements live in a dialog, so every case that touches the picker has to be re-written and re-proved,
and the file's seven failing cases each burn a 180-second timeout while they fail. Half-migrating would leave a
file that is red in a new way and reads as if the screen had changed again, which is a worse signal than a red
file with a filed reason. The peer that built the picker owns that migration.

**How to close it.** For each failing case, the new path is: click the slot's `Choose a card in Slot N`
button, read the card list inside the dialog, click the row's `Equip` button, and then assert the slot's own
text. The filters move to `#deck-filter-type` and `#deck-filter-search`, the results list is the dialog's
`ul[role="list"]` (it has no id, so a locator needs one that does not assume one), and the empty-filter message
is `No support card matches that filter.` The `ownership` absence's `title` is `No ownership been recorded for
slot yet.`. The preflight case needs the ancestor summary's new home, or a ruling that the preflight does not
carry one.

**What closure does not cover.** The two files are the browser gate for the deck picker, and until they are
migrated that gate does not exist: eleven cases have been red since 2026-10-19 without an entry, which is the
process gap `AGENTS.md` §9's failure-classification rule names. The `No Sparks recorded.` paragraph this entry
restores was removed with the same class of accident, and nothing in the tree would have noticed its absence
except a browser case; a static gate that reads the shipped copy for a cell the screen is supposed to carry
would have caught it, and no such gate exists.
### KI-115 Per-run aptitude letters are not stored, and that is correct (Group A / A-0.2) - FILED 2026-10-24 (Group A + B audit pass)

**Status: CLOSED - does not reproduce.** The aptitudes are catalog facts, not run facts.

**Current state.** Aptitude grades live on the `umamusume` catalog table (migration `2026_09_27_090000_add_aptitude_grades_to_umamusume_table.php`); `TrainingRun` carries no aptitude columns (`app/Models/TrainingRun.php`). Walk-1 finding D8 already verified the catalog letters are authoritative and the frame that disagreed was wrong (`docs/audits/rice-shower-unity-cup-ux-walk-resolutions.md:44,156`). Ruling A-0.2 (2026-10-24): catalog-only, no per-run storage.

**What this does not cover.** A post-inheritance aptitude drift carried by a Spark is not representable on a run; if that is ever wanted it is a new column set and an ADR, not a display gap.

---

### KI-116 The run's reached per-card effect values are unrecorded (Group A / A-0.4) - FILED 2026-10-24 (Group A + B audit pass)

**Status: OPEN - blocked on A-0.5.** The builder's analysis uses catalog maxima.

**Current state.** `ADR-0014:8` states the reached effect values are still unrecorded and the deck analysis uses catalog anchors. A per-run reached value needs a card level first (A-0.5) plus an effect lookup.

**Ruling.** A-0.4 ruling (2026-10-24): deferred to A-0.5. See `docs/proposals/group-ab-rulings-owed.md#a-04`.

**What closure would need.** The A-0.5 per-run `deck_slots.card_level` column and an ADR, then a derivation from level to effect values.

---

### KI-117 The run's per-run card level is not stored (Group A / A-0.5) - FILED 2026-10-24 (Group A + B audit pass)

**Status: OPEN - blocked: ADR.** A per-run card level is a migration; §11 needs an ADR first.

**Current state.** `ADR-0014:24` declines the collection half (`user_support_cards` - level, breaks, perk) as the §6.9-cut feature; `ADR-0014:8` leaves "card levels and limit breaks are still unrecorded" open. `deck_slots` carries `support_card_id`, `slot_position`, `ownership` and no level column.

**Ruling.** A-0.5 ruling (2026-10-24): accepted as a per-run `deck_slots.card_level` column, distinct from collection ownership. The ADR is the first artefact; the slice is its own small pass. See `docs/proposals/group-ab-rulings-owed.md#a-05`.

**What closure would need.** A one-page ADR saying a per-run card level is stored on `deck_slots` and does not contradict ADR-0014's decline of `user_support_cards`, then the migration and the deck surfaces.

---

### KI-118 Team stat grades have no column (Group B / B-0.5, R4-12) - FILED 2026-10-24 (Group A + B audit pass)

**Status: OPEN - blocked: ADR.** A team-grades column is a migration.

**Current state.** `TeamPanelBuilder::teamSection()` hard-codes `grade => null` for every stat (`app/Services/Career/TeamPanelBuilder.php:501-506`); `UnityCupPanel.vue` prints the five-row absence. Ruling B-0.5 (2026-10-24): accepted; ADR first. See `docs/proposals/group-ab-rulings-owed.md#b-05`.

**What closure would need.** An ADR, a `team_stat_grades` column (or table), the digest line, and the editor field.

---

### KI-119 The Current Spirit gauge has no column (Group B / B-0.6, R4-14) - FILED 2026-10-24 (Group A + B audit pass)

**Status: OPEN - blocked: ADR.** A Spirit value is a migration.

**Current state.** `UnityCupPanel.vue:231-241` mounts `ResourceMeter` with `value: null, max: null` and the absence sentence; no column or payload reader. Ruling B-0.6 (2026-10-24): accepted; ADR first. See `docs/proposals/group-ab-rulings-owed.md#b-06`.

**What closure would need.** An ADR, a `current_spirit` column, the digest line, and a writer.

---

### KI-120 Skill levels have no column or table (Group B / B-0.7, R4-16) - FILED 2026-10-24 (Group A + B audit pass)

**Status: OPEN - blocked: ADR.** Skill levels are a migration.

**Current state.** `SkillsPlannerController` carries acquisition state only (`app/Http/Controllers/Career/SkillsPlannerController.php:216-228`); no level column exists and `SkillAcquisition` has no level member. Ruling B-0.7 (2026-10-24): accepted; ADR first. See `docs/proposals/group-ab-rulings-owed.md#b-07`.

**What closure would need.** An ADR, a `skill_levels` table (or per-run level column), the digest line, and the planner field.

---

### KI-121 The borrowed card's source-account identity is not recorded, and that is correct (Group B / B-0.8, R4-18) - FILED 2026-10-24 (Group A + B audit pass)

**Status: CLOSED - ruled out.** A loopback, single-Trainer tool has no second account identity to record.

**Current state.** `deck_slots.ownership` (migration `2026_10_09_120000_add_ownership_to_deck_slots_table.php:24`) records `OWNED` / `RENTED` / nothing - the borrowed flag, which is the distinction that matters. Ruling B-0.8 (2026-10-24): will not add. See `docs/proposals/group-ab-rulings-owed.md#b-08`.

**What this does not cover.** If the tool ever supported more than one Trainer, the account identity would become a real column; that is out of scope by design.

---

### KI-122 A Unity Cup mandatory list naming the shared finals rounds is correct, not a leak (Group B / B-0.1, R4-06) - FILED 2026-10-24 (Group A + B audit pass)

**Status: CLOSED - does not reproduce.** The R4-06 premise that a Unity Cup run is shown URA-specific rounds is contradicted by the source.

*Dated note 2026-10-24 (release review).* The owner's Phase 3 ruling (B-0.1, "filter mandatory races by
scenario key") was withdrawn on this evidence: implementing it would have deleted the shared qualifier and
semifinal from every run. R4-06 moves to `ruled-out`, with `docs/scenarios/09-global-race-calendar.md:105`
as the reference; walk-4 finding F-05 was itself wrong, not only the app.

**Current state.** `docs/scenarios/09-global-race-calendar.md:105` states the three URA Finals rounds are shared and only the Final row forks per scenario; lines 632-633 name Unity Cup's final as Final (Aoharu). The seeded mandatory rows match: `Junior Make Debut` (null), `URA Finals Qualifier` and `URA Finals Semifinal` (null, shared), and the four scenario finals, with `final_aoharu` scoped to `unity_cup`. `RaceCatalogSlot::scopeForScenario()` (`app/Models/RaceCatalogSlot.php:136-140`) already keeps null-scoped rows plus the run's own key, so a Unity Cup run correctly sees the shared qualifier/semifinal and its own Aoharu final. An owner filter that removed every "URA Finals" title would delete legitimate shared obligations. No code change made; recorded so the finding is not re-opened.

**What this does not cover.** If the shared qualifier/semifinal are ever established to be URA-only, that is a parser mapping change (`GametoraRaceCatalogParser::GLOBAL_FINALS_BY_SLOT`) and a re-seed, not a render filter.

---

### KI-123 The snapshot door already records year, month and phase (Group B / B-0.3, R4-08) - FILED 2026-10-24 (Group A + B audit pass)

**Status: CLOSED - does not reproduce.** The snapshot form already carries the date input.

*Dated note 2026-10-24 (release review).* The owner's Phase 3 ruling (B-0.3, "add a snapshot date field")
was satisfied before it was made: `Snapshot/Setup.vue:165-186` renders Year/Month/Phase and
`CreateSnapshotRun:58-79` writes `career_position`. R4-08's date-input half is `landed`; walk-4 missed the
existing input. The separate pending-turn-state question remains open and is not covered here.

**Current state.** `resources/js/pages/Career/Snapshot/Setup.vue:165-186` renders Year, Month and Phase selects (`career_year`, `career_month`, `career_phase`); `StoreSnapshotRequest:45-47` requires them; `CreateSnapshotRun:58-79` writes `career_position` with `career_position_source = imported`. The R4-08 note that "the date is not an input on either path" is stale at HEAD. Ruling B-0.3 (2026-10-24) chose the snapshot date field, which already exists. No code change made.

**What this does not cover.** The separate R4-08 question - a pending-turn state on the guided turn form - is a different shape and is not addressed here.
### KI-124 Training-tile banner and the three card avatars are not stored (Group A / A-0.12) - FILED 2026-10-24 (audit addendum)

**Status: OPEN - blocked: ADR.** A turn-level banner and a card-avatar list are both migrations.

**Current state.** `app/Models/TurnEntry.php:61` `Fillable` stores the facility levels, `preview_gains` and
`failure_rate`, but no facility banner and no list of the cards whose avatars the tile showed;
`TrainingDetail.vue` renders neither. Walk-1 §1.7 records "Speed facility Lv4 with the banner 'Floor
Cleaning', three card avatars on the tile".

**Blocker.** `needs-schema`: a `facility_banner` string and a `training_card_ids` list (or join table) on
`turn_entries`, each needing an ADR, an `ARCHITECTURE-ESSENTIALS.md` digest line, and a PRD citation before
code.

---

### KI-125 The 20/20 team-members counter has no stored roster to count (Group A / A-0.13) - FILED 2026-10-24 (audit addendum)

**Status: OPEN - blocked: ADR.** The counter rides the stored-roster blocker A-0.6 was ruled to derive around.

**Current state.** The landed A-0.6 roster (`Cockpit.vue:862-869`, `CockpitController::rosterSection()`)
renders the six deck cards' names and states with no "N of 20" counter; `TeamPanelBuilder.php:442-507`
derives a second roster from `spiritBurstRoster()` with no counter either. The source's 18 named members
against a 20/20 counter (§1.9) is a count of a stored roster the tool does not hold (the derived deck
roster is 6, not 18).

**Blocker.** `needs-schema`: a stored roster (or team-membership table) to count against 20.

---

### KI-126 Guest skill lists on the roster are not stored or derivable (Group A / A-0.14) - FILED 2026-10-24 (audit addendum)

**Status: OPEN - blocked: ADR.** A member's skill list needs a character-to-skill link or stored per-roster skills.

**Current state.** The A-0.6 derived roster carries each member's name and state only. `app/Models/Skill.php:39,58`
marks `is_unique` as a bare boolean with no character link, so a roster member's own skills cannot be
resolved from the catalogue the way a support card's `hint_skills`/`event_skills` can. Walk-1 §1.9 records
"the 18-member roster and the guests' skill lists".

**Blocker.** `needs-schema`: a character-to-skill link (or per-roster-member skill storage).
---

### KI-127 The team race facility panel is the only scenario panel with no card wrapper and no capsule header - FILED 2026-10-24 (capsule pass)

**Status: OPEN - not landed here.** A separate panel, not either block the capsule pass fixed.

**Current state.** `resources/js/pages/Career/Cockpit/Panels/TeamRaceFacilityPanel.vue:51` renders a bare
`<div class="flex flex-col gap-4">` holding `AlertRow`s and one `<section>` per facility group with an
`<h3>` heading (`:61-72`). It carries no `.card` and no `CapsuleHeader`. It is registered against the
`team_race_facility` flag (`resources/js/components/scenario/ScenarioPanel.vue:55,79`) and is not either
block the round-4 screenshot showed.

**Natural home of the fix.** Wrap the root in `.card p-3` and add `CapsuleHeader :title="props.label"`
(the component already receives `label` from the composition), keeping the per-facility `<h3>` groups.

**Why it is not landed here.** `05aefea4` closed the two visible blocks
(`UnityCupTeamBoard.vue`, `UnityCupTournamentReadiness.vue`). This panel is a third surface with its own
heading hierarchy; folding it in would have made one commit cover two different shapes.

---

### KI-128 `Delete this career` sits inside the `Races in this run` region - FILED 2026-10-24 (capsule pass)

**Status: OPEN - owner placement decision.** A run-level destructive control in a per-race container.

**Current state.** The danger-zone `<details data-region="danger-zone">` (`resources/js/pages/Career/Cockpit.vue:1157-1188`)
renders at the bottom of the section whose heading is `Races in this run` (`:1108-1111`, section
`aria-labelledby="career-timeline-heading"`), below the race strip and the Timeline and Career Result doors.
Its blast radius is the whole run (turns, races, events, skills, deck slots, linked Veteran record), which
is not what the surrounding region describes.

**Natural home.** The `Run identity` section (`:610-611`, `h3#cockpit-header-edit`), where status and
scenario are already edited.

**Why it is not landed here.** Moving a destructive control's placement is an owner call, not a visual
consistency fix, and it was not part of the capsule change.

---

### KI-129 Capsule headers are used only in the Scenario section - FILED 2026-10-24 (capsule pass)

**Status: OPEN - owner design call.** Affirm, extend, or remove.

**Current state, measured.** `CapsuleHeader` is imported by six files and called eleven times, every one a
scenario panel: `UnityCupPanel.vue` (six), `UnityCupTeamBoard.vue`, `UnityCupTournamentReadiness.vue`,
`SpiritBurstRoster.vue`, `TeamRacePanel.vue`, `TeamRankGauge.vue`. No cockpit card outside the Scenario
section uses it; neither the Timeline nor the Catalog imports it (the round-4 note that they do is wrong).
Every other cockpit card group (`Run identity`, Actions, Advisor, `Races in this run`, stats) uses `.card`
with a plain `<h*>` heading.

**Documented position.** `DESIGN.md:398,488-493` records the argyle lattice capsule as an intentional
measured motif on capsule headers, and `BrowserSpecClassAssertionsTest` plus `DesignTokensTest` pin the
class and token side of it, so the usage is not a defect on its face. Whether the Scenario section should
look structurally distinct from the rest of the cockpit has not been ruled for this surface.

**Owner actions, one line each.** (a) Affirm the current design (the scenario section is thematically
differentiated) and close. (b) Extend the capsule to every cockpit card. (c) Remove it from the scenario
panels and amend `DESIGN.md` section 2.3 in the same commit.

**Why it is not landed here.** Only the owner can choose between (a), (b) and (c), and (c) changes a
documented design decision.
