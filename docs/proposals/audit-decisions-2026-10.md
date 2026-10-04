# Audit decisions owed: the items this pass could not settle alone

Status: **Proposals and measurements only. No code, no migration, no schema change, no owner ruling
recorded here.** Every item below was either re-verified at `master` `40018c08` (2026-10-04) or settled
by a peer commit and needs only to be acknowledged. Where a finding has already been answered by code,
that is stated first so the owner does not re-decide it.

Re-verification of the whole 2026-10-01 audit list, with the file:line each verdict was read from, is in
`.scratch-uma/audit-status.md` (untracked, disposable). The committed half of that work is the nine fix
commits this pass landed; this file is the other half.

## 1. Settled by a peer commit, no decision left

| Item | What the audit asked | What the tree does now |
|---|---|---|
| F-6 dead job | `FetchSourceJob` is declared and never dispatched | Deleted at `fda8bba` (32 lines). `app/Jobs/` is empty and `git grep FetchSourceJob` hits only prose in `docs/research-scratch/AUDIT-AND-VERIFICATION.md` and `RACE-AND-SLICE-RESEARCH.md`. Nothing to approve |
| F-4 unbounded growth | No unique constraint on `data_sources` or `match_candidates` | `2026_10_01_124039` puts a unique index on `data_sources (umamusume_id, source_key, url)` and `2026_10_01_124051` a partial unique on `match_candidates (source_key, IFNULL(external_ref, ''), proposed_match_key)`, both at `fda8bba`. `PromoteMatchedRecord.php:86` writes provenance through `DataSource::updateOrCreate` on exactly the indexed triple, so the constraint and the writer agree |
| F-8 unlocked reparse | `uma:reparse` wrote without the fetch lock | `UmaReparse.php:43` takes the same `Cache::lock("uma-fetch:{$key}")` as `UmaFetch.php:55` |
| L-F01 target size | Nav links, export links, helper link and `<summary>` under 24px | All carry `min-h-11`: `components/layout.blade.php:45,49,52,53,54,61`, `runs/show.blade.php:55,56,448,655,812`. Peer commit `ecae77d` |
| F-04 error envelope | Two error treatments on one envelope | One `<ul>` now carries both the `previewed` stage marker and the field errors, `runs/show.blade.php:571-576`. Peer commit `a3e323c` |
| KI-35 debut copy | "Unknown" invented as an absence word | `catalog/show.blade.php:164,172` render `N/A` with a `title`. Commit `80caefd` |
| `.gitignore` tail | UTF-16 dead line, `/vibe_images/` pointing nowhere | The file is ASCII end to end (`file .gitignore`), and line 102 is `docs/vibe_images/`, confirmed by `git check-ignore -v docs/vibe_images/` |
| KI-51 untracked seeders | Three seeder classes on disk, in no commit | `git ls-files database/seeders` now lists `ReadsCommittedSource.php`, `SourceDocumentSeeder.php` and `UmamusumeRosterSeeder.php`. The register entry that recorded them as unreachable is now stale |

## 2. F-5, KI-24, KI-27: what a snapshot is for

The three are one decision. The audit's F-5 asked for a content-hash snapshot path; that landed at
`20364ae` and now reads `snapshots/{sourceKey}/{hash}.html` (`SourceFetcher.php:60`). Fixing the key is
what surfaced KI-27: the path is a property of the **document**, while `storage/` is shared by every
database on this machine, so a second database is told "nothing changed" and stays empty.

Measured by the audit, not by me: after 1,910 rows landed in `.scratch-uma/skills-c5.sqlite`,
`uma:fetch` against `database/database.sqlite` printed "unchanged", and that database held 0
GlobalReleased client-named skills. `uma:reparse` then wrote 7 updated and 1,903 created.

KI-24 is the same class from the other end: a withdrawn document keeps answering `200` with stale
content, so a pinned URL is silent about being behind. `gametora-skills` and the sources that copied its
shape now resolve through a manifest; `gametora-characters` and `race_instances` still pin.

Options, as KI-27 lists them, with what each costs:

1. **Compare against what the database holds**, not against the file. The short-circuit asks the right
   question and the snapshot stays a body cache. Costs a per-source read of the promoted rows on every
   fetch.
2. **Keep the snapshot as a body cache and always continue into the pipeline.** Cheapest, idempotent
   because every store action upserts (proved this pass: `migrate --seed` twice exits 0 the second time
   and reports 0 created), but a fetch that used to short-circuit now reparses on every run.
3. **Key the snapshot per database.** Preserves the short-circuit and fixes the cross-database lie, at
   the cost of a second copy of a ~2.5 MB body per database and a path that names something nobody
   reads.

Recommendation, not a ruling: option 2. It is the only one that does not require the fetch to know what
another database did, and the idempotency it depends on is now tested (`RosterSeederReRunTest`,
`2aa0e5a`).

Owner: Data Engineer for the shape, Architect for the decision, per KI-27's own line.

## N-3 addendum: the fetch-catch was narrower than the audit premise

The audit's N-3 reads: `SourceFetcher.php` catches `RequestException` and returns null, and because
`->retry(..., throw: false)` is in play, an exhausted retry arrives at that catch and leaves without a log
line. Measured while fixing it, the premise held for half the failure modes and failed for the common one.
On this framework version `ConnectionException` is **not** a subtype of `RequestException`: both extend
`HttpClientException` (`vendor/laravel/framework/src/Illuminate/Http/Client/ConnectionException.php:5` and
`.../RequestException.php:7`). A DNS fault, a refused connection or a timeout therefore never entered the
`catch (RequestException)` block. It escaped `send()`, escaped `fetch()`, and ended the whole `uma:fetch`
run with an unhandled exception, while the same body fed through `db:seed` was caught by
`SourceDocumentSeeder`'s `catch (Throwable)` and turned into a warning plus a skipped source. One document
failure, two different outcomes, and the silence N-3 named was only the smaller half of it.

`9b8a9d5` widens the catch to `HttpClientException`, the parent of both, which is what `fetch()`'s own
docblock had always promised (null when the request ultimately failed) and what lets one source fail
without ending the run. Both failure branches now log the URL with the reason or the status.
`tests/Feature/FetchFailureReportingTest.php` covers the three paths: a connection fault returns null and
logs, a 404 returns null and logs, and a successful fetch logs nothing, which is what stops the first two
from passing because every path talks.

The audit entry for N-3 should be updated to record the mechanism rather than only the logging gap, because
the entry as written describes a defect that would have been fixed by adding a log line, and the defect that
crashed the run needed the catch class changed.

## 3. C-4: the `scenarios` table holds caps the app never consults

`Scenario::cap_speed`, `cap_stamina`, `cap_power`, `cap_guts`, `cap_wit` and `hard_cap` are filled by
`GametoraScenarioParser.php:78-80` and declared at `app/Models/Scenario.php:39,57-59`. Every ceiling the
app actually uses comes from `App\Services\ScenarioCaps`, which `ADR-0015` names the single owner. The
columns are therefore fetched truth nobody reads, and a future reader will not know which is authoritative.

This is a decision, not a bug: the numbers in the table are the source's, and `ScenarioCaps` is this
tool's arithmetic. Three ways out:

1. **Read them.** Make `ScenarioCaps` consult the stored caps when a scenario row carries them and fall
   back to its own table otherwise. Cost: two owners of one number, which is exactly what `ADR-0015`
   exists to prevent.
2. **Drop the columns.** A migration plus the `ARCHITECTURE-ESSENTIALS.md` digest and PRD citation
   `AGENTS.md` §11 requires. Cost: the fetched values stop being auditable, and the source's own ceiling
   can no longer be compared against ours.
3. **Keep and label.** Add a column comment or a docs line stating the columns are a source snapshot and
   not the ceiling the app enforces. Cost: one paragraph. Benefit: the next reader is not deciding this
   again.

Recommendation: option 3, because option 1 contradicts an accepted ADR and option 2 spends a migration to
lose data the fetch engine was told to keep.

Owner: Architect.

## 4. KI-38: one card stores the source's placeholder as its title

`character_cards` row `card_id=103601` carries `title="[unsigned]"` beside a real release date and
rarity, and the catalog list, the detail page and the run-form selector print `[unsigned]` as though it
were the costume name. Re-measured against `database/database.sqlite` on 2026-10-04 (read-only,
`.scratch-uma/ki38-probe.php`): exactly one row, `rarity 3`, `global_release_date 2026-06-18`,
`unconfirmed = 0`, out of 106 stored cards. The audit's own figure was one placeholder over the 268-row
export, so the export and the table agree.

The entry's three options: treat a placeholder title as absent for display and search and keep the row;
flag it `unconfirmed` and let the cross-check queue decide; or accept it as a dated snapshot and render it
with a marker.

One consequence the entry does not state, and it changes the ranking: `unconfirmed = false` is a filter on
the read paths, not just a column (`SkillController.php` `holders()` and the run-page card scope both
constrain on it). Setting option 2's flag therefore does not rename the card, it deletes it from the deck
picker and the holders list, and a Trainer who owns that costume loses a legal choice. Option 2 is the
option that quietly destroys data the Trainer can see.

Recommendation: the first option, scoped to the display path, because `AGENTS.md` §5 gates this on the
display path and never by editing the data. The entry itself asks Lore Guardian to be consulted on the
third option only.

Owner: Data Engineer, with Lore Guardian.

## 5. KI-55: two documents `AGENTS.md` cites do not exist

Confirmed again this pass: `ls docs/GATE-REGISTRY.md docs/PRE-MORTEM.md` returns no such file for both,
and neither is tracked at HEAD. Their content lives as sections inside
`docs/research-scratch/GOVERNANCE.md`, which `AGENTS.md` §2 already points at for the quality bar.
`docs/research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md:243-244` contradicts this and marks both
files as tracked; that row is wrong, and it is the reason KI-55 reads as a conflict rather than a typo.

Three ways out, as the entry states them: restore both files at the cited paths, re-point the three
`AGENTS.md` citations at the GOVERNANCE.md sections, or cut the citations. Restoring is the one that
creates a second copy of a register, which is how `AGENTS.md` §3's file discipline says registers drift.

Recommendation: re-point. It is a three-line edit to `AGENTS.md` and it makes the precedence chain name a
file that exists.

Owner: Docs Writer for the re-point; the restore/re-point/cut choice is the owner's.

## 6. KI-45: the tier-to-grade mapping now misses every row

The entry's headline (no offline population path for `race_catalog_slots`) is superseded by `8b17703`,
and this pass confirmed the correction by seeding a fresh database offline: 410 race rows and a `migrate
--force --seed` that exited 0.

The second half has not improved. Read-only query against `database/database.sqlite` on 2026-10-04:
`scenario_slots` holds 296 rows and `tier` is NULL on **296 of 296**. At filing it was 141 of 296. Any
feature that maps a race's tier to a grade now misses every row, which is why Slice 3 stayed held.

The decision is upstream of code: either a source names the tier per slot, or the tier column is declared
unpopulated and the mapping is dropped from the plan. Inventing the mapping is the one thing
`AGENTS.md` §5 forbids, and `docs/UMAMUSUME_REFERENCE.md` carries grade-point figures the KI-10 entry
below says do not resolve this.

Owner: Architect with Planner Domain Specialist.

## 7. KI-25 and R82: phone width, and the floor that is not ratified

`R82` is not a defect, it is an unratified ruling: `PLANS-AND-BRIEFS.md:188-201` drafts it as a D-40
amendment saying 768px is the supported minimum and below it wide regions scroll rather than reflow, and
`PROCESS-PLANS.md:416` registers it as "the unratified proposal for a 768px responsive floor" with the
ratification gate at `PROCESS-PLANS.md:391,862-864`.

KI-25 (the nine-column turn log forcing the whole page into horizontal scroll at 390px: `scrollWidth 476`
against `innerWidth 390`) was closed on rendered attributes, re-opened by R85 because the closure never
read a browser, and cannot be finished before R82 is decided: whether the fix is a scoped scroll region
or a stacked layout below a breakpoint is exactly the difference R82 has not yet ruled.

Decision the owner owes: ratify R82 as drafted, or amend D-40. Everything about KI-25's fix shape follows
from that one answer, and the re-measurement has to be done in a browser either way.

Owner: human owner (ratification), then Frontend/Design-system with Architect.

## 8. KI-42: nothing runs the gates on push

`.github/` holds agents, hooks, prompts and skills and there is no `.github/workflows/`, so the full gate
is a local `composer test` run when a person chooses. The entry's own analysis is that the two defects it
recites were caught by a person noticing, not by a control, and that the common cause is every gate
running inside one developer's working tree where ignored files exist.

This is not a code question in this repository's current shape: there is no remote deployment path, no
CI in the plan, and `AGENTS.md` §1 says every gate runs locally and its output is the only evidence.
Options, in the order the entry implies:

1. **A clean-checkout job on push** (one workflow: `composer install`, `npm ci`, `php artisan test`,
   `pint --test`, `phpstan`, the lore gates). It makes the ignored-input and wrong-tree classes visible
   without a person. Cost: a CI surface the repo has deliberately never had, and it needs the host to
   push somewhere that can run it.
2. **A local pre-push hook** running the same sequence. Cost: per-machine, so it is exactly the
   "whoever remembers" control the entry is complaining about.
3. **A named-owner ritual**: the hand-off sequence in `AGENTS.md` §9 already is this, and the register
   records it being followed.

Recommendation: none is free, and option 1 contradicts §1's "no CI" statement, which would need an owner
decision of its own. If a control is wanted, the smallest honest one is a tracked script that runs the
hand-off sequence end to end and prints the evidence, so the gate is one command rather than nine
remembered ones.

Owner: human owner.

## 9. KI-43: the deck picker is most of the run page

Measured by the entry and worth restating: a six-equipped run page is 360,492 bytes, of which the deck
block is 296,537 (82.3%); with **nothing** equipped it is 329,355 bytes with the block at 291,547
(88.5%), because six `<select>` elements each repeat all 252 Global cards, 1,512 `<option>` elements on
every run screen.

Options: a search-first picker reusing `resources/js/trainee-combobox.ts` with the six selects as a
disabled no-JS fallback; a filtered shortlist of about forty cards plus a "show every card" affordance;
or leave it.

The decision is a design call about what a run page is for, and the fallback rule matters: whichever
picker shape is chosen has to keep the no-JS path, because `ADR-0007` and the server-driven disclosure
convention in `DESIGN.md` are what the rest of the screen already obeys.

Owner: human owner with the designer.

## 10. KI-10 and KI-15: the grade-point halves that need a source or a ruling

Both are data questions the repository cannot answer from itself.

**KI-10**, `Trackblazer Grade Points cannot be totalled`: `gradeEarned()` returns null for any finish
below 1st because no source names the placement ratio. The objective-bucket and stored-figure halves are
closed (`e103122`, `3711894`) and `GradePointPeriodTest` passes nine cases. Open half needs either a
sourced placement ratio or an owner ruling to ship an `[Unverified]` placeholder, and any turn/year bucket
on `race_entries` is a schema decision travelling with an ADR.

**KI-15**, `the three Grade Point tracks have no sourced rule for choosing one`: the page renders the
`standard` track (60/300/300) for every Trackblazer run while a dirt-leaning trainee asks 30/200/300 and a
weak-turf trainee 60/200/300. Either a dated capture names the condition, or the owner rules a
Trainer-entered track selector, which is a third column on `training_runs` and follows the `D-270` pattern.

Read against the corpus rather than the entry alone, the gap is narrower than "no source":
`docs/UMAMUSUME_REFERENCE.md:883-888` states the three threshold sets, says the choice is **by aptitude**
("a high-dirt or low-turf character takes 30 / 200 / 300, and a turf character whose range is narrow takes
60 / 200 / 300"), names Haru Urara and Curren Chan as the two cases, and records two independent
extractions agreeing. What it does not give is the *letter threshold*: `aptitude_dirt` at what grade,
`turf` at what grade, "range is narrow" measured against what. So the rule exists as a qualitative
statement and cannot be computed from the columns this database holds.

Recommendation for both: do not infer the threshold. For KI-15 the choice is therefore between a capture
that states the letters and a Trainer-entered track selector, and the second is the only one this tool can
ship today. For KI-10 the placement ratio has no figure anywhere in the corpus (`:900-901` covers shop
coins by placement, which is a different currency), so a sourced ratio or an `[Unverified]` placeholder is
still the whole decision.

Owner: Planner Domain Specialist with the human owner; the schema half is the Architect's alone.

## 11. F-01: the inline confirm on deleting a run, closed

The frontend register (`AUDIT-AND-VERIFICATION.md:3948`, §4.1) filed `onsubmit="return confirm(...)"` on
the Delete Run form as an Observation, not a defect, with "Fix in scope: No, its own dispatch" and no
claimed owner. That dispatch has since run: `ecae77d` is titled "Fix F-01: remove inline JS from run
detail route (WCAG 2.2)", and the tree now shows the replacement at `runs/show.blade.php:806-823`, a
`<details>` disclosure whose `<summary>` is the prompt and whose form submits the DELETE only once the
Trainer opens it, with `RunViewFrameTest` pinning the disclosure count. A `grep` for `onsubmit` or
`confirm(` across `resources/views/runs/` returns that comment text and nothing else.

Nothing is owed here except the register row's status, which is the closure/hold process this pass was
told not to run.

One hazard worth keeping in mind when reading history: the audit's own N-1 records that commit `4e17997`
used the labels "F-1, F-2" for unrelated UI fixes, so a search for `F-1`/`F-2`/`F-01` in the log can
return a fix for a different finding. The `F-n` audit ids from 2026-10-01, the `F-nn` frontend ids from
2026-10-03, and the `KI-nn` register are three separate numbering systems.

## 12. Residuals from this pass that are file-ownership problems, not findings

Recorded here because a later pass will otherwise re-find them.

- **KI-41 has a seventh site this pass could not commit.** `SkillController.php:53` orders the skill list
  by name with no tiebreaker, same as the six that were fixed at `f8594e1`. That file carries a peer's
  uncommitted `json_valid` guard in `holders()`, so committing it would have landed their work under this
  commit's message.
- **KI-46's envelope is a view decision.** The messages now name their row, but
  `resources/views/runs/import.blade.php:153` prints only the first message per column (Blade's `@error`
  resolves through `MessageBag::first()`), so two broken rows still show one line. That file is peer-dirty.
- **Two `KNOWN-ISSUES.md` forward notes are drafted and unapplied.** The welcome.blade.php sweep and the
  KI-23 versus KI-23b reconciliation are written out in `.scratch-uma/audit-status.md` §5. Root
  `KNOWN-ISSUES.md` is in the working tree as a 71-line pointer stub against a 2,655-line committed
  register, a peer's 2,722-line change in progress. Appending to it and committing would land that
  restructure under a docs-fix message, which `AGENTS.md` §11 and the shared-worktree rule both forbid.
- **`runs/import.blade.php` and `form-detail.blade.php` are peer-dirty**, so any copy fix on those screens
  needs the peer's change landed first.

## Register corrections from the 2026-10-04 pass

Three findings' recorded status no longer matches what the tree does. None of them needs code; each needs
the register line moved. They are listed here rather than edited into the register because root
`KNOWN-ISSUES.md` is a peer's in-flight rewrite (see section 12) and
`docs/research-scratch/AUDIT-AND-VERIFICATION.md` is closed to this pass by the dispatch that ordered it.

**F-2, `cff9e92`: the fix carries a consequence the entry does not name.** `db:seed` now exits non-zero when
a source fails to import, which is what F-2 asked for. The consequence: `DatabaseSeeder` calls seeders in a
fixed order, and because the failure is raised at the end of `SourceDocumentSeeder::run()`, anything after it
in that list does not execute on a partial seed. Today that is `ScenarioSlotSeeder`, so a failed source also
leaves the URA finale goal races unpopulated.

Accepted as the trade, and the reasoning is recorded because "seed exits with a code" does not say it: the
skip is survivable only since KI-56 (`2aa0e5a`) made `db:seed` re-runnable. Before that, a re-run was the one
action that could not repair a partial seed, because it aborted on its own first pass. The repair path for a
failed source is `php artisan uma:reparse <source>` for a snapshot the engine already holds, or a plain re-run
of `migrate --seed` once the input is fixed. A register entry that records F-2 as closed without naming the
seeder skip will read, to the next person, as though one source failed quietly.

**KI-50, `5a7e5e6`: one half of option (a) landed.** The DDL half is closed:
`tests/Feature/SchemaCheckConstraintTest.php` reads `sqlite_master` and asserts both that each of the five
tables carrying a constrained domain has a real `CHECK` clause and the value list that clause permits. It was
mutation-checked by deleting the `support_effects` calc clause, which turned two of its seven cases red.

The grep half of option (a), "a gate that greps `database/migrations/` for `->check(` and fails", is not
built, and it cannot be built as written: `->check(` is present today in
`2026_09_30_142618_create_support_cards_and_support_effects_tables.php` and in
`2026_09_30_151945_correct_support_card_schema_and_constraints.php`, and the entry's own text says those two
files "must not be modified" because `151945`'s `down()` deliberately reconstructs the constraint-free shape.
A gate that fails at HEAD is either suppressed or wrong, and the Floor forbids the new suppression. So KI-50
should read **partially resolved: DDL guard closed at `5a7e5e6`, spelling gate open, blocked by the two
grandfathered migrations**, not open and not closed. If the owner wants the spelling gate, the decision it
needs is whether those two migrations may carry an allowlist exception, which is a different ruling than the
one the entry already records.

**KI-55: the re-point this document recommended has already happened.** Section 5 above was written from the
2026-10-02 register entry and recommends re-pointing `AGENTS.md`'s three citations at the GOVERNANCE.md
sections. Checked at this HEAD: `git show HEAD:AGENTS.md` lines 5, 94 and 178 already name
`docs/research-scratch/GOVERNANCE.md` §"GATE-REGISTRY.md" and §"PRE-MORTEM.md", and the re-point landed as
`bc42d93` ("O-2: repoint citations to GOVERNANCE.md sections"). Section 5's recommendation is therefore
closed, and the owner decision it asked for is already given.

What remained open was the other half of the contradiction, and it is the reason section 5's own citation was
wrong: the rows marking the two paths as tracked are at lines 90 and 91 of
`docs/research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md` as that file was written, **not** at the
`243-244` this document cited. Lines 243-244 are frontend probe records. That citation came from a subagent
report this pass relayed without re-reading the target, which is the failure mode the relay rule exists to
stop. Item 1 of this dispatch corrected rows 90 and 91 in place at `f1c18fc`.

Still open on KI-55, and small: the inventory file names `docs/GATE-REGISTRY.md` or `docs/PRE-MORTEM.md` as
live paths in thirteen other rows and tables too (counted at `f1c18fc` by grepping both paths and excluding
the three corrected lines; the line numbers move on every edit, so re-run the grep rather than trusting a
list here). Those are outside Item 1's stated scope and are recorded rather than edited.

## Coordination: the peer-dirty file pattern

Three files have blocked items across two dispatches and all three are still dirty at this HEAD:
`KNOWN-ISSUES.md` (a 2,655 to 71 line restructure in flight, 2,722 lines of deletion in the working tree),
`app/Http/Controllers/SkillController.php` (an uncommitted `json_valid` guard in `holders()`), and
`resources/views/runs/import.blade.php` (two uncommitted copy hunks). What they cost, in order: the two
register forward notes, KI-41's seventh `orderBy('name')` site, and KI-46's view half. This is the second
dispatch to stop at the same three files.

The pattern is wider than three files. `git status --porcelain -- resources/views/` at this HEAD returns 13
entries, and 11 of them are `resources/views/components/*.blade.php`. A fourth blocked dispatch looks more
likely than a coincidence. The file this dispatch suspected as the fourth,
`resources/views/preferences/edit.blade.php`, is not among them.

Three ways out, for the owner to choose. This pass takes none of them and lands none of the blocked items.

**Option A, peer commits first.** The session holding those files lands its work before the next triage pass
runs. Cheapest, no new machinery, nothing to maintain. It depends on coordination between two agent sessions
that cannot signal each other from inside the worktree boundary, which is what the owner already observed on
the last coordination request of this kind.

**Option B, fork per session.** Each session works on a named branch (`session/triage-2026-10-04`,
`session/audit-2026-10-04`) and merges on owner review. Ends the collision class outright, at the cost of one
merge step per dispatch. Two measurements bear on it. `git worktree list` at this HEAD shows three working
directories, the primary plus two `.kilo` sandboxes, and one of those is at detached HEAD, so isolated
workspaces already exist on this host while carrying no reviewable branch name; a detached worktree's commits
are reachable only by that SHA until someone branches them. And a prior pass on this repository measured that
a second worktree arrives with no `vendor/`, that linking the primary's `vendor/` makes Composer's autoloader
resolve through the link back to the primary's `tests/` so Pest's binding never applies, and that a real
`composer install` is the only version that runs the suite. Option B pays that per branch.

**Option C, file-ownership lockfile.** A tracked record declares which paths each active session holds, and a
dispatch reads it at premise time and refuses to start on a locked file. Explicit and auditable, which is what
this repository's provenance rules ask of any coordination mechanism, and it turns "we both rewrote
`stat-band.blade.php`" into a precondition instead of a post-merge repair. Costs one more tracked thing to
maintain, one more thing a session can forget to update, and AGENTS.md §3 forbids creating a new markdown file
in the repository root without a ruling, so the lockfile would have to live under `docs/research-scratch/` or
as a section of `INDEX.md` rather than at `.worktree-locks` in the root.

If the same three files block a third dispatch, A has been shown not to work and B or C is warranted.
