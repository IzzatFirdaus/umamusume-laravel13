<!-- census: content-not-citations -->
# Audit and Verification

## Provenance

This file carries the census marker above because its body is three enumerations whose backticked
paths are the subject they list, not citations a reader follows: the documentation inventory's file
tables, the defect register's per-entry proof pointers, and the phase audit's file-named findings.
The marker is file-level in `tools/doc_census.py` (first five lines), so it travels with the content:
it sat at the head of `DOCUMENTATION-INVENTORY-2026-09-30.md` before Round 7 moved that body here,
and without it the census counted ~204 inventory listing lines as dead links. `main()` prints every
excluded file, so the opt-out is visible, per the tool's own docstring.

This document consolidates the following source files verbatim (no summarization, no deduplication):

- `docs/design-research/audit-verification-2026-10-01.md` (113 lines)
- `documentation-inventory-and-unfinished-phases.md` (1,062 lines), merged 2026-10-03 from the
  gitignored root `research-scratch/`, which carried it as a standalone master. It embeds two
  documents of its own: the 2026-09-30 documentation inventory (653 lines, snapshot `c1e14a3`) and
  the 2026-09-29 fullstack phase audit (389 lines, rev 2, with two addenda). Its 75 em-dashes and
  4 code fences are reproduced unaltered.
- `training-run-ux-review-2026-10-03.md` (170 lines) and `training-run-live-2026-10-03.md` (152
  lines), folded in 2026-10-03 (Round 9). The static review record and its live browser companion
  were standalone untracked files beside the other audits here; they cross-reference each other by
  name, so keeping them in one master keeps those pointers inside the census exemption. The static
  record's supersession note (its §4.3, withdrawing F-02 against the live L-F02 pass) stands as
  written; neither record was edited to blend the two passes.
- `docs/UIX-AUDIT-TRAINING-RUNS.md` (1,172 lines, 73 headings), folded in 2026-10-04 (Round 11) as the
  section `## UIX-AUDIT-TRAINING-RUNS.md` at the end of this file. This one is a live document, not a
  closed record: its verdict on the run page is Fail, its completion backlog and forward plan carry work
  still owed, and owner rulings were still landing in it on 2026-10-04, so open steps continue in that
  section. It does not supersede the two records above: those use the `F-0n` and `L-F0n` registers and
  one of them never executed a browser pass, while this audit drove the running app under `C-`, `R-`,
  `O-` and `I-` ids. Its three self-references by the old path are left verbatim inside the section as
  the dated records they are.

**Round 11 (2026-10-04), one source in and one deliberately not:** the pending-rulings half of the same
audit pass, `audit-decisions-2026-10.md`, went to `PLANS-AND-BRIEFS.md` rather than here, because the
register copy in this file is declared not edited forward above and that master already carries
owner-gate packages. A reader looking for the decisions owed on the 2026-10 pass needs both files.

**Round 12 (2026-10-06), two of that round's ten sources in, both from the gitignored scratch tree:**

- `.scratch-uma/audit-status.md` (113 lines), folded in as the section `## audit-status.md` at the end of
   this file. It is the Phase 0 re-verification of the 2026-10-01 audit against `master` at `40018c0`, with
   a verdict and a file:line proof per item, and it is the committed audit pass's other half: its prose
   sibling `audit-decisions-2026-10.md` lives in `PLANS-AND-BRIEFS.md` §`## audit-decisions-2026-10.md`,
   which points back at this source by its old scratch path at `PLANS-AND-BRIEFS.md:500` and `:779`. The
   nine fix commits the two halves record are split unevenly, so they are named rather than blurred: the
   decisions file carries `fda8bba`, `20364ae`, `ecae77d`, `a3e323c` and `80caefd`, while `bd3f83d`,

```text
`13b50e5`, `08852c1` and `9e65561` (F-1, F-7 and F-3) are recorded only in the verdict table embedded
below. Chosen home: this file is the audits master. Two of its citations into this file were already
stale when it was written and are left verbatim inside the section as the dated record they are: it
cites `AUDIT-AND-VERIFICATION.md:906` and `:3135` for the KI-23 / KI-23b reconciliation. Both targets
are named rather than numbered here, because any line number written in this header moves the next time
this header is edited: the first is the `KI-23` row of the phase audit's open-issues table, the second
is the `### KI-23b` heading in the register below (`:951` and `:3180` as this block now stands). Its §7
inventory of peer-dirty files is a 2026-10-04 measurement and is not re-verified here.
```

- The five register snapshots `.scratch-uma/ki_{base,head,s6,s7,s9}.md` were **not** embedded whole. Each is
  a complete copy of the register above as it stood after one slice, and every entry body is already here;
  what they uniquely held was their status headers, two fragments of which the register's own chain had
  dropped. Those fragments are embedded verbatim in the section `## Register snapshots, 2026-09-28`, which
  also records the containment measurement that decided the rest was redundant. That section is the record
  of the fold; the register above it stays as it is, because it is the dated snapshot that is not edited
  forward.
- The originals of every source in this round were untracked and gitignored, so the embedded copies here
   are now the only durable ones. The five snapshots were left on disk at first, then **deleted 2026-10-06 on
   the owner's instruction**, because a delete at `.scratch-uma/` (gitignored, `.gitignore:90`) is
   unrecoverable and the owner was asked before it happened. Each was re-verified first on the same rule as
   a tracked fold: the audit came back 90/90 non-blank lines found in its `## audit-status.md` section, and the
   snapshots re-measured 423/437, 423/437, 423/437, 484/514 and 514/548 contained against the register, with
   their unique status fragments embedded as recorded above. The embedded sections are now the only copies of
   those files. The two dated exceptions to "no longer cited as live sources" (`PLANS-AND-BRIEFS.md:500` and
   `:779`, which named the deleted `.scratch-uma/audit-status.md`) were corrected forward with dated notes the
   same day rather than edited in place, so both now name this file's `## audit-status.md` section.

Round 7 dissolved four single-topic masters into the masters that already owned their subject. This
file is the audits master, so both audits now sit here rather than in a fifth file. Note that
`docs/research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md` (Round 5) holds the same inventory
content and is now a duplicate of Part 1 below; retiring it is the owner's call and it is recorded,
not done, in `INDEX.md`.

**Round 8 (2026-10-03), owner-authorized:** the defect register, `KNOWN-ISSUES.md` (2,655 lines), is
embedded here as the "KNOWN-ISSUES.md (defect register)" section, and the root file is now a pointer
stub that remains the live append target: agents continue to add new KI entries to `KNOWN-ISSUES.md`
at the root, and the snapshot in this file is a dated record that is not edited forward. This file
carries the census marker at its top for that reason; see the header note.

**Round 14 (2026-10-10, augment-only):** the Rice Shower / Unity Cup audit set, four live documents
that remain at their own paths, embedded verbatim as their own sections: `rice-shower-unity-cup-ux-walk.md`
(957 lines), `rice-shower-unity-cup-ux-walk-2.md` (941), `rice-shower-unity-cup-ux-walk-resolutions.md`
(495), and `rice-shower-unity-cup-round2-reconciliation.md` (169 at first augment, re-synced the same day
to the live source's current state). The first three are closed records; the reconciliation is a live
record still being appended to, so its section was regenerated once on 2026-10-10 and will need the same
treatment again when the round closes. Nothing was deleted and no citation was repointed.

**Appended 2026-10-12:** the section `## Gemini Deep Research reconciliation, 2026-10-12`, a claim-level
reconciliation of an incoming Gemini Deep Research report on `[Global]` mechanics (with its peer check) against
this repository's own evidence. It is a new dated section, not a fold: no source file was absorbed, nothing was
deleted, and no citation was repointed. It is a live record of a single pass and is not expected to be
re-appended. The one dependent edit it required outside this file is a dated erratum at
`UMAMUSUME_REFERENCE.md` §1.3.5 (finding F-1); the guide itself is otherwise untouched.

---

## audit-verification-2026-10-01.md

### Audit Verification: the Eighteen Findings Against Post-Fix State

**Date:** 2026-10-01
**Pass:** third. First pass produced the findings, second confirmed them, this pass verifies whether the corrections landed.
**Repository state verified at:** `master` @ `4129cf0` (working tree clean of tracked edits except the peer files listed in the fence section).
**Stance:** read-only. Nothing was fixed, edited, migrated, committed or pushed by this pass. The only write is this file.

#### 1. Method note

**Disciplines applied.** Doubt-driven development on every verdict, meaning no verdict rests on a commit message or a prior audit's citation. Source-driven development, meaning every citation below was re-read from the current file, not carried over. Code-review-and-quality supplied the six axes in section 5. Testing-best-practices and test-driven-development governed the test-honesty axis. Security-and-hardening governed F-9 and F-3. Performance-optimization governed F-4, F-5, F-7, E-8. Laravel-best-practices governed the app-layer reads. Careful governed the fence.

**Skill invocations.** See section 6. Two of the named skills could not be attempted safely and one was declined on cost grounds. That is stated rather than papered over.

**Tooling reliability, and it matters to every number below.** Earlier in this session a tool layer rewrote several of my commands and returned output attributing work to git objects that do not exist. Four SHAs appeared in that output, `9274f7d`, `c1882f1`, `742390c`, `8443b84`, and `git cat-file -e` fails for all four, so none exists. Two file rewrites also reported success while the file on disk did not change. Consequently every claim in this document was produced by one of three methods: the Read tool on the file itself, `git grep` or `git ls-files` whose result I cross-checked against a second command, or a test run whose output is reproducible. No verdict in section 2 rests on a single unrepeated shell echo.

**Commands run.** `Read` on `StoreCharacterCards.php`, `StoreRaceCatalogSlots.php`, `SourceDocumentSeeder.php`, `SkillSeeder.php:55-82`, `SourceFetcher.php:140-170`, `CatalogController.php:214-236`. `git grep` for `DB::transaction`, `Cache::lock`, `FetchSourceJob`, `maxRedirects`, `retry(`, `catalog:version`, `is_manual`, `cap_speed`, `cache.ttl`, `seed_file`, `1200`. `git ls-files --error-unmatch` on six paths. `git log` for last-touch per finding file. `git show HEAD:` for ref-state comparisons. `php artisan test --compact` and one filtered run.

**Not verified, and why.** The runtime behaviour of the redirect posture (F-9) was not tested against a live source, because that means a network fetch and the fence keeps this pass offline. The concurrency windows in F-7 and E-6 were reasoned from code shape, not reproduced with parallel requests. The test-honesty axis could not be exercised on the fixes, because no fix added a test; see section 5.

#### 2. Verification table

| ID     | Verdict                        | Evidence, current                                                                                                                                                                                                        | Reasoning                                       |
| ------ | ------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ----------------------------------------------- |
| F-1    | **NOT FIXED**                  | `StoreCharacterCards.php:35-108` loops over records with `CharacterCard::create` at `:97` and `->update` at `:103`, no `DB::transaction`, no `DB` import in the use list. `StoreRaceCatalogSlots.php:28-55` same shape   | Both halves unchanged                           |
| F-2    | **NOT FIXED**                  | `SourceDocumentSeeder.php:80-91` still catches `Throwable`, logs, warns, continues                                                                                                                                       | Identical behaviour                             |
| F-3    | **NOT FIXED, both halves**     | `SkillSeeder.php:75-80` delete has no `is_manual` predicate. `updateOrCreate` at `:61-68` writes null over imported rows                                                                                                 | Both halves still present                       |
| F-4    | **NOT FIXED**                  | No unique constraint on `data_sources` or `match_candidates`. Write sites unchanged                                                                                                                                      | Growth still unbounded                          |
| F-5    | **NOT FIXED**                  | `SourceFetcher.php:57` still date-scoped path; no content-hash check                                                                                                                                                     | Short-circuit still missed                      |
| F-6    | **NOT FIXED**                  | `FetchSourceJob` only hit is its own class declaration. Nothing dispatches it                                                                                                                                            | Still dead code                                 |
| F-7    | **NOT FIXED**                  | `storeTurn` and `storeRace` write multiple rows with no transaction                                                                                                                                                      | Non-atomic multi-write persists                 |
| E-3    | **NOT FIXED**                  | Six untracked files confirmed not in git. Was never added                                                                                                                                                                | KI-49 and KI-51 remain open                     |
| F-8    | **NOT FIXED**                  | Only `UmaFetch.php:55` uses `Cache::lock`. `UmaReparse` has none                                                                                                                                                         | Reparse writes unlocked                         |
| F-9    | **NOT FIXED**                  | `SourceFetcher.php:167` still `maxRedirects(2)`. No post-redirect host assertion                                                                                                                                         | SSRF posture unchanged                          |
| F-10   | **NOT FIXED**                  | `CatalogController.php:233` still `Cache::remember('catalog:version', 3600, fn () => 0)`. Writer does `Cache::add` then `Cache::increment`                                                                               | Counter collision window                        |
| E-1    | **NOT FIXED, doc side**        | `ARCHITECTURE.md:276` still claims exponential backoff; code uses flat 500ms                                                                                                                                             | Doc not corrected                               |
| E-8    | **NOT FIXED**                  | Factory `numberBetween(0, 1200)`; `stat-band.blade.php:189` literal 1,200; scenario caps unused                                                                                                                          | All four sites unchanged                        |
| E-9    | **NOT FIXED, still skipped**   | 2 skipped tests unchanged                                                                                                                                                                                                | D-288 and G-18 still don't execute              |
| C-3    | **NOT FIXED**                  | Five `config/uma.php` lines still claim cache TTL bounds fetch load                                                                                                                                                      | Comment credit wrong                            |
| C-4    | **NOT FIXED**                  | `Scenario::cap_*` never read outside model declaration and parser                                                                                                                                                        | Table holds fetched truth app doesn't consult   |
| E-2    | **NOT FIXED**                  | `UmaFetch` returns `FAILURE` on abort; `SourceDocumentSeeder` catches and continues                                                                                                                                      | Asymmetry intact                                |
| E-10   | **NO CHANGE REQUIRED**         | `composer.json:49` still runs bare `php artisan serve` concurrently                                                                                                                                                      | State recorded                                  |

**Tally:** 0 FIXED, 17 NOT FIXED, 1 no-change, 0 REGRESSED.

#### 3. New findings

**N-1, High:** `F-1` and `F-2` naming collision — a commit `4e17997` uses "F-1, F-2" for unrelated UI fixes. A false `FIXED` verdict possible if searching history by label.

**N-2, Medium:** Three files (`SourceDocumentSeeder.php`, `ReadsCommittedSource.php`, `UmamusumeRosterSeeder.php`) are untracked — no committed state to verify against.

**N-3, Medium:** Source fetch failure returns `null` silently at the fetch layer (`SourceFetcher.php:149-155` catches `RequestException` → `null`; combined with `->retry(..., throw: false)`, exhausting retries yields null with no log line).

**N-4, Medium:** Version key resets mid-life — `Cache::add('catalog:version', 0, 3600)` then `Cache::increment`. Page cache keys embed the version number, and version 1 of hour 2 reuses the key namespace of version 1 of hour 1, serving stale pages as fresh.

#### 4. Doc alignment

| Doc                                | State                                   | Match                                                        |
| ---------------------------------- | --------------------------------------- | ------------------------------------------------------------ |
| `ARCHITECTURE.md:272, :279`        | Still present `FetchSourceJob`          | Nothing dispatches the job                                   |
| `ARCHITECTURE-ESSENTIALS.md:49`    | Lists `ShouldBeUnique FetchSourceJob`   | Same, not dispatched                                         |
| `ARCHITECTURE.md:276`              | Claims exponential backoff              | Flat 500ms in code                                           |
| `SkillSeeder.php:30-38` docblock   | Explains cost/type left for import      | Matches intent, contradicts effect — nulls imported values   |

#### 5. Not-a-finding checks, and the six axes

**Not-a-finding, confirmed clean.** `StoreSupportEffects.php:19-23` still discloses the missing `is_manual` guard.

**Six axes:** Correctness (not assessable, no fix landed). Regression (none — suite returns 1003 passed, 2 skipped, 16,404 assertions, byte-identical to previous audit). Scope creep (not assessable). Test honesty (not assessable — no test forces mid-batch throw). Doc alignment (section 4). Recovery (N-3 is the only new failure mode).

#### 6. Skills invoked, and the refusals

Resolved from roster: `doubt-driven-development`, `source-driven-development`, `code-review-and-quality`, `debugging-and-error-recovery`, `security-and-hardening`, `performance-optimization`, `test-driven-development`, `laravel-best-practices`, `testing-best-practices`, `antislop-copywriting`, `gstack:careful`, and `gstack:codex`.

**Not resolvable:** `infer-conventions` exists on disk but not in session roster.

**Declined:** `gstack:codex` — with zero FIXED verdicts, nothing to second-guess.

#### 7. Severity tally

| Bucket     | Original findings                       | New findings        |
| ---------- | --------------------------------------- | ------------------- |
| Critical   | 3 (F-1, F-2, F-3), all open             | 0                   |
| High       | 5 (F-4–F-7, E-3), all open              | 1 (N-1)             |
| Medium     | 6 (F-8–F-10, E-1, E-8, E-9), all open   | 3 (N-2, N-3, N-4)   |
| Low        | 4 (C-3, C-4, E-2, E-10)                 | 0                   |
| Fixed      | **0**                                   | 0                   |

#### 8. What this pass did not do

No file other than this one was written. No source file, view, controller, model, migration, test, config, PRD, ADR, or DESIGN.md was edited. Nothing was committed or pushed. No network fetch was attempted.

---

## documentation-inventory-and-unfinished-phases.md

## Documentation inventory and unfinished development phases

**This is the consolidated reference for the 2026-09-30 documentation inventory and the 2026-09-29 unfinished-phases audit. Do not create new files for this topic. Update this file instead.**

### Provenance

- research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md (653 lines, 7 em-dashes preserved verbatim)
- research-scratch/unfinished-phases-audit-2026-09-29.md (389 lines, 66 em-dashes preserved verbatim)

Both parts are embedded verbatim. Every original heading is demoted by one level so this file carries a single H1; each part opens with its own ## header, and inline markdown (tables, block quotes, bold/italic, task lists, code fences) is byte-identical to the source. Nothing was summarised, deduplicated, or reworded; where the two documents describe the same tree from different angles, both readings stand.

---

### Part 1 — Documentation inventory, 2026-09-30

### Documentation inventory, 2026-09-30

Snapshot SHA: `c1e14a3`. Every count, line number and hash below was read from that tree.

This is an inventory. It merges nothing, deletes nothing, edits nothing. Section 10 proposes; the owner rules.

#### 1. Skills invoked

| Skill                            | Used for                                                                                         | Result                                                                                                                                                                       |
| -------------------------------- | ------------------------------------------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `documentation-and-adrs`         | Governing skill. Type classification, ADR lifecycle, the heading-over-line citation convention   | Loaded. Its ADR template says `docs/decisions/`; this repo uses `docs/adr/NNNN-title.md` with an inline `Status:` line, so the repo convention won, as the skill instructs   |
| `gstack:careful`                 | The read-only fence                                                                              | Loaded. Its own telemetry line writes to `~/.gstack/analytics/skill-usage.jsonl`; I skipped that write because the fence says read-only                                      |
| `antislop-copywriting`           | Every Description cell and every finding in this file                                            | Loaded. No em dash in this document's own prose except inside verbatim filenames                                                                                             |
| `doubt-driven-development`       | The Status column, which carries a burden of proof                                               | Loaded earlier in the session. Its fresh-context reviewer step was not run; see section 2, limit 5                                                                           |
| `source-driven-development`      | Citation resolution: a link is live only if the target resolves                                  | Applied through the scan in section 5, not by fetching external docs                                                                                                         |
| `humanizer`                      | Final prose pass                                                                                 | Requested by the brief. Not invoked before writing this draft; it is still owed on the sections the owner decides to keep                                                    |
| `using-agent-skills`             | Confirming the skill set named in the brief is current                                           | Confirmed against the live session roster rather than by loading the skill                                                                                                   |
| `interview-me`                   | Ambiguous fence                                                                                  | Not needed as a flow: the two ambiguities went to `AskUserQuestion` and both were answered (see section 2, limit 1)                                                          |
| `document-generate` (Diataxis)   | Type sanity-check                                                                                | Unavailable under that bare name in this session; it is `gstack:document-generate`. Diataxis was applied by hand to the reference/explanation split in section 3.1           |
| `guard`                          | Alternative fence                                                                                | Not loaded. The brief allowed `careful` or `guard`; `careful` was taken                                                                                                      |

#### 2. Method note, and its limits

**What was scanned.** Every tracked and every untracked-but-not-ignored file whose basename ends `.md`, `.txt`, `.rst`, `.adoc` or `.org`. Plus `.json`, `.yaml`, `.csv` and `.sql` under `docs/`. Plus `Makefile`, the one extensionless tracked file that carries prose. `artisan` is extensionless and tracked but is code, so it is out.

**What was excluded, with counts.** `.rst`, `.adoc` and `.org`: none exist. Ignored doc-shaped files: 2442, and they are not documentation of this project. The top of that pile: `vendor/` 351, `.agents/` 197, `node_modules/` 144, then a long tail of agent-config directories the repo carries but does not track: `.opencode` 137, `.kilo` 135, `.claude` 132, `.cursor` 127, `.kiro` 117, `.gemini` 106, `.augment` 60, `.codebuddy` 60, `.codewhale` 60, `.continue` 60, `.factory` 60. `research-scratch/` is ignored and holds 300-plus working files; `docs/vibe_images/` is untracked and holds 10 PNGs and no text. Both are noted, not inventoried.

**Tool limitations, all of which cost accuracy before I caught them.**

1. Two fences in the standing instruction collided with this brief: it asked for the deliverable at `docs/design-research/DOCUMENTATION-INVENTORY-2026-09-30.md`, and an earlier Authorized Execution Turn put `docs/design-research/**` on the forbidden-edit list. I stopped and asked. Owner's answer: write to `research-scratch/` instead. That is why this file is where it is.
2. The depth of section 5 was also ambiguous, and also asked. Owner's answer: full verification.
3. `git ls-files` quotes non-ASCII paths. That hid one untracked file, `docs/UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md`, from my first two enumerations because the quoted line ends with `"` and not `.md`. Every count after `core.quotepath=false` is the corrected one. Anyone re-running this scan should pass that flag first.
4. Two regular-expression bugs reached a first draft, and I caught both only because the output looked wrong rather than because the check would have failed. `js` before `json` in an alternation matched every `.json` citation as `.js`. A greedy path prefix turned `PRD.md` into `D.md`, which zeroed the inbound count for all eleven root documents and made them look orphaned. A checker that reports the same shape for a correct and an inverted result is not a checker; both bugs were found by reading the output, not by the script.
5. Independent review was not performed. `doubt-driven-development` wants a fresh-context adversarial reviewer for each Status verdict, and three Explore agents in an earlier pass died on a daily usage limit. The verdicts here are single-observer. Each carries its evidence so a second reader can disagree against a fact rather than against a feeling.
6. **The tree moved during the scan.** It started at `85b37a4`, was at `89675e6` mid-pass, and finished at `c1e14a3`, three commits later, all three of them documentation commits by a concurrent session. `docs/adr/0013-character-profile-source.md` and `docs/data/2026-09-30-characters-source-probe.md` were branch-only at the start of this session and are tracked on master now, with ADR-0013 marked Withdrawn. A prior audit of mine said the opposite; that audit is at `research-scratch/unfinished-phases-audit-2026-09-29.md` and section 11 of it needs correcting forward, not overwriting.
7. Classification is judgment. Type and Status are the only judgment columns, Status is the one with a burden of proof, and every non-`unknown` Status in section 4 names its evidence in the same row.

#### 3. Summary counts

**202 files in scope.** 193 tracked, 9 untracked, 0 ignored-and-in-scope.

| Dimension          | Breakdown                                                                                                                                                                                                                                                                                               |
| ------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| By tracked state   | tracked 193, untracked 9                                                                                                                                                                                                                                                                                |
| By extension       | `.md` 100, `.txt` 95, `.json` 6, extensionless 1 (`Makefile`)                                                                                                                                                                                                                                           |
| By directory       | `docs/frontend-review/2026-09-28/` 97, `docs/design-research/**` 44, repo root 13, `docs/adr/` 13, `.ai/**` 12, `docs/scenarios/` 9, `docs/requests/**` 8, `docs/` (top) 5, `docs/data/` 3, `docs/flows/` 1, `docs/GATE-REGISTRY.md` and 3 sibling top-level `docs/` files counted above, `public/` 1   |
| By type            | probe-record 104, reference 19, verification 15, adr 13, audit-report 9, constraint 6, brief 6, config-doc 5, spec 5, scenario 5, plan 3, research 3, readme 2, notes 2, other 2, session-log 2, issue-register 1                                                                                       |
| By status          | current 169, unknown 20, stale 8, superseded 5, duplicate 0                                                                                                                                                                                                                                             |

Why `duplicate` is empty: no two files here are duplicates in the sense a consolidation pass can act on. Six pairs cover overlapping ground while describing different things, and section 8 lists them as candidates rather than as duplicates. `probe-record` carrying 104 of 202 rows is also not an accident: 94 of them are one frozen capture set, described in section 4.2.

`unknown` is 20 rather than a smaller number because the Status column carries a burden of proof and most of those files have no evidence either way. Under `doubt-driven-development` the honest verdict for "this looks old but I cannot say what would prove it is current" is `unknown`, not `stale`.

#### 4. The flat inventory

Columns: Path | bytes / lines | Tracked | Last commit | Type | Description | Status | Superseded by or duplicates | Inbound cites | Line-citation risk.

`Inbound cites` is how many other in-scope files name this file, from section 6. Read it precisely: the count is **distinct in-scope files whose citation resolves to this exact path**, so a file citing both `CONSTRAINTS.md` and `docs/design-research/CONSTRAINTS.md` contributes 1 to each of the two rows. A looser count of files whose text contains the bare basename gives 52 for root `CONSTRAINTS.md` instead of 45, because 29 files write the full path to the design-research twin and 22 of those write both. The gap is section 6.2's collision, not a counting error. `Line-citation risk` is `yes` when the file itself cites another file by `path:NNN`.

Verification of these columns is in section 14.

##### 4.1 Authored documentation (105 files)

| `Makefile` | 3377 / 69 | tracked | `5820e77` 2026-09-29 | config-doc | Names the repo's gate targets: `lore`, `lore-code`, `test`, `lint`, `stan`. | current | - | 0 | no |
| `.ai/guidelines/custom/domain.md` | 2462 / 44 | tracked | `a72ee76` 2026-09-27 | config-doc | Tells agents the domain vocabulary, the directory boundaries and the authorization pattern to follow. | stale | - | 1 | no |
| `.ai/guidelines/framework/core.md` | 3000 / 62 | tracked | `a72ee76` 2026-09-27 | config-doc | Laravel Boost's framework rules for this repo: strict types, thin controllers, named routes. | stale | - | 0 | no |
| `.ai/rules/code-style.md` | 2444 / 68 | tracked | `a72ee76` 2026-09-27 | constraint | Records the formatting conventions Pint enforces, written down so agents can read them. | unknown | - | 0 | no |
| `.ai/rules/index.md` | 147 / 5 | tracked | `a72ee76` 2026-09-27 | config-doc | Maps file globs to the rule file an agent must read before editing that path. | stale | - | 3 | no |
| `.ai/rules/testing-standards.md` | 2697 / 59 | tracked | `a72ee76` 2026-09-27 | constraint | Sets the Pest 4 test conventions: layout, naming, database handling. | unknown | - | 0 | no |
| `.ai/skills/creating-models/SKILL.md` | 4379 / 141 | tracked | `a72ee76` 2026-09-27 | reference | How to scaffold an Eloquent model, migration, factory and seeder in this stack. | unknown | - | 0 | no |
| `.ai/skills/deploying-to-cloud/SKILL.md` | 2729 / 84 | tracked | `a72ee76` 2026-09-27 | reference | How to deploy to Laravel Cloud. Never triggered here; the product is local-only. | unknown | - | 0 | no |
| `.ai/skills/infer-conventions/SKILL.md` | 2810 / 57 | tracked | `a72ee76` 2026-09-27 | reference | How an agent should read neighbouring files and copy their conventions. | unknown | - | 0 | no |
| `.ai/skills/laravel-best-practices/SKILL.md` | 3410 / 109 | tracked | `a72ee76` 2026-09-27 | reference | Laravel review checklist: controllers, models, migrations, requests, policies, jobs. | unknown | - | 0 | no |
| `.ai/skills/pest-testing/SKILL.md` | 2388 / 77 | tracked | `a72ee76` 2026-09-27 | reference | Pest 4 syntax and structure for tests in this repo. | unknown | - | 0 | no |
| `.ai/skills/running-tests/SKILL.md` | 1511 / 43 | tracked | `a72ee76` 2026-09-27 | reference | How to invoke the suite and read the result. | unknown | - | 0 | no |
| `.ai/skills/tailwindcss-development/SKILL.md` | 1805 / 55 | tracked | `a72ee76` 2026-09-27 | reference | Tailwind v4 CSS-first usage: `@theme`, no config file, responsive grids. | unknown | - | 0 | no |
| `AGENTS.md` | 26470 / 390 | tracked | `6f9c98a` 2026-09-27 | spec | The role table, per-agent duties, escalation paths and the lore gate, plus embedded Laravel Boost guidance. | stale | - | 27 | no |
| `ARCHITECTURE-ESSENTIALS.md` | 13015 / 94 | tracked | `26aa9fe` 2026-09-29 | reference | The short digest of ARCHITECTURE.md that gets pasted into agent context. | unknown | - | 12 | no |
| `ARCHITECTURE.md` | 22672 / 324 | tracked | `26aa9fe` 2026-09-29 | spec | System design: schema, fetch pipeline, routes, security model, revision 0.2 marked by repo of origin. | unknown | - | 20 | no |
| `CONSTRAINTS.md` | 5308 / 48 | tracked | `e9a779d` 2026-09-30 | constraint | The global quality bar C-1 to C-9, each with a threshold and the command that proves it. | current | - | 45 | no |
| `DESIGN.md` (root) | 27646 / 394 | tracked | `bcd8abe` 2026-09-29 | constraint | The shipped visual system for Trainer Desk: tokens, components, surfaces. | current | - | 41 | yes |
| `KNOWN-ISSUES.md` | 150145 / 1944 | tracked | `89675e6` 2026-09-30 | issue-register | The defect register. Each entry names the command or file that proves it. | current | - | 25 | yes |
| `PLAN.md` | 68985 / 701 | tracked | `a4da6d1` 2026-09-30 | plan | The slice-by-slice frontend plan, rulings R-nn, and the open Livewire question with its evidence. | stale | - | 16 | yes |
| `PRD.md` | 20450 / 153 | tracked | `3bac088` 2026-09-30 | spec | Product truth: users, stories, functional requirements, non-goals, open questions. | current | - | 35 | no |
| `PRODUCT.md` | 8409 / 170 | tracked | `e697ce3` 2026-09-28 | reference | Product summary for design and agent context: name, audience, commitments. | unknown | - | 8 | no |
| `README.md` | 7657 / 136 | tracked | `bbfa3de` 2026-09-29 | readme | The front door and the documentation map that says which file owns which question. | current | - | 0 | no |
| `SKILL.md` | 19502 / 231 | tracked | `775b88a` 2026-09-27 | reference | A hand-counted table of the skills installed in this project. | stale | the `refresh-skill-registry` skill, which rebuilds this from disk (no in-repo file) | 1 | no |
| `source.md` | 9988 / 172 | tracked | `775b88a` 2026-09-27 | notes | The system prompt that initialised this repo through Laravel Boost, kept as the record of that step. | current | - | 1 | no |
| `docs/GATE-REGISTRY.md` | 12724 / 159 | tracked | `31f97a5` 2026-09-29 | constraint | Which gate enforces what, where the gate lives, and which gates are knowingly not automated. | current | - | 12 | yes |
| `docs/PRE-MORTEM.md` | 12682 / 98 | tracked | `775b88a` 2026-09-27 | research | What could go wrong in the consolidation, written before the artefacts it then shaped. | current | - | 11 | no |
| `docs/SKILL_AUTOMATION.md` | 3077 / 91 | tracked | `cf8021d` 2026-09-27 | config-doc | How the repo's skill-discovery hook finds and loads skills. | unknown | - | 1 | no |
| `docs/SOURCE-OF-TRUTH.md` | 19283 / 295 | tracked | `2033434` 2026-09-29 | reference | One page that restates the binding rules from seven other documents for agents that read only one. | stale | - | 10 | no |
| `docs/UMAMUSUME_REFERENCE.md` | 448522 / 2179 | tracked | `24e491c` 2026-09-29 | reference | The mechanics corpus, eight sections, source-cited and dated. Everything the UI asserts about the game traces here. | current | - | 41 | yes |
| `docs/data/2026-09-29-global-roster-crosscheck.md` | 36974 / 504 | tracked | `07941eb` 2026-09-30 | probe-record | Every Global costume card checked against two publishers, with the method and its date. | current | - | 2 | no |
| `docs/data/2026-09-30-characters-source-probe.md` | 13228 / 201 | tracked | `4ba2962` 2026-09-30 | probe-record | Re-resolves the `characters` source: coverage per field over 163 rows, measured before any column was drawn. | current | - | 3 | yes |
| `docs/data/roster-crosscheck-table.md` | 13112 / 109 | tracked | `cadb4e3` 2026-09-30 | other | The generated 107-row card table that the crosscheck narrative reads, produced by `tools/roster-crosscheck.php`. | current | - | 2 | no |
| `docs/adr/0001-lift-no-prediction-nongoal-for-energy-guidance.md` | 12315 / 118 | tracked | `27ff1f6` 2026-09-27 | adr | Accepted in part: the Energy guidance decision stands, its schema section moved into ADR-0003. | superseded | ADR-0003 for section 5, recorded inside the file | 1 | no |
| `docs/adr/0002-scenario-caps-exceed-validation-bound.md` | 14012 / 119 | tracked | `27ff1f6` 2026-09-27 | adr | Accepted: scenario caps pass the validation bound, so the bound moves to 0..2000. Not implemented yet. | current | - | 2 | no |
| `docs/adr/0003-consolidated-phase1-schema-expansion.md` | 23562 / 231 | tracked | `5c65597` 2026-09-28 | adr | The one schema amendment that absorbs Energy, Fans, `turn_events` and race tracking. | current | - | 3 | yes |
| `docs/adr/0004-aptitude-and-scenario-cap-reference-data.md` | 4859 / 76 | tracked | `775b88a` 2026-09-27 | adr | Accepted: aptitude letters and scenario caps stored as reference data rather than computed. | current | - | 1 | no |
| `docs/adr/0005-support-card-entities.md` | 13887 / 187 | tracked | `94db315` 2026-09-28 | adr | Declined for Phase 1 under owner ruling R37: no support-card entities, and the question stays closed. | superseded | closed by R37; the file is the record of that | 2 | no |
| `docs/adr/0006-design-authority-and-theme-default.md` | 5735 / 101 | tracked | `da6747a` 2026-09-27 | adr | Accepted, Option 2: light base, stored preference, then `prefers-color-scheme`. | current | - | 0 | no |
| `docs/adr/0007-c7-loading-state-scope-for-server-rendered-views.md` | 3879 / 73 | tracked | `e697ce3` 2026-09-28 | adr | Narrows C-7: an initial server render may rely on the browser's own loading, user-initiated async may not. | current | - | 1 | no |
| `docs/adr/0008-character-card-catalog-layer.md` | 36463 / 448 | tracked | `038dd43` 2026-09-30 | adr | The costume-card catalog layer, its tables, and a card reference on the run. | current | - | 5 | yes |
| `docs/adr/0009-scenario-slot-seeding.md` | 11917 / 174 | tracked | `b295d16` 2026-09-29 | adr | Ruled in part: Option A only, URA Finale from a committed client export. Options B and C stay open. | current | - | 1 | yes |
| `docs/adr/0010-legacy-selection-payload.md` | 7730 / 71 | tracked | `26aa9fe` 2026-09-29 | adr | Accepted: the Legacy Select read-back is one typed JSON payload on the run, not columns. | current | - | 1 | yes |
| `docs/adr/0011-skills-reference-import.md` | 12210 / 161 | tracked | `aa5b05c` 2026-09-29 | adr | Accepted in part: what the skills export actually carries, and the fields the owner authorised storing. | current | - | 2 | yes |
| `docs/adr/0012-card-detail-fields-and-images.md` | 18955 / 225 | tracked | `038dd43` 2026-09-30 | adr | Card detail fields, stat arrays, images, objectives. Its Decision 4 is what replaced ADR-0013. | current | - | 3 | yes |
| `docs/adr/0013-character-profile-source.md` | 16155 / 216 | tracked | `5a50900` 2026-09-30 | adr | The character profile source, withdrawn on 2026-09-30 in favour of ADR-0012 Decision 4. | superseded | ADR-0012 Decision 4, stated in its own Status line | 1 | yes |
| `docs/design-research/CONSTRAINTS.md` | 142636 / 973 | tracked | `9300e6c` 2026-09-29 | constraint | The user-facing design contract, D-numbered, covering Blade, CSS, client JS, copy and export headers. | current | - | 30 | yes |
| `docs/design-research/DESIGN.md` | 152232 / 1640 | tracked | `a22d38c` 2026-09-30 | spec | The Phase 2 design contract: screens, tokens, component anatomy and motion budgets. | current | - | 21 | yes |
| `docs/design-research/EXTERNAL-DESIGN-REVIEW-TRIAGE-2026-09-28.md` | 7908 / 131 | tracked | `3ad10da` 2026-09-28 | audit-report | Disposition of an outside design review, including an erratum against a count that was read off the wrong tree. | current | - | 0 | yes |
| `docs/design-research/HANDOFF-RACE-READ-PATH-2026-09-29.md` | 6051 / 80 | tracked | `7897684` 2026-09-29 | notes | Handoff note: the race read path moves to `race_catalog_slots`, and three collisions get named before anyone edits. | current | - | 1 | yes |
| `docs/design-research/RACE-CALENDAR-GAPS.md` | 19727 / 352 | tracked | `a8a52cd` 2026-09-29 | research | The open questions on the race calendar, each left open on purpose with what would close it. | current | - | 2 | yes |
| `docs/design-research/RAW-FINDINGS.md` | 34485 / 367 | tracked | `a7cabc0` 2026-09-27 | research | Visual research from the game client and store properties, recorded before the design contract was written. | current | - | 8 | no |
| `docs/design-research/SCENARIO-DIFFERENCES.md` | 29737 / 211 | tracked | `a51e2f0` 2026-09-27 | reference | Comparison matrix of the three Global career scenarios, built so the UI could be scenario-aware. | current | - | 9 | no |
| `docs/design-research/SCREENSHOT-MANIFEST.md` | 7617 / 80 | tracked | `a51e2f0` 2026-09-27 | reference | Triage of `docs/game-screenshots/` by scenario and screen type, so gaps in coverage are visible. | current | - | 5 | no |
| `docs/design-research/SESSION-CONSOLIDATION-2026-09-30.md` | 39814 / 187 | tracked | `c1e14a3` 2026-09-30 | audit-report | Verification of eight session reports against the tree, with the refutation rate and the claims that survived. | current | - | 0 | yes |
| `docs/design-research/SKILLS-GAPS.md` | 58715 / 710 | tracked | `da8d6c5` 2026-09-29 | audit-report | The skill-system gap audit, with the dark-theme measurement pass in section 8. | current | - | 6 | yes |
| `docs/design-research/replan-mobile-first.md` | 26200 / 372 | tracked | `9e896d5` 2026-09-29 | plan | Slice 16's replan: mobile-first, Legacy Select, and whether narrow screens can reach the content. | current | - | 2 | yes |
| `docs/design-research/_scratch/WEB-FINDINGS.md` | 28769 / 390 | tracked | `e0e043c` 2026-09-27 | probe-record | Measured findings from Cygames' web properties. Its own first line calls it the companion to `CLIENT-FINDINGS.md`, which is not in the tree. | stale | - | 1 | no |
| `docs/design-research/_scratch/accents.json` | 9643 / 519 | tracked | `e0e043c` 2026-09-27 | probe-record | Accent-colour measurements pulled from the screenshots. | unknown | - | 1 | no |
| `docs/design-research/_scratch/clusters.json` | 192287 / 10174 | tracked | `e0e043c` 2026-09-27 | probe-record | Colour clusters computed from the screenshot set. | unknown | - | 2 | no |
| `docs/design-research/_scratch/colorprobes.json` | 9616 / 685 | tracked | `e0e043c` 2026-09-27 | probe-record | Point colour probes taken against the screenshots. | unknown | - | 1 | no |
| `docs/design-research/_scratch/colorprobes2.json` | 3992 / 293 | tracked | `e0e043c` 2026-09-27 | probe-record | The second round of point colour probes. | unknown | - | 1 | no |
| `docs/design-research/_scratch/signatures.json` | 249719 / 1 | tracked | `e0e043c` 2026-09-27 | probe-record | Per-screenshot luminance, saturation and cell signatures, one JSON array on a single line. | unknown | - | 1 | no |
| `docs/design-research/_scratch/tokens.json` | 5584 / 252 | tracked | `e0e043c` 2026-09-27 | probe-record | The measured design tokens `tools/gate.py` reads as its anchor input. | current | - | 1 | no |
| `docs/design-research/prototypes/superseded/README.md` | 1550 / 16 | tracked | `e0e043c` 2026-09-27 | readme | Says plainly that the four prototypes here are kept for the record and are not the current design. | current | - | 0 | no |
| `docs/design-research/verification/cardless-band-2026-09-30.md` | 8404 / 130 | tracked | `8acb678` 2026-09-30 | verification | Record of the cardless band in the default trainee list, with the pixel measurements that left the fold question open. | current | - | 0 | yes |
| `docs/design-research/verification/design-pass-trainee-detail-2026-09-29.md` | 11848 / 170 | tracked | `a18d77a` 2026-09-29 | verification | Design pass on the trainee detail page and the run screen's skill selector, and what it deliberately did not touch. | current | - | 1 | yes |
| `docs/design-research/verification/slice-2-2026-09-28.md` | 21100 / 406 | tracked | `78697e9` 2026-09-28 | verification | Slice 2 record, including the destructive gate a safety policy refused three times. | current | - | 3 | yes |
| `docs/design-research/verification/slice-3-2026-09-28.md` | 12233 / 210 | tracked | `8c9faf9` 2026-09-29 | verification | Slice 3 record, KI-4's resolution and the untracked-file scope left open. | current | - | 2 | no |
| `docs/design-research/verification/slice-5-2026-09-28.md` | 17049 / 294 | tracked | `8c9faf9` 2026-09-29 | verification | Slice 5 record, and section 4, the round-trip latency numbers the Livewire decision rests on. | current | - | 5 | no |
| `docs/design-research/verification/slice-6-2026-09-28.md` | 16607 / 238 | tracked | `8c9faf9` 2026-09-29 | verification | Slice 6 record: adjudicating what Slice 5 left open and landing the doc amendments. | current | - | 3 | yes |
| `docs/design-research/verification/slice-7-2026-09-28.md` | 15006 / 246 | tracked | `0d2dbdc` 2026-09-28 | verification | Slice 7 record, the schema session R33-R37 where three questions were ruled together. | current | - | 2 | yes |
| `docs/design-research/verification/slice-8-2026-09-28.md` | 8912 / 131 | tracked | `5d2ddcc` 2026-09-28 | verification | Slice 8 record: the Unity Cup and Trackblazer panels on Slice 7's payloads. | current | - | 2 | yes |
| `docs/design-research/verification/slice-9-2026-09-28.md` | 5799 / 82 | tracked | `8c9faf9` 2026-09-29 | verification | Slice 9 record, R43-R49: branch reconciliation, register landing, audit fixes. | current | - | 5 | yes |
| `docs/design-research/verification/slice-10-2026-09-29.md` | 28995 / 426 | tracked | `93f860b` 2026-09-29 | verification | Slice 10 record: maintenance and drift, the lore line marker, the `scenario_slots` proposal. | current | - | 2 | yes |
| `docs/design-research/verification/slice-11-2026-09-29.md` | 13262 / 231 | tracked | `6300221` 2026-09-30 | verification | Slice 11 record: calendar slots, the free-race writer, KI-20 closed. | current | - | 3 | yes |
| `docs/design-research/verification/slice-12-2026-09-29.md` | 20612 / 360 | tracked | `c849fc1` 2026-09-29 | verification | Slice 12 record, and the halt: why T4 was not run and nothing was pushed. | superseded | master's own history, which moved past the halt it describes | 2 | yes |
| `docs/design-research/verification/slice-13-2026-09-29.md` | 20718 / 344 | tracked | `debc4d0` 2026-09-29 | verification | Slice 13 record: KI-21 closed, the ordinal fixed, R70's deferred push. | current | - | 3 | yes |
| `docs/design-research/verification/slice-14-2026-09-29.md` | 13999 / 255 | tracked | `952f41a` 2026-09-29 | verification | Slice 14 record: the tier question settled on per-race sourced evidence. | current | - | 4 | yes |
| `docs/design-research/verification/slice-15-2026-09-29.md` | 51714 / 822 | tracked | `ad7cb0a` 2026-09-29 | verification | Slice 15, the schema session: record, verification, and the register write that carried a peer's entries. | current | - | 2 | yes |
| `docs/flows/create-run-and-legacy-select.md` | 7275 / 117 | tracked | `8c9faf9` 2026-09-29 | reference | The create-run and Legacy Select flow, with the validation bound and the error states. | current | - | 2 | yes |
| `docs/requests/2026-09-29-catalog-roster-and-trainee-selector.md` | 16760 / 89 | tracked | `7fea901` 2026-09-29 | brief | The request that opened the catalog roster and searchable trainee selector work. | current | - | 2 | yes |
| `docs/requests/2026-09-29-catalog-roster-and-trainee-selector-plan.md` | 229496 / 3999 | tracked | `d7ae84c` 2026-09-30 | plan | The 3999-line implementation plan for that request, task by task, with the acceptance values quoted from the constraint files. | current | - | 4 | yes |
| `docs/requests/2026-09-29-catalog-roster-report.md` | 18935 / 357 | tracked | `6300221` 2026-09-30 | audit-report | The closing report on the roster and selector work. | current | - | 3 | yes |
| `docs/requests/game-mechanics-condition-labels.md` | 2752 / 61 | tracked | `8c9faf9` 2026-09-29 | brief | Asks for the three lowest Global mood-pill colours, and accepts an explicit UNVERIFIED answer. | current | - | 3 | no |
| `docs/requests/reports/2026-09-30-port-and-cleanup.md` | 11430 / 169 | tracked | `8c16edc` 2026-09-30 | session-log | Run report: three artefacts ported, ADR-0013 withdrawn, the branch deleted. | current | - | 1 | yes |
| `docs/scenarios/01-ura-finale.md` | 10687 / 133 | tracked | `3f631fc` 2026-09-29 | scenario | URA Finale guide for the Global EN server: the baseline scenario every other one is compared to. | current | - | 13 | no |
| `docs/scenarios/02-unity-cup.md` | 25669 / 259 | tracked | `3f631fc` 2026-09-29 | scenario | Unity Cup (Aoharu Hai) guide, Global EN: the team mechanic that replaces solo training. | current | - | 13 | no |
| `docs/scenarios/03-trackblazer.md` | 7664 / 69 | tracked | `3acbb06` 2026-09-27 | scenario | Trackblazer (Make a New Track) guide, Global EN, including its JP release history. | current | - | 7 | no |
| `docs/scenarios/04-trackblazer-umaguide.md` | 15960 / 266 | tracked | `3acbb06` 2026-09-27 | reference | The uma.guide community framing of Trackblazer decks: two frameworks and their assumptions. | current | - | 12 | no |
| `docs/scenarios/05-trackblazer-gametora.md` | 11514 / 216 | tracked | `3acbb06` 2026-09-27 | reference | GameTora's Trackblazer reference: objectives, grade points, conditions. | current | - | 15 | no |
| `docs/scenarios/06-unity-cup-gametora.md` | 18787 / 323 | tracked | `3acbb06` 2026-09-27 | reference | GameTora's Unity Cup reference, and the patch that makes older material wrong. | current | - | 8 | no |
| `docs/scenarios/07-grand-concert.md` | 8372 / 118 | tracked | `3f631fc` 2026-09-29 | scenario | Our Grand Concert: a known-gap stub. Every row is quoted from material already in the repository. | current | - | 10 | yes |
| `docs/scenarios/08-grand-masters-jp-only.md` | 12562 / 180 | tracked | `8c9faf9` 2026-09-29 | scenario | Grand Masters as a `[JP-Only]` note. Nothing in it may be imported until a Global date exists. | current | - | 4 | no |
| `docs/scenarios/09-global-race-calendar.md` | 81903 / 751 | tracked | `122d12b` 2026-09-28 | reference | Which race exists at which turn and what it costs to enter: the availability calendar `scenario_slots` reads. | current | - | 15 | yes |
| `public/robots.txt` | 24 / 2 | tracked | `fda6ff0` 2026-09-27 | other | Disallows all crawlers, which is the machine-readable form of the local-only promise. | current | - | 8 | no |
| `docs/Scenario-Specific User Flows & Frontend Specifications.md` | 65085 / 1174 | untracked | `never` | brief | Per-scenario user flows and frontend specifications, grounded in the reference docs already in the repo. | unknown | - | 0 | no |
| `docs/UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md` | 103979 / 2144 | untracked | `never` | brief | An incoming external UX write-up, marked at the top as triaged on 2026-09-27 and retained rather than adopted. | superseded | its own banner, plus `docs/design-research/MECHANICS-TRANSLATION-TRIAGE.md` | 0 | yes |
| `docs/UX Behavior Specification - Umamusume Trainer Companion.md` | 32288 / 576 | untracked | `never` | spec | A synthesis of DESIGN.md, CONSTRAINTS.md, PRD.md, the ADRs and the scenario files into one behavioural contract. | unknown | - | 0 | no |
| `docs/design-research/FRONTEND-BRIEF-AUDIT.md` | 14644 / 191 | untracked | `never` | audit-report | Checks the incoming frontend brief against the tree and lists the corrections the brief owes. | current | - | 6 | yes |
| `docs/design-research/FRONTEND-SPEC-DIVERGENCE.md` | 22055 / 297 | untracked | `never` | audit-report | Divergence audit between a pasted frontend spec and what this app actually is. | current | - | 10 | yes |
| `docs/design-research/MECHANICS-TRANSLATION-TRIAGE.md` | 19343 / 234 | untracked | `never` | audit-report | Triage of an incoming write-up that recast every game system as a UX pattern catalog. | current | - | 10 | yes |
| `docs/design-research/TASK-16-RUN-VIEW-FRAME-BRIEF.md` | 30220 / 481 | untracked | `never` | brief | Task 16's brief for the run view axis: the two-region frame and the scenario comparison, revised against the tree at `a8a52cd`. | current | - | 1 | yes |
| `docs/requests/reports/2026-09-30-c5-down-enforcement-gap.md` | 4151 / 67 | untracked | `never` | audit-report | Records that C-5 requires a `down()` on every migration and nothing verifies it. | current | - | 0 | no |
| `docs/requests/reports/2026-09-30-characters-source-findings.md` | 12477 / 231 | untracked | `never` | probe-record | Characters-source findings handed to the profile-block slice. | current | - | 0 | no |

##### 4.2 Frozen frontend-review captures (94 files)

Every file under `docs/frontend-review/2026-09-28/` with a `.txt` extension. All 94 landed in one commit, `c6c0567`, on 2026-09-28, except the three under `resolutions/`, which landed in `b6fa798` on 2026-09-29. Status is `current` for all 94 on one argument: a capture is a record of what was observed on a date, and the date is in the directory name. Nothing can make it stale except deleting it, and the brief forbids that judgment here.

| `docs/frontend-review/2026-09-28/api-training-runs-detail.txt` | 854 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Raw JSON body of the `api-training-runs-detail` endpoint as served on 2026-09-28. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/api-training-runs-index.txt` | 2079 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Raw JSON body of the `api-training-runs-index` endpoint as served on 2026-09-28. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/api-umamusume-detail.txt` | 333 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Raw JSON body of the `api-umamusume-detail` endpoint as served on 2026-09-28. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/api-umamusume-index.txt` | 686 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Raw JSON body of the `api-umamusume-index` endpoint as served on 2026-09-28. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-detail-populated-dark.png.console.txt` | 211 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Costume-card detail page in the `populated` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-detail-populated-dark.png.network.txt` | 343 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Costume-card detail page in the `populated` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-detail-populated-light.png.console.txt` | 211 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Costume-card detail page in the `populated` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-detail-populated-light.png.network.txt` | 343 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Costume-card detail page in the `populated` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-empty-dark.png.console.txt` | 214 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `empty` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-empty-dark.png.network.txt` | 349 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `empty` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-empty-light.png.console.txt` | 214 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `empty` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-empty-light.png.network.txt` | 349 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `empty` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-filter-announced-dark.png.console.txt` | 221 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `filter-announced` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-filter-announced-dark.png.network.txt` | 363 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `filter-announced` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-filter-announced-light.png.console.txt` | 221 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `filter-announced` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-filter-announced-light.png.network.txt` | 363 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `filter-announced` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-filter-global-light.png.console.txt` | 220 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `filter-global` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-filter-global-light.png.network.txt` | 361 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `filter-global` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-page2-out-of-range-dark.png.console.txt` | 205 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `page2-out-of-range` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-page2-out-of-range-dark.png.network.txt` | 331 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `page2-out-of-range` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-page2-out-of-range-light.png.console.txt` | 205 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `page2-out-of-range` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-page2-out-of-range-light.png.network.txt` | 331 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `page2-out-of-range` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-populated-dark.png.console.txt` | 198 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `populated` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-populated-dark.png.network.txt` | 317 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `populated` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-populated-light.png.console.txt` | 198 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `populated` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-populated-light.png.network.txt` | 317 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `populated` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-search-dark.png.console.txt` | 213 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `search` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-search-dark.png.network.txt` | 347 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `search` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-search-light.png.console.txt` | 213 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Catalog index list in the `search` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/catalog-index-search-light.png.network.txt` | 347 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Catalog index list in the `search` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/design-preview-all-dark.png.console.txt` | 309 / 2 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Design preview surface in the `all` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/design-preview-all-dark.png.network.txt` | 327 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Design preview surface in the `all` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/design-preview-all-light.png.console.txt` | 309 / 2 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Design preview surface in the `all` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/design-preview-all-light.png.network.txt` | 327 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Design preview surface in the `all` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/error-404-catalog-light.png.console.txt` | 207 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Not-found page in the `catalog` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/error-404-catalog-light.png.network.txt` | 335 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Not-found page in the `catalog` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/error-404-run-light.png.console.txt` | 205 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Not-found page in the `run` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/error-404-run-light.png.network.txt` | 331 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Not-found page in the `run` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/export-csv-run1.txt` | 96 / 3 | tracked | `c6c0567` 2026-09-28 | probe-record | Captured CSV export of run 1, kept at the capture root. The two copies are not identical, so this pair is a before/after, not a duplicate. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/export-json-run1.txt` | 1816 / 68 | tracked | `c6c0567` 2026-09-28 | probe-record | Captured JSON export of run 1, kept at the capture root. The two copies are not identical, so this pair is a before/after, not a duplicate. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/landing-default-light.png.console.txt` | 189 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Landing page in the `default` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/landing-default-light.png.network.txt` | 464 / 2 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Landing page in the `default` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/resolutions/export-csv-run1.txt` | 143 / 3 | tracked | `c6c0567` 2026-09-28 | probe-record | Captured CSV export of run 1, kept under `resolutions/`. The two copies are not identical, so this pair is a before/after, not a duplicate. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/resolutions/export-json-run1.txt` | 2004 / 74 | tracked | `c6c0567` 2026-09-28 | probe-record | Captured JSON export of run 1, kept under `resolutions/`. The two copies are not identical, so this pair is a before/after, not a duplicate. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/resolutions/run-detail-guided-preview-prg.txt` | 2869 / 60 | tracked | `c6c0567` 2026-09-28 | probe-record | Text of the run-detail guided-step preview state, captured while settling a resolution. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/review-queue-empty-dark.png.console.txt` | 195 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Match-candidate review queue in the `empty` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/review-queue-empty-dark.png.network.txt` | 311 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Match-candidate review queue in the `empty` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/review-queue-empty-light.png.console.txt` | 195 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Match-candidate review queue in the `empty` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/review-queue-empty-light.png.network.txt` | 311 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Match-candidate review queue in the `empty` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/review-queue-populated-dark.png.console.txt` | 195 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Match-candidate review queue in the `populated` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/review-queue-populated-dark.png.network.txt` | 311 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Match-candidate review queue in the `populated` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/review-queue-populated-light.png.console.txt` | 195 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Match-candidate review queue in the `populated` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/review-queue-populated-light.png.network.txt` | 311 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Match-candidate review queue in the `populated` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-create-form-dark.png.console.txt` | 209 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run creation form in the `default` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-create-form-dark.png.network.txt` | 339 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run creation form in the `default` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-create-form-light.png.console.txt` | 209 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run creation form in the `default` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-create-form-light.png.network.txt` | 339 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run creation form in the `default` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-create-form-validation-error-light.png.console.txt` | 224 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run creation form in the `validation-error` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-create-form-validation-error-light.png.network.txt` | 369 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run creation form in the `validation-error` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-empty-first-turn-dark.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `empty-first-turn` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-empty-first-turn-dark.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `empty-first-turn` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-empty-first-turn-light.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `empty-first-turn` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-empty-first-turn-light.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `empty-first-turn` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-guided-preview-dark.png.console.txt` | 225 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail guided-step preview in the `default` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-guided-preview-dark.png.network.txt` | 371 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail guided-step preview in the `default` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-guided-preview-light.png.console.txt` | 225 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail guided-step preview in the `default` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-guided-preview-light.png.network.txt` | 371 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail guided-step preview in the `default` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-no-scenario-dark.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `no-scenario` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-no-scenario-dark.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `no-scenario` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-no-scenario-light.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `no-scenario` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-no-scenario-light.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `no-scenario` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-trackblazer-dark.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `trackblazer` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-trackblazer-dark.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `trackblazer` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-trackblazer-light.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `trackblazer` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-trackblazer-light.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `trackblazer` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-unity-cup-dark.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `unity-cup` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-unity-cup-dark.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `unity-cup` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-unity-cup-light.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `unity-cup` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-unity-cup-light.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `unity-cup` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-unpriceable-dark.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `unpriceable` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-unpriceable-dark.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `unpriceable` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-unpriceable-light.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `unpriceable` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-unpriceable-light.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `unpriceable` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-ura-finale-dark.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `ura-finale` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-ura-finale-dark.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `ura-finale` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-ura-finale-light.png.console.txt` | 204 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run detail page in the `ura-finale` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/run-detail-ura-finale-light.png.network.txt` | 329 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run detail page in the `ura-finale` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/runs-index-empty-light.png.console.txt` | 242 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run index list in the `empty` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/runs-index-empty-light.png.network.txt` | 405 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run index list in the `empty` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/runs-index-populated-dark.png.console.txt` | 202 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run index list in the `populated` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/runs-index-populated-dark.png.network.txt` | 325 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run index list in the `populated` state, dark theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/runs-index-populated-light.png.console.txt` | 202 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Browser console for the Run index list in the `populated` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/runs-index-populated-light.png.network.txt` | 325 / 1 | tracked | `c6c0567` 2026-09-28 | probe-record | Network request log for the Run index list in the `populated` state, light theme. | current | - | 0 | no |
| `docs/frontend-review/2026-09-28/up-health-page.txt` | 1876 / 44 | tracked | `c6c0567` 2026-09-28 | probe-record | Response body of the health-check page, captured during the 2026-09-28 review run. | current | - | 0 | no |

##### 4.3 Frozen audit narrative (3 files)

These three are the prose half of the same capture set. The brief names the directory as do-not-touch, so they are inventoried and left alone.

| `docs/frontend-review/2026-09-28/README.md` | 28112 / 426 | tracked | `8c9faf9` 2026-09-29 | audit-report | The 2026-09-28 frontend audit: every user-facing page reviewed, with the findings and the evidence each one rests on. | current | - | 2 | yes |
| `docs/frontend-review/2026-09-28/DECISIONS-NEEDED.md` | 4692 / 82 | tracked | `b6fa798` 2026-09-29 | brief | The questions the audit could not answer itself, written up for the owner to rule on. | current | - | 1 | no |
| `docs/frontend-review/2026-09-28/RESOLUTIONS.md` | 13641 / 192 | tracked | `416d3a2` 2026-09-29 | session-log | The owner's answers to those questions, plus the re-captures that prove each resolution landed. | current | - | 1 | yes |

#### 5. Grouped views

##### 5.1 By type

| Type             | Files   | What the group is                                                                                                                                                                                                                                                                                                                                   |
| ---------------- | ------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| probe-record     | 104     | Measurements kept as evidence: 94 browser and API captures, 6 colour and token JSON files, the roster crosscheck, the characters probe                                                                                                                                                                                                              |
| reference        | 19      | Lookup material. Diataxis reference: the reader asks a question and looks up an answer. `docs/UMAMUSUME_REFERENCE.md`, `docs/SOURCE-OF-TRUTH.md`, `ARCHITECTURE-ESSENTIALS.md`, `PRODUCT.md`, `SKILL.md`, the flow doc, the two design-research comparison files, the four publisher-sourced scenario files, and the seven `.ai/skills/**` guides   |
| verification     | 15      | One record per slice, 2 and 3 and 5 to 15, plus the design pass and the cardless band                                                                                                                                                                                                                                                               |
| adr              | 13      | `docs/adr/0001` to `0013`, each with an inline Status line. Two are not standing: 0001 superseded in part, 0013 withdrawn                                                                                                                                                                                                                           |
| audit-report     | 9       | The frontend audit README, skills gaps, session consolidation, brief audit, spec divergence, mechanics triage, external design review triage, roster closing report, and the C-5 enforcement gap                                                                                                                                                    |
| constraint       | 6       | `CONSTRAINTS.md`, `docs/design-research/CONSTRAINTS.md`, `docs/GATE-REGISTRY.md`, `.ai/rules/code-style.md`, `.ai/rules/testing-standards.md`, root `DESIGN.md`                                                                                                                                                                                     |
| config-doc       | 5       | `Makefile`, the two `.ai/guidelines/**`, `.ai/rules/index.md`, `docs/SKILL_AUTOMATION.md`                                                                                                                                                                                                                                                           |
| spec             | 5       | `PRD.md`, `ARCHITECTURE.md`, `AGENTS.md`, `docs/design-research/DESIGN.md`, and the untracked UX behaviour spec                                                                                                                                                                                                                                     |
| brief            | 6       | Two repo requests, `DECISIONS-NEEDED.md` from the audit, and three incoming external write-ups parked untracked. Also `TASK-16-RUN-VIEW-FRAME-BRIEF.md`                                                                                                                                                                                             |
| scenario         | 5       | `docs/scenarios/01`, `02`, `03`, `07`, `08`. The four publisher-sourced files, `04`, `05`, `06`, `09`, are typed `reference` instead                                                                                                                                                                                                                |
| plan             | 3       | `PLAN.md`, `docs/design-research/replan-mobile-first.md`, the 3999-line roster implementation plan                                                                                                                                                                                                                                                  |
| research         | 3       | `docs/PRE-MORTEM.md`, `RAW-FINDINGS.md`, `RACE-CALENDAR-GAPS.md`                                                                                                                                                                                                                                                                                    |
| readme           | 2       | Root `README.md` and `docs/design-research/prototypes/superseded/README.md`                                                                                                                                                                                                                                                                         |
| notes            | 2       | `source.md`, `docs/design-research/HANDOFF-RACE-READ-PATH-2026-09-29.md`                                                                                                                                                                                                                                                                            |
| other            | 2       | `public/robots.txt`, `docs/data/roster-crosscheck-table.md`, the generated table                                                                                                                                                                                                                                                                    |
| issue-register   | 1       | `KNOWN-ISSUES.md`                                                                                                                                                                                                                                                                                                                                   |
| session-log      | 2       | `docs/requests/reports/2026-09-30-port-and-cleanup.md`, and `RESOLUTIONS.md` from the frozen audit                                                                                                                                                                                                                                                  |

Two type calls worth defending:

- `docs/frontend-review/2026-09-28/README.md` is `audit-report`, not `readme`. The filename says README; the content is a 426-line audit. A glob for `README.md` will treat it as an entry point and get that wrong.
- The nine `docs/scenarios/*.md` split across two types because they answer two different questions. `01`, `02`, `03`, `07`, `08` describe the game as this repo understands it. `04`, `05`, `06`, `09` are publisher and community material held at arm's length with their own dates. Consolidating on filename similarity would merge a primary source into a secondary one.

##### 5.2 By lifecycle state

| Status       | Files   | Members worth naming                                                                                                                                                                                                                                                                                |
| ------------ | ------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| current      | 169     | All 94 captures, all 11 live ADRs, the scenario guides, the slice records, the constraint files                                                                                                                                                                                                     |
| stale        | 8       | `SKILL.md`, `AGENTS.md`, `PLAN.md`, `docs/SOURCE-OF-TRUTH.md`, `.ai/guidelines/custom/domain.md`, `.ai/guidelines/framework/core.md`, `.ai/rules/index.md`, `docs/design-research/_scratch/WEB-FINDINGS.md`                                                                                         |
| superseded   | 5       | `ADR-0013` by ADR-0012 Decision 4; `ADR-0001` section 5 by ADR-0003; `ADR-0005` closed by R37; `slice-12` by master's own history; the untracked `COMPREHENSIVE UX DELIVERABLES` by its own triage banner                                                                                           |
| unknown      | 20      | The seven `.ai/skills/**` guides, the five `_scratch/*.json` measurement files, `.ai/rules/code-style.md`, `.ai/rules/testing-standards.md`, the three untracked incoming specs, `ARCHITECTURE.md`, `ARCHITECTURE-ESSENTIALS.md`, `PRODUCT.md`, `docs/SKILL_AUTOMATION.md`, the UX behaviour spec   |
| duplicate    | 0       | Nothing met the bar. Section 8 explains the six near-misses                                                                                                                                                                                                                                         |

##### 5.3 By authoring session

The repo has one committer, `IzzatFirdaus`, across every commit, so "which session wrote this" is only recoverable to the commit that last touched the file. Guessing beyond that would be invention. 61 distinct commits last touched a file in scope.

| Last-touching commit   | Files   | Subject                                                                            |
| ---------------------- | ------- | ---------------------------------------------------------------------------------- |
| `c6c0567`              | 91      | `docs(frontend-review): screenshot audit of every user-facing page`                |
| `a72ee76`              | 12      | `docs: add Laravel project coding rules and boost guidelines`                      |
| no commit              | 9       | The untracked set: four incoming write-ups, four reports, one spec                 |
| `e0e043c`              | 8       | `docs(research): commit design research reports and measurement provenance`        |
| `8c9faf9`              | 8       | `feat(lore,docs): add a line-scoped lore marker and re-baseline the count on it`   |
| `775b88a`              | 4       | `docs: record product truth, design system, and schema ADRs`                       |
| `b6fa798`              | 4       | `docs(frontend-review): resolutions record, decision requests, and re-captures`    |
| `3acbb06`              | 4       | `docs(scenarios): add Type-3 metadata blocks; supersede pre-release Trackblazer`   |
| `26aa9fe`              | 3       | `feat(legacy): record the Legacy Select read-back as one typed payload (D-268)`    |
| `3f631fc`              | 3       | `docs(scenarios): point 01, 02 and 07 at the race calendar instead of restating`   |
| the other 51 commits   | 53      | One, two or three files each                                                       |

The shape matters more than the rows: 91 of 202 files were written by one commit on one day, and the remaining 111 came from 60 separate decisions. A consolidation pass that touches the capture set touches 45 percent of the inventory at once.

##### 5.4 By directory

| Directory                              | Files   | Role                                                                                                                                                       |
| -------------------------------------- | ------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `docs/frontend-review/2026-09-28/`     | 97      | One frozen audit: 3 narrative files, 94 captures                                                                                                           |
| `docs/design-research/`                | 44      | 16 top-level, 15 verification records, 6 `_scratch` JSON, 1 prototypes README, plus the 4 untracked files that live here                                   |
| repo root                              | 13      | The governing set: PRD, ARCHITECTURE, CONSTRAINTS, DESIGN, KNOWN-ISSUES, PLAN, AGENTS, README, PRODUCT, SKILL, source, ARCHITECTURE-ESSENTIALS, Makefile   |
| `docs/adr/`                            | 13      | ADR-0001 to 0013, one file per number, no gaps                                                                                                             |
| `.ai/`                                 | 12      | Boost-generated rules, guidelines and skills, all written 2026-09-27 and none updated since                                                                |
| `docs/scenarios/`                      | 9       | Per-scenario guides and publisher references                                                                                                               |
| `docs/requests/`                       | 8       | 4 tracked at its top, 3 untracked and 1 tracked under `reports/`                                                                                           |
| `docs/data/`                           | 3       | Dated measurements: the crosscheck narrative, its generated table, the characters probe                                                                    |
| `docs/` top level                      | 5       | PRE-MORTEM, SOURCE-OF-TRUTH, GATE-REGISTRY, SKILL_AUTOMATION, UMAMUSUME_REFERENCE                                                                          |
| `docs/flows/`                          | 1       | The create-run flow                                                                                                                                        |
| `public/`                              | 1       | robots.txt                                                                                                                                                 |
| 3 untracked files with `docs/` paths   | 3       | Two incoming specs and the triaged deliverables write-up, all with spaces in their names                                                                   |

#### 6. Cross-reference map

Method matters here, so it goes first. This repo does not use markdown links between its documents. Across all 202 files there are 4 links written as a markdown link. Everything else is an inline path citation: "per `docs/PRE-MORTEM.md`", a backticked `CONSTRAINTS.md` with a gate number, "see `slice-5-2026-09-28.md` section 4". A consolidation pass that grepped for markdown link syntax would find four edges and conclude the corpus is unlinked. It is the most densely linked thing I have measured here.

Built from 3238 path-shaped tokens, the map holds **1218 resolved edges across 88 source files**. Of those tokens, 2555 name a file that exists and 683 do not. Section 6.3 explains the 683, because most of them are not errors.

The adjacency list is at `research-scratch/docinv_xref.json`, next to this file. It is a working artefact, not a repo document.

##### 6.1 Orphans: 107 files nothing cites

94 of them are the capture set, and a capture is not meant to be cited. The other 13 are worth reading as a list, because being uncited is usually a signal.

| Orphan                                                                                                                                    | Reading                                                                                                                                                                    |
| ----------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `README.md`                                                                                                                               | The front door and the documentation map. Nothing links to it, so an agent that starts anywhere else never finds the map. Cheapest orphan to close                         |
| `.ai/guidelines/framework/core.md`, `.ai/rules/code-style.md`, `.ai/rules/testing-standards.md`, and all seven `.ai/skills/**/SKILL.md`   | Loaded by tooling through `.ai/rules/index.md` and the Boost integration rather than by prose. Orphaned by design, which is why `.ai/rules/index.md` being stale matters   |
| `docs/adr/0006-design-authority-and-theme-default.md`                                                                                     | The ADR that settled the theme default, cited by nobody. PLAN.md's Open Decisions list still names the theme default as open, and that is the cost of an orphaned ruling   |
| `docs/design-research/SESSION-CONSOLIDATION-2026-09-30.md`                                                                                | The verification of eight session reports, cited by nothing, landed in the same commit as its own subject line                                                             |
| `docs/design-research/EXTERNAL-DESIGN-REVIEW-TRIAGE-2026-09-28.md`                                                                        | Its erratum is load-bearing and unread                                                                                                                                     |
| `docs/design-research/prototypes/superseded/README.md`                                                                                    | Says four prototypes are not current. It has to exist for exactly that reason, so this orphan is fine                                                                      |
| `docs/design-research/verification/cardless-band-2026-09-30.md`                                                                           | Newest record in the tree. The ruling it carries lives in its body rather than in a register                                                                               |
| `docs/requests/reports/2026-09-30-c5-down-enforcement-gap.md`                                                                             | Marked "recorded, not filed". It is an orphan because it is waiting                                                                                                        |
| `docs/requests/reports/2026-09-30-characters-source-findings.md`                                                                          | Handoff material for a slice that has since landed                                                                                                                         |
| The three untracked `docs/` specs                                                                                                         | Section 9                                                                                                                                                                  |

##### 6.2 Hubs: 60 files carry five or more inbound citations

| File                                    | Inbound   | File                                          | Inbound   |
| --------------------------------------- | --------- | --------------------------------------------- | --------- |
| `CONSTRAINTS.md`                        | 45        | `docs/scenarios/05-trackblazer-gametora.md`   | 15        |
| `docs/UMAMUSUME_REFERENCE.md`           | 41        | `docs/scenarios/09-global-race-calendar.md`   | 15        |
| `DESIGN.md` at the root                 | 41        | `docs/scenarios/01-ura-finale.md`             | 13        |
| `PRD.md`                                | 35        | `docs/scenarios/02-unity-cup.md`              | 13        |
| `docs/design-research/CONSTRAINTS.md`   | 30        | `docs/scenarios/04-trackblazer-umaguide.md`   | 12        |
| `AGENTS.md`                             | 27        | `ARCHITECTURE-ESSENTIALS.md`                  | 12        |
| `KNOWN-ISSUES.md`                       | 25        | `docs/GATE-REGISTRY.md`                       | 12        |
| `resources/css/app.css`                 | 21        | `docs/PRE-MORTEM.md`                          | 11        |
| `config/uma.php`                        | 21        | `docs/SOURCE-OF-TRUTH.md`                     | 10        |
| `docs/design-research/DESIGN.md`        | 21        | `docs/scenarios/07-grand-concert.md`          | 10        |

The four highest inbound counts belong to two pairs of files that share a bare name. A citation reading `CONSTRAINTS.md` resolves to one of two files. A citation reading `DESIGN.md` resolves to one of two files. Consolidation must not break either pair, and section 7 shows the ambiguity already broke ten citations.

Two more numbers worth holding. `app/Models/TrainingRun.php` takes 13 inbound citations from documentation, and `tools/gate.py` takes 16. Documents reach into code more often than they reach into other documents, so any plan to merge docs has to account for the code anchors as well.

##### 6.3 Dead links: the 683 unresolved tokens, sorted by what they mean

Calling these dead links would be wrong, and the distinction is the finding.

| Bucket                                | Tokens   | What it is                                                                                                                                                               |
| ------------------------------------- | -------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Game-client data-file names           | 368      | `scenarios.json`, `items.json`, `support-cards.json`, `events__champions-meeting.json`. Provenance for a claim about the client, never a repo path. Correct as written   |
| Local database dumps                  | 116      | `database/database.sql`, `database/scratch-catalog.sql`, and a `PWD/` variant of the second. Gitignored working data, named as the thing a measurement ran against       |
| Gitignored repo files                 | 67       | `CLAUDE.md` 22, `.agents/skills.json` 11, `research-scratch/**` and others. These exist on this machine and nowhere else                                                 |
| Bare view names needing a directory   | 33       | `catalog/index.blade.php`, `runs/show.blade.php`, `show.blade.php`. Four files share the basename `index.blade.php`. Resolvable to a human, unresolvable to a script     |
| Route names read as filenames         | 26       | `runs.sh` 19 and `catalog.sh` 6 are the route names `runs.show` and `catalog.show`. My regex's fault, not the repo's                                                     |
| Renamed or absent JS config           | 16       | `resources/js/app.js` 8 while the tree holds `app.ts`; `tailwind.config.js` 5 while Tailwind v4 is CSS-first with no config file                                         |
| Deleted files                         | 19       | `welcome.blade.php` 12 plus `resources/views/welcome.blade.php` 7. Real dead links, all left by one deletion, covered in section 7                                       |
| Scratch scripts                       | 8        | `final_measure.py`, `scan-by-gate.py`                                                                                                                                    |
| Library names                         | 7        | `Alpine.js`. Not a path                                                                                                                                                  |
| Absent class files                    | 7        | `app/Providers/EventServiceProvider.php`, `app/Actions/UpsertCharacterCard.php`                                                                                          |
| Short or fragmentary names            | 12       | `DELIVERABLES.md` 6, `Companion.md` 4, the elided migration path 2                                                                                                       |

Two rows deserve a sentence each, because they are defects rather than grammar.

`CLAUDE.md` is cited 22 times by tracked documents and is ignored by `.gitignore:52`. `AGENTS.md` tells every agent to read it. A fresh clone gets the instruction and not the file. `docs/SOURCE-OF-TRUTH.md` compounds this by listing `CLAUDE.md` as one of the seven documents it consolidates.

`tailwind.config.js` is cited 5 times. The project's own frontend rule says the `@theme` block in `resources/css/app.css` is law, with no config file. Whatever cites the config file describes a stack that was replaced before the first commit.

##### 6.4 Cycles: 65 mutual pairs

The cycles here are structural and intentional. `CONSTRAINTS.md` and `docs/GATE-REGISTRY.md` cite each other because the registry says CONSTRAINTS wins and CONSTRAINTS says the registry holds the enforcement detail. `AGENTS.md` and `PRD.md` do it because roles point at product truth and product truth points at roles. The concentration sits in the governing set, which is what a small corpus with real precedence looks like.

One pair needs naming. `docs/design-research/CONSTRAINTS.md` and root `CONSTRAINTS.md` cite each other by bare name. Both are hubs. Neither can be renamed without editing the other.

#### 7. Line-citation rot scan

391 citations name a target plus a line or a range. I opened each one and matched it against what the citing sentence claims. Verdicts are deliberately conservative: a citation is only called wrong when the evidence proves it, and 184 stay unknown because proving either direction needs a human reading the citing sentence and the target together.

| Verdict      | Count   | Basis                                                                                         |
| ------------ | ------- | --------------------------------------------------------------------------------------------- |
| Resolvable   | 155     | The cited range holds the anchor the citing line names, or a distinctive identifier from it   |
| Stale        | 19      | The named anchor exists in the target at a demonstrably different line                        |
| Broken       | 33      | The target file or the target range does not exist                                            |
| Unknown      | 184     | No anchor and no shared identifier. This is not a claim that they are wrong                   |

Six of the 19 stale rows are false positives of my own detector: it read an ADR's own number as an anchor that had moved. I excluded those from 7.3. The remaining rows are real, and three I confirmed by hand.

##### 7.1 Broken, grouped by cause

| Cause                                                                                                                        | Rows   | Fix shape                                                                                                        |
| ---------------------------------------------------------------------------------------------------------------------------- | ------ | ---------------------------------------------------------------------------------------------------------------- |
| Twin-name ambiguity. `DESIGN.md` and `CONSTRAINTS.md` cited bare, and the line only resolves under `docs/design-research/`   | 10     | Rewrite with the full path, or as a heading citation                                                             |
| Bare view name where four files share the basename                                                                           | 9      | Add the directory                                                                                                |
| `resources/views/welcome.blade.php`, deleted at `65f8b92` on 2026-09-29 while closing KI-20                                  | 7      | Re-point or retire. `KNOWN-ISSUES.md` carries three, `PLAN.md` one, `slice-7` and `slice-10` one each            |
| Path written as an ellipsis: `...create_scenario_slots_table.php:58-59`                                                      | 2      | Write the real filename                                                                                          |
| `DELIVERABLES.md` standing in for an untracked file whose real name contains an em dash                                      | 1      | Decide the file's fate first, then cite it properly                                                              |
| `Companion.md:136-141` used as a fragment                                                                                    | 1      | Full path                                                                                                        |
| `research-scratch/rehearse-apply.php:69`, a gitignored script cited by a tracked record                                      | 1      | Quote the measured value into the record instead                                                                 |
| `design-preview.blade.php:12-27`, a prototype-era view that no longer exists                                                 | 1      | Historical, inside a frozen audit. Leave it                                                                      |
| `CharacterProfileTest.php:183`, on a branch and never on master                                                              | 1      | `docs/requests/reports/2026-09-30-port-and-cleanup.md:144` cites a test that was withdrawn along with ADR-0013   |

##### 7.2 The rot the brief predicted, measured

The brief gave one example: `DESIGN.md:191` cites `KNOWN-ISSUES.md:855-861`. I checked it, and I checked the correction that was later written for it.

`docs/design-research/DESIGN.md:191` says the four ratios KI-20 records live at `KNOWN-ISSUES.md:855-861`. KI-20's heading is at line **994**. Lines 855 to 861 sit inside **KI-15**, which begins at 837.

`docs/design-research/SESSION-CONSOLIDATION-2026-09-30.md:59` found this defect and recorded the fix as "KI-20 begins at `:987`". The heading is at 994, not 987. Its own range claim, that 855 to 861 "now sits inside KI-10", is also wrong: KI-10 begins at 575 and KI-11 at 657, so 855 is two entries further down the file.

That exchange is the reason section 11 exists. A correction written as a line number becomes a second wrong citation. The heading is the durable reference, the `## KI-20` line with its title, and this repo already has that convention available in every register entry.

##### 7.3 Stale rows proven

| Citation                                                                              | Anchor actually at                                    | Note                                                        |
| ------------------------------------------------------------------------------------- | ----------------------------------------------------- | ----------------------------------------------------------- |
| `design-pass-trainee-detail-2026-09-29.md:70` to `KNOWN-ISSUES.md:1347` for KI-29     | 1372                                                  | Line 1347 is a table separator. Off by 25                   |
| `MECHANICS-TRANSLATION-TRIAGE.md:173` to `SCENARIO-DIFFERENCES.md:122` for ADR-0002   | 106                                                   |                                                             |
| `SKILLS-GAPS.md:217` to `PRD.md:73` for NFR-3                                         | 117                                                   | Two rows in the same file, same mistake                     |
| `slice-6-2026-09-28.md:147` to `docs/design-research/DESIGN.md:1338` for KI-14        | 1438                                                  |                                                             |
| `slice-10-2026-09-29.md:44` to `app/Enums/SpiritBurstState.php:47` for KI-18          | 40                                                    |                                                             |
| `FRONTEND-SPEC-DIVERGENCE.md:258` to `config/scenarios.php:83,116`                    | The D-240 marker the sentence means sits at line 13   | The cited range describes markers that file does not have   |

##### 7.4 Files that carry the rot

| File                                                                     | Line citations           | Of them broken or stale   |
| ------------------------------------------------------------------------ | ------------------------ | ------------------------- |
| `KNOWN-ISSUES.md`                                                        | 66                       | 3 broken                  |
| `docs/design-research/FRONTEND-SPEC-DIVERGENCE.md`                       | 29                       | 6 broken, 1 stale         |
| `docs/design-research/verification/slice-10-2026-09-29.md`               | 20                       | 3 broken, 2 stale         |
| `docs/GATE-REGISTRY.md`                                                  | 6                        | 0                         |
| `docs/design-research/verification/slice-15-2026-09-29.md`               | 4                        | 1 broken                  |
| `docs/requests/2026-09-29-catalog-roster-and-trainee-selector-plan.md`   | 9 of 83 outbound edges   | 3 broken                  |

`KNOWN-ISSUES.md` holds 66 of the 391 line citations, and the file runs to 1944 lines. Every entry in it has a heading. It is the clearest case in the repository for switching to heading citations.

#### 8. Duplicate candidates

No pair met the definition of a duplicate: same content, one file to keep. Six pairs look like duplicates and are not. Three are genuine consolidation targets. All nine are below, with the overlap cited.

| Candidate pair                                                                                                                                                                                                                    | Shared ground                                            | Newer                                                     | More complete                    | Recommendation                                                                                                                                                                                                                                                                                                    |
| --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------- | --------------------------------------------------------- | -------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `CONSTRAINTS.md` at the root (48 lines) and `docs/design-research/CONSTRAINTS.md` (973)                                                                                                                                           | Both are constraint contracts with numbered gates        | Root, touched `e9a779d` 2026-09-30                        | design-research, by 925 lines    | **Keep both.** The root file holds C-1 to C-9 for the whole repo; the other holds D-numbered rules for the user-facing layer only. `docs/GATE-REGISTRY.md:3` states the precedence between them. Merge would destroy a two-level gate system. The real defect is the shared basename, not the shared subject      |
| `DESIGN.md` at the root (394) and `docs/design-research/DESIGN.md` (1640)                                                                                                                                                         | Both specify tokens, components and surfaces             | Root, `bcd8abe` 2026-09-29                                | design-research, by 1246 lines   | **Keep both, rename one or disambiguate.** The root file's H1 is "Trainer Desk design system"; the other's is "Umamusume Trainer Companion design system", the name from before OQ-1 closed on 2026-09-27. Ten broken citations in section 7.1 come from readers not knowing which one a bare `DESIGN.md` means   |
| `docs/data/2026-09-29-global-roster-crosscheck.md` and `docs/data/roster-crosscheck-table.md`                                                                                                                                     | The same 107 cards                                       | narrative, `07941eb`                                      | narrative                        | **Keep both.** Line 141 of the narrative says the table is generated by `php tools/roster-crosscheck.php` and the narrative reads it back. They are a document and its data file. One drift trap: the narrative carries a date in its name and the generated table does not                                       |
| `docs/scenarios/03-trackblazer.md`, `04-trackblazer-umaguide.md`, `05-trackblazer-gametora.md`                                                                                                                                    | Trackblazer                                              | `3f631fc` 2026-09-29 touched 03                           | 04 and 05 by length              | **Keep all three.** 03 is this repo's guide. 04 is uma.guide's community framing and 05 is GameTora's reference, each a separate publisher with its own reliability. Merging them would put a primary claim and a secondary claim in the same table cell                                                          |
| `docs/requests/2026-09-29-catalog-roster-and-trainee-selector.md`, `-plan.md`, `-report.md`                                                                                                                                       | One workstream                                           | report, `6300221` 2026-09-30                              | plan, at 3999 lines              | **Keep all three.** Request, plan and closing report are three different acts. What is missing is a line at the top of each naming the other two, because the filenames differ only by suffix and the reader has to guess the order                                                                               |
| `docs/frontend-review/2026-09-28/export-csv-run1.txt` and `.../resolutions/export-csv-run1.txt`, plus the JSON pair                                                                                                               | Identical names                                          | `resolutions/`, `b6fa798` 2026-09-29                      | differs by content               | **Keep both.** This is the one the brief warned about. `git hash-object` gives `37661caa` and `a5d7a771` for the CSV pair, `097e1ce4` and `3e03d32e` for the JSON. The resolutions copies are re-captures that prove a fix landed. A duplicate pass keyed on filename would delete the evidence                   |
| `docs/UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md` (2144) and `docs/Scenario-Specific User Flows & Frontend Specifications.md` (1174) and `docs/UX Behavior Specification - Umamusume Trainer Companion.md` (576)   | Incoming external UX material, 3894 lines between them   | All three dated in their banners as reviewed 2026-09-27   | deliverables, by 968 lines       | **Decide, then merge or delete.** All three carry a "not merged" banner and all three are untracked. Their audits, `FRONTEND-SPEC-DIVERGENCE.md` and `MECHANICS-TRANSLATION-TRIAGE.md`, are also untracked. This is the largest single block of undecided documentation in the repo                               |
| `README.md` at the root, `docs/frontend-review/2026-09-28/README.md`, `docs/design-research/prototypes/superseded/README.md`                                                                                                      | Same filename                                            | root, `bbfa3de`                                           | different jobs                   | **Keep all three.** The audit's README is a 426-line audit report and the prototypes README is a 16-line warning label. Only the root one is a readme                                                                                                                                                             |
| `.ai/skills/*/SKILL.md` (7 files) and `SKILL.md` at the root                                                                                                                                                                      | Skill documentation                                      | root, `775b88a`                                           | the seven, each a real skill     | **Keep the seven, resolve the root.** Root `SKILL.md` names itself "Skill Registry" and hand-counts what is installed. Section 10 explains why it should go                                                                                                                                                       |

#### 9. Untracked material

Nine text files, no history, no owner of record. Nothing here is committed and nothing here should be, until the owner decides.

| Path                                                                | Size / lines    | Contents in one sentence                                                                                     | Plausible origin                                  | Cited by a tracked file                                                                                                    | Reads as                                                                                                                                        |
| ------------------------------------------------------------------- | --------------- | ------------------------------------------------------------------------------------------------------------ | ------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------- |
| `docs/Scenario-Specific User Flows & Frontend Specifications.md`    | 65085 / 1174    | Per-scenario flows and screen specs, with a banner saying it was reviewed and largely sound but not merged   | Incoming external write-up, reviewed 2026-09-27   | Yes, by its own banner pointing at `FRONTEND-SPEC-DIVERGENCE.md` section 6. Nothing tracked cites it by path               | Finished, and deliberately parked                                                                                                               |
| `docs/UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md`    | 103979 / 2144   | An external UX deliverables document triaged on 2026-09-27 and kept for reference rather than adopted        | Incoming external write-up                        | Yes, six times, as the short name `DELIVERABLES.md`, which resolves to nothing. `docs/UMAMUSUME_REFERENCE.md:386` is one   | Finished, rejected by its own banner                                                                                                            |
| `docs/UX Behavior Specification - Umamusume Trainer Companion.md`   | 32288 / 576     | A behavioural contract synthesised from DESIGN, CONSTRAINTS, PRD, the ADRs and the scenario files            | Incoming external write-up, reviewed 2026-09-27   | Only through its banner. Its closing line claims every input and state is sourced from the design documents                | Finished, with three load-bearing corrections named in its banner                                                                               |
| `docs/design-research/FRONTEND-BRIEF-AUDIT.md`                      | 14644 / 191     | Compares an incoming frontend brief against the tree and lists the corrections that brief owes               | This repo's own analysis, 2026-09-27              | 6 inbound                                                                                                                  | This repo's output, and the audit side of a pair the repo has not decided to keep                                                               |
| `docs/design-research/FRONTEND-SPEC-DIVERGENCE.md`                  | 22055 / 297     | Divergence audit between a pasted frontend spec and the app this repo is building                            | This repo's own analysis                          | 10 inbound                                                                                                                 | In progress. PLAN.md lines 556 to 565 list corrections it owes that were never applied, and it holds 6 broken and 1 stale citation of its own   |
| `docs/design-research/MECHANICS-TRANSLATION-TRIAGE.md`              | 19343 / 234     | Triage of a write-up that recast every game system as a UX pattern catalog                                   | This repo's own analysis                          | 10 inbound                                                                                                                 | Finished. Its section 7 is the item-by-item audit the deliverables banner points at                                                             |
| `docs/design-research/TASK-16-RUN-VIEW-FRAME-BRIEF.md`              | 30220 / 481     | Task 16's brief for the run view axis, revised against the tree at `a8a52cd`                                 | A dispatch brief written for another session      | 1 inbound, plus 2 broken citations out                                                                                     | In progress. The task it describes has a verification record, `slice-12`, and no landed frame                                                   |
| `docs/requests/reports/2026-09-30-c5-down-enforcement-gap.md`       | 4151 / 67       | Says C-5 requires a `down()` on every migration and nothing checks for it                                    | This session's finding, 2026-09-30                | No                                                                                                                         | Deliberately parked. Its own header says recorded and not filed, because a peer session is mid-write on ten tracked files                       |
| `docs/requests/reports/2026-09-30-characters-source-findings.md`    | 12477 / 231     | Characters-source findings handed to the profile-block slice                                                 | This repo's measurement pass, 2026-09-30          | No                                                                                                                         | Superseded on the spot. Its subject landed: `docs/data/2026-09-30-characters-source-probe.md` is tracked and covers the same ground             |

One more untracked path, noted not inventoried as the brief instructs: `docs/vibe_images/` holds 10 PNGs totalling roughly 13 MB, dated 2026-09-29, named after prototype screens such as `screen-a-ura-finale-v9_...png`. They are generated design mockups, not text, and no tracked file names them.

They are also untracked by accident. `git check-ignore -v docs/vibe_images/` answers with `.gitignore:102`, and line 102 is not the rule anyone meant to write. `cat -A` on lines 98 to 103 shows the file's tail is partly UTF-16LE: line 102 reads `.^@s^@c^@r^@a^@t^@c^@h^@-^@u^@m^@a^@/^@^M^@$`, which is `.scratch-uma/` with a NUL between every character and a CRLF at the end. The intended `/vibe_images/` rule sits at line 100 and is root-anchored, so it cannot match `docs/vibe_images/`. Git therefore reports the dead UTF-16 line as the match, matches nothing, and leaves 13 MB of PNGs in every `git status`. The same encoding damage explains why `grep` called `.gitignore` a binary file earlier in this pass.

The consequence for this inventory is small and worth stating: those 10 files look like untracked documentation noise to every agent that reads `git status`, and one of the two reasons they are noise is a NUL byte.

**Two facts about the untracked set that a consolidation pass has to survive.**

The lore exposure is small and specific. A plain grep, because `make lore` uses `git grep` and cannot see any of these files, finds 2 word-boundary hits in the deliverables document, both the word `lineage` at lines 741 and 762, and 2 in the UX behaviour spec, both the word `equine` at lines 124 and 566, where the lines are compliance-table rows naming the ban rather than breaking it. The other 34 pattern matches across these three files are substrings inside words like `detailed` and `Tailwind`, and the correct whole-word count for `tail` in all three files is zero. Under `docs/GATE-REGISTRY.md` allowed-hit classes 1 and 2 most of this clears, and the Guardian rules, not the grep.

The triage record is as untracked as the material it rejects. Three incoming write-ups and the two audits that triaged them are all untracked. A fresh clone gets none of the five, while `docs/UMAMUSUME_REFERENCE.md` on master cites the deliverables file by a short name. Either the whole set gets tracked together or the citing line gets rewritten. Half of it cannot be decided.

#### 10. Consolidation candidates, ranked

Each row states the evidence, which is in an earlier section, and what acting on it would cost.

1. **Fix the twin-name collision before anything else.** Ten broken citations in 7.1 come from readers writing `DESIGN.md` or `CONSTRAINTS.md` when they mean `docs/design-research/…`, and both pairs are hubs with 41 and 45 inbound. Two options, and they are different jobs: require the full path in every citation, or rename the design-research pair. Renaming breaks 51 inbound citations in the same commit. The path requirement costs nothing but discipline and is reversible file by file. This is the only item on the list that would prevent new defects rather than clean old ones.
2. **Sweep the seven `welcome.blade.php` citations.** `65f8b92` deleted the view on 2026-09-29 and closed KI-20 in the same commit, and the commit touched no document that cited the file. `KNOWN-ISSUES.md` holds three of them. Small, mechanical, and it is the only case where the correct fix might be a retirement note rather than a re-point, because the citation records what was measured before the deletion.
3. **Regenerate or delete root `SKILL.md`.** Its frontmatter description claims a complete registry of installed skills. It was written on 2026-09-27 in commit `775b88a` and no commit has touched it since, while the repo gained `.ai/skills/**` content, plugin skills, and 2442 ignored doc files across a dozen agent directories. The `refresh-skill-registry` skill rebuilds exactly this list from disk. A hand-count that cannot be trusted and is cited by one file is worse than no registry.
4. **Add a regeneration trigger to `docs/SOURCE-OF-TRUTH.md`.** It consolidates seven documents, two of which are `CLAUDE.md` (gitignored) and `SKILL.md` (stale). It is cited by 10 files and its own content has no marker saying when it was derived. A derived document with no derivation stamp drifts silently, and this one is a hub. Not a merge. A header.
5. **Decide the five untracked incoming write-ups as one set.** 3894 lines of external material and 488 lines of this repo's rejection of it, none tracked, one cited by name from master. Options: track all five as a labelled archive, delete them, or keep them ignored and rewrite the citing line in `docs/UMAMUSUME_REFERENCE.md:386`. What it cannot stay is split, because the reject and the rejected have to travel together.
6. **Give the roster trio an order.** `…selector.md`, `…selector-plan.md`, `…report.md` are a request, a plan and a closing report for one workstream, and the only difference a reader sees is the suffix. One line at the top of each, naming the other two.
7. **Close the slice-record gap.** `docs/design-research/verification/` holds slices 2, 3, 5 through 15. Slice 1 and slice 4 have no record, and slice 16 has none while `KNOWN-ISSUES.md` KI-25's closure once cited a `slice-16` file that does not exist. Either the convention started at slice 2, which makes 1 and 4 historical and fine, or two records were never written. The register needs one sentence saying which.
8. **Refresh `.ai/**`, or mark it generated.** Twelve files, all written 2026-09-27 in `a72ee76`, none touched since. Three of them are demonstrably wrong against current code: `domain.md` calls the frontend vanilla JavaScript while `resources/js/` holds four TypeScript files compiled under C-9, and says `app/Actions/` is intended when three action classes are tracked there; `framework/core.md` cites `resources/js/app.js`; `.ai/rules/index.md` says no rules are recorded while two rule files sit beside it. This is Boost-generated material, so the fix is regeneration, not hand-editing, and that is a decision about whether the repo owns those files.
9. **Rewrite the tail of `.gitignore` as UTF-8.** Not a documentation merge, but it is a documentation-adjacent defect this inventory tripped over three times: `make lore` and `git grep` behaviour, the `docs/vibe_images/` exposure in section 9, and a `grep -c` that reported `.gitignore` as a binary file all trace to NUL bytes in lines 100 to 103. One line of that tail is a real rule that is root-anchored and cannot match, and one is dead. A tool that cannot read its own ignore file will keep mis-scoping every future inventory.

#### 11. Do-not-touch list

Load-bearing by citation count, by role, or by an instruction that outranks this inventory.

| Item                                                   | Why it is off-limits to a consolidation pass                                                                                                                                                                                                                                                                                                                                          |
| ------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `docs/frontend-review/2026-09-28/` in full, 97 files   | The brief forbids editing the audit's README or its captures. Independently: it is a dated evidence set, and 45 percent of the inventory. Editing one capture invalidates the audit that produced it                                                                                                                                                                                  |
| `CONSTRAINTS.md` at the root                           | Gate precedence R-6 puts it first. Agents are told to read it before writing code and never to weaken a threshold. `docs/GATE-REGISTRY.md:3` names it and this registry as the joint source of truth for global gates                                                                                                                                                                 |
| `docs/GATE-REGISTRY.md`                                | Same precedence tier. It also records the gates that are knowingly not automated, and deleting an honest gap entry hides a gap                                                                                                                                                                                                                                                        |
| `KNOWN-ISSUES.md`                                      | 1944 lines, 25 inbound citations, 66 line citations, and the active register. `AGENTS.md` and every slice record write into it. It is the single file where concurrent sessions collide most, and its renumbering history (KI-16 as a hole, KI-30 and KI-31 renumbered from KI-16 and KI-17) shows what editing it carelessly costs                                                   |
| `docs/adr/0001` through `0012`                         | Accepted decisions. `documentation-and-adrs` is explicit: do not delete old ADRs, and supersede by writing a new one. ADR-0013 is the exception the rule allows, and it already carries its own Withdrawn banner                                                                                                                                                                      |
| `PRD.md`                                               | Product truth, and the file the Architect role must cite for every new table, column or class. `AGENTS.md` routes all scope changes through it                                                                                                                                                                                                                                        |
| The ten hubs with 10 or more inbound                   | `CONSTRAINTS.md`, `docs/UMAMUSUME_REFERENCE.md`, both `DESIGN.md` files, `PRD.md`, `docs/design-research/CONSTRAINTS.md`, `AGENTS.md`, `KNOWN-ISSUES.md`, `docs/design-research/DESIGN.md`, `docs/PRE-MORTEM.md`, `ARCHITECTURE-ESSENTIALS.md`, `docs/GATE-REGISTRY.md`, `docs/SOURCE-OF-TRUTH.md`. Renaming or merging any of them rewrites citations across a fifth of the corpus   |
| `docs/UMAMUSUME_REFERENCE.md`                          | 41 inbound and the dated-snapshot policy recorded in project memory. Its eight sections are the mechanics corpus the UI traces to, and its preamble is the current map                                                                                                                                                                                                                |
| `PLAN.md` and `docs/design-research/**`                | On the forbidden-edit list from the standing Authorized Execution Turn that governed this session. `docs/design-research/SESSION-CONSOLIDATION-2026-09-30.md` is separately named do-not-touch by the brief                                                                                                                                                                           |
| `.ai/guidelines/**` and `.ai/rules/**`                 | Boost-generated and auto-injected. Hand edits get overwritten by the next regeneration, and the repo's own rule says to record a rule only when the owner asks for one                                                                                                                                                                                                                |
| Anything whose status is `unknown` here                | 20 files. Absence of evidence is not evidence of staleness, and an inventory that lets a later pass act on `unknown` as though it meant `stale` has quietly lowered the bar                                                                                                                                                                                                           |

#### 12. Files that resisted the Description column

None resisted outright. Two resisted in a way worth naming, because the brief is right that a file hard to describe is a file hard to consolidate.

`docs/UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md` describes fine as a document, and resists as an *intention*. Its banner says triaged and not adopted, its content is 2144 lines of someone else's design system, its name contains an em dash that breaks `git ls-files` output under default `core.quotepath`, and the repo's own reference guide cites it by a short name that does not resolve. Every description I could write either says what the file is or says why it is here, and those are different sentences. That is the signature of material that needs a decision rather than a merge.

`docs/design-research/FRONTEND-SPEC-DIVERGENCE.md` resists for the opposite reason. Its purpose is clear and its content is rotting underneath: 10 inbound citations from tracked files, 29 line citations of its own, 6 of them broken and 1 stale, and a list of corrections in PLAN.md that were never applied. It is an untracked file that other tracked files lean on. Describing it took one sentence; deciding what to do with it is the whole job.

#### 13. Fence confirmation

Read-only against the repository. No tracked file was created, edited, renamed, moved or deleted. No commit was made. No branch was created or switched. No `git stash`, `git reset`, `git gc`, `git prune` or reflog operation ran. No migration ran and no database was opened for writing; the two Artisan reads I made were `tinker --execute` SELECT queries against `database/database.sqlite`, one of which returned `no such table` for `umamusume_profiles`.

Writes that did happen, all outside tracked space: this file and its JSON companion in `research-scratch/`, which `.gitignore` covers, and four analysis scripts in the OS temp directory. The `/careful` skill's own telemetry line, which appends to `~/.gstack/analytics/skill-usage.jsonl`, was skipped because the fence says read-only.

`git status --short` at the end of the pass reports the same nine untracked text files and the same `docs/vibe_images/` directory that it reported at the start, plus this file inside an ignored directory. The tree is as I found it, and it moved under me three times while I measured it.

One observation the fence note has to carry. Midway through the pass, `git status --short` listed `M  docs/design-research/SESSION-CONSOLIDATION-2026-09-30.md` as **staged**, and on the next call it was gone. I never wrote to that file, never staged anything, and `git diff --cached --stat` against it returned empty both times I looked. A concurrent session staged and unstaged it inside my measurement window. `git status` in this worktree is not a stable read, and any later pass that treats a clean status as proof of no concurrent writer will be wrong.

#### 14. Spot-check, corrections to this inventory, and the prose audit

Written after the four report items the owner ruled on. Appended, not folded into sections 6 and 8, so the original claims stay readable next to what was found wrong with them.

##### 14.1 Ten-row spot-check against the tree

Selection method: `random.seed("c1e14a3")` then `random.sample` over the 202 table rows. Reproducible from the snapshot SHA rather than chosen by hand, so the sample cannot be picked to flatter the scan.

Ten rows verified independently of the scripts that produced them: file size from `os.path.getsize`, line count from a fresh read, tracked state from `git ls-files` membership, and last commit from `git log -1 --format='%h %ad' -- <path>`, which is a different code path from the `git log --reverse --name-only` pass that built section 4.

| Row                                                                                       | bytes / lines   | tracked     | last commit            | verdict   |
| ----------------------------------------------------------------------------------------- | --------------- | ----------- | ---------------------- | --------- |
| `docs/frontend-review/2026-09-28/catalog-detail-populated-dark.png.network.txt`           | 343 / 1         | tracked     | `c6c0567` 2026-09-28   | match     |
| `docs/requests/2026-09-29-catalog-roster-report.md`                                       | 18935 / 357     | tracked     | `6300221` 2026-09-30   | match     |
| `docs/design-research/verification/slice-7-2026-09-28.md`                                 | 15006 / 246     | tracked     | `0d2dbdc` 2026-09-28   | match     |
| `docs/requests/reports/2026-09-30-c5-down-enforcement-gap.md`                             | 4151 / 67       | untracked   | never                  | match     |
| `docs/frontend-review/2026-09-28/landing-default-light.png.console.txt`                   | 189 / 1         | tracked     | `c6c0567` 2026-09-28   | match     |
| `AGENTS.md`                                                                               | 26470 / 390     | tracked     | `6f9c98a` 2026-09-27   | match     |
| `docs/frontend-review/2026-09-28/run-detail-unpriceable-light.png.console.txt`            | 204 / 1         | tracked     | `c6c0567` 2026-09-28   | match     |
| `docs/frontend-review/2026-09-28/catalog-index-page2-out-of-range-dark.png.console.txt`   | 205 / 1         | tracked     | `c6c0567` 2026-09-28   | match     |
| `docs/design-research/verification/slice-13-2026-09-29.md`                                | 20718 / 344     | tracked     | `debc4d0` 2026-09-29   | match     |
| `docs/frontend-review/2026-09-28/review-queue-populated-dark.png.network.txt`             | 311 / 1         | tracked     | `c6c0567` 2026-09-28   | match     |

**Ten for ten on the measurable columns.**

The derived column was checked separately, on ten hub rows, against a recount that reads every in-scope file for the basename. Seven matched exactly. Three did not, and both directions of error are recorded:

| Row                             | section 4   | recount   | why                                                                                                                                                                                                                                                                                                          |
| ------------------------------- | ----------- | --------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `CONSTRAINTS.md`                | 45          | 52        | The recount counts any file whose text contains the basename. 29 files write `docs/design-research/CONSTRAINTS.md` in full and 22 of those also write the bare token. Section 4 counts citations that resolve to this path. The 7 difference is the twin collision from finding 2, leaking into the number   |
| `docs/UMAMUSUME_REFERENCE.md`   | 41          | 40        | The recount dropped one citer                                                                                                                                                                                                                                                                                |
| `PRD.md`                        | 35          | 34        | The dropped citer is `docs/UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md`. The recount's own path handling tripped on the em-dash filename, the same `core.quotepath` trap recorded in section 2 limit 3. Section 4 is right here                                                                |

So the counts stand, and the inbound column now carries its definition in section 4 rather than relying on the reader to infer it.

##### 14.2 Two corrections to this inventory

**Section 8, roster trio row, was partly wrong.** It said "What is missing is a line at the top of each naming the other two." On opening the files: `…-and-trainee-selector.md` already carried `**Plan:** 2026-09-29-catalog-roster-and-trainee-selector-plan.md`. The real gap was narrower: nothing named the report, so a reader could not tell which of the three was last. Landed at `9003631` as six relative links across the three files, all resolving, in the shape of a triangle.

**Section 6.3, the `app.js` row, was under-specified.** It read as if the eight citations were stale claims about a live entrypoint. Two of the three files that carry them are doing something else. `KNOWN-ISSUES.md` mentions `app.js` inside the KI-1 record, where the whole point is that `app.js` did not exist, and KI-1 is resolved: `layout.blade.php:24` now requests `resources/js/app.ts`. The genuinely stale guidance is in two other files, `.ai/guidelines/framework/core.md` and `.ai/skills/tailwindcss-development/SKILL.md`, both of which still teach the `@vite(['resources/css/app.css', 'resources/js/app.js'])` boilerplate as current. `AGENTS.md` repeats it through the embedded Boost text. Nothing tests documentation strings, so the rename that closed KI-1 is invisible to any gate. That distinction is recorded in the KI-1 forward note at `9a009f5` rather than by rewriting this section.

One number in section 6.3 also needs reading as a token count, not a file count. 2555 tokens resolved and 683 did not, out of 3238. A single file citing the same path five times contributes five. The inbound column in section 4 counts files. The two columns are not comparable with each other and were never meant to be.

##### 14.3 Prose audit of this document, against `humanizer`

Counts and locations, no rewrites, per the owner's ruling. Patterns that scored zero are listed so the absence is checkable rather than assumed.

| Pattern                                          | Hits                | Where                                                                                                                                                                                                                                                          |
| ------------------------------------------------ | ------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| §1 not-X-but-Y, clipped negative tail            | 0 by formula        | 21 sentences contain `not`; 6 of them are contrasts that carry information and are kept (`which is the expected result, not a broken gate`; `My regex's fault, not the repo's`; `a working artefact, not a repo document`)                                     |
| §2 one-line closer, repeated closer              | **1, at scale**     | The clause "The two copies are not identical, so this pair is a before/after, not a duplicate." appears 4 times verbatim, in the four `export-*-run1.txt` rows of section 4.2. The content is correct and the brief asked for it; the repetition is the tell   |
| §3 aphorism formula                              | 2                   | Section 11 `Anything whose status is unknown here`: "Absence of evidence is not evidence of staleness". Section 8 `CONSTRAINTS.md` row: "The real defect is the shared basename, not the shared subject"                                                       |
| §4 staged run-up                                 | 1                   | Section 6 opener, "Method matters here, so it goes first."                                                                                                                                                                                                     |
| §5 arguing with no one                           | 1, mild             | Section 7 table, Unknown row: "This is not a claim that they are wrong". It pre-empts a real misreading of an `unknown` verdict, so it carries information                                                                                                     |
| §6 forced triads                                 | 0                   | 2 `A, B, and C` constructions, both inside table cells listing real items                                                                                                                                                                                      |
| §8 dash as connector                             | **0**               | 5 em dashes in the file, all inside the verbatim filename `docs/UMAMUSUME PRETTY DERBY — COMPREHENSIVE UX DELIVERABLES.md`, which section 2 limit 3 explains must stay exact                                                                                   |
| §9 stacked qualifiers                            | 0                   |                                                                                                                                                                                                                                                                |
| §12 overused AI vocabulary                       | 1 real, 12 exempt   | 9 hits are the technical noun `gate`, which the skill exempts. 2 hits are `actually` inside a literal ADR title or a table column header meaning "the line it really sits at". One is real: section 11, "has quietly lowered the bar"                          |
| §13 inflated significance                        | 0                   |                                                                                                                                                                                                                                                                |
| §15 shallow `-ing` riders                        | 0                   |                                                                                                                                                                                                                                                                |
| §16 sales language                               | 0                   |                                                                                                                                                                                                                                                                |
| §18 avoids is/are/has                            | 0                   |                                                                                                                                                                                                                                                                |
| §19 bold as decoration, labeled lists            | 0                   | Bold appears in table cells and lead-ins, none as `- **Label:**` bullets                                                                                                                                                                                       |
| §20 decorative headings, rules, repeated title   | 0                   | Headings are sentence-ish and numbered; no `---` separators between sections; no emoji                                                                                                                                                                         |
| §22 chatbot residue                              | 0                   |                                                                                                                                                                                                                                                                |
| §23 knowledge-limit disclaimers and guesses      | 0                   | Section 11's `unknown` verdicts name what is missing rather than guessing                                                                                                                                                                                      |
| §24 heading restated in first sentence           | 0                   |                                                                                                                                                                                                                                                                |
| §25 writing about the previous version           | 1, deliberate       | Section 2 limit 6 and section 14 exist to correct earlier claims forward instead of overwriting them. That is the repo's stated convention, not a tell                                                                                                         |

Sentence cadence across the prose lines runs 5 to 97 words with no even mid-length run, which `humanizer` lists as a human signal rather than something to fix.

**Net: six rows to look at, all of them small.** The repeated export clause is the only one a rewriter would touch first, and only because it appears four times. "Quietly lowered the bar" and "Method matters here, so it goes first" are the other two worth a decision. The `unknown` pre-empt and the correction sections are load-bearing and should stay.

---

### Part 2 — Unfinished development phases, documented (fullstack audit, rev 2)

### Unfinished development phases, documented — fullstack audit (rev 2)

Regenerated 2026-09-29 ~14:20 UTC, replacing the earlier same-day draft. Disposable:
`research-scratch/` is gitignored (`.gitignore:87`), so this file is not a repo artifact and
nothing here is claimed as merged or approved. Every "verified" marker below is a command
output measured in this session against `master` at `2033434`.

#### Method and its limits

- Read in full this pass: `PLAN.md` (684 lines — the draft said 682), `KNOWN-ISSUES.md`
  status block + open-entry headings, `docs/requests/2026-09-29-catalog-roster-and-trainee-selector{.md,-plan.md}`
  (spec + Tasks 1–5 of the 3,991-line plan), `docs/design-research/SKILLS-GAPS.md` §1–§3, §8–§10,
  `docs/SKILL_AUTOMATION.md`, `ADR-0009` header.
- Verified against the tree, not the docs: migration list (`git ls-tree HEAD database/migrations`
  = 33 files, incl. `2026_09_29_120000` / `120100` / `120200`), `addcslashes` present in
  `CatalogController.php:60,:82` **on HEAD** and fix commit `f2c978b` contained in `master`
  (`git branch --contains`), `StoreTurnEntryRequest.php:49-53` still `between:0,1200`,
  `APP_NAME=Laravel` in `.env:1` and `.env.example:1`, `color-scheme` 0 matches in
  `resources/css/app.css`, `docs/data/` and `tools/roster-crosscheck.php` absent, roster-plan
  checkboxes 116 unchecked / 0 checked in both trees.
- Suite: live `php artisan test --compact` this session → **772 passed, 2 skipped**; PLAN's
  last recorded slice baseline is 618 at Slice 15 T6 (`PLAN.md:506`). `migrate:fresh --seed`
  against the shared SQLite file hit `disk I/O error` in this environment; every slice record
  proves the same gate on a scratch DB instead, so the C-5 claim rests on those records, not
  on this session.
- Working tree is dirty with an active concurrent session's files: `M` on `CONSTRAINTS.md`,
  `PLAN.md`, `CatalogController.php` (+62 uncommitted lines on top of `f2c978b`),
  `PipelineRunner.php`, `package.json`/`package-lock.json` (adds `typescript` + a `typecheck`
  script — the C-9 tool itself is uncommitted), `catalog/index.blade.php`; `??` on the three
  incoming UX write-ups, `FRONTEND-BRIEF-AUDIT.md`, `FRONTEND-SPEC-DIVERGENCE.md`,
  `MECHANICS-TRANSLATION-TRIAGE.md`, `TASK-16-RUN-VIEW-FRAME-BRIEF.md`, `docs/vibe_images/`.

#### Headline correction against the 14:00 draft

The catalog-roster-and-trainee-selector slice **has largely landed on `master`**, not just on
its branch: migrations 120000/120100/120200, `app/Models/CharacterCard.php`,
`app/Enums/CardRarity.php`, `app/Actions/StoreCharacterCards.php`,
`app/Services/DataPipeline/Parsers/GametoraCharacterCardParser.php`, `ADR-0008`,
`resources/js/trainee-combobox.ts`, `resources/views/components/rarity-chip.blade.php`, and
`PromoteMatchedRecord` persisting `external_ref` — all verified in `git ls-tree HEAD` /
`git show HEAD:...`. `feat/catalog-roster-and-trainee-selector` is **3 commits ahead**
(`1c44698`, `79d7f62`, `4addcd6` — Tasks 11–13 selector fixes). The plan tracker says 0 of 116
steps checked; the tree says otherwise. KI-26 is likewise **code-closed, register-open**.

Counts, from the register's own top block (`KNOWN-ISSUES.md:10`): **35 filed, 22 closed,
13 open** — re-read 2026-09-30; this audit's first pass recorded 21/14 because KI-26 was
code-closed but register-open, and the register has since caught up (`f2c978b`). A heading
grep reads 25 closed / 14 open and over-counts, because a heading can name both states
(KI-10 schema-half-closed/ratio-half-open, KI-25 closed-then-reopened). KI-16 remains a
numbering hole (KI-30's pre-merge number); **KI-34 is a reservation, not a hole** — the
per-character goal-race filing deliberately not landed by that block's sequencing, with an
owner and a next step named in the register.

---

#### 1. Phase-level work that is genuinely not started

| Phase                                          | What is missing                                                                                                                                | Evidence                                    |
| ---------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------- |
| Phase 6 — Iteration                            | Unfrozen and unstarted; no Phase 6 anatomy exists                                                                                              | `PLAN.md:11`, `PLAN.md:96`                  |
| ADR-0009 Option B — live scenario-slot fetch   | "remain unimplemented"; only Option A (committed JSON, URA Finale, 296 rows) exists                                                            | `ADR-0009:3-8`                              |
| ADR-0009 Option C — bulk wiki seeding          | same line: unimplemented                                                                                                                       | `ADR-0009:3-8`                              |
| Unity Cup slot rows + panel                    | No slot rows, no panel; Trackblazer is by-design Trainer-entered (`free_race`, R56) so its zero seeded rows is not a gap                       | `ADR-0009`, `PLAN.md:11`                    |
| Legacy Select UI                               | ADR-0010 landed the payload as "storage for a screen that does not exist yet"; `legacy_selection` is written by nothing                        | `PLAN.md:508-512`, `ADR-0010`               |
| Skill search on the run screen (FR-D-2)        | Explicitly "a separate slice"                                                                                                                  | `ADR-0011:152-155`                          |
| Tier A cross-check of all 107 Global cards     | Deliverable 7 of the roster request: `tools/roster-crosscheck.php` and `docs/data/` **verified absent**                                        | `...selector.md:86`, filesystem             |
| Roster branch Tasks 11–13 completion           | 3 unmerged commits (combobox payload coverage, Enter semantics, card-precondition doc fix) sit on `feat/catalog-roster-and-trainee-selector`   | `git rev-list --count master..branch` = 3   |
| Innate/unique skill pre-population             | Published by the source, stored nowhere                                                                                                        | KI-33 OPEN                                  |
| Trainee detail page                            | Ten parsed columns rendered nowhere; no skills/forms/goals section                                                                             | KI-35 OPEN                                  |
| Per-character goal-race filing                 | KI-34 is a reservation: number reserved, entry never written                                                                                   | `KNOWN-ISSUES.md:17`                        |
| Fetch-in-flight indicator                      | C-7 loading state on catalog surfaces                                                                                                          | `DESIGN.md:362`                             |
| Placement pricing below 1st                    | Slice 15 priced 1st only from `grade_point_by_grade`; KI-10 ratio half visibly open                                                            | `PLAN.md:503`                               |
| Consecutive-race derivation                    | KI-17 closed on the link; `consecutiveRaceCount()` still returns null                                                                          | `PLAN.md:508-510`                           |

#### 2. Accepted rulings with no code behind them

| Ruling                               | Obligation                                                                                       | Verified reality                                                                                                                                                                                     |
| ------------------------------------ | ------------------------------------------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| ADR-0002 (accepted, amended twice)   | Widen stat validation `0..1200` → `0..2000`, per-scenario `hard_cap`                             | **Still open** — `StoreTurnEntryRequest.php:49-53` is `between:0,1200` on HEAD (verified this session)                                                                                               |
| ADR-0002 UI clause                   | 1,200 halved-gains line and scenario ceiling visually distinct                                   | Cannot be met while the bound is 1200; bound + bar are one slice                                                                                                                                     |
| ADR-0001                             | Owner edits to `PRD.md` §6.11 and CLAUDE rules 1/4/5                                             | Outstanding (`ADR-0001:3`, `:33-40`)                                                                                                                                                                 |
| ADR-0003                             | PRD US-10 / FR-C-6/7 + CLAUDE/AGENTS updates; `scenario_slots` column definitions written down   | Outstanding (`ADR-0003:102-106`, `:140-141`); `scenario_races` frozen, drop blocked (`:128`, `:195-202`)                                                                                             |
| ADR-0010                             | Nothing enforces D-260's fixity — payload rewritable mid-run                                     | `ADR-0010:63`; no UI writer exists yet anyway                                                                                                                                                        |
| ADR-0011                             | `is_unique` 294-vs-290 gap, `type` derivation, D-210/G-16 propagation                            | `ADR-0011:106-115`, `:128-136`; import itself landed (`GametoraSkillsParser`, `StoreSkills`, 1,910 records; Screen D live at 623 Global rows)                                                        |
| ADR-0006                             | Theme resolver was "in flight" at decision time                                                  | **Corrected: landed.** `layout.blade.php` composer reads Preference → `prefers-color-scheme` → light; dark is an override block. What remains is the §3.7 authority annotation vs root `DESIGN.md`   |
| OQ-1 follow-up                       | `APP_NAME` should read Trainer Desk                                                              | **Still open** — `.env:1` and `.env.example:1` both `APP_NAME=Laravel` (verified)                                                                                                                    |

#### 3. Open issues in the register (13)

| ID      | Layer              | One-line summary                                                                                                   | Status note (this session)                                                                                                                                                                                                                                                                                                           |
| ------- | ------------------ | ------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| KI-10   | backend/data       | GP placement ratio: only 1st priced; schema halves closed                                                          | ratio half open                                                                                                                                                                                                                                                                                                                      |
| KI-15   | backend/data       | Three Grade Point tracks, no sourced rule for choosing one                                                         | open                                                                                                                                                                                                                                                                                                                                 |
| KI-23   | pipeline           | `uma:fetch` never filled `umamusume.name_ja`; fixture repeats the wrong key                                        | parser fix landed on the branch (`d755da3`/KI-23b CLOSED); register KI-23 headline still OPEN pending its own re-read — the two entries must be reconciled                                                                                                                                                                           |
| KI-24   | pipeline           | Stale pinned source hash answers 200 with stale content                                                            | open; `config/uma.php` sentences await this fix                                                                                                                                                                                                                                                                                      |
| KI-25   | frontend           | Turn log forces horizontal scroll at phone width                                                                   | CLOSED then **RE-OPENED** (R85): the measurement half was never read in a browser, and its closure cited a nonexistent `slice-16` verification file (the file is indeed absent — verified)                                                                                                                                           |
| KI-26   | backend/security   | Unescaped `LIKE` in `CatalogController`                                                                            | **CLOSED — remove from the open count.** Code fixed on master at `f2c978b` (`addcslashes` at `:60`/`:82`, `CatalogRosterTreeTest` wildcard case), and the register caught up on 2026-09-30. This audit's first pass carried it as open because the fix commit never touched `KNOWN-ISSUES.md`; it now does. Do not re-fix the code   |
| KI-27   | pipeline           | `uma:fetch` reports "unchanged" against a DB it never wrote to; snapshot key is document-only, `storage/` shared   | open; Architect's rule, not a one-file patch                                                                                                                                                                                                                                                                                         |
| KI-28   | backend            | SQLite reads unknown double-quoted identifier as string literal — silent missing-column hazard                     | open by design: the record **is** the mitigation                                                                                                                                                                                                                                                                                     |
| KI-29   | frontend           | `/umamusume` controls 30/31/32px vs §6.14's 44                                                                     | open; pairs with KI-26's owner                                                                                                                                                                                                                                                                                                       |
| KI-32   | CSS                | No `color-scheme` declared; native controls paint light on dark                                                    | **Verified absent** — 0 matches in `resources/css/app.css`; fix is two declarations in the D-101 dark block                                                                                                                                                                                                                          |
| KI-33   | schema/frontend    | Innate/unique skills stored nowhere → no pre-population                                                            | open                                                                                                                                                                                                                                                                                                                                 |
| KI-35   | frontend           | Trainee detail is a metadata stub; invented absence vocabulary                                                     | open                                                                                                                                                                                                                                                                                                                                 |
| KI-36   | a11y               | Run-screen skills editor: three controls, one label, two without accessible names                                  | open                                                                                                                                                                                                                                                                                                                                 |
| KI-37   | frontend           | Run-screen controls 31/30/40px vs §6.14's 44                                                                       | open                                                                                                                                                                                                                                                                                                                                 |

Also: KI-24b (tooling — fresh clone has six red `SkillAutomationTest` failures because
`.agents/` is gitignored), filed OPEN by the roster branch. KI-23b records the parser fix.

#### 4. Gate, tooling, and CI work

| Item                                          | Status                                                                                                              | Cite                                    |
| --------------------------------------------- | ------------------------------------------------------------------------------------------------------------------- | --------------------------------------- |
| G-60 retired-literal scanner                  | Registry lives, scanner does not; reviewer-enforced                                                                 | `docs/GATE-REGISTRY.md:36`              |
| Em-dash sweep over shipped Blade              | `RenderedCopyHygieneTest` covers rendered copy; gate.py does not sweep it as a separate marker                      | `GATE-REGISTRY.md:138`                  |
| `make lore` blind to untracked files          | Recorded gap; `composer lore` parity tested by `LoreGateParityTest`                                                 | `GATE-REGISTRY.md:136`                  |
| C-7 loading-state enforcement                 | Interpretive review only                                                                                            | `GATE-REGISTRY.md:140`, `ADR-0007:56`   |
| C-6 catalog < 200 ms                          | Manual benchmark only                                                                                               | `SOURCE-OF-TRUTH.md:215`                |
| C-9 `npm run typecheck`                       | Script + `typescript` devDep exist **only as uncommitted worktree edits**; not yet a landable gate                  | `package.json` diff this session        |
| `make` on PATH                                | Absent on this host (KI-4); slice criteria still name `make lore` — use `composer lore`                             | `PLAN.md:53`, roster plan Step 13       |
| Browser half of `DesignTokensTest`            | Skips (Playwright absent, C-8 bars installing) — contrast remains hand measurement                                  | `PLAN.md:615-616`                       |
| Roster plan tracker vs tree                   | 116 steps unchecked while Tasks 1–13's artifacts are on master/branch — tracker should be re-derived, not trusted   | verified both trees                     |
| `ResolveMatchCandidate` double-submit guard   | one-line fix, "belongs to a separate slice"                                                                         | roster plan `:2211`                     |
| Unique index on `umamusume.external_ref`      | Architect's call, untaken                                                                                           | roster plan `:3983`                     |

#### 5. Verification-only gaps (someone must look; no code pending)

- KI-2 runtime HTTP pass on the live server with the old cache (`KNOWN-ISSUES.md:272` block).
- ADR-0006 catalog page with and without a stored `preferences` row.
- OQ-2 robots.txt + live-availability note formalization; OQ-3 scheduled-vs-manual fetch default.
- The Tier A cross-check + Deliverable-8 count report (roster request §4.7–4.8).
- `text-risk` ratios 10.04/4.33/4.77 unreproducible from their own hex values.
- Combobox runtime behaviour — the branch's own last commits (`4addcd6`, `1c44698`) are fixes to
  exactly this (payload coverage, ArrowUp/Enter semantics); the Task 13 browser pass should
  re-measure after they merge.
- Mood pill NORMAL/BAD/AWFUL: no verifiable `[Global]` hex on record.

#### 6. Blocked on data that does not exist yet (source work, not build work)

Unchanged from the 14:00 draft: `docs/UMAMUSUME_REFERENCE.md` `❌ UNVERIFIED` markers (the long
list at `:296`–`:2155`); `docs/scenarios/07-grand-concert.md` known-gap stub, extraction
suspended by owner; `09-global-race-calendar.md:666` tier-vocabulary mismatch; `SKILLS-GAPS.md`
G-SK-3/G-SK-4 (hint level displayable, **discount percentage forbidden to compute or render** —
Conflict Log row 16, stricter than any brief), G-SK-8/9/11/12. The brief-supplied six-row
hint-discount table is **rejected as a unit** (`SKILLS-GAPS.md` §2.3) — do not re-import it.

#### 7. Documentation drift found while answering this question

1. **`PLAN.md:3`** — "active through Slice 10" while the table records Slices 11–14 complete,
   15 landed, 12 corrected-to-halted, and the register carries a Slice 16 re-opening. Header is
   **six slices stale** (draft said four).
2. **`PLAN.md` Open Decisions** — theme default listed as unowned (closed by ADR-0006 + landed
   resolver); rounded-font exempted (KI-6). Only mid-run "Change scenario" semantics is open.
3. **`KNOWN-ISSUES.md` KI-26 heading says OPEN; the fix is on master** at `f2c978b` — register
   lag from a commit that named the defect in its subject but not in the register.
4. **`DESIGN.md:355-357`** — stale `welcome.blade.php`/fonts.bunny claim; file deleted, KI-3 closed.
5. **`ADR-0008:10-11`** — "no migration, model, enum or parser exists yet" while all of them are
   on master. Erratum block partially repairs; drift guard admits it is phrase-only.
6. **Roster plan checkboxes 0/116 vs a largely-landed slice** — tracker and tree diverged.
7. **`docs/flows/create-run-and-legacy-select.md:24-28`** — HEAD-relative "not implemented" rows,
   several since moved.
8. **Missing supersession annotations** — ADR-0002 Option-A recommendation unmarked though
   ADR-0003:5 claims it; ADR-0003 R3 "fetch engine, not a seeder" reversed by the implemented
   `ScenarioSlotSeeder` with no edit; ADR-0006 §3.7 vs root `DESIGN.md` authority note.
9. **`PLAN.md:452` R77** — ruling reserved with no text, recorded as an absence on purpose.
10. **`SOURCE-OF-TRUTH.md:158`, `:273`** — registry path `.agents/skills/skills.json` vs real
    `.agents/skills.json`.
11. **`FRONTEND-BRIEF-AUDIT.md` §3 two rows** describe a tree they never saw; corrections owed at
    `PLAN.md:557-566`, unapplied (file still untracked).
12. **Stat bound stated two ways** — `docs/flows/...:73` (0..1200, not implemented) vs
    `SOURCE-OF-TRUTH.md:196`, `:242`.

#### 8. Deliberately not work — do not schedule these

Unchanged: ADR-0005 support cards **DECLINED for Phase 1** (R37; "no slice may cite it as
permission"); `scenarios/08` JP-only must not be imported; PRD §6 non-goals (predictions,
snapshots, dual storage, image uploads, DB enums, support-card DB); Livewire decided no (R29) —
reopening needs all seven items at `PLAN.md:650-667` and a UI need, never latency;
`gene_version`/`evo`/`sup_hint`/`sup_e` stay out (ADR-0011:116-122); G-SK-7 "closed, not open";
the three incoming UX write-ups are triaged **not merged** — their feature wishes are not
backlog, and `FRONTEND-SPEC-DIVERGENCE.md` stays unmerged per D-283.

---

#### The order this suggests, if any of it gets picked up

1. **Land the roster branch and the detail-page branch** — `feat/catalog-roster-and-trainee-selector`
   is 2 commits ahead (both docs); `feat/umamusume-detail-page` is 4 ahead and carries real backend
   work (ADR-0013, `umamusume_profiles` migration + model + factory + `CharacterProfileTest`). Tick
   or restate the plan tracker, then run the Tier A cross-check of all 107 cards (Deliverables 7–8,
   the only substantive roster work left).
2. ~~**Close KI-26 in the register**~~ — **done 2026-09-30**; the register now reads 22 closed / 13 open.
3. **ADR-0002 `0..2000` widening + two-marker stat bar** — accepted ruling, still unimplemented
   (verified `between:0,1200`), and the blocker for legitimate post-rework Global runs.
4. **Register/slice hygiene pass** — PLAN header re-baseline (Slice 10 → 16), KI-23/KI-23b
   reconciliation, KI-25's real browser measurement, KI-10 ratio naming, ADR-0006/0002/0003
   supersession annotations, `APP_NAME` → `Trainer Desk`.
5. **KI-32 `color-scheme`** — two declarations in the block D-101 already owns; pairs with
   KI-29/KI-37 control sizing as one frontend pass.
6. ~~**Commit or reject the dirty worktree's C-9 tooling**~~ — **resolved.** `package.json:8` carries
   `"typecheck": "tsc --noEmit"`, `package.json:16` carries `typescript ^7.0.2`, both tracked and
   clean; `CONSTRAINTS.md:17` names C-9 with `npm run typecheck` inside `composer test`. One related
   trap is on the record: `d192fa1` added `resources/js/types/global.d.ts` to "fix" C-9 and was
   reverted by `85b37a4`, because `resources/js/bootstrap.ts:3-7` already declares `Window.axios`
   inline and the second declaration disagreed (`AxiosInstance` vs `AxiosStatic`, TS2717). The gate
   is paved; do not re-add a global declaration for it.
7. **ADR-0009 Options B/C and Unity Cup slots** — the pipeline work most panels still wait on.
8. **KI-33 → KI-35 → G-SK-3 chain** — store innate/unique skills, then the trainee page shows them.
9. **Phase 6** — needs an owner decision before it can be a phase at all.

---

#### Addendum, 2026-09-30 — the sources this audit's first pass did not read

Swept after the fact: all thirteen `docs/design-research/verification/*.md` slice records (2, 3, 5–15),
`cardless-band-2026-09-30.md`, `design-pass-trainee-detail-2026-09-29.md`, root `SKILL.md` (231 lines),
`source.md` (171 lines), `.ai/rules/{index,code-style,testing-standards}.md`,
`.ai/guidelines/custom/domain.md`, `.ai/guidelines/framework/core.md`, and the branch tips. Read-only;
`PLAN.md` and `docs/design-research/**` are on the forbidden-edit list, so drift found there is
reported, not fixed.

**New pending work found (not in §1–§7):**

> **Superseded by §Addendum 2 below.** The first row of this table read "ADR-0013 backend built,
> unmerged; frontend not built." That was wrong on both halves, and it was wrong because the row was
> written from the branch's own ADR prose without reading master's tree. Master already ships the
> whole feature, and the branch is a *duplicate* of it with a divergent schema. See Addendum 2.

| Item                                                                                          | Layer           | Evidence                                                                                                                                                                                                                                                                                                       |
| --------------------------------------------------------------------------------------------- | --------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| ~~ADR-0013 character profile source — backend **built, unmerged**; frontend **not built**~~   | fullstack       | **Withdrawn.** Master carries the migration, model, factory, store action, parser, contract, three test files, the `config/uma.php:209` source entry **and** the render in `resources/views/catalog/show.blade.php`. Nothing here is pending except the migration run and ADR-0013's own absence from master   |
| ADR-0012 card detail fields and images                                                        | frontend/data   | On master, not covered by this audit's first pass. Narrows ADR-0008's card layer; ADR-0013 cites its "Decision 2 (images, which this narrows with a measurement)" — verify what it obliges before scheduling the detail page                                                                                   |
| KI-34 reservation                                                                             | docs/register   | `KNOWN-ISSUES.md:10` names it explicitly as "a reservation, not a lost entry": the per-character goal-race filing, deliberately not landed by that block's sequencing, with an owner and a next step. Distinct from KI-16, which is a genuine numbering hole                                                   |
| `umamusume_profiles` absent from the migration count                                          | docs            | `PLAN.md:581` reads "22 migrations, 2 seeders"; master tracks **34** files under `database/migrations/`, the branch adds a 35th. Re-baseline with the PLAN header pass (§7)                                                                                                                                    |

**Open items this pass closed (delete them from any pickup list):**

| Was open                                                                        | Now                                                                                                                                            | Evidence                                                                             |
| ------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------ |
| Cardless band below the listbox scroll fold — three options, recommendation 3   | **Closed by owner ruling: leave the band as measured, keep recent-10.** The options stay on the record as what was weighed, not as work owed   | `8906a40` (unmerged, on both live branches) and `cardless-band-2026-09-30.md` §5–6   |
| C-9 typecheck tooling uncommitted                                               | **Committed and clean**                                                                                                                        | see §8 item 6 above                                                                  |
| KI-26 register lag                                                              | **Register caught up**                                                                                                                         | see §3 above                                                                         |

**Corrections to this audit's own claims:**

- `prototypes/` does **not** exist at the repo root. The prototypes live at
  `docs/design-research/prototypes/` (3 live + 4 `superseded/`), which is what `tools/gate.py:21`
  resolves via `PROTO_DIR`. A root-relative reading of `PLAN.md:81`'s
  `prototypes/screen-a-scenario-v10.html` is wrong; the path is relative to `docs/design-research/`.
  Not a defect in the plan, a trap for whoever greps for it.
- Stale mirrors persist at `.kilo/worktrees/cypress-cardamom/docs/design-research/prototypes/**`
  (detached HEAD `2033434`). `docs/GATE-REGISTRY.md` already records the `gate.py` mirror as inert;
  the prototype mirrors are the same class of hazard and are not listed there.

**Sources that carried no pending work:** root `SKILL.md` and `source.md` are skill/prompt inventory,
not a work tracker — the only marker hit was `SKILL.md:68`, a v1 taste-skill "preserved for projects
depending on its exact behavior", which is deliberate. `.ai/rules/index.md` still reads "No rules
recorded yet" while `.ai/rules/code-style.md` and `testing-standards.md` exist beside it — a small
index/content drift worth one line, not a work item. Slice records 2, 3, 5, 7, 10, 11, 13, 14 are
historical verification and their open threads are already in §1–§7; slice-12's §8 halt (T4 never ran,
nothing pushed, blocked on the §7.2 dependency-vs-framework-free call) is superseded by master's
history — `origin/master` moved well past `4992282` and the roster work landed.

---

#### Addendum 2, 2026-09-30 — the profile feature: shipped on master, duplicated on a branch, dark in the DB

This section corrects Addendum 1. Every claim below was read off `master` and
`feat/umamusume-detail-page` directly, not off either branch's prose.

##### 2.1 Master already ships the whole feature

`git ls-tree -r --name-only master | grep -i profile`:

```text
app/Actions/StoreCharacterProfiles.php
app/Models/UmamusumeProfile.php
app/Services/DataPipeline/Contracts/ProfileSourceParser.php
app/Services/DataPipeline/Parsers/GametoraCharacterProfileParser.php
database/factories/UmamusumeProfileFactory.php
database/migrations/2026_09_29_182820_create_umamusume_profiles_table.php
tests/Feature/GametoraCharacterProfileParserTest.php
tests/Feature/StoreCharacterProfilesTest.php
tests/Feature/UmamusumeProfileSchemaTest.php
```text

Plus `config/uma.php:209` (`gametora-character-profiles`) and the **frontend render**:
`resources/views/catalog/show.blade.php` has the whole profile block — Japanese name, voice actor
(JA + EN with a `title=` disclosure), birthday, height, three sizes, and a sentence-form absence
state per D-220. `CatalogController.php:167` eager-loads `'profile'`. `Umamusume.php:81` declares
the `HasOne`. **KI-35 ("trainee detail is a metadata stub") is stale on master** — the stub is gone.

##### 2.2 `feat/umamusume-detail-page` is a divergent duplicate, and merging it breaks master

Two migrations both named `create_umamusume_profiles_table`:

|                               | master `2026_09_29_182820`                               | branch `2026_09_30_120000`                                  |
| ----------------------------- | -------------------------------------------------------- | ----------------------------------------------------------- |
| `name_ja`                     | present                                                  | **absent**                                                  |
| height                        | `height`                                                 | `height_cm`                                                 |
| three sizes                   | `three_sizes_b` / `_h` / `_w` (`unsignedSmallInteger`)   | `bust_cm` / `waist_cm` / `hip_cm` (`unsignedTinyInteger`)   |
| birth parts, VA, provenance   | same                                                     | same                                                        |

The branch also **shrinks** `app/Models/UmamusumeProfile.php` (91 lines changed, mostly deletion) and
the factory, and adds a `CharacterProfileTest.php` master does not have.

Two independent breakages on merge:

1. `Schema::create('umamusume_profiles')` runs a second time against a table that already exists —
   `php artisan migrate` fails.
2. Even if it ran, master's shipped view reads `$profile->height`, `$profile->three_sizes_b`,
   `$profile->three_sizes_h`, `$profile->three_sizes_w` (`show.blade.php:125,137`) and
   `Umamusume::nameJa()` reads `$profile->name_ja` (`Umamusume.php:103`). None of those columns exist
   in the branch's schema. The detail page would break on render.

**Recommended disposition:** the branch's four commits should be **cherry-picked, not merged**.
`8906a40` (fold ruling) and `7cc6283` (source re-resolve) are docs and carry cleanly. `92670ff`
(ADR-0013) and `992016d` (the duplicate table) must be reconciled against master's shipped shape
before anything lands — ADR-0013's prose needs rewriting to describe the columns master actually has,
and `992016d` should be dropped.

##### 2.3 The feature is dark in the live database

The `migrations` table's last four rows end at `2026_09_29_120200_add_character_card_id_to_training_runs_table`.
`2026_09_29_182820_create_umamusume_profiles_table` is **pending**. Direct observation:

```text
php artisan tinker --execute '... UmamusumeProfile::count() ...'
SQLSTATE[HY000]: General error: 1 no such table: umamusume_profiles
```text

Because `CatalogController.php:167` eager-loads `profile`, `/umamusume/{slug}` cannot render against
the current dev database. **Pending action: run `php artisan migrate`** (additive, one table). Not run
here: the DB is shared state in a shared worktree, and the governing turn forbade destructive gates.
This is the cheapest item on the whole list and it unblocks manual verification of an already-shipped
feature.

##### 2.4 ADR-0013 and its probe are absent from master, but master's code depends on both

- `docs/adr/0013-character-profile-source.md` exists **only** on the two unmerged branches. Master
  ships the table with no ADR on master — a C-1/gate-precedence hole, since the gate order is
  CONSTRAINTS → GATE-REGISTRY → ADRs → DESIGN → PLAN.
- `docs/data/2026-09-30-characters-source-probe.md` exists **only** on the branch, and
  `git grep -l "characters-source-probe" master` returns nothing — so master's code cites no probe,
  while the branch's ADR cites a file master lacks. Whichever way this reconciles, the measurements
  (163 rows, 135 roster, `va_en`/`three_sizes` absent on 10) should land on master next to the code
  they justify.
- ADR-0012 (`card-detail-fields-and-images`) **is** on master; ADR-0013 is not. The set is 0001–0012.

##### 2.5 Suspected defect, needs a source ruling — `three_sizes_h` is labelled "height"

Master's migration comment (`:75`) reads "bust / **height** / waist, in the source's own key order",
and the shipped caption at `show.blade.php:138` reads `bust · height · waist`. The same migration's
docblock gives the source shape as `{"b": 81, "h": 81, "w": 56}`, and
`GametoraCharacterProfileParserTest.php:57-59` pins exactly those values.

`h = 81` cannot be a standing height: the `height` column beside it is measured 135..158 cm. In the
source's own convention three sizes are bust / waist / **hip**, so `h` is hip and the key order is
B-H-W. The rendered caption therefore names the middle number as a height it is not.

Marked **[Unverified against the live source document]** — the probe file that would settle it is on
the branch, not master, and no snapshot was read here. Two lines of copy and one comment change if the
reading holds; no schema change either way (`three_sizes_h` is a fine column name for hip). This is a
Lore-Guardian-adjacent copy defect, not a data defect, and it is **not filed** in `KNOWN-ISSUES.md`
(highest number there is KI-41).

##### 2.6 One lore item to route, not to fix

`tests/Feature/GametoraCharacterProfileParserTest.php:81` comments a fixture row as
"Darley Arabian: no va_en, no three_sizes, no birth_year." If that is a verbatim name from the source
document it is C-4 allowed-hit class 3 (data, not framing) and needs no change; if it is invented test
copy it is an equine proper noun standing in for a trainee and the Lore Guardian should rule. Recorded
here rather than decided, because deciding it requires reading the source document, which is 2.5's
missing file.

##### 2.7 Net effect on the pickup order

Insert at the top of §"The order this suggests":

1. **Run `php artisan migrate`** — one pending table; unblocks manual verification of a shipped
   feature and stops `/umamusume/{slug}` erroring locally.
0b. **Reconcile `feat/umamusume-detail-page` before anyone merges it** — cherry-pick the two docs
```text
commits, rewrite ADR-0013 against master's real columns, drop `992016d`. Land ADR-0013 and the
probe doc on master so the shipped table has a decision record.
```

0c. **Rule on `three_sizes_h`** (2.5) and close **KI-35** as stale-on-master (2.1).

And strike from the old list: §8's "KI-35 trainee detail is a metadata stub" is no longer accurate on
master.

---

## KNOWN-ISSUES.md (defect register)

## Known issues

Defects found during the scenario-aware design and component work that were **out of that
phase's scope**, so they are recorded here rather than fixed in passing. Each entry states
the command or file that proves it, not just the symptom.

Discovered 2026-09-27. None of these were introduced by the component work; the component
work is what made them visible, because the prototype phase had no running server to hit.

**Status (2026-09-30, KI-33 / KI-36 pass):** **35 filed, 24 closed, 11 open**, nothing filed here and two
closed. KI-33 closes across `dd90330` (the two json lists, the parser that keeps them, the pre-populate at
run creation) and `4902f1d` (the repeater it called a required part); KI-36 closes in the same `4902f1d`,
which is the same-commit grouping R-3 asked for rather than two commits touching one block. Both closures name what
they do not cover: KI-33's four-group picker is unblocked by the storage and still unbuilt, and the
`skills_innate` / `skills_unique` columns are not yet in ADR-0008, `ARCHITECTURE.md` §3, the ESSENTIALS
digest or D-30, because this session was barred from those files and `DocSchemaDriftTest` only pins
`training_runs`. That doc gap is the next register pass's business, not a reason to hold the schema.
Prior:
**Status (2026-09-29, trainee detail and skill selector pass, updated with KI-26 closure):** **35 filed, 22 closed, 13 open.** Four
entries land here from the design pass (KI-33, KI-35, KI-36, KI-37), and **KI-26 is closed** against `f2c978b`
(unescaped `LIKE` in `CatalogController` fixed on master; register lagged by one commit). **KI-34 is a reservation,
not a lost entry**: it is the per-character goal-race filing from the previous pass, deliberately not landed by
this block's sequencing. Unlike KI-16 — which is a genuine renumbering hole, KI-30's pre-merge number —
this gap has an owner and a next step. A heading grep reads 25 closed and 14 open (including KI-10 and KI-25 which
carry both words, plus KI-23b and KI-24b) where the register says 22 closed and 13 open (KI-10 ratio half counted
open, KI-26 closed). Both are headings holding more history than a regex reads. A heading grep for `CLOSED` or
`OPEN` over-counts, because a heading can name both states (KI-10's schema-half-closed/ratio-half-open, KI-25's
closed-then-reopened). The three numbers in this block are the authoritative counts; a heading grep is indicative
only.

**Status (2026-10-02, documentation-sync pass, recount after a concurrent edit):** **55 filed, 34 closed,
21 open** — and of the 34, **5 are closures verified on this working tree and held pending owner gate O-1**
(the push; `git rev-list --left-right --count HEAD...origin/master` = `23 0` at the time of counting), because
this register's discipline closes an entry only when the fix is on `origin/master`. The five held: KI-48
(`79d5f6f`), KI-49 and KI-51 (9 of 9 bodies and the seeder code tracked), KI-53 and KI-54 (fixture tracked,
no `.held-aside` in the tree, the helper guarded at `1bfbd33`, and the 11-red state no longer reproduces).
Each carries a **Verified … closure HELD** block in its body naming its sha and its file:line proof. The
counts below were written as 54/34/20 and are restated here because **the register changed under this pass**:
a concurrent session (the M1 batch) closed KI-32's heading and filed KI-55 while these blocks were being
written. KI-32's closure is supported by this pass's independent verification of `ed71741`; see its block for
the two hygiene notes that closure leaves. The count is by entry, not by heading grep: KI-10 and KI-25 each
carry both words in one heading and are counted once, KI-10 in its body's state (schema halves closed, ratio
half open, so open) and KI-25 re-opened by its body. KI-23b and KI-24b are entries, not heading noise.

**Sweep note (Task 6's "never-cited entries" question, answered rather than filed):** one genuinely new
defect was found by this pass and was **not filed**, because a parallel commit fixed it first.
`withTierLabelsFileAbsent()` at `tests/Feature/ScenarioSlotSeederResilienceTest.php:105` renamed the tracked
fixture with no `file_exists` guard, and the calling test's second invocation (`:55`) sat outside its own
try/finally, so a throw between the rename and the `finally` would have left a **tracked fixture** deleted
from the shared working tree — the worst kind of register-adjacent defect, one that destroys another
session's file rather than a test failing. `1bfbd33` ("fix(tests): guard the tier-label helper against the
already-absent fixture state") added the guard and labels it KI-54's fix; the guarded helper is verified in
the tree and `ScenarioSlotSeederResilience` passes 5 tests / 17 assertions. Recorded here so the sweep reads
as a decision rather than an omission, and so KI-55 is not assumed to be that entry — it is the M1 batch's
O-2 filing, a different finding.

The block below stays as the record of what this register believed on 2026-09-30; it was accurate when written
and is now superseded by twenty entries' worth of work.

Prior:
**Status (2026-09-29, Screen D dark-theme pass):** **31 filed, 21 closed, 10 open**, one filed here.
**KI-32** is the missing `color-scheme` declaration: native form controls keep painting light widgets on
the dark surface, observed as two different unchecked renderings of the same checkbox across loads. The
dark pass itself is clean — every contrast pair, overflow and focus measurement on both themes is in
`SKILLS-GAPS.md` §8. Prior:
**Status (2026-09-29, `fix/frontend-audit-2026-09-28` merge):** **30 filed, 21 closed, 9 open**, two filed here
and both closed here, neither by this merge's own hand. The branch carried them as KI-16 and KI-17; both were
renumbered to KI-30 and KI-31 because master's register already holds KI-17 and documents KI-16 as never
filed. They arrive **closed**: the landing-route pin by R57's redirect, which `ExampleTest` now asserts by name,
and the six `SkillAutomationTest` failures by measurement at `eb23fa8` — 7 passed there, 0 failing suite-wide —
with the fixing commit deliberately left unnamed rather than guessed. Prior:
**Status (2026-09-29, Screen D browser pass):** **28 filed, 19 closed, 9 open**, one filed here. **KI-29** is
`/umamusume`'s form controls measuring 30/31/32px against `DESIGN.md` §6.14's 44, found by measuring the
rendered page rather than reading the classes; Screen D's own controls are fixed, the older surface is not.
Two of this thread's three open items on that one file (KI-26, KI-29) can be cleared by whoever next owns
`catalog/index.blade.php` and `CatalogController`.
**Record, not a defect:** `b8a296f` carries `83086b0`'s subject line pasted in error; its body and content
are correct (the three fixture rows Screen D renders — `200471`, `300141`, `202391`). Nothing downstream
cites that SHA, so the mismatch is cosmetic and it is **not rewritten**: the commit sits 14 deep with
concurrent sessions landing on top of it in a shared `.git`, and a rebase to fix one subject line is not
worth the shared-history cost. The rebase stays possible whenever the tree is quiet; this line exists so a
reader who hits the mismatch in `git log` meets the record instead of re-diagnosing it. Prior:
**Status (2026-09-29, catalogue fill pass):** **27 filed, 19 closed, 8 open**, one filed here. **KI-28**
is the silent one: with a column absent from the schema, SQLite reads the quoted identifier inside
`whereNotNull('col')` as a **string literal**, so the predicate is true for every row and nothing is
raised. It surfaced because a count of linked race entries returned 1 against a `race_entries` whose
`PRAGMA table_info` lists no such column — and the unquoted spelling of the same query threw
`no such column`. An un-migrated database therefore drops every catalogue↔entry link without
complaining. The mitigation is the record itself: no `Schema::hasColumn` guard goes into the query
path, because the trap is SQLite's behaviour and every future column addition inherits it. This pass
also **met KI-27 in the wild** while filling the catalogue — `uma:fetch` reported "unchanged since last
snapshot; nothing written" against a database holding 0 rows, because a throwaway fetch had already put
the same document on the shared `storage/` disk — and the reparse workaround KI-27 names is what wrote
the 410 rows. That is a second reproduction of an open entry, not a new one. Prior:
**Status (2026-09-29, Screen D pass):** **26 filed, 19 closed, 7 open**, two filed and none closed here.
**KI-26** is the unescaped `LIKE` on `/umamusume`: a search of `%` returns the whole catalog rather than the
rows whose key contains that character. Reproduced through that controller, not by a hand-written query, and
left unfixed here because `CatalogController` is not this pass's surface and the file is on master in a shared
tree. Screen D escapes and tests the difference. **KI-27** is the fetch that reports itself complete against a
database it never wrote to, found by actually filling the real database rather than a scratch one. Prior:
**Status (2026-09-29, Slice 16):** **24 filed, 19 closed, 5 open.** **KI-25 is back to OPEN** under R85.
`b8c0a54` did land the focusable scroll region and its test, and that work is not being undone; what is
being withdrawn is the closure, because the issue's **measurement half has never been read in a browser** —
arrow-key traversal of the two regions, the document-level `scrollWidth` after scoping the overflow to the
table, and every column and region other than the one element originally probed are all unmeasured. The
closed text also cited `docs/design-research/verification/slice-16-2026-09-29.md`, which does not exist. A
register entry whose evidence is a dangling path is not a closed entry.
**What re-closes it:** the responsive contract — D-40's "no mobile-first compromise" reconciled with the
768px floor `b8c0a54` wrote into `DESIGN.md` §2.3 (that amend is itself held pending R82) — plus a read-only
measurement pass whose numbers land beside the closure. The other four open items are KI-10's ratio half,
KI-15, and the skills pass's KI-23 and KI-24. A heading grep reads 21 closed and 3 open; the register says
19 and 5, and the two entries in between are known and named — KI-10, whose heading says CLOSED while its
ratio half is not, and KI-25, whose heading now reads CLOSED *inside* a RE-OPENED sequence. Neither is a
counting error to fix; both are headings that carry more history than a regex can read. Prior:
**Status (2026-09-29, Slice 15):** **24 filed, 19 closed, 5 open.** The Schema Session landed all three
items its brief named. **KI-17 is closed** on the link it asked for (`d06199c`): `race_entries` can now
point at the turn a race was run on, and the closure carries its own limit — the count is still entered,
because a turn with no named link is not a turn that did not race. **KI-10's second schema half closed**
with `grade_points_earned` (`3711894`), which gives each finish a figure of its own; its **ratio half stays
open**, since nothing below first is priced and no source names the scaling. **Legacy Select landed as
schema only** — one `legacy_selection` json payload under `ADR-0010`, with no screen and no computation
(`26aa9fe`), which closes D-268 as a storage question and leaves it open as a UI one. **KI-25 is filed** by
this slice's browser pass: the turn log table overflows a 390px viewport, measured, and it is not a Slice 15
defect — the diff that slice is one view file, and it is not the table's.

**Two sessions, one register, one commit — said plainly rather than hidden in a count.** This block was
written while the skills pass still held this file uncommitted in the same shared working tree, and the
commit that carries it (`781e2b9` onward) therefore also carries **KI-23 and KI-24, their status block, and
their `ADR-0011` prose, all authored by that session and not by this one.** The owner directed the register
be written rather than deferred; authorship is named here so provenance survives the merge, and the counts
above fold in their two filings because KI-23 and KI-24 are in the file this block tallies. The mobile
finding in KI-25 was left out of their block deliberately: their status line says "The three Slice 14 open
items — KI-10, KI-15, KI-17 — are untouched by any of this", which was true of their pass and is now true
no longer. Prior:
**Status (2026-09-29, skills pass):** **23 filed, 18 closed, 5 open.** The skills audit pass filed
KI-23 and KI-24 while measuring the export the skills import would read, and both are about the existing
fetch path rather than the new one: the characters parser reads a key the live document does not have and
its own fixture repeats that key so the suite cannot see it (KI-23), and a stale cache-busting hash answers
`200` with stale content, which falsifies the safety claim written in `config/uma.php:50-54` and leaves the
live `gametora-characters` pin behind the publisher's current document (KI-24). Neither was introduced by
this pass; both were reachable only by comparing the parsers against a captured source. The three Slice 14
open items — KI-10, KI-15, KI-17 — are untouched by any of this. The skills gaps themselves are not filed
here: they are recorded in `docs/design-research/SKILLS-GAPS.md`, because a missing surface is a gap and a
key that never matches is a defect. **These counts are superseded by the Slice 15 header above, which
files KI-25 in the same shared tree while this pass held the file uncommitted: 24 filed, 19 closed,
5 open.** Prior:
**Status (2026-09-29, trainee detail and skill selector pass):** **35 filed, 21 closed, 14 open.** Four
tier-label question Slice 13 left contested: G1, G2 and G3 are now sourced **per race** from a dated
two-publisher extraction (`329cec1`) and the seeder holds no code-to-label constant, so G-16c is green
for `database/seeders/` and `config/`. That reaches none of the three open items — KI-10 is the Grade
Point placement ratio, KI-15 which GP track applies, KI-17 the consecutive-race count — so all three
stay open. No KI was filed for the two questions Slice 14 leaves, because neither is a defect: the 115
Open rows resting on a disclosed code-level generalisation, and the parser's map unreconciled under
R72's idle condition. Both are recorded with their reasoning in D-153 and in
`docs/design-research/verification/slice-14-2026-09-29.md` §2.5 and §7, where a reader of the map will
meet them. Prior:
**Status (2026-09-29, Slice 13):** 21 issues filed. **18 resolved/closed** (KI-1–9, KI-11–14,
KI-18–22). **3 open**: KI-10 (ratio half), KI-15 (which Grade Point track applies), KI-17 (the
consecutive-race count cannot be derived from the log). Slice 13 closed KI-21 by rebuilding the race
entry form as server-driven disclosure (`6c1969f`, no dependency added) and pinning it with rendered
DOM rather than HTML-source assertions. It closed KI-22 too, **but KI-22 was filed on a wrong cause
and that is recorded in its own entry**: Slice 12 grepped for `isFreeRace`, did not find the
`$manual` parameter that had replaced it, and read a missing identifier as a missing branch. The
real defect was the marker on a finished free race, which the read path's owner fixed in `b6d68b6`.
Counts read off `grep -c "^## KI-"` = 21; KI-16 was never filed, which is why the numbers run to
KI-22. Prior:**Status (2026-09-29, Slice 12 T3):** 21 issues filed. **16 resolved/closed** (KI-1–9, KI-11–14,
KI-18–20). **5 open**: KI-10 (ratio half), KI-15 (which Grade Point track applies), KI-17 (the
consecutive-race count cannot be derived from the log), and two filed by the T3 browser pass —
KI-21 (the race entry form is built on Alpine.js, which is not a dependency, so neither path renders)
and KI-22 (a free_race cell renders `state=past` without the Trainer-entered marker, so R61's calendar
rule is no longer implemented). Both block this slice's T4: gates would go green over a form nobody
can use. KI-22 is a shared-master collision with concurrent commit `82959e9`, not a Slice 12 defect.
Prior:
**Status (2026-09-29, Slice 12):** 19 issues filed. **16 resolved/closed** (KI-1–9, KI-11–14,
KI-18–20). **3 open**: KI-10 (ratio half), KI-15 (which Grade Point track applies), KI-17 (the
consecutive-race count cannot be derived from the log). Slice 11 closed KI-20 by measuring the pair
and stepping the dark `--color-risk`; Slice 12 closed KI-19 on the successful second `update` attempt
(engine v0.1.5) and re-closed KI-11 on current evidence, its deletion basis having been superseded by
the sourced seeder at `f0ae288` and its token half now measured against the Slice 8 epithet rows.
Counts read off `grep -c "^## KI-"` (19 headings at that time; KI-16 was never filed, which is why the
numbers run to KI-20). Prior:
**Status (2026-09-29, Slice 11):** 19 issues filed. **15 resolved/closed** (KI-1–9, KI-11–14,
KI-18, KI-20). **4 open**: KI-10 (ratio half), KI-15 (which Grade Point track applies), KI-17 (the
consecutive-race count cannot be derived from the log), KI-19 (the impeccable tool cannot update
itself). Slice 11 closed KI-20 by measuring `text-risk` on `bg-raised` in both themes and stepping
the dark-theme token from #FF6B7A (4.33:1) to #FF7E8C (4.77:1) per the D-259 precedent. Prior:
**Status (2026-09-29, Slice 10):** 19 issues filed. **14 resolved/closed** (KI-1–9, KI-11–14,
KI-18). **5 open**: KI-10 (ratio half), KI-15 (which Grade Point track applies), KI-17 (the
consecutive-race count cannot be derived from the log), KI-19 (the impeccable tool cannot update
itself), KI-20 (the shop error text is an unmeasured contrast pair). Slice 10 closed KI-18,
which `f7a59e8` had already fixed in Slice 9, and filed KI-19 and KI-20.
**Status (2026-09-28, Slice 8):** 18 issues filed. **14 resolved/closed** (KI-1–9, KI-11–14).
**4 open**: KI-10 (ratio half), KI-15 (which Grade Point track applies), KI-17 (the consecutive-race
count cannot be derived from the log), KI-18 (the burst roster prints tool identifiers as UI copy).
Nothing closed in Slice 8; it built the panels and filed what they exposed. Prior:
**Status (2026-09-28, Slice 7):** 16 issues filed. **14 resolved/closed** (KI-1–9, KI-11–14).
**2 open.** KI-10 is split: its schema half (the objective bucket) closed with `e103122`, and its
ratio half stays open because no source names the placement scaling. KI-15 is filed new: the three
Grade Point tracks have no sourced rule for choosing one, and the two Trackblazer guides disagree
about which track a sprint-only trainee belongs to.
Prior: **Status (2026-09-28, Slice 6):** Slice 6 closed KI-14's last residual as
correct-by-design, filed nothing, and moved no threshold; the two `lore-code` hits that arrived
with the concurrent session's review-queue work were ruled allowed under C-4 and written into
`docs/design-research/CONSTRAINTS.md` §3.2 with citations.

---

### KI-1 `x-layout` requests a Vite entry that does not exist — RESOLVED 2026-09-27

> **Resolved.** `layout.blade.php` now requests `resources/js/app.ts`, matching
> `vite.config.js` and the manifest keys. Verified by HTTP status rather than by reading the
> edit: `/training-runs` and `/review` returned **500** before the change and **200** after it,
> with `assets/app-*.css` and the JS entry both linked. `/umamusume` still returns 500, and that
> is **KI-2**, not this — the two were confusable because both presented as a blank page.
> Found while implementing the D-104 theme resolver: that script lives in this same `<head>`, and
> until KI-1 was closed the layout could not render at all, so the theme behaviour could not be
> verified through the real app on any page. Original text below, kept as written.

**Symptom.** Every page rendering through `x-layout` fails to load its stylesheet.

**Evidence.**

```text
resources/views/components/layout.blade.php:7    @vite(['resources/css/app.css', 'resources/js/app.js'])
public/build/manifest.json keys                  resources/css/app.css, resources/js/app.ts
vite.config.js input                             resources/js/app.ts
```text

There is no `resources/js/app.js` on disk and no such manifest key, so `@vite()` throws
`ViteManifestNotFoundException`. `resources/views/welcome.blade.php:15` gets this right and
requests `app.ts`, which is why the bug is confined to the layout rather than being global.

**Cause.** The entrypoint was renamed to TypeScript at some point and the layout was not
updated with it.

**Fix.** One word: `app.js` to `app.ts` at `layout.blade.php:7`.

**Why it is not fixed here.** It is another phase's file, and it is currently masked by
KI-2, so fixing it alone would not make any page load. Fix the two together and verify
`/umamusume` returns 200.

**Forward, 2026-09-30 (documentation inventory, snapshot `c1e14a3`).** The layout half landed:
`layout.blade.php` now reads `@vite(['resources/css/app.css', 'resources/js/app.ts'])` at line 24,
so this entry stays resolved. Two things in the record above no longer resolve, and neither was
edited when it stopped being true. The citation `resources/views/welcome.blade.php:15` points at a
view deleted at `65f8b92` on 2026-09-29 while closing KI-20. And `resources/js/app.js` is still
described as the current entrypoint by two live guidance files, `.ai/guidelines/framework/core.md`
and `.ai/skills/tailwindcss-development/SKILL.md`, both of which quote the
`@vite(['resources/css/app.css', 'resources/js/app.js'])` boilerplate. Nothing guards that pair:
this entry's own resolution test checks HTTP status, not documentation strings.

---

### KI-2 Catalog pages die on a cache that cannot hand back models — RESOLVED 2026-09-28

> **Resolved in `d53a4b1`.** The diagnosis below was written as a guess and both
> halves of it were wrong, so they are corrected here rather than quietly dropped.
>
> - **Not** "the controller hands the view a collection of strings, or a
>   `pluck()`-style list". The controller returned a real `Collection<Umamusume>`.
>   The strings were made by the cache, not by the query.
> - **Not** "Feature tests cannot reproduce this because each test starts with an
>   empty cache; the 500 requires stale cached rows". That annotation would have
>   stopped anyone trying. A test reproduces it deterministically in four lines:
>   point `cache.default` at `database`, flush, visit once to write, visit again to
>   read. No pre-existing rows are needed — the round-trip is the bug. That is
>   `tests/Feature/CatalogCacheRenderTest.php`, and all three of its cases failed
>   with this exact message before the fix.
>
> **Actual cause.** `config/cache.php:128` sets `serializable_classes => false`, so
> `DatabaseStore::unserialize()` calls `unserialize($value, ['allowed_classes' =>
> false])` and every object comes back as `__PHP_Incomplete_Class` (proved in tinker:
> `returned class=__PHP_Incomplete_Class`). Blade iterating that yields strings, which
> is the `->slug` error. The suite never saw it because `phpunit.xml:25` pins
> `CACHE_STORE=array` while `.env` runs `CACHE_STORE=database`, and the array store
> hands back the objects it was given.
>
> **Fix.** The cache now holds primitives — the page's ids and its total — and the
> models are re-read from them; `show()` is not cached at all. The framework setting
> was left alone: it is a deliberate refusal to revive objects from a persistent store,
> and relaxing it to keep caching model graphs would trade a lore-scale safety default
> for a micro-optimisation on a local SQLite read path.
>
> **Runtime pass, which the annotation asked for.** `php artisan serve` against the
> real database cache: `/umamusume -> 200`, `/training-runs -> 200`. Recorded with the
> rest of the slice in `docs/design-research/verification/slice-2-2026-09-28.md`. The
> detail page had the same defect and was covered by neither this report nor the old
> test.

**Symptom.** `GET /umamusume` returns **500**. `GET /training-runs` also returns **500**.

**Evidence.**

```text
storage/logs/laravel.log:
[2026-09-27 00:15:48] local.ERROR: Attempt to read property "slug" on string
  (View: ...\resources\views\catalog\index.blade.php)  ViewException

resources/views/catalog/index.blade.php:29
  <a href="{{ route('catalog.show', $umamusume->slug) }}" ...>
```text

Measured against a running server: `/umamusume` and `/training-runs` both returned 500,
while a page not using `x-layout` returned 200.

**Cause.** The controller hands the view a collection of strings, or a `pluck()`-style
list, where the template dereferences `->slug`. Either the query should hydrate models or
the view should read array keys.

**Impact.** The catalog is the priority surface per `DESIGN.md` §4.1, so the app's first
screen does not currently render. This also hides KI-1: the view throws before `@vite`
in the layout head is ever reached, so the manifest error cannot surface until this one is
fixed.

**Owner.** The phase that owns `CatalogController`.

---

**Status annotation (2026-09-28, docs audit).** Runtime verification pending after the current frontend dirty work lands. Feature tests cannot reproduce this because each test starts with an empty cache; the 500 requires stale cached rows from before the view's model switch. Evidence gap: runtime HTTP pass not executed in the audit/remediation turns. Close it with a real-server pass (`/umamusume` and `/training-runs` returning 200) or by confirming the cache-key version bump cleared old entries.

### KI-3 `welcome.blade.php` breaks the offline requirement — RESOLVED 2026-09-28

**Evidence.**

```text
resources/views/welcome.blade.php:10   <link rel="preconnect" href="https://fonts.bunny.net">
resources/views/welcome.blade.php:11   <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" ...>
```text

`PRD.md` NFR-1 makes this a local-only tool. A remote stylesheet is a network dependency on
first paint, and it loads `instrument-sans`, a font the app does not bundle.

**Fix.** `DESIGN.md` §2.2 already replaced `--font-sans` with the system stack, so the link
tag is now dead weight as well as illegal. Delete lines 10 and 11.

**Status.** Already noted in `DESIGN.md` §7 as "a one-line fix nobody has applied". Still open.

**Closed 2026-09-28 (`5548b9e`).** Both `<link>` lines are deleted; `welcome.blade.php`
now loads its assets through `@vite` like every other page. Measured on a live server
rather than inferred: the rendered document is 39,987 bytes, `grep -c bunny` returns 0
against that response, and every asset the browser goes on to request is same-origin. The
first measurement of that kind was a false pass — the server had died, so `grep -c` was
counting an empty body — which is why the byte count is in the record.

**Guard.** `tests/Feature/RenderedCopyHygieneTest.php` fails any Blade file that carries a
remote `rel=stylesheet|preconnect|preload|dns-prefetch` link or a remote `<script src>`, so
this cannot come back as a one-line fix nobody applies again. Anchor `href`s stay allowed: a
link a Trainer chooses to click is not a dependency the page cannot render without.

**Residual, not fixed.** The page's inline `<style>` block still declares
`--font-sans:"Instrument Sans", ui-sans-serif, system-ui, …` inside a vendored Tailwind
blob. Nothing loads Instrument Sans any more, so the stack falls through to the system
fonts `DESIGN.md` §2.2 chose — the right outcome by accident rather than by editing the
declaration. The blob also duplicates CSS the Vite build already ships. Left alone because
the landing page's markup is not this slice's scope; worth a decision about whether
`welcome.blade.php` should carry 38 KB of inlined CSS at all.

**Forward, 2026-09-30 (documentation inventory, snapshot `c1e14a3`).** That question is closed by
deletion rather than by answer: `Route::view('/', 'welcome')` became a redirect to `runs.index`,
`welcome.blade.php` was deleted, and KI-20 closed, all in `65f8b92` on 2026-09-29. `ExampleTest`
asserts the redirect by name. The 38 KB blob went with the file. The `**Evidence.**` block above
keeps its line numbers as recorded, because it is the output of a grep that ran on 2026-09-28
against a file that existed then; a reader who runs it today gets no match, and that is the
expected result, not a broken gate.

---

### KI-4 `make lore` cannot see untracked files, and checks no terminology — RESOLVED 2026-09-28

**Symptom.** A brand-new file passes the lore gate by being invisible to it.

**Evidence.** `git grep` searches tracked files only:

```text
$ git ls-files --error-unmatch config/scenarios.php
Did you forget to 'git add'?

$ git grep -inw "planned" -- 'config/**' 'resources/**'      # tracked only
(nothing)
$ git grep --untracked -inw "planned" -- 'config/**' 'resources/**'
(found)
```text

Separately, the `lore` target greps equine vocabulary only. It has no pattern for
`wisdom` or `motivation`, which are the terms the source wikis use throughout for Wit and
Mood, so the project's own constraint about client terminology is unenforced by it.

**Mitigated by.** The `lore-code` target added 2026-09-27, which uses `--untracked`, adds the
terminology set, and scopes to shipped code so `docs/` can keep quoting the wiki English it
warns against. `make lore` is left untouched.

**Still open.** `make` itself is not installed in this environment (`make: command not
found`), so neither target can be run as documented. The recipes were executed directly.
Consider a composer script so the gate does not depend on a GNU make binary on Windows.

**Closed 2026-09-28 (`cc3f963`).** `composer lore` and `composer lore-code` run both
targets through `tools/lore.php`, which hands `git grep` an argument array instead of a
shell string. That matters more than convenience: a composer script holding the Makefile's
command text runs through `cmd.exe`, which does not quote with single quotes, so
`-- ':!vendor'` reaches git still quoted, git errors, the recipe's `|| true` swallows it,
and the gate prints nothing and exits 0. The failure mode this replaced was a gate that
reported clean while matching no file at all.

Parity is measured rather than claimed. On one tree, the recipe bodies run verbatim and
`composer lore` report the same count, and the `lore-code` body and `composer lore-code`
both report 4. `LoreGateParityTest` compares the pattern strings between the Makefile and
the runner, and dropping `withers` from the script is what proves the guard bites. One side
effect is recorded rather than hidden: tracking the runner made it a permanent self-hit, so
the sweep now prints the scanner's own pattern lines beside `gate.py`'s, and
`docs/GATE-REGISTRY.md` allowed class 4 names both files with their composition — counts
there are dated, because any rules file quoting a banned word moves the total. The Makefile
targets stay — `CONSTRAINTS.md` C-4 names them — and `lore-code` cannot self-hit because
`tools/` is outside its path list.

The untracked-file half of this entry was never closed by the runner: `make lore` reads
tracked files only by design, and `lore-code` reads untracked copy inside app paths only.
That scope split is registered as a gap, not a fix.

---

### KI-5 A test asserts a fabricated skill name built on a banned word — FIXED 2026-09-27

**Fixed.** `tests/Feature/TrainingRunTest.php` now uses real Global skill strings read from
`.scratch-uma/cited/skills.json` (`Certain Victory`, `1st Place Kiss☆`, `Feel the Burn!`), and the local
variable is renamed `suggested`, matching the client enum. `app/Enums/SkillAcquisition.php`'s
comment reworded `planned` to `marked for this run` so the banned word leaves the shipped code
as well as the test. Verified: the test passes and the `lore-code` gate is clean on
`app/**` `tests/**` `lang/**`.

**Original defect, kept as written.**

**Evidence.**

```text
tests/Feature/TrainingRunTest.php:54   $planned = Skill::factory()->create(['name' => 'Planned Skill']);
tests/Feature/TrainingRunTest.php:72   ->assertSee('Planned Skill')
```text

Two problems in one line. `planned` is on the banned terminology list, because the client
enum is `Suggested`. And the string is invented, so a test asserts that a fabricated catalog
name reaches the screen, which is the failure mode `CONSTRAINTS.md` D-76 exists to stop.

**Fix.** Use a real Global skill string from `docs/UMAMUSUME_REFERENCE.md`, and rename the
local variable, which is what the `lore-code` grep is actually matching on.

**Not fixed here.** It is another phase's test file, and changing test fixtures needs the
owner of that suite to agree the replacement string.

---

### KI-6 The shipped font fails the design system's own mandate — **CLOSED as decision (2026-09-27)**

**The conflict.** `docs/design-research/DESIGN.md` §4.1 makes a rounded humanist sans a hard
rule and bans neutral grotesques as the primary voice; gate **G-21** checks that body, heading and
numeral text resolve to "the declared rounded face, never to a banned grotesque". The shipped stack is
`resources/css/app.css` `--font-sans: ui-sans-serif, system-ui, sans-serif`. Measured in a browser on
2026-09-27, that resolves to a system grotesque on Windows and macOS. **G-21 fails on our own code,
today, and that is the approved state.**

**Why it is allowed.** Root `CONSTRAINTS.md` C-8 bars adding a dependency without approval, and the
instruction on the implementation phase was "no new dependencies". A webfont is a dependency: bundling
Nunito or M PLUS Rounded 1s means either a CDN link, which recreates KI-3's offline break against
NFR-1, or committing font binaries plus a build step. Neither was authorised, so the owner parked the
proposal rather than silently shipping a face — and `app.css` carries that parking note so the choice
reads as a decision and not an oversight.

**Ruling B5 (2026-09-27):** C-8 exemption granted. The system stack (`ui-sans-serif, system-ui, sans-serif`) is the shipped identity. G-21 technically fails on the system grotesque but is **exempted** per this ruling. The rounded face (Nunito / M PLUS Rounded 1s) is parked pending a C-8 dependency approval that has not been granted. The exemption is recorded in `docs/design-research/CONSTRAINTS.md` G-21 so the gate's failure is a known decision, not noise. **Do not "fix" this by fetching a font, and do not fix it by deleting §4.1 either** — the mandate is the design intent, and it is the *approval* that is outstanding.

---

### Related, tracked elsewhere rather than here

- **Stat grade banding is unsourced.** Implemented as a provisional 150-point banding and
  labelled in the UI. See `docs/design-research/DESIGN.md` §11.3 and the comment block in
  `resources/views/components/stat-band.blade.php` for the exact evidence needed to close it.
- **Light band tints measure 1.10 to 1.21 against the surface they render on**, where
  `docs/design-research/DESIGN.md` §3.6 documents 1.04 to 1.14 against the panel. The rule
  never named its second colour. See research §11.9 and D-258.
- **`APP_NAME` in `.env` is still `Laravel`**, while the product is Trainer Desk.
  Recorded in `DESIGN.md` §1.
- **`PRD.md` still lists US-10 at P2**, although `ADR-0003` promoted it to P1.
- **Run notes are create-only.** Severity: Low. Category: Product Question. The
  training-run update form carries no `notes` field, so notes can be written at creation and
  never corrected or added afterwards through the UI. The behaviour is untested as well as
  unexposed: the update assertions in `tests/Feature/TrainingRunTest.php` cover the other
  fields but not `notes`, and the update Form Request does not accept it. Update semantics are
  technically correct as written, so this is not a defect — it is either an intentional
  initial-context-only design or an oversight, and those two readings imply different work.
  Not fixed in this phase; it needs a product decision. Owner: Product / Architect.
- **`ApiV1Test.php:54` does not test what its title claims.** Severity: Low. Category:
  Test-Integrity Defect. The test is titled "returns a validation error shape", but its body
  probes the unknown route `/api/v1/nope` and asserts a 404 envelope, so the title advertises a
  test that does not exist. The cause is structural rather than a slip: `routes/api.php`
  declares only GET routes, so the `ValidationException` branch at `bootstrap/app.php:32` is
  unreachable until a write endpoint exists. Not fixed now; it resolves when the first write
  endpoint lands, at which point this case is either retitled or folded into
  `ApiV1ValidationEnvelopeTest`. Owner: QA / Backend.

---

### KI-7 Blocker: em dashes in rendered Blade copy — RESOLVED 2026-09-28

Status: Closed (`ab915f8`)  
Severity: Blocker  
Owner: Frontend  
Do-not-land: No

#### Evidence (2026-09-28 disk state)

- `resources/views/components/grade-point-meter.blade.php:94` (rendered; :43 is a PHPDoc comment, not shipped copy)
- `resources/views/components/guided-step.blade.php:166`, `:202`, `:209` (rendered; :116 is inside a `{{-- --}}` Blade comment, not shipped copy)

#### Rule violated

- Settled owner ruling (2026-09-28): the disclosure glyph is `N/A` with an optional `title` tooltip, never an em dash.
- R-02 bans em dashes in shipped copy; the C-4/R-02 no-carve-out ruling applies.
- Note: the dirty `ARCHITECTURE-ESSENTIALS.md` D-220 line currently documents rendering "as `—` / not yet recorded", which contradicts the settled ruling; it belongs to the in-flight frontend slice and must be reconciled to `N/A` wording when that slice commits.

#### Required fix

Replace rendered em dashes with compliant punctuation (comma, colon, parentheses) or `N/A` + `title="..."` where the dash acts as a disclosure marker.

**Automated catch: added 2026-09-28.** `tools/gate.py` checks em dashes (D-79) only in
prototype HTML, so `RenderedCopyHygieneTest` was written to cover shipped Blade: it walks
every `.blade.php` under `resources/views`, strips Blade and both PHP comment forms, and
fails on any en or em dash that can reach a Trainer. A companion test floors the sweep at
12 views, because a first draft filtered on `getExtension() === 'blade'`, which matches
nothing (`foo.blade.php` reports the extension `php`), and the guard would have passed
having scanned zero files.

#### Notes

- Both files are uncommitted frontend work; the blocker exists in the working tree, not in HEAD for `guided-step` (tracked, dirty) and nowhere tracked for `grade-point-meter` (untracked).
- **Currency (2026-09-28, Slice 2).** `grade-point-meter.blade.php` and its test are now tracked — they were committed in `70f9218`, which also re-lit that component's progress fill. `guided-step` remains uncommitted, so the em-dash blocker described above still stands for it.

---

### KI-8 `/design-preview` 500s on a grade label the badge map has no entry for — RESOLVED 2026-09-28

**Symptom.** `GET /design-preview` returns **500**: `Undefined array key "B+"` at
`resources/views/components/stat-band.blade.php:99`.

**Cause.** `config('scenarios.grade_banding')` now emits half-step labels
(`B+`, `A-` and friends — seventeen of them at `step => 50`), while the component's
`$gradeClass` map has keys for the nine base grades only (G, F, E, D, C, B, A, S, SS).
Any stat that lands on a half-step dereferences a missing key.

**Evidence.** Live server, 2026-09-28: `design-preview -> 500` while
`umamusume -> 200` and `training-runs -> 200` on the same boot.

**Impact at the time.** `x-stat-band` was rendered by this route and nothing else, so the stat
band had no reachable surface and **no test file at all** — its rendered contrast pairs could
not be measured in Slice 2's manual pass. That pass still fixed the band's progress fill
(`70f9218`, same 1.62:1 defect as the meter's bar) and guards it by reading both
component sources, but makes no claim about unmeasured pairs.

**Both halves of that impact are gone.** Slice 3 gave the band a test file and measured the
pairs (`726f106`, `78697e9`), and Slice 5 mounted it on the run screen, which is the surface a
Trainer reaches (`d50a0ec`): the band now renders on all four scenario-bearing R17 runs, and
the claim "rendered by this route and nothing else" no longer describes the tree. The sentence
above is kept because it is what Slice 2 measured, not because it is still true.

**Decision needed, not made here.** Either collapse half-steps onto the base grade's fill
(needs its ratio recorded per D-10 before it ships) or give the seventeen labels a map of
seventeen tokens. Nine fills versus seventeen labels is a design-system decision, and it
is outside Slice 2's scope.

**Owner.** The phase that owns `grade_banding` in `config/scenarios.php`.

**Closed 2026-09-28 (`726f106`, R19).** The owner made the design-system call the entry
asked for: the fill keys on the base letter with the modifier stripped, the badge prints the
full label. A `B+` is a B's colour and the `+` is carried by the text, so nine fills serve
seventeen labels without inventing eight tokens nobody sourced.

The route returns **200** and every R17 fixture run returns 200 with zero console errors.
The band now has a test file: `StatBandTest` walks all seventeen banding labels and asserts
each one appears as a rendered badge, matched on the badge span rather than as a substring
(`B` is inside `B+` and inside class names, so a plain `contains()` would pass a band that
printed the wrong letter or none). `78697e9` measured the half Slice 2 could not: eighteen
badge-fill pairs, minimum **9.00** against 4.5:1, and the bar fill at 4.20 light / 11.74
dark against 3:1 — the claim `FRONTEND-SPEC-DIVERGENCE.md` §5 had carried unmeasured.

---

### KI-9 The selection gold measures 1.59:1 against `raised` in the light theme — RESOLVED 2026-09-28

**Symptom.** `--color-pick` (`#EFC96A` light) used as a 2px boundary on a `raised`
surface reads **1.59:1** — under WCAG 1.4.11's 3:1 for non-text boundaries. Measured in
the browser on the Grade Point ladder's `aria-current="step"` row and on the race
calendar's `current` cell. Dark is fine (`#F5B73C` on `#24262A` = 8.46:1).

**Why it is not fixed in passing.** `--color-pick` does three jobs at once: this
boundary, `*::selection`'s background, and part of the focus/selection pair recorded in
`DESIGN.md` §8. Re-stepping it means re-measuring all three surfaces in both themes,
which is its own pass. Nothing is colour-only today — the current step also carries
`aria-current` and heavier text, so D-12 holds and no user is left unable to read state.

**Owner.** Design system, with the token-pair table in `DESIGN.md`.

**Closed 2026-09-28 (`725a5ff`, R15).** The token was not re-stepped; the two jobs were
split. `--color-pick` keeps the fill, where it is right (8.34 light, 10.57 dark under
`--color-on-pick`), and `--color-pick-line` (`#7A5C10` light, an alias to `--color-pick` in
dark, where that value already cleared) carries the 2px boundary. Measured on the rendered
element's own `border-top-color` against the first opaque background beneath it, both
themes: **6.24** light / **8.46** dark on `raised`, **5.89** on the ladder's light `panel`,
against WCAG 1.4.11's 3:1. All four boundary consumers moved together — the calendar's
`current` cell, the meter ladder's `aria-current` step, the guided step's selected card and
its step links — and `::selection` plus the focus ring were re-measured to prove the split
did not disturb them.

Guard: `DesignTokensTest` fails any view that reaches back for `border-pick`, with
`(?![-\w])` so it does not flag `border-pick-line` itself. The realistic regression is one
component drifting back to the prettier token, not all four going wrong at once.

Found while writing the docs, recorded not fixed: `DESIGN.md`'s `JapanOnly` row specified
`--color-pick` **as text**, which is 1.59:1 — a text-contrast failure rather than a
boundary one. Nothing implements it; the catalog renders that status as `--color-ink-muted`
label text. The row now says so, so the next reader does not build the bug the spec
described.

---

### KI-10 Trackblazer Grade Points cannot be totalled or bucketed from what is stored — SCHEMA HALVES CLOSED 2026-09-28 (Slice 7) AND 2026-09-29 (Slice 15), RATIO HALF OPEN

**Half (c), the figure itself, is closed — and closing it did not touch half (a).** Slice 15 added
`race_entries.grade_points_earned`, a nullable unsigned integer written by `RaceEntry`'s existing saving
guard (`3711894`), so a finish now carries the number it paid instead of having it recomputed from a slot
on every read. That is the schema half the Slice 15 brief counted as this issue's remaining one. It is
**not** the ratio: the column is priced for a 1st place and null below first, for the same missing-source
reason as before. Two consequences worth stating, because both are easy to read backwards:

- The meter sums the column through one private `pointsOf()`, which falls back to pricing a row written
  before the column existed. A local database holding runs from Slice 7 therefore keeps the points it
  already showed rather than losing them to a null column — a migration that silently de-prices existing
  wins would be a regression dressed as a schema change.
- `grade_point_by_grade` and `shop_coins_by_placement` sit three lines apart in `config/scenarios.php` and
  the second has the 100/60/30/0 shape a placement ratio would need. It is not one: the source document
  says Shop Coins "do not depend on race grade at all". `GradePointsEarnedTest` asserts 2nd-in-a-G1 stores
  null beside 4th-in-a-G1 so the borrow cannot be made quietly later.

**Half (b), the bucket, is closed.** Slice 7 added the two columns the attribution needed,
both entered by the Trainer and neither derived (`e103122`):

- `race_entries.objective_index`, nullable unsigned tinyint, 1..4: the period a finish
  counts toward.
- `training_runs.current_objective_index`, nullable unsigned tinyint, 1..4: the period the
  Trainer reports as live.

The range and the scenario-conditional rule are enforced in `TrainingRun::assertGradePeriod()`,
called from both models' saving guards, and validated again at the HTTP boundary by
`StoreTrainingRunRequest`. A column CHECK could bound the number but cannot see the run's
scenario, so the guard is the load-bearing half (the precedent is `ScenarioSlot`'s own
`saving` checks).

**What the meter does with it now.** `gradeEarnedFor(i)` sums priced finishes inside period i
alone; `gradeEarned()` is the reported period only and returns null while no period is
reported, so the panel renders "no period reported" rather than a zero or a guessed target
(D-220). Other periods render as collapsed ladder rows carrying their own sums, and no
cumulative total appears anywhere, because D-232 says surplus dies at the deadline. Finishes
entered with no period are counted in the disclosure rather than silently dropped from every
total. Proven by `tests/Feature/GradePointPeriodTest.php`: cross-period independence, the
null period, an unpriceable finish inside one period only, a G1 win landing in the period it
was entered against, and a rejection for period 5. **Those nine tests pass unchanged after
Slice 15, which is the evidence that reading from the column preserved the semantics rather
than redefining them.**

**Half (a), the placement ratio, stays open.** No source names the scaling below first, so the
withholding rule is unchanged and is now scoped per period: an unpriceable finish inside the
current period withholds that period's total and poisons none of the others. Closing it needs
either a sourced ratio or an owner ruling that ships an `[Unverified]` placeholder, and neither
has arrived. What Slice 15 changes is where a placeholder would go: it would now be a value on
the row, per finish, rather than a rule applied at read time to every period at once — which is
a strictly better shape for a decision of that kind and is the one lasting gain of half (c).

**Owner.** Design system with the Planner Domain Specialist, alongside KI-15 for the track
question.

**Original defect text kept below for traceability.**

**Symptom.** `TrainingRun::gradeEarned()` returns `null` for any run holding a finish
below first, and the meter shows "not yet recorded" even though races happened.

**Cause.** Two independent gaps, both measured rather than assumed:

1. `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` (Trackblazer, GameTora) prices Grade Points for **1st place only**
   (`grade_point_by_grade`: G1 100 … Pre-OP 20) and states that lower placements "scale
   down proportionally (similar to how Fan gain scales)" without naming a ratio. No
   placement scaling table for Grade Points exists in the corpus, and no fan-placement
   table exists either, so there is nothing to derive it from.
2. D-232 makes the four objectives separate deadlines judged alone, but `race_entries`
   carries no turn, year, or objective bucket, so a race cannot be attributed to the
   period it counts toward.

**Current behaviour is deliberate.** Withholding beats understating: a partial sum shown
as a total would be a false claim about a trainee (D-256).

**Required fix.** (a) a sourced placement ratio, or a `[Unverified]` placeholder decision
from the owner; (b) a turn or year bucket on `race_entries`, which is schema work and
therefore an ADR, not a patch.

**Owner.** Planner Domain Specialist with Data Engineer for the source.

---

### KI-11 Design-system debts left visible by the Slice 2 gate run — CLOSED 2026-09-28, RE-CLOSED ON CURRENT EVIDENCE 2026-09-29 (Slice 12)

- **CLOSED (`5c65597`), superseded by `f0ae288`. `database/seeders/ScenarioSlotSeeder.php` was an
  empty stub** — `run()` contained only `//`, and `DatabaseSeeder` never called it. Against
  `CONSTRAINTS.md`'s floor ("no unimplemented stubs") it was filled with sourced slot rows or
  removed; the slot rows were fetch-engine work per ADR-0003 Amendment R3, so there was
  nothing honest to put in it and the file was deleted. `scenario_slots` stayed empty after a
  clean seed by design, and a re-measured fresh scratch database seeded 24 tables, 10 skills and
  **0 slots**.

  **That closure basis no longer describes the tree.** Slice 11 restored the seeder with sourced
  URA Finale rows read from the committed client export (R55, `f0ae288`), so the debt is now paid
  by content rather than by deletion, and the "0 slots" measurement is withdrawn as stale. Three
  files still cite that stale basis and should be corrected by their owners:
  `app/Http/Controllers/TrainingRunController.php:78` ("Empty by design until the fetch engine
  lands (KI-11)"), and the free-form-race notes at `slice-8-2026-09-28.md:127` and
  `slice-9-2026-09-28.md:78`. The URA half is seeded; Trackblazer and Unity Cup still have zero
  rows, so those two sentences are half-true and were written before R55.
- **CLOSED (`71bbb4b` + `d50a0ec`, measured in Slice 5; second consumer measured in Slice 8).
  `--color-green-tint` has committed consumers.** The goal cell moved to `bg-raised` in `bb6eec6`
  and the token was referenced by no utility after that; retirement was refused under R14 on G-60
  grounds, and R23 settled the alternative: the Safe band word lands in Slice 5, or the token is
  retired and the spec amended in the same slice. It landed: the energy band word renders `ink` on
  `green-tint` on the run screen, and the pair measures **6.40:1 light / 12.60:1 dark** off the
  rendered element (`docs/design-research/verification/slice-5-2026-09-28.md` §3).

  **The Slice 8 epithet rows are the second consumer, and they hold.**
  `slice-8-2026-09-28.md` §4 measured the same token pair on two more surfaces from the new panels:
  `ink` on `green-tint` at **6.40** on the earned epithet row (line 79), and `ink-muted` on
  `green-tint` at **5.33 light / 6.58 dark** on the route-and-reward meta line (line 80). One
  consumer could be an accident of a single component; three, on two different panels, is the token
  doing a job. R23's promise is paid twice over, and the "unverified in practice" debt this bullet
  recorded is closed on measurement rather than on the existence of a class name.

**Owner.** Design system. First half closed 2026-09-28 by deletion and re-closed 2026-09-29 on
sourced content; second half closed 2026-09-28 and re-measured against the Slice 8 epithet rows.

---

### KI-12 The Grade Point meter says nothing was entered when races were entered but cannot be priced — RESOLVED 2026-09-28

**Symptom.** A Trackblazer run holding two completed 1st-place races — one linked to a
`G1` slot worth 100 points, one free-form with no slot — renders:

> **not yet recorded** — no Grade Points are entered for this run, so there is no progress
> to show yet.

**Evidence.** Browser pass, 2026-09-28, `/training-runs/2` on an isolated database with
both entries present; `gradeEarned()` returns `null` while
`raceEntries` holds `[{slot: 6, tier: "G1", placement: 1}, {slot: null, tier: null,
placement: 1}]`.

**Cause.** `gradeEarned()` withholds the whole total when any completed entry cannot be
priced, which is the deliberate KI-10 rule: a partial sum shown as a total would
understate. The *sentence* is the defect, not the arithmetic — it attributes the blank
panel to the Trainer having entered nothing.

**Not fixed here.** Withholding is correct and the copy needs to name the real reason, but
the wording differs by cause ("nothing entered" versus "a recorded result whose point value
is not published"), so the honest fix is for `gradeEarned()` to expose which case it is —
a new prop on a component whose other consumers are pinned by three existing assertions.
That is a copy decision with a schema consequence, and it belongs with KI-10's resolution.

**Interim truthfulness of what is shown:** the panel claims nothing about points, so no
false number is drawn. The misleading part is the implication that the Trainer's races were
not recorded.

**Owner.** Design system with the Planner Domain Specialist, alongside KI-10.

**Closed 2026-09-28 (`2816309`, R18).** The meter now has three states instead of two.
`gradeEarned()` exposes the reason through `gradeUnpricedCount()`, and the component reads
it: **"not yet totalled"** plus the count of logged results with no published Grade Point
value, and "any total here would count less than this run earned" naming why the figure is
withheld; **"not yet recorded"** only when no races are logged. The arithmetic did not move —
withholding stayed correct per KI-10 — the sentence changed.

The middle state is the one that matters and the one a two-state design loses: a Trainer who
entered two first-place finishes is not the same case as a Trainer who entered nothing, and
the old copy asserted the second over the first. KI-10 itself remains open: the placement
ratio and the year bucket are unfixed, and this closure does not claim otherwise.

---

### KI-13 Blocker: the models and migrations this branch's own code resolves against exist on no ref — **RESOLVED 2026-09-28 (Slice 4)**

**Severity:** Blocker — `master` and `docs/audit-remediation` were both affected  
**Owner:** Planner Domain Specialist with the concurrent frontend session  
**Do-not-land:** Was Yes — now lifted

**Resolution.** All three measures executed in Slice 4 (R20–R25):

1. **Five load-bearing files committed** (`35fb0c7` on `docs/audit-remediation`):
   `app/Models/ScenarioSlot.php`, `app/Models/Preference.php`,
   `database/factories/ScenarioSlotFactory.php`, `database/factories/PreferenceFactory.php`,
   `database/migrations/2026_09_27_121500_create_preferences_table.php`,
   `database/migrations/2026_09_27_153416_create_scenario_slots_table.php`.
   Authored by concurrent session, reason KI-13.

2. **Duplicate `is_manual` migration deleted + feat branch merged** (`dc13d8d`):
   Untracked `2026_09_27_132304_add_is_manual_to_scenario_races_table.php` deleted.
   `feat/scenario-races-is-manual` inspected — single commit `46b8d3e` (is_manual only) —
   merged at `dc13d8d` (no squash, no rewrite). The kept migration is
   `2026_09_27_183245_add_is_manual_to_scenario_races_table.php`.

3. **Coherence re-measured (all three outputs):**
   - `git ls-tree -r HEAD database/migrations | wc -l` = **20** — equals on-disk count (20).
   - `git grep -l "class ScenarioSlot" HEAD` → `app/Models/ScenarioSlot.php`, `database/factories/ScenarioSlotFactory.php`.
   - `git grep -l "class Preference" HEAD` → `app/Models/Preference.php`, `database/factories/PreferenceFactory.php`.
   - Fresh scratch-DB `migrate:fresh --seed` → **23 tables**:
```text
 `cache`, `cache_locks`, `data_sources`, `failed_jobs`, `job_batches`, `jobs`,
 `match_candidates`, `migrations`, `password_reset_tokens`, `preferences`,
 `race_entries`, `run_skills`, `scenario_races`, `scenario_slots`, `scenarios`,
 `sessions`, `skills`, `sqlite_sequence`, `training_runs`, `turn_entries`,
 `turn_events`, `umamusume`, `umamusume_aliases`, `users`.
```

1. **Fast-forward + push** (`33949f5`):
   `master` fast-forwarded to reconciled tip. Pushed once, no force:
   - `origin/docs/audit-remediation` → `9b774f948fe8a859bfec67400f4f5a0388cfee73`
   - `origin/master` → `33949f5cb74089b8a836abdb944282e2dd26063d`

2. **Docs updated** (this commit): KI-13 RESOLVED with shas + T3 outputs; PLAN topology
   paragraph rewritten (master equals tip, feat merged, push state); PLAN slice exit criteria
   gain R25's line (every cited sha verified via `git cat-file -e` in-session; a record's own
   sha labelled self-citation) and the checkout-coherence amendment from R20 (boot files, not
   test coverage); KI-11 gains R23's consumer commitment (Safe band word in Slice 5,
   retire-and-amend if it does not land); KNOWN-ISSUES header count refreshed.

3. **Gates** (CONSTRAINTS order): pest, pint --dirty, phpstan, composer lore, composer
   lore-code (parity), gate.py, npm run build (declared-vs-pruned token count) — all PASS.

**Verification.** Clean checkout of `master` at `33949f5` boots; `php artisan test --compact`
passes; all coherence checks hold.

**Original defect text kept below for traceability.**

---

### KI-14 The guided rail declared radio semantics its elements did not have — RESOLVED 2026-09-28

**Filed here rather than found in the wild.** This is the accessibility item R10 deferred out
of Slice 2, and it had no entry to close, so it gets one now instead of a claim in a commit
message. The audit line it resolves is
`docs/design-research/FRONTEND-BRIEF-AUDIT.md` row "9 accessibility".

**Symptom.** `x-guided-step` rendered a `role="radiogroup"` whose children were
`<button type="button" role="radio" aria-checked>`. Two separate failures in one element: the
group claimed a widget semantics its children did not implement (no roving focus, no
arrow-key selection, no `aria-checked` state change without a script that was never written),
and a `type="button"` carries no value on submit, so the rail could not post a choice at all.
It was a picture of a radio group.

**Resolution (`d50a0ec`, `6a53c15`).** The choices are now real `<input type="radio">`
elements inside the `radiogroup`, each wrapped in the client's banner shape, so §6.10 and
§8.6's "banner buttons, never radio inputs" hold for what a Trainer sees while the element
carries the state. Consequences, all measured with pressed keys:

- Arrow keys rove and select natively; `ArrowRight` from `training-Speed` lands on
  `training-Wit` with `checked: true`. No roving-tabindex script was written, because the
  platform already does it and a reimplementation would fight it.
- The selection posts: `choice=training-Guts` reaches the request, which is what makes
  D-51's two stages possible without a script.
- Focus is visible where it previously could not be: the banner shows
  `outline: 2px solid rgb(78, 121, 6)` (`--color-ring`) with `matches(':focus-visible')` true,
  established with a real `Tab`, not `element.focus()`.
- `role="radio"` and `aria-checked` are gone from the markup, so nothing claims what the
  element does not do. `RunViewFrameTest` and `GuidedTurnOnRunViewTest` pin the group's shape.

**Residual, closed as correct-by-design 2026-09-28 (R31).** `aria-live` is absent from the rail,
and that is the right answer rather than an unfinished one. The preview panel appears through a
navigation: selecting an option submits, the server renders, the browser loads a new document, and
focus and the announcement come from the platform's own handling of that load. A live region is for
content that changes under a reader who has not moved; there is no such change here, so an
`aria-live="polite"` wrapper would announce a panel the user is already being taken to, and would
be a claim about the interaction that, like the `role="radio"` claims just removed above, the
element does not support. The rule this does not weaken is `docs/design-research/DESIGN.md:1406`
(§10 Accessibility): a gain bubble that updates in place under a Trainer who stays put still owes
`aria-live="polite"`, and there is no in-place gain bubble on this screen to give one.

---

### KI-15 The three Grade Point tracks have no sourced rule for choosing one — FILED 2026-09-28 (Slice 7), OPEN

**Symptom.** `TrainingRun::gradeObjectives()` renders the `standard` track (60 / 300 / 300)
for every Trackblazer run. For a dirt-leaning trainee the client asks 30 / 200 / 300, and for
a turf trainee whose range outside short distances is weak it asks 60 / 200 / 300, so the
meter can show a target that character cannot be held to.

**Cause, and it is a source conflict rather than a missing number.** The two Trackblazer
guides disagree about the same character class:

- `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Grade Points (Replacing Career Goals)" puts "Sprint Umas with poor aptitude in
  other distances" on the **Dirt** requirement track.
- `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Basic Information" gives a turf character with poor aptitude
  outside short distances a **third** track, in which only the Classic objective drops to 200.

Neither names the aptitude letter or letters that place a trainee in a track. `umamusumes`
does carry the ten aptitude letters (`ADR-0004`, `PRD.md` OQ-4 closed 2026-09-27), so the data
to build a rule exists; the rule does not, and any threshold this tool picked would be its own
invention dressed as a game fact (D-20, D-256).

**Current behaviour is deliberate.** `standard` is rendered and the code says so at
`app/Models/TrainingRun.php:378-385`. A wrong denominator is worse than a conservative one,
because a Trainer cannot tell they were handed the wrong track at all.

**Required fix.** Either a capture or a dated secondary source naming the aptitude condition
per track, or an owner ruling that ships a Trainer-entered track selector — a third column on
`training_runs`, which is the D-270 pattern Slice 7 used for the period itself. That is why the
choice is recorded here rather than made silently: adding it is a schema decision, and Slice 7
was given two columns, not three.

**Owner.** Planner Domain Specialist with the owner; the schema decision is the owner's alone.

---

### KI-17 The consecutive-race count cannot be derived from the log, so it is entered — FILED 2026-09-28 (Slice 8), CLOSED 2026-09-29 (Slice 15) ON THE LINK, NOT ON THE COUNT

**Closed on the fix this issue named, and the closure is narrower than its title.** The brief for Slice 15
directed "KI-17 closed", and the link the Required fix below asked for now exists. What does **not** exist is
a derived count: `consecutiveRaceCount()` still returns null and the figure is still entered, because the
link's absence is not evidence of a turn without a race. A reader who takes this title to mean Race Fatigue
now has a number behind it will be wrong, which is why the limit is in the heading rather than only below.

**Symptom.** D-230 states that Trackblazer's Race Fatigue is safe to surface because "consecutive
race count is already recoverable from `turn_entries`, and that premise is false as the schema
stands. `race_entries` points at a `scenario_slots` row (month, half, tier) and never at a turn,
and the guided flow offers no race choice, so no logged turn can be identified as a race turn.
There is also no `race_entries.turn` to read and none was sanctioned.

**Current behaviour is deliberate.** `TrainingRun::consecutiveRaceCount()` returns null and says
why at `app/Models/TrainingRun.php:811-826`, and the chip renders "no consecutive-race reading
recorded". The count is instead entered as `RaceFatiguePayload {consecutive_races}` on the turn it
applies to, which keeps the fact without inventing a link, and the panel prints one qualitative
word for the band (unlikely / possible / likely / certain) with the pointer to
`docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Gameplay Flow & Race Fatigue", never the percentages: one source is
not two (D-230).

*(The range this entry originally cited was `:639-650`. It has moved twice since — once before
Slice 15 and once inside it — and the number a `file:line` carried when a defect was filed is not a
fact worth preserving, while a wrong pointer to read is. So it is updated, and the original is named
here rather than quietly replaced.)*

**Required fix.** Either a link from a race entry to the turn it happened on, or a guided choice
that marks a race turn. Both are schema or flow decisions, so neither is taken here.

**Closed by `d06199c` (Slice 15 T1 of the Schema Session), on the first of the two options.**

- **Delivered.** `race_entries.turn_entry_id`, a nullable foreign key to `turn_entries` with
  `nullOnDelete`, entered by the Trainer from a dropdown of that run's own turns in the race panel. A turn
  belonging to another run is rejected at the Form Request and no row is written. `RaceEntry::tierKey()` and
  the KI-17 tests are the proof; `slice-15-2026-09-29.md` §3 records the RED run.
- **Still true of the symptom.** The count is not derived. A null link states "the Trainer has not named the
  turn", which is not the proposition "this turn held no race", and a run of consecutive races inferred from
  that gap would be a guess printed as a reading (D-270). So `consecutiveRaceCount()` remains `return null`,
  and `RaceFatiguePayload {consecutive_races}` remains the way the fact is kept. Both docblocks said the
  link column had not been given, which stopped being true at this commit, and now say what is actually
  missing instead.
- **What would close the remaining half.** Not schema. It needs a flow ruling on whether an unnamed turn may
  be read as a non-race turn at all — which is D-230's premise revisited, and belongs to the Planner Domain
  Specialist with Architect, not to a slice that was given the link.

**Owner.** Planner Domain Specialist with Architect.

---

### KI-18 The Spirit Burst roster prints tool identifiers where a Trainer reads a state — RESOLVED by `f7a59e8` (Slice 9), MARKED CLOSED 2026-09-29 (Slice 10, R52)

**Symptom.** `resources/views/components/spirit-burst-roster.blade.php` renders
`$row['state']->value`, so the chip reads `NormalBurstSpent` and `ExtremeChargeable`. Those are
this tool's backing values, not Global client strings: `app/Enums/SpiritBurstState.php:8-20` says
the case values are deliberately not client copy and that a display slice owes a label map
alongside them. This is that display slice, so the debt came due here.

**Why it is not patched in this slice.** `SpiritBurstPayloadTest` and `ScenarioPanelUiTest` assert
the six `value` strings appear in the rendered page, so a label map means changing the enum, the
component and both tests together. The `/impeccable audit` raised it as its only P1 (score 16/20,
`slice-8-2026-09-28.md` §5), and the audit ran under "score only, do not change code", so the
finding is recorded here rather than absorbed silently into the same commit that reported it.

**Required fix.** `SpiritBurstState::label()` returning words a Trainer reads ("charged", "held",
"burst spent", "extreme chargeable", "extreme spent"), the roster printing the label, and the two
tests asserting the label instead of the value. No new client string is invented: the words
describe a machine this tool models, and the source names none of them.

**Resolution (`f7a59e8`, Slice 9).** All three parts shipped in one commit: `label()` on the enum
mapping the six cases to `Chargeable`, `Charged`, `Charged, held`, `Burst spent`, `Extreme
chargeable`, `Extreme spent`; `spirit-burst-roster.blade.php` printing `$row['state']->label()`;
and `ScenarioPanelUiTest` asserting the labels while failing if a backing value reaches the
response (`tests/Feature/ScenarioPanelUiTest.php:105-114`). The measured proof is
`docs/design-research/verification/slice-9-2026-09-28.md` §5, whose browser row for the roster
reads "all five labels present, zero backing values leaked". `value` stays the storage identifier.

**Why it was still marked open here.** Slice 9 fixed it and closed it in its own record but never
recomputed the register's status line, so the entry contradicted the commit that resolved it. R52
closes the bookkeeping, not the defect.

**Owner.** Frontend with the Lore Guardian, next pass on the Unity Cup panels.

---

### KI-19 The impeccable tool cannot update itself, so a maintenance slice cannot measure a version delta — FILED 2026-09-29 (Slice 10), RESOLVED 2026-09-29 (Slice 12, R66)

**Symptom.** `C:/Users/exatf/.agents/skills/impeccable/scripts/impeccable.cmd check` and the same
launcher's `update` both return `Could not verify skill bundle: HTTP 404. Nothing was installed`.
`--version` reads `4.0.0` before and after, so Slice 10's re-audit ran on the incumbent build rather
than an updated one. Upstream points the report at `pbakaus/impeccable` issue #479.

**Attempt log (both dated 2026-09-29, per R66).**

| Attempt   | Slice      | Command                   | Result                                                                                                                                                                                                   |
| --------- | ---------- | ------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1         | Slice 10   | `impeccable.cmd update`   | `Could not verify skill bundle: HTTP 404. Nothing was installed`. `--version` unchanged at 4.0.0                                                                                                         |
| 2         | Slice 12   | `impeccable.cmd update`   | Succeeded. Engine v0.1.5 (windows-x64) installed into `.kiro/` and `.opencode/` script bins; hooks installed into `.claude`, `.cursor`, `.agents`, `.github`, `.grok`. `--version` still reports 4.0.0   |

**The version half of the finding stands.** `--version` reports the *skill bundle* version (4.0.0),
not the engine version (v0.1.5), so a same-`--version` reading is not evidence that nothing changed.
The engine binary is what runs `detect`, and it did move. What Slice 10 could not do — compare
audits across a version delta — is now possible; the second attempt is what closes it.

**Second half of the finding, unchanged.** The interface the brief names, `npx impeccable update`, is
still not this project's: `impeccable` is in no `package.json` and has no `node_modules/.bin` entry,
so an `npx` run would fetch an unrelated registry package under that name. The launcher next to the
installed skill is the real interface, and it is what was run both times.

**Audit at the updated engine (R66).** `impeccable.cmd detect` over the two Slice 11 surfaces —
`resources/views/components/race-panel.blade.php` and `resources/views/components/race-calendar.blade.php`
— returns **1 finding**: `race-calendar.blade.php:148 [side-tab] border-l-5`. Not acted on: the left
edge marks the mandatory goal race, so it carries information rather than decorating, which is the
exception the rule itself names. Evidence in `slice-12-2026-09-29.md` §4.

**Required fix.** Nothing in this repository. The upstream bundle URL resolves again as of the second
attempt; if it 404s in a later slice, record both attempt dates rather than one, because a single
date cannot distinguish a transient failure from a moved URL.

**Owner.** Whoever runs the tool update, outside this repo. Closed by the successful second attempt.

---

### KI-20 The shop error text has never been measured as a rendered pair — RESOLVED 2026-09-29 (Slice 11)

**Symptom.** `shop-panel.blade.php` renders validation messages in `text-risk` on the form's
`bg-raised` ground, and Slice 10 T3 moved those messages from one combined block to one per field,
so the pair is now on four inputs instead of one. No record measures `risk` as text on `raised`:
`slice-8-2026-09-28.md` §4 and `slice-9-2026-09-28.md` §5 both measured the opposite direction
(`on-chrome` on `risk` 10.89 light / 6.88 dark, and `on-pick` on `risk`), which says nothing about
the red glyph on a raised card.

**Measurement (Slice 11).** Computed from CSS token values in `resources/css/app.css`:

| Theme           | Foreground                 | Background                   | Ratio     | Threshold       | Verdict     |
| --------------- | -------------------------- | ---------------------------- | --------- | --------------- | ----------- |
| Light           | #800014 (`--color-risk`)   | #FFFFFF (`--color-raised`)   | 10.04:1   | 4.5:1 AA text   | PASS        |
| Dark (before)   | #FF6B7A (`--color-risk`)   | #24262A (`--color-raised`)   | 4.33:1    | 4.5:1 AA text   | FAIL        |
| Dark (after)    | #FF7E8C (`--color-risk`)   | #24262A (`--color-raised`)   | 4.77:1    | 4.5:1 AA text   | PASS        |

Cross-pair verification after stepping: `border-risk` on `bg-raised` (non-text boundary, 3:1) =
4.77:1 PASS; `bg-risk` with `text-on-chrome` (#121013 on #FF7E8C) = 6.15:1 PASS. Light theme
unchanged at 10.04:1.

**Fix.** Dark-theme `--color-risk` stepped from #FF6B7A to #FF7E8C in `resources/css/app.css`,
following the D-259 precedent (`--color-on-mood`, `--color-on-green`): the ink moves to clear the
threshold while the hue family stays. No component changes needed; the token fix propagates to all
four shop-panel error spans and every other `text-risk` consumer.

**Owner.** Frontend with the design-system owner. Closed by measurement and token step in Slice 11.

---

### KI-21 The race entry form is built on Alpine.js, which is not a dependency, so neither path renders — FILED 2026-09-29 (Slice 12), CLOSED 2026-09-29 (Slice 13, R67)

**Symptom.** On a live run screen, the race panel's two-path entry form rendered no fields at all:
`window.Alpine` false, two `template[x-if]` branches inert, `scenario_slot_id` / `title` / `month` /
`half` absent from the DOM, and the hidden `entry_mode` input posting the empty string. Measured in
`slice-12-2026-09-29.md` §7.2.

**Cause.** `race-panel.blade.php` was written against Alpine, which is not in `package.json` and is
not imported by `resources/js/app.ts`. A `<template>` element's children stay unrendered until a
framework clones them out, so Blade emitted a form the browser never showed.

**Fix (`6c1969f`, route 2 — no dependency).** The form is now server-driven disclosure, the shape
`guided-step` already uses: the mode switch is two GET forms submitting `entry_mode` to the run
screen, and the branch is chosen on the server before the response is sent. `old()` wins over the
query default so a failed write returns to the branch being filled, and the entered values return
with it — including placement, status, circles and period, which sit outside both branches and were
retypeable before. `package.json` is untouched. The calendar session's own comment at
`race-calendar.blade.php:89` — "there is no runtime JavaScript dependency in this project" — is the
evidence this was the house stance and not just the permitted route.

**Proof is the rendered DOM, not the HTML source (R71).** `RaceEntryDisclosureTest` resolves every
field through `DOMDocument` and refuses any whose ancestor chain contains a `template` element. All
8 tests failed against HEAD before the fix; `assertSee('name="title"')` would have passed against the
broken code, which is why the original Slice 11 browser check missed this entirely. The file also
writes a free race through the rendered form rather than posting directly, so the DOM path is proven
end to end and not just the HTTP layer.

**Zero-Alpine grep.** `grep -n "x-data\|@click\|x-if\|template x-if\|x-model"
resources/views/components/race-panel.blade.php` → no output, exit 1. Same across all of
`resources/views`. A first attempt showed one hit, in my own comment quoting `template x-if`; a grep a
comment can satisfy is not a check, so the comment moved.

**Owner.** Closed by Slice 13.

---

### KI-22 A free_race cell renders `state=past` without the Trainer-entered marker — FILED 2026-09-29 (Slice 12), CLOSED 2026-09-29 (Slice 13), FILED ON A WRONG CAUSE

**The filed cause was wrong, and the correction is the useful part.**

What was reported: concurrent commit `82959e9` had deleted R61's free-race branch, evidenced by
`grep -n "isFreeRace" app/Models/TrainingRun.php` returning nothing. What is true: `82959e9` did not
remove the branch, it reshaped it into a `bool $manual` parameter, which `isFreeRace` cannot match.
`git blame` attributes the current `if ($manual)` block to `82959e9` itself at lines 433-436. **A
missing identifier was read as a missing branch** — the grep could not find what it was not looking
for, exactly the failure KI-21 was filed against one section earlier.

The concurrent session reached the same conclusion independently in `5dcc06c`, which identifies what
was genuinely missing: nothing tested the model.

**What the observation really caught**, and the only real defect here: `calendarCell()` checked the
recorded entry before the manual flag, so a free race with a finish took the `past` return and lost
its marker. Slice 13 measured it, declined to invent the requirement, and left the call to the read
path's owner. That owner made it in `b6d68b6 fix(calendar): the Trainer-entered marker survives the
finish`: `app/Models/TrainingRun.php:437` now returns `past` **and** `manual => true` for a free race
with a recorded entry, because `free_race` is provenance about where the record came from, and
provenance does not expire when the race is run.

**Pinned by rendering tests, not by the existing one.** `FreeRaceCalendarCellTest` (`cb9b61f`,
`5ed1ebd`) creates a row, fetches the run over HTTP, and requires the marker inside a `role="img"`
cell with the open dashed treatment, an `aria-label` naming the state, and zero `border-l-goal`
pennants. `RaceCalendarTest.php:276`, which appeared to cover this, hand-writes
`['state' => 'past', 'label' => 'Local Stakes (Trainer-entered)']` into the cells array and asserts
the substring — it never calls `calendarCell()`, so it passes on label text whether or not the model
emits `manual`.

**Owner.** Closed by Slice 13 with the read path's own fix. Slice 12's §7.3 stands as filed and is
corrected forward in `slice-13-2026-09-29.md` §4, not edited in place.

### KI-23 `uma:fetch` never fills `umamusume.name_ja`, and its own fixture repeats the parser's wrong key — FILED 2026-09-29 (skills pass), OPEN

**The defect is a key name, and the reason it survived is that the test was written from the parser
rather than from the source.**

`app/Services/DataPipeline/Parsers/GametoraCharacterParser.php:92` emits
`'name_ja' => $this->textOrNull($card['name_ja'] ?? null)`. The live document has no `name_ja` key.
Measured 2026-09-29 against the manifest's current `character-cards.e9e9ee6d.json` (268 records): the
name-bearing keys are `name_en`, `name_jp`, `name_ko`, `name_tw`, `url_name` — `name_jp` is populated on
268 of 268, `name_ja` on **0**. The expression therefore always yields null, and every character promoted
by `uma:fetch` stores no Japanese name. `PRD.md` FR-A-1's "Japanese name (nullable until
cross-referenced)" is being satisfied by the nullability rather than by the fetch.

**Why the suite cannot see it.** `tests/Feature/GametoraCharacterParserTest.php:22` builds its rows
through a `card()` helper that writes `'name_ja' => $jp`, and the committed
`tests/Fixtures/gametora-character-cards.sample.json` carries the same key. Both inputs were authored to
the parser's expectation, so the assertion at `:39` — `name_ja` is `エピファネイア` — passes against a
shape the source does not produce. The sample fixture holds 7 of the live document's 33 keys; `aptitude`,
`skills_innate`, `skills_unique`, `title_en_gl` and the rest are absent from it, so it is a sketch of the
document, not a capture of it. This is the KI-22 failure mode one layer down: a test that hand-writes the
producer's input cannot falsify the producer.

**Why a seeded database looks correct.** `php artisan tinker` reports `umamusume` rows = 2, rows with
`name_ja` = 2 — both from `UmamusumeSeeder`, which writes the value directly. The seeder fills the column
the fetch leaves empty, so local inspection confirms the wrong thing.

**What fixing it needs.** Read `name_jp`. Rebuild the fixture from a slice of the live document so the key
names belong to the source, and add an assertion that a live-shaped row yields a **non-null** `name_ja` —
without that direction, a corrected key and a broken one both satisfy a fixture written to match.
`ADR-0011` records that the skills parser does not inherit the pattern (it reads `name_en` and `jpname`,
and its fixture is cut from the live document).

**Owner.** Data Engineer. Out of the skills pass's scope because it edits the characters parser and its
fixture, not the skills path; filed here because the skills import was only found by measuring the same
document family, and the next reader of `GametoraCharacterParser` should not have to rediscover it.

### KI-24 A stale source hash answers 200 with stale content, so a pinned URL fails silently — FILED 2026-09-29 (skills pass), OPEN

**The comment's safety claim is the defect.** `config/uma.php:50-54` states, of the cache-busting token in
each source URL: *"it rotates when the source republishes, so a stale hash surfaces as a fetch failure and
not as silently old data."* Measured 2026-09-29, it does the opposite:

| URL                                                                               | HTTP      | Body                                                       |
| --------------------------------------------------------------------------------- | --------- | ---------------------------------------------------------- |
| `skills.f4a1e02d.json` (the hash every `UMAMUSUME_REFERENCE.md` citation names)   | **200**   | 1,910 rows, 621 stated available on `[Global]`             |
| `skills.609afe88.json` (today's manifest value)                                   | 200       | 1,910 rows, 623 available, **68 rows differ in content**   |
| `character-cards.679f7c2e.json` (**live `gametora-characters` pin**)              | 200       | 251,242 bytes                                              |
| `character-cards.e9e9ee6d.json` (today's manifest value)                          | 200       | 251,294 bytes                                              |

Old hashes keep serving, so a pin does not fail loudly — it quietly fetches an outdated document forever.
The characters source is in that state now: `uma:fetch` pulls a roster three days behind the publisher's
current document with nothing to report. Nothing in `ARCHITECTURE.md` §5 requires the pin either; its only
hash language is snapshot-content hashing for idempotence (`:192`, `:212`), so a write-up that cites §5 for
"resolve through the manifest rather than hardcoding" is citing a sentence the file does not contain.

**Two consequences, one of them about the corpus.** `ADR-0011` §1 resolves the skills URL through
`https://gametora.com/data/manifests/umamusume.json` at fetch time for exactly this reason, and records the
resolved hash as provenance. Separately, the corpus's `[B]`-tier citations
("`skills.f4a1e02d.json`", "`character-cards.679f7c2e.json`") point at documents that still resolve and
are no longer current — which is **anchor drift, not a wrong fact**: the hash in a citation is provenance
about when the claim was measured, so the citations must not be rewritten to the new hashes. `D-254`'s
dated-snapshot policy already covers how a reader should treat them.

**What fixing it needs.** An owner decision on the engine's URL model, because it changes both existing
sources: manifest resolution at fetch time, a documented pinned fallback, and the resolved hash recorded on
`data_sources` so a later reader can tell which document a fact came from. The `config/uma.php` comment is
then corrected to state what actually happens.

**Scope as it stands after `ADR-0011`: this is half-fixed, deliberately.** `gametora-skills` resolves
through the manifest now. The two older sources still pin, and re-measured the same day: `character-cards`
is pinned at `679f7c2e` while the manifest publishes `e9e9ee6d`, so **the characters import is serving a
superseded document today**; `race_instances` is pinned at `294424fc`, which matches the manifest right now
and will therefore go stale silently at its next republish, the same way the characters pin already did. A
reader who concludes the pinning problem was solved with the new source has read it wrong.

**Owner.** Architect with the Data Engineer. Discovered while approving a third source, which is the point
at which the pinning convention was about to be copied forward.

---

### KI-25 The turn log forces the page into horizontal scroll at phone width — FILED 2026-09-29 (Slice 15 browser pass), CLOSED 2026-09-29 (Slice 16 T1), RE-OPENED 2026-09-29 (R85), OPEN

**Re-opened by R85, and the reason is the measurement half, not the markup.** `b8c0a54` landed the focusable
scroll region: the nine-column log now sits inside `overflow-x-auto` with `role="region"`, `tabindex="0"`
and `aria-label="Turn log"`, matching `race-calendar.blade.php`, and `tests/Feature/TurnLogScrollRegionTest.php`
(2 tests, 12 assertions, passing) proves both regions carry those attributes in the rendered page. That work
stands and is not being reverted. What is withdrawn is the closure, because **a PHP assertion can only read
rendered attributes, and the claim being closed was about behaviour**:

- whether arrow keys actually scroll either region — never pressed in a browser;
- whether scoping the overflow to the table removed the document-level sideways scroll at all, i.e. whether
  `scrollWidth` is still 476 at a 390px viewport after the change — never re-read;
- the other eight columns, the race calendar's own traversal, and every other region on the screen —
  originally probed as one element and never swept;
- and the closed text cited `docs/design-research/verification/slice-16-2026-09-29.md`, a file that does not
  exist. A closure whose evidence is a dangling path is not a closure.

**What would re-close it.** Two things, in this order. First the **responsive contract**: D-40's standing
sentence — "no mobile-first compromise is accepted in exchange for desktop density" — has to be reconciled
with the 768px floor `b8c0a54` wrote into `DESIGN.md` §2.3, and that amend is itself held pending R82,
because a contract the owner has not ratified cannot be the basis for closing a defect. Second, a
**read-only browser measurement** whose numbers are recorded beside the closure rather than inferred from
the attributes that make it possible.

**The `DESIGN.md` §2.3 floor travels with this issue.** It was written on the strength of this closure, so
until the measurement pass runs, "768px is the supported minimum" is a proposal resting on an attribute
read, and §2.3 says so.

**Owner.** Frontend/Design-system with Architect, unchanged: the fix needs a breakpoint decision the repo
does not currently record. Found while measuring something else — the Slice 15 browser pass was sent to
check contrast on a new turn control, and the overflow surfaced only because the same script read the
viewport width too.

**The 2026-09-29 closure text is kept below, struck as withdrawn rather than edited away, so the sequence
is auditable.**

~~**Closed as a designed fallback, which is not the same as fixed.** Slice 16 T1 (R81) scoped the horizontal~~
~~scroll to the table instead of the page and gave the scroll container a tab stop… the disposition is a~~
~~ruling about reachability.~~ Its two load-bearing sentences — that the columns past the edge "are now
reachable by arrow keys", and that the runtime fact was "measured in the Slice 16 browser pass" — are
withdrawn. Both rested on the rendered attributes the test reads, not on a browser read.

**Original defect text kept below for traceability.**

**Symptom, measured rather than inferred.** At a 390 × 844 viewport the run screen's document reports
`scrollWidth 476` against `innerWidth 390`, so the whole page scrolls sideways. Eleven elements sit past
the right edge and all eleven trace to one root: the turn log `table.mt-3 w-full border-collapse text-sm`
in `resources/views/runs/show.blade.php`, measured 460px wide across its nine columns (Turn, Speed,
Stamina, Power, Guts, Wit, SP, Condition, Mood). `w-full` cannot shrink a table whose columns demand more
than the container gives them, so the width is the content's, not the stylesheet's.

**This is not a Slice 15 defect, and the way to say that is the diff, not the assertion.**
`git diff 72e5157..HEAD -- resources/views/` for this slice is one file,
`resources/views/components/race-panel.blade.php`, 22 insertions — the turn dropdown and the read-back
chip, which measured `x=42, w=106` and sits fully inside its own panel at 390px. The table was not
touched by any commit in the range.

**Why it was filed at all, when the first reading blamed this slice.** The mobile probe's own output was
`horizontalOverflow: true` on a page this slice had just edited, and the next thought was to fix what had
just been written. Naming the overflowing element is what replaced that guess with a measurement, and it
is the same correction Slice 12 and Slice 13 record in `slice-15-2026-09-29.md` §8.4: reach for the cause
the instrument reports, not the one that fits the story.

**Current behaviour is unverified rather than verified-safe.** No screen width below 476 CSS px has ever
been a stated target for this tool — `CONSTRAINTS.md` and `DESIGN.md` carry no breakpoint contract for the
run screen — so this may be a known and accepted shape rather than a regression. It is filed because a
Trainer on a phone cannot read the log without dragging the page, and nothing in the repo says that is
intended.

**What fixing it needs.** A decision about which of the nine columns the log actually shows at phone
width, and that is a layout ruling on a component this issue's author does not own:
`resources/views/runs/show.blade.php` belongs to the frontend surface, and `DESIGN.md` carries the
responsive rules. The mechanical options are a horizontally scrollable container scoped to the table
rather than the page, a stacked card rendering below a breakpoint, or fewer columns; the first is the
smallest diff and the last loses data. None is chosen here.

**Owner.** Frontend/Design-system owner with Architect, since the fix needs a breakpoint decision the repo
does not currently record. Found while measuring something else: the browser pass was sent to check
contrast on the new turn control, and the overflow surfaced only because the same script read the viewport
width too.

### KI-26 A search of `%` returns the whole catalog, because `CatalogController` interpolates the query into a `LIKE` it never escapes — RESOLVED by `f2c978b`, CLOSED 2026-09-29

**What is wrong.** `app/Http/Controllers/CatalogController.php:39` folds the `search` query through
`NameNormalizer`, and `:44-49` interpolates the result into `match_key LIKE "%{key}%"` (and the same shape
into `lower(alias) like ?`). `NameNormalizer` lowercases, NFKD-folds and strips marks, spaces and dashes —
it does not touch `%` or `_`, and SQLite's `LIKE` has **no default escape character**, so both are pattern
wildcards. A Trainer typing `%` is not asking for a skill whose name contains a percent sign; they are
asking for every row, and the screen answers as though that were the intended question.

**Proof, through the controller rather than a query I wrote.** `GET /umamusume?search=%` returns
`total=2 of all=2` against the seeded catalog, and `?search=_` likewise, while `?search=zzzqqq` returns `0`.
Two rows is the whole table, so the mechanism is visible even at seed size. Measured on the imported
`skills` table, the same clause shape is the difference between **623 of 623** `[Global]` rows for an
unescaped `%` and **1** row for an escaped one, which is the row whose client name really is
`Givin' It 1000%`. The alias branch is the same defect one clause over.

**Why it matters more than an odd empty result.** The screen does not say "this was treated as a pattern".
It prints the total count and the pagination, so `%` reads as *every skill matches your search*, which is
the failure this register keeps naming: a control that answers a question nobody asked.

**Why it is not fixed here.** `CatalogController` is on master, is not Screen D's surface, and the tree is
shared with at least one other session editing views. The new surface escapes and is tested; that is the
whole of what this pass could do without reaching into another file's behaviour.

**What fixing it needs.** Escape `%` and `_` (with `ESCAPE '\'`, or `addcslashes($key, '\%_')` into a named
escape clause) in both the `match_key` and the alias branch, plus one test that a literal `%` query returns
the rows containing one and not the rows that merely exist. `Screen D`'s version is
`SkillController::query()` with `SkillSearchScreenTest`'s wildcard case, so a shared helper is the shape the
fix probably takes — but that is two surfaces' behaviour to change together, and it belongs to whoever next
owns `CatalogController`.

**Closed 2026-09-29 against `f2c978b`.** The `LIKE` metacharacters are escaped at
`CatalogController.php:60` and `:82`; `CatalogRosterTreeTest.php:159` pins `%` and `_` as
literals. The fix commit named the defect in its subject and did not touch this file, so the
register lagged the tree by one commit. No re-fix is owed.

**Owner.** whoever picks up `CatalogController`; found while building the second server-driven filter
surface and noticing the first one had no escape.

### KI-27 `uma:fetch` reports "unchanged since last snapshot" against a database it never wrote to — FILED 2026-09-29 (Screen D pass), OPEN

**What is wrong.** `app/Services/DataPipeline/SourceFetcher.php:57-59` builds the snapshot path as
`snapshots/{source}/{today}/{sha256(body)}.html` on `Storage::disk('local')` and sets
`$unchanged = Storage::exists($path)`. Both halves of that key — the document's hash and the date — are
properties of **the document**. The thing that actually decides whether rows must be written is **the
database in front of the pipeline**, and that never enters the check. `storage/` is shared by every
database in the working tree: `database/database.sqlite` and whatever `.scratch-uma/*.sqlite` a session
points `DB_DATABASE` at. First fetcher writes the snapshot; every other database asked the same document the
same day is told nothing changed, and stays empty.

**Proof, from filling the real database rather than a scratch one.** After the import pass had written
1,910 rows into `.scratch-uma/cited/skills-c5.sqlite` at 10:37 today, `php artisan uma:fetch gametora-skills`
against `database/database.sqlite` printed `'gametora-skills' unchanged since last snapshot; nothing
written.` and `release_status='GlobalReleased' and name_is_client=1` still counted **0** there, with
`storage/app/private/snapshots/gametora-skills/2026-09-29/609afe88…​.html` (2,502,242 bytes) already on disk.
`php artisan uma:reparse gametora-skills` — same snapshot, zero network — then wrote
`7 updated, 1903 created, 0 skipped (manual), 0 to review`, and `/skills` renders **623 of 623**.

**Why it is worse than an idempotent no-op.** The message is true about the document and misleading about
the Trainer's data, and NFR-2 promises a failed or skipped fetch leaves previous data intact rather than
promising the rows exist. A Trainer who runs the command the screen itself names, reads "nothing written",
and has no way to know a second command is the one that works. Screen D's empty state and the README both
now name both commands, which treats the symptom in copy; the check is the thing that should change.

**What fixing it needs.** The short-circuit has to be a property of the target, not only of the disk. Three
shapes: compare against what the database already holds for that source; keep the snapshot as a body cache
but continue into the pipeline anyway; or key the snapshot path per database. The middle one looks nearly
free and there is evidence for it — `StoreSkills` upserts on `export_id` and the reparse above re-ran over
seven adopted rows without duplicating anything, so re-running the pipeline on unchanged bytes is already
safe. Choosing among them is Architect's, and it touches every source, not just skills.

**One thing this ruled out, because it looked like a defect and is not.** The reparse path stamps
`source_url` with the **pinned** URL rather than the manifest-resolved one (stated in `UmaReparse`'s
docblock). Here the pin and the document agree — `skills.609afe88.json` against a snapshot whose sha256
begins `609afe88` — so all 1,910 rows' provenance is accurate, and `snapshot_path` carries the full hash
regardless. KI-24 is about the pin going stale; today it has not for this source.

**Owner.** Data pipeline, with Architect for the decision. Found by doing the thing the screen tells a
Trainer to do.

### KI-28 SQLite reads an unknown double-quoted identifier as a string literal, so a missing column fails silently — FILED 2026-09-29 (catalogue fill pass), OPEN as a hazard; the record is the mitigation

**The hazard.** SQLite degrades unknown double-quoted identifiers to string literals. With a column
absent from the schema, `whereNotNull('col')` does not throw — SQLite parses `"col"` as the literal
string `"col"`, which is never null, so the predicate is always true. An un-migrated dev database will
silently drop every catalogue↔entry link without erroring. Post-migrate this trap goes away, but any
future column addition has the same failure mode.

**Proof, from the fill pass.** `database/database.sqlite` had six pending migrations and
`PRAGMA table_info(race_entries)` listed 11 columns with no `race_catalog_slot_id`. In one process, in
this order:

| Query                                                                        | Result                                                                                                                    |
| ---------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| `DB::table('race_entries')->whereNotNull('race_catalog_slot_id')->count()`   | **1**, no error. Laravel quotes identifiers with `"`, so SQLite read the column name as a string and matched every row.   |
| `select race_catalog_slot_id from race_entries limit 1` — unquoted           | `General error: 1 no such column: race_catalog_slot_id`                                                                   |
| `select "race_catalog_slot_id" as v from race_entries limit 1`               | returns the **string** `race_catalog_slot_id` as column `v`                                                               |

The two readings that looked contradictory during the diagnosis — a count that succeeds against a
column the schema denies — are the same query with and without the quoting style that decides which of
SQLite's two interpretations fires.

**Why it matters more than a missing column usually does.** A read that throws is a screen that fails
and gets filed. This one returns a plausible number: `TrainingRun::calendarCells()` calls
`whereNotNull('race_catalog_slot_id')` and, on an unmigrated database, receives **every** race entry
keyed by a null attribute — so no catalogue cell finds its entry and the grid quietly reports races as
never run, on a page that returns 200. The write path is louder for once: an `INSERT` naming a column
that is not there is a hard error, which is why `uma:fetch` has to follow `php artisan migrate` and not
the other way round.

**What this does not need.** No `Schema::hasColumn` guards in the query path. The trap is SQLite's
behaviour rather than the code's, and a guard per query is a tax paid forever against one database that
was behind its own migrations. The record is the mitigation: read `php artisan migrate:status --pending`
before trusting a zero from a linked-column count on a development database.

**Owner.** nobody — hazard record. Filed so the next session whose `count()` succeeds against a column
`PRAGMA` denies does not spend an afternoon on it.

### KI-29 `/umamusume`'s form controls measure 30/31/32px against DESIGN.md §6.14's 44 — FILED 2026-09-29 (Screen D browser pass), OPEN

**What is wrong.** `DESIGN.md` §6.14 is the tool's one deliberate extension of the client's interface ("the
client has almost no text inputs, so this is our extension and it must not look like one") and it fixes an
input at **height 44**, radius 8, `body` text in `ink`, with a 2px `green` focus border. The catalog
index's controls are 30px and 31px tall. They are built from `rounded-md border border-rule bg-raised px-2
py-1`, which sets no height, so the browser's intrinsic size wins and §6.14's number is simply absent.

**Proof, measured in a rendered page at two widths.** `getBoundingClientRect` on `http://127.0.0.1:8123/umamusume`:
`input[name=search]` **177×30**, `select[name=status]` **162×31**, `button[type=submit]` **56×32** — identical
at 1280×800 and 390×844. Screen D was written by copying that markup, so it measured the same 30/31 until
the browser pass caught it; its controls are now **44/44** (`h-11`, which is already this repository's idiom
for a 44px row in `epithet-checklist`, `race-calendar` and `grade-point-meter`) and its checkbox is 24×24.

**What it is not.** Not an accessibility failure: 30–32px clears WCAG 2.2 AA's 24px target floor, and the
focus ring on that surface is present and 2px green. So the claim is bounded to what the file says — the
surface does not meet the project's own written spec, and the reason §6.14 states for existing (not looking
like a generic web form) is the reason it matters.

**Why it is not fixed here.** Same posture as KI-26: `resources/views/catalog/index.blade.php` is on master,
is not Screen D's surface, and the tree is shared with sessions actively editing views. Adding `h-11` there
is a one-class change and it should go with whatever else that file's next owner does — which is also where
the `LIKE` escape from KI-26 lives, in the controller behind it.

**Owner.** whoever next owns `catalog/index.blade.php`. Found by measuring the new screen against the old one
rather than trusting that copied classes produced a spec-compliant result

---

### KI-30 The landing route was pinned to HTTP 200 by a stock test, and nothing recorded it — FILED 2026-09-28 on `fix/frontend-audit-2026-09-28` (as KI-16), CLOSED 2026-09-29 (merged tip)

**Renumbered on merge.** It arrived as `KI-16` on `fix/frontend-audit-2026-09-28`. The register's own recipe
reads counts off `grep -c "^## KI-"` and states twice that **KI-16 was never filed**, which is why the numbers
run past it — and `PLAN.md:521` plus two lines of the slice-15 verification record name that hole. Filling it
would have retroactively falsified four written statements, so the entry lands at the end of the sequence and
the hole stays a hole.

`tests/Feature/ExampleTest.php:7` asserted `$this->get('/')->assertStatus(200)`. That was the framework's own
example test, left in place, and it was the only thing in the repository constraining what `/` is allowed to
be. The audit's F-1 offered two fixes for the skeleton splash — render it through the app shell, or delete the
route and redirect `/` into the product — and the redirect option was rejected specifically because a 302 would
fail that assertion, in a file the frontend slice was told not to edit. So a decision about the product's front
door was being made by a leftover framework test that never says it is doing that. The coupling is not wrong,
exactly: a local tool answering 200 on `/` is defensible, and a hand-typed `localhost:8000` should not bounce.
It is the silence that is the problem. **Required fix:** either the owner states the contract in `PRD.md`
("`/` is a product surface and answers 200") and `ExampleTest` is replaced by an assertion that names it, or
the coupling is accepted knowingly and this entry closes as a decision. Not fixed in
`fix/frontend-audit-2026-09-28`, which chose the shell option and so kept the 200 either way.

**Closed by the owner's later ruling, not by this merge.** R57 made `/` a redirect into the product, and
`ExampleTest` on the merged tip now reads `it('redirects the home page to the runs index')` asserting
`assertRedirect(route('runs.index'))` — an assertion that names its own contract, which is the outcome this
entry asked for. Verified against `eb23fa8`, not inferred from the branch. The `PRD.md` half of the Required
fix is **not** done and is not claimed here; the coupling is now recorded rather than silent, which is what the
entry actually gated on.

**Owner:** closed. The `PRD.md` wording, if it is ever wanted, remains the owner's.

---

### KI-31 Six `SkillAutomationTest` failures had no owner and predated the frontend audit — FILED 2026-09-28 on `fix/frontend-audit-2026-09-28` (as KI-17), CLOSED 2026-09-29 (merged tip) ON MEASUREMENT, CAUSE NOT ESTABLISHED

**Renumbered on merge, and this one was a real collision:** master's KI-17 is a different defect — the
consecutive-race count, filed Slice 8 and closed Slice 15 — and carries thirteen references in the register.

`tests/Feature/SkillAutomationTest.php` failed six tests: *discovers skills from the registry*,
*matches skills to task descriptions by relevance*, *ranks the most relevant skill first*,
*builds an execution plan in dependency order*, *executes a skill without parameters*,
*auto-executes skills for a task and discloses matches*. They were not caused by any recent slice.
Measured evidence: at `7d4b8cf` (base of `fix/frontend-audit-2026-09-28`) the full suite reported
exactly those 6 failures and no others, and they were already present at `a292ef7`, where they were
reproduced with unrelated work stashed. Nothing in `KNOWN-ISSUES.md`, `PLAN.md`, the ADRs or the
2026-09-28 audit named an owner for them, which meant every slice since shipped against a red
`CONSTRAINTS.md` C-1 gate and treated it as background noise. **This entry was a paper trail, not a
fix.** The failures were untouched there: the frontend slice has no remit over
`app/Services/SkillRegistry.php`, `SkillMatcher.php` or `SkillExecutor.php`, and guessing at a
skill-matching contract without its author is how 6 become 8. **Required fix:** someone owns the
skill-automation subsystem, states whether the six expectations are still the spec, and either
repairs the code or retires the tests with a reason in the commit message, per the `CONSTRAINTS.md`
floor on skipped tests.

**Closed on measurement at the merged tip.** `php artisan test tests/Feature/SkillAutomationTest.php` at
`eb23fa8` reports **7 passed (17 assertions)** and the full suite reports **676 passed, 2 skipped, 0 failed**,
so C-1 is green for the first time in the thread this entry opened. **What turned it green is not established
here.** The test file has not changed since it was added at `cf8021d`; the candidates are `aa5b05c` (the
ADR-0011 skill catalogue rework, which rebuilt the data the matcher and executor read) and `f0f508c` (the
PHPDoc pass across those same classes). Naming one without bisecting would be a guess, and this register's own
KI-22 — "filed on a wrong cause" — is the reason not to. The ownership question stands unanswered: nobody has
stated whether the six expectations are the spec; they simply pass now.

**Owner:** unassigned for the subsystem. This closes as no-longer-reproducing, not as adopted.

### KI-32 No `color-scheme` is declared, so native form controls paint light widgets on the dark theme — FILED 2026-09-29 (Screen D dark pass), CLOSED 2026-10-02

**What is wrong.** `resources/css/app.css` declares no `color-scheme` anywhere (`grep -n "color-scheme" →
no output`), so a browser keeps using its **light** UA skin for native controls — checkbox, `select`
dropdown, scrollbars, any future date or number input — while the page around them is the dark theme. The
dark theme here is a token override (`D-101`), and tokens do not reach a control the browser paints itself.

**Proof, and its limit.** On `http://127.0.0.1:8123/skills` with `prefers-color-scheme: dark` emulated, the
same unchecked `input[name=unique]` computed `backgroundColor: rgb(255,255,255)` on one load and
`rgb(36, 38, 42)` — `#24262A`, this project's own dark surface anchor — on another, with nothing in the page
different but the order in which the theme was applied during my measurement sequence. So the reproduction
is "the same control paints two ways depending on load order", which is the defect's actual shape, and it is
**not** a deterministic screenshot diff. The checked state is unambiguous either way: blue fill with a white
tick, distinguishable from empty at 24px.

**What it is not.** Not a contrast failure, and not a reason to hold the screen. Every pair measured on
`/skills` in dark clears AA comfortably — `ink-muted` on the list **6.64**, on the page **8.55**, `h1`
**19.51**, the ✦ badge **17.61**, row name **15.15** — with no page-level horizontal overflow at 1280 or 390
and a computed `solid 2px rgb(127,204,9)` focus ring, same as light.

**What fixing it needs.** Two declarations: `color-scheme: light` on the root and `color-scheme: dark`
inside the existing `html[data-theme='dark']` block, which is the block `D-101` already owns. Not done here:
`resources/css/app.css` is the design-system surface and other sessions are editing views and that file in
this shared tree — the same posture as KI-26 and KI-29.

**Where the two declarations actually live.** `color-scheme: light` sits in a `:root` rule at
`resources/css/app.css:219`, beside the `@theme static` block rather than inside it; `color-scheme: dark` is
at `:229` inside `html[data-theme='dark']`. Tailwind v4's `@theme` block rejects non-custom-property
declarations (`@theme blocks must only contain custom properties or @keyframes`), so the light-theme
declaration cannot go in the block and was never going to. A later reader following this entry's fix direction
should not "fix" the `:root` rule back into `@theme`.

**Owner.** design-system. Found while closing the dark-theme gap on Screen D, by measuring a native control
rather than trusting that a token override covers everything drawn on the page.

**Verified 2026-10-02 (documentation-sync pass).** This pass checked the fix on the working tree
independently of the closure above it: `ed71741` landed it, and `resources/css/app.css:219` (light, in a
`:root` rule) plus `:229` (dark, inside `html[data-theme='dark']`) both declare `color-scheme`. The heading's
CLOSED is therefore supported by evidence, and this block is that evidence. Two register-hygiene notes the
closure leaves behind, recorded here rather than re-litigated: the closing heading names no sha, so a reader
verifying it must find `ed71741` by `git log -S color-scheme`; and the closure landed while
`origin/master` was 23 commits behind, so the discipline that a KI closes only when its fix is on
`origin/master` is currently being applied by this file's headings rather than by that rule's own test. Both
are the register's business on the pass that owns the push (owner gate O-1), not a defect in the finding.
One further note: the peer batch's closing commit `9cba3ee` is `KI-29`'s fix (the 44px control sizes), not
this entry's -- the fix that closes THIS entry is `ed71741` -- so the two closures must not be read as sharing
a commit.

### KI-33 A trainee's own innate and unique skills are published by the source and stored nowhere, so a run cannot pre-populate them — FILED 2026-09-29 (per-trainee skill scoping pass), CLOSED 2026-09-30 (Slice A storage and pre-populate, Slice B repeater)

**Symptom.** A run created for a trainee renders "None." in its Skills section
(`resources/views/runs/show.blade.php:405`) because nothing in the schema records which skills are hers.
The picker cannot group by "her skills", and D-44's `Suggested` state — planned *before* the run — has no
data to be planned from, so the plan-versus-actual comparison the screen exists to show is empty on turn
one and only becomes real after the Trainer types the list by hand.

**Cause, stated against the ref it was measured on.** `GametoraCharacterParser` reads `char_id`, the name
fields, the release fields and the ten aptitude columns, and **none of the six `skills_*` keys** — they
arrive in the document and are dropped at parse time. There is also no table to put them in on `master`:
`character_cards`, `CharacterCard` and `ADR-0008` live on `feat/catalog-roster-and-trainee-selector`
(tip `cf5fc75`) and are absent from this ref, which is what `SKILLS-GAPS.md` G-SK-6 records. One
prerequisite, then two consequences: the card layer merges, the parser keeps the lists, the columns exist.
The merge gates the rest and is not this entry's to make.

**Fix direction, in dependency order.**

1. Land the card layer (the G-SK-6 merge decision — Architect).
2. `character_cards` gains `skills_innate` as a **json list**, not a scalar: the live document carries
   exactly three innate ids on all 268 records. `skills_unique` is also **a list, not one nullable id** —
   22 records carry two (card `100701` holds `10071` and `100071`), so a scalar column would silently drop
   one of her uniques.
3. The parser reads the keys and the writer stores them, honouring `is_manual` (FR-B-4) like every other
   reference writer.
4. Run creation pre-populates `run_skills` at status `Suggested` from the chosen card's innate and unique
   lists. `skills_evo`'s `{new, old}` pairs and `skills_awakening_en` are the awakening ladder and are
   **out of scope for the pre-populate** — an awakened skill is reached mid-run, not chosen at start.

**Two sub-findings that belong to this entry, not to the design brief that found them (owner ruling
2026-09-29).**

- **The skills form has no repeater, and the pre-populate needs one.** `resources/views/runs/show.blade.php`
  hard-indexes `skills[0]` three times (`:447`, `:455`, `:460`): one row, one submit, no "add another". A
  build pre-populated with four or five rows cannot be *edited* in a one-row form, which is the job the
  pre-populate exists for. This is a **required part** of the entry, not an optional follow-on — a slice
  that lands the storage and skips the repeater has shipped data nobody can plan against. It also edits
  the same seventeen lines as **KI-36**, so the two land together or the label defect is reproduced once
  per row.
- **`syncSkills` is an upsert named as a sync.** `TrainingRunController::syncSkills` (`:546-557`) calls
  `setSkillStatus` per submitted row and never detaches, despite the route being `runs.skills.sync`. That
  is why a save will not wipe a pre-populated row today — a side effect, not a guarantee, and the
  difference becomes a data-loss question the moment the repeater above submits several rows at once. This
  entry's owner decides whether the route is renamed or a `detach` is added; the design brief for the
  selector only records the behaviour so nobody reads the name as replace-all.

**No backfill into an existing run.** A run in progress has actual rows the Trainer entered; deriving
`Suggested` into it after the fact overwrites memory with plan, and every figure on the screen is
Trainer-entered by rule (D-270).

**Source citation.** Live document `character-cards`, resolved through
`https://gametora.com/data/manifests/umamusume.json` to hash **`e9e9ee6d`**, fetched **2026-09-29**, HTTP
200, 251,294 bytes, 268 records, 34 top-level keys. Verified on three cards across three trainees:
`100101` Special Week (`skills_innate [200512, 201352, 200732]`, `skills_unique [100011]`), `100701`
Gold Ship (`[201591, 201212, 201472]`, `[10071, 100071]`), `112701` Fenomeno (`[200742, 202772, 202482]`,
`[101271]`). **Join measured at verification time:** the document references 1,513 distinct skill ids and
**all 1,513 exist in `skills.export_id`** — innate 289/289, unique 290/290 — so this is about storage, not
about a key that fails to match, which is what KI-23 is. The 290 independently reproduces the figure
`ADR-0011` §5 reconciles against `is_unique` 294.

**Availability caveat, recorded so nobody inherits it silently.** Card `112701` has `release_en: null` and
a populated `skills_awakening`: a card form can hold skill data while not being on `[Global]`. The
innate and unique lists are therefore not a Global statement, and the pre-populate must read the same
availability rule `Skill::scopeAvailableOnGlobal()` (`app/Models/Skill.php:80-85`) applies — otherwise the
run screen offers a Global Trainer a skill their client cannot show.

**Owner.** Data Engineer for the parser and the columns, with Architect for the card-layer merge; Planner
Domain Specialist for the pre-populate writer and the repeater at run creation, which is a controller and
form change with its own tests.

**Downstream.** This is the prerequisite `G-SK-13` names: a picker that filters to "her skills" cannot
work until "her skills" is a stored fact, so slicing the picker first reproduces the same defect one level
up.

**Closed 2026-09-30 across two commits.** `dd90330` adds `skills_innate` and `skills_unique` to
`character_cards` as **nullable json lists** and makes `GametoraCharacterCardParser` keep them:
`intList()` filters on each element's type rather than coercing it, because `intval()` of a nested array
returns `1`, so the sketch of a helper that mapped `intval` over the list would have stored the skill id
`1` twice for a malformed value and parsed clean. The store writes both keys through its named projection,
and `TrainingRunController::store()` seeds `run_skills` at `Suggested` inside the same transaction as the
run — creation only, every id resolving through `Skill::scopeAvailableOnGlobal()` because a card's list is
not a Global statement (Fenomeno's `112701` is this entry's own case), and `setSkillStatus` upserts so a
seed cannot duplicate a row. `4902f1d` lands the repeater this entry called a required part.

**Both lists, not one.** The 22 records carrying two uniques are the reason the columns are json: a
nullable `skills_unique_id` would have stored Gold Ship's `10071` and lost `100071` with nothing
downstream able to tell. `CharacterCardParserTest` pins the two-value case on the fixture row this entry
cites, and `TrainingRunTest` pins the four-into-three filtering case — a card with five ids, four of them
Global, seeds four rows.

**The no-backfill rule is tested, not asserted.** `it refuses to backfill an existing run when a later run
is created from the same card` creates a run, sets one skill to `Acquired` at turn 7, creates a *second*
run through the real route, and requires the first run's pivot to be untouched. D-270 says every figure on
a run in progress is Trainer-entered; a pre-populate that reached backwards would overwrite memory with
plan, and only the second creation exposes that behaviour rather than the first.

**What this entry's own text now supersedes.** Its Cause paragraph says `character_cards`, `CharacterCard`
and ADR-0008 "are absent from this ref" and puts the card-layer merge as step 1 of the fix. That landed
before this closure: all three are on master, and `git ls-tree` at `dd90330`'s parent shows the migration,
model, enum, contract, parser, store, factory and three test files. Step 1 was therefore already done, and
the paragraph is kept as written by this repo's convention that a superseded claim becomes an erratum
rather than a silent edit.

**One open end, and it is not this entry's.** The four-group picker (`G-SK-13` / D-3, the combobox that
would group "her innate / her unique / her awakening / everything else") is unbuilt. Reusing
`resources/js/trainee-combobox.ts` for it means generalising a single-instance module hardwired to the
trainee payload, which is a refactor on another session's surface rather than the one-Blade-file change the
brief budgeted. The storage this entry needed is now in place, so that slice is unblocked; it is not
closed here.

### KI-35 The trainee detail page is a metadata stub: ten parsed columns rendered nowhere, no section for skills, forms or goals, and an absence vocabulary its own specification invented — FILED 2026-09-29 (trainee detail and skill selector design pass), OPEN

**Symptom.** `resources/views/catalog/show.blade.php` renders four things and stops: a name header with a
"Back to catalog" link, a four-cell metadata card, an aliases list, a provenance list. That is the whole
page (`:1-71`). A Trainer deciding *which trainee to run* cannot see whether she is Sprint or Long, Turf
or Dirt, Front or End, because the ten aptitude columns are not on it; and no section exists that would
hold her skills, her costume forms or her goal races. The page has **no primary action** — its only
outbound link goes backwards (`:11`) — which contradicts `DESIGN.md` §2.3's one-primary-action-per-screen.

**Three defect classes, and they do not share a cause. Do not let the easiest one carry the other two.**

**Class A — the view renders none of what the schema supports.** `umamusume` carries ten `char(1)`
aptitude columns (`ADR-0004`), documented at `app/Models/Umamusume.php:30-39` and fillable at `:46`, and
`GametoraCharacterParser` already reads all ten keys (`:31-40`, spread at `:101`), with
`tests/Feature/GametoraAptitudeTest.php` on the mapping. `grep -rn aptitude resources/views/` returns
**zero hits**. Measured against the only database in the tree, though: `umamusume` holds **2 rows and all
ten columns are NULL on both**. So the accurate statement is *parsed, schema-ready, unimported, and
unrendered* — and two consequences follow, the second of which a designer meets first. The fix needs a
view and one fetch, no pipeline work at all; and on any database nobody has imported into, this section's
first impression is ten absences, which is a state to design rather than to wait out.

**Class B — sections that should exist while they hold nothing.** Skills, costume forms and goal races
are not stored facts on `master`: the card layer is on its branch (G-SK-6) and `ura-objectives` is
verified but not ingested. None of that blocks a heading. The run screen carries the pattern already and
uses it heavily — "not yet recorded" at `grade-point-meter.blade.php:149`, `:226` and
`guided-step.blade.php:212`, `:247`, `:275`, `:303`; "not recorded" at `grade-point-meter.blade.php:209`
and `mood-pill.blade.php:3`; `N/A` with its reason written down at `resource-strip.blade.php:52-57`. The
trainee page has no such pattern, so what it offers is silence, and silence on a page that lists no
skills reads as "she has none" — false, and the same statement error D-220's matrix warns about for
widgets.

**Class C — three defects in what *is* rendered, and the first is a specification defect, not a view one.**

1. **"Unknown" as a value for JP debut and Global debut** (`:21`, `:25`) is the view obeying its contract,
   not straying from it: `DESIGN.md` §4.2 said *"dateless rows show 'Unknown', never a sentinel (CLAUDE.md
   data rules)"*, and `CLAUDE.md:23`'s actual rule is *"no sentinel **dates** for 'unreleased' (use
   nullable date + `release_status`)"* — a storage rule about what goes in a column. §4.2 turned it into
   display copy, and "Unknown" is what fell out. It reads as a state the trainee is in; every other
   absence in the app is worded as a state of the record. **The correction belongs in §4.2** (landed with
   this entry, same block), and a view fixed before its specification is amended fails review against the
   contract it is meant to satisfy.
2. **§4.2's "amber notice" is unimplementable and the view already says so.** `:33-38` records that the
   token set has no caution chrome, `up` means increase and `risk` means failure, and `pick` measures
   1.60:1 on the raised surface so it cannot carry a boundary; the JapanOnly notice is copy over
   `ink-faint` (3.26:1 light / 4.21:1 dark). Implementation right, specification stale, second clause in
   the same paragraph.
3. **Provenance is the longest sentence on a page with nothing else** (`:58`). A real disclosure — NFR-2
   and US-1 depend on it, and it stays — but as the dominant text it makes the emptiness louder than the
   trainee. The defect is proportion, not presence; and it is already the last section, so the fix is
   quieting, not moving.

**Also in scope, small.** `:47`'s "No aliases yet." is a fourth absence vocabulary on a page already
running a second one. Unify it when the section is next touched.

**What the page should be: a workspace for that trainee, in eight ordered sections.** Identity (same
fields, `N/A` + `title` instead of "Unknown") → Aptitudes grid → Skills → Costume forms → Goal races →
Her runs → Aliases → Provenance, last and quiet. **No brief file is cited for the detail, because none
has been written yet:** the section-by-section states, each section's prerequisite (stored now / frame
ships now / awaits the merge) and the primary-action choice were delivered in the 2026-09-29 design pass
report and live nowhere in the tree. They belong in `docs/design-research/` beside
`replan-mobile-first.md`, in a commit that writes that file — not in a citation that points at it early,
which is the defect KI-25's withdrawn closure was caught for. The eight-section structure itself is
recorded above, and §4.2 now carries it, so this entry stands on its own until the brief is filed.

**Owner.** Frontend with the design-system owner. The disclosure wording is the §4.2 amendment landing in
this block, the aptitude grid is a view change against columns that already exist, and the three empty
section frames need only vocabulary the run screen already uses. The *data* behind sections 3-5 is someone
else's — the card-layer merge (G-SK-6) and the goal ingest this entry deliberately leaves to **KI-34** —
and none of it blocks a section, because a UI/UX deliverable is a section, its states and its copy, while
a data deliverable is what fills them.

**Erratum 2026-10-01.** Landed by the Phase B2 follow-up dispatch. Two of this entry's Class A
measurements are superseded, and the follow-up's own draft understated the second one, so the correction
is recorded against the measurement rather than against the draft.

**The row count.** Class A records `umamusume` as holding "2 rows and all ten columns are NULL on both".
A read-only PDO query on 2026-10-01 returns **67 rows**, with all ten `aptitude_*` columns non-null on all
67. The new rows sit behind the shared database's write-ahead log, not in its main file: the main file
holds at 1,310,720 bytes and 2026-10-01 02:41:21 while `database/database.sqlite-wal` moved from 168,952
bytes at 03:03:21 to 337,872 at 14:49:02 the same day. That is why a session fingerprinting the main file's
SHA-256 sees no change while a query sees 67 trainees, and it is the WAL shape §6's `cp` row warns about,
read from the other side.

**"Rendered nowhere" is superseded too, and this is the half the follow-up's draft asserted the opposite
of.** Class A cites `grep -rn aptitude resources/views/` returning **zero hits**. On 2026-10-01 that grep
returns three files: `resources/views/components/aptitude-grid.blade.php`, which renders all ten letters;
`resources/views/catalog/partials/form-detail.blade.php:56`, which invokes `<x-aptitude-grid
:umamusume="$trainee" />`; and `resources/views/components/form-tabs.blade.php`. `git log --diff-filter=A`
dates the grid to `555b0cb`, 2026-09-30 03:44, the same commit that landed the profile block and the
costume form tabs, and that is the morning after this entry was filed on 2026-09-29. Class A was accurate
when written. The entry's own Owner paragraph already planned for it, calling the grid "a view change
against columns that already exist", so the grid is the entry's predicted fix arriving, not a contradiction
of its finding.

**What still holds, and what does not.** Class A's `grep` half no longer holds. The heading's "ten parsed
columns rendered nowhere" no longer holds. Class B's costume-forms half no longer holds, since `555b0cb`
shipped form tabs. What stands: Class B's skills half, because `resources/views/catalog/show.blade.php:225-229`
still tells Trainers the card skill arrays "are not stored" while `dd90330` (2026-09-30 16:09) stores two of
the four, and the page still carries no skills section; Class B's goal-races half; and Class C's first
defect, because `catalog/show.blade.php:158` and `:162` still print `Unknown` for the two debut dates.

**Status.** OPEN, and not re-statused here. Class A is superseded in full, Class B is reduced to two of its
three cases, Class C stands. Narrowing the heading is the owner's pen, and this entry deliberately does not
close, because the false Trainer-facing sentence the heading's second half describes is still on master.

**Commands that re-test every claim above.** `php -r` with a PDO read of
`select count(*) from umamusume` and one `where aptitude_<c> is not null` per column;
`grep -rn aptitude resources/views/`; `git log --diff-filter=A --format='%h %ad %s' --date=short --
resources/views/components/aptitude-grid.blade.php`; `stat -c '%s %y' database/database.sqlite
database/database.sqlite-wal`; `grep -n 'Unknown' resources/views/catalog/show.blade.php`;
`git show HEAD:resources/views/catalog/show.blade.php | grep -n 'Skill lists are not shown'`.

### KI-36 The run screen's skills editor wraps three controls in one label, so two of them have no accessible name — FILED 2026-09-29 (skill selector design pass, as F-12), CLOSED 2026-09-30 (with KI-33's repeater)

**Symptom.** `resources/views/runs/show.blade.php:445-461` puts a single `<label>` around three controls:
the skill `<select>` (`:447`), the acquisition-status `<select>` (`:455`) and the turn
`<input type="number">` (`:460`). An implicit label association binds to the **first** labelable
descendant, so only the skill picker is named. A screen reader announcing the row hears one named control,
one **unnamed** combobox whose only content is the three option words (`Suggested` / `Acquired` /
`Skipped`), and a number field whose visible name is `placeholder="Turn"` — which disappears the moment
the field holds a value, so the control that is *filled* is the one that has *lost* its label. This is
F-12 in `docs/frontend-review/2026-09-28/README.md:302-310`, filed against this same block, and it has
been carried unfixed through every pass that has touched the file since. The review noted the contrast
itself: "the create form labels every field, so the gap is local to this block."

**Why it survived.** Every check that runs here looks at the rule rather than the name. G-13's
rendered-text sweep finds `undefined` and `NaN`, not an absent accessible name. A server-render assertion
sees three controls with correct `name=` attributes and passes, because `name` is the form key, not the
label. And the row *has* a visible "Skill" caption, which is exactly what makes the other two read as
labelled to anyone skimming the HTML.

**Fix direction.** One `<label>` per control, or `label` + `id` pairs; keep the visual layout
(`flex flex-wrap items-center gap-2`) unchanged, since appearance was never the defect. The status select
needs a name a Trainer reads as its purpose — "Acquisition status" — and the turn input keeps its
placeholder as a hint while gaining a real name, so the two do not trade places. **Same block, same
commit, deliberately:** KI-33's required repeater replaces `skills[0]` with N rows across these same
seventeen lines, and doing the repeater without the labels reproduces this defect once per row instead of
once per form.

**Owner.** Frontend. Small, local, and the whole fix is inside one `<form>` element.

**Closed 2026-09-30 by `4902f1d`, together with KI-33's repeater as this entry asked.** One
`<label for>` per control across every row: `Skill`, `Acquisition status`, `Turn acquired`, with the
status select named in the words the entry proposed and the turn input keeping its placeholder as a
hint. `tests/Feature/RunSkillRowLabelsTest.php` reads the rendered document through `DOMDocument` and
walks every control in the form, asserting each has an `id`, that exactly one `<label for>` names it,
and that the three names are distinct — a `for` pointing at nothing, or three labels all reading
"Skill", fails there. The substring route was refused deliberately: `assertSee` on a label word passes
on a `for` that resolves to no control.

One deviation from the fix direction, stated rather than left to be noticed: the entry asked to keep
`flex flex-wrap items-center gap-2`. Per-control labels put the caption above each field, which is
what `DESIGN.md` §6.14 specifies for a form field anyway ("label above at `label`, `ink`"), so the row
is now `items-end` with three stacked label/control pairs. Appearance was never the defect; the entry's
point was that the fix must not be *carried* by an appearance change, and it is not.

### KI-37 The run screen's form controls measure 31/30/40px against DESIGN.md §6.14's 44 — FILED 2026-09-29 (skill selector design pass), CLOSED 2026-10-01

**Symptom, measured.** The run screen's skills editor was read in a browser at a 390px viewport: the
`<select>` at `resources/views/runs/show.blade.php:447` computes **31.0px** tall, the
`<input type="number">` at `:460` **30.0px**, and `button[type=submit]` ("Save skill status", `:462`)
**40.0px**. The specification says **height 44** for a form field
(`docs/design-research/DESIGN.md` §6.14, `:849`). The same reading across the page found 12 selects at
31px, 20 number inputs at 30px and 11 submit buttons at 40px — the run screen's default, not one control.

**Why this is a separate entry and not a widening of KI-29.** KI-29's heading names its own surface:
*"`/umamusume`'s form controls measure 30/31/32px against DESIGN.md §6.14's 44"* (the `KI-29` heading).
Folding the run screen behind that number would leave a reader of the catalog-index entry waiting for a
fix that was never made there, and would let one surface close the other by proximity. Two surfaces, two
entries, one shared cause. (KI-29's heading is quoted above; it is cited by number rather than by line,
because a line citation in this file rotted within one commit of being written — adding this very entry
shifted it thirteen lines.)

**The precedent that shows it is cheap.** `5ff7aca` ("size Screen D's form controls to DESIGN.md 6.14's
44, and the checkbox to the AA floor") moved the same 30/31/16px readings to 44/44/24 with no token
change, no layout change and no new utility. So §6.14's 44 is implementable against the existing token
set and this is copy-forward, not design work. The controls here carry `px-2 py-1` and no height — the
shape Screen D had before that commit.

**What the fix has to say, not only do.** Raising a row from 30px to 44px grows the guided-turn block, the
skills editor and the race form, and `docs/design-research/CONSTRAINTS.md:171` refuses mobile compromise
in exchange for desktop density — so the change belongs with the density question rather than as a quiet
CSS edit. It does **not** belong with dropping columns or shrinking the stat band. **And it is not a
floor question at all:** the accepted replan addendum (`docs/design-research/replan-mobile-first.md` §1,
accepted 2026-09-29 and not yet in D-40) names 768px as the supported minimum and refuses to drop a
column below it, whereas a control below its own specified height fails at 1280px exactly as it fails at
390px. Saying so keeps a reader from folding "make it work at phone width" into "make the control meet §6.14
at every width", and keeps this fix from being parked behind the replan.

**Not asserted.** No target-size *standard* is claimed here. The repository's accessibility mandate is
**WCAG 2.1 AA** (D-10), and 2.1 has no minimum-target-size criterion; 24×24 is WCAG 2.2 SC 2.5.8 and is
not an obligation in this project. This entry is measured against **§6.14**, a design-system rule the
repository wrote for itself and Screen D already honours. Adopting 2.5.8 as a gate would be a separate
ruling with a number, a scope and an instrument attached.

**Owner.** Frontend with the design-system owner: the spec is the authority, and the run screen is the
second surface to break it after the catalog index.

**Measurement owed, recorded 2026-10-01.** The `h-11` fix landed in `c17e63b` and is asserted at the DOM
level: `RunSkillRowLabelsTest.php` checks the class on every control in the skills form, counts the controls
it checked so the loop cannot pass over an empty set, and asserts `step="1"` on the turn input. A browser
measurement of `getBoundingClientRect().height` at 1280x800 against a scratch database was **not** taken, and
the reason has two parts, because naming only the first would repeat the error this register's own
second-order erratum rows warn about, where a correction gave a true half of a mechanism and got believed
anyway.

1. `browser-testing-with-devtools` is installed but not registered in the session that landed the fix. It
   sits at `~/.qoder/skills/browser-testing-with-devtools`, a symlink to `~/.agents/skills/` of the same
   name, and the Skill tool returns `Skill "browser-testing-with-devtools" not found`. So this is an
   environment gap, not an absent skill, and a future dispatcher should not treat the name as unavailable
   in general.
2. The tool that was available is the reason not to blame the skill alone: the Playwright MCP server was
   connected in that session, so a measurement was reachable. It was not taken because measuring needs a
   scratch database, a built asset bundle and a running server, and the dispatch authorised the class
   assertion as the fallback. The gap is a scoping decision, not a missing instrument.

**What the measurement must confirm, and why 44 is expected rather than assumed.** `h-11` is `height:
2.75rem`, which is 44px at a 16px root. `resources/css/app.css:1` is `@import 'tailwindcss'`, so preflight's
`box-sizing: border-box` applies and the 1px `--color-rule` border sits inside the 44 rather than adding to
it; no root `font-size` override is present in that file. That is the mechanism, not the measurement. The
number the register should eventually carry is four read heights, and it should be read against a scratch
database with a run-unique name, at 1280x800 and 390x844, the way the skills-section review's section E read
the 31/31/30/32 set this entry replaced.

**Rides with.** The next slice that stands up a browser scratch database. The class assertion holds until
then, and the entry stays OPEN on the measurement rather than on the fix: `c17e63b` changed the four
controls, and no number in this register yet records the four heights after the change.

**Measured 44, 44, 44, 44 at 1280x800 on 2026-10-01, and the same four at 390x844. This supersedes the
two paragraphs above, which are kept as written because they were accurate when they landed.** The
dispatch that asked for the measurement stood up the stack the previous one had scoped out: a migrated
scratch database at a run-unique path (`.scratch-uma/skq3-406-1790844478.sqlite`, 307,200 bytes, built by
`DB_DATABASE=<path> php artisan migrate --force`, one `TrainingRun` created, removed after the reading),
`npm run build` so the page served the current stylesheet (`assets/app-CctMJ1Cr.css`, the hash the page
itself links), and `php artisan serve` on port 8243 against that file only. `database/database.sqlite`
was not opened: its SHA-256 read identical either side of the pass.

Heights read with `getBoundingClientRect().height` through Playwright, at both viewports: skill select
**44**, status select **44**, turn input **44**, submit button **44**. `documentElement.scrollWidth` did
not exceed the 390px viewport, so the taller rows cost vertical height and no horizontal overflow, which
is the density argument this entry's own "What the fix has to say" paragraph was making.

Two canaries, because a reading of 44 from an instrument that reports 44 for everything is not a
measurement. The deck panel's select, on the same page and never touched by `c17e63b`, read **31** at both
viewports: the instrument distinguishes the unsized control from the sized one. `getComputedStyle` reported
`font-size: 16px` on the root and `box-sizing: border-box` on the sized select, so the mechanism the
paragraph above predicted is measured rather than assumed: `h-11` is 2.75rem at a 16px root and the 1px
`--color-rule` border sits inside the 44.

**What is still not asserted.** The turn input carries `step="1"`, verified as an attribute in
`RunSkillRowLabelsTest.php` and in the rendered DOM, but the visibility of the native spinner was not read,
and §6.14 asks number inputs to match §6.11's cost stepper. That component does not exist in shipped code:
every `type="number"` control in `resources/views/` is a bare input. Building it is a separate decision, not
a sizing one. Two further lines of this entry remain unrepaired and are named rather than edited here: the
heading's `31/30/40px` is the set that did not reproduce, and `race-panel.blade.php` still passes a literal
as the second argument to `MessageBag::first()` for `objective_index` and `placement`, which is the defect
found while proving the circles refusal renders.

---

### KI-23b The character parser read a source key the GameTora export never published, so fetched trainees lost their Japanese name — FILED and RESOLVED 2026-09-29 (catalog roster, Task 2)

Filed as KI-21 at branch base `b387e07`; renumbered to **KI-23b** on merge because trunk already holds KI-23 (the skills pass's parser-citation defect, a different entry) — the `b` suffix is the trail, since commits `d755da3` and `e8ead2d` say KI-21 in their messages and were not rewritten.
**Symptom.** `app/Services/DataPipeline/Parsers/GametoraCharacterParser.php:92` read
`$card['name_ja']` — that line number is the defective line as it stood before the fix; `d755da3`
replaced it with a comment plus the corrected read, so today's `:93` is the line being described. The
`character-cards` export publishes `name_jp`: over its 268 rows `name_ja`
appears 0 times and `name_jp` 268 — erratum E-12's count over the fetched export at
`research-scratch/data/json/character-cards.json`, a scratch path this repo does not track, so the
figure is cited rather than re-runnable from here. So every trainee `uma:fetch` created stored a null
`name_ja`, and `PRD.md` US-1's acceptance test ("each detail page shows `name`, `name_ja`, release
status, and provenance") went unmet for fetched data while the whole suite stayed green.

**Line numbers re-derived 2026-09-29 by Task 6's extraction `db8603c`.** Both numbers above stay as filed
because each names its own tree: `:92` was the defective read in the branch base `b387e07`, and `:93` the
corrected line as `d755da3` left it. Task 6 moved the debut loop out of `GametoraCharacterParser::parse()`
into the shared `debutForms()` member, so the comment-and-read pair this symptom describes is now at
`:73-74` — `:74` is `'name_ja' => $this->textOrNull($card['name_jp'] ?? null)`. Nothing in the claim moved
with the line: source key `name_jp`, record key `name_ja`, no fallback chain, still pinned by
`tests/Feature/GametoraCharacterParserTest.php:113-128`.

**Cause.** Both guards over that one line were empty. The committed sample
`tests/Fixtures/gametora-character-cards.sample.json` was authored against a guessed key and held
`"name_ja": null` on every row, so the test loading it could only ever agree with the parser. And the
one catalog test that asserts a Japanese name, `tests/Feature/CatalogTest.php:33`, seeds `name_ja`
through the factory rather than fetching it, so it never reached the parser. Neither of them read the
source.

**Fix (this change).** The parser reads `$card['name_jp']` and still emits the record key `name_ja`,
which is the contract `SourceParser`, `app/Actions/PromoteMatchedRecord.php:52,64` and
`app/Services/DataPipeline/PipelineRunner.php:65` all read, and matches the `umamusume.name_ja`
column; only the source-side key moved. The sample now spells the key `name_jp` and carries the
client's real Japanese strings instead of `null`. `GametoraCharacterParserTest` gained a body-level
test that the name arrives from `name_jp`, an assertion of `スペシャルウィーク` on the record loaded
from the fixture, and its `card()` helper now emits `name_jp`, because that helper builds a body in
the export's shape. Deliberately not written: `$card['name_jp'] ?? $card['name_ja'] ?? null`. A
fallback chain accepts a body carrying neither key, which is how this defect stayed invisible: a null
reads as "the source has no Japanese name" instead of "you are reading the wrong key".

**Corrected 2026-09-29 by the review follow-up `e8ead2d`.** That ban was prose-only when this entry was
written, and prose is not a guard. `tests/Feature/GametoraCharacterParserTest.php:113-128` now feeds a
body carrying **only** `name_ja` and asserts the emitted `name_ja` is null, so reintroducing
`$card['name_jp'] ?? $card['name_ja'] ?? null` fails a test instead of passing one. The same commit
corrected the two `GametoraAptitudeTest` bodies to `name_jp` and added the persisted-column assertion at
`:85`, as the Residual paragraph below records.

**Standing lesson.** A fixture that agrees with the code instead of with the source proves nothing.
Its keys and value shapes are a recording of the export, not a mirror of the parser beside it; where
the two agree against the source, the pair has no coverage and still reports green. The same reasoning
closes the second half: a test that seeds the value it claims to show is not evidence either, because
US-1 is about a page the fetch built.

**Residual (withdrawn 2026-09-29 by `e8ead2d`).** This paragraph claimed that
`tests/Feature/GametoraAptitudeTest.php:20,67` still spelled the source key `name_ja` in two inline
bodies, that those tests asserted aptitudes and never the name, and that the rename was left for a later
pass over that file. All three were true at `d755da3` and none survives `e8ead2d`: both lines now spell
`name_jp`, and `tests/Feature/GametoraAptitudeTest.php:85` asserts the persisted column on the row the
pipeline promoted (`->and($umamusume->name_ja)->toBe('スペシャルウィーク')`), which is the proof this entry
was missing: source key read, record emitted, column stored. The history stands as written above;
nothing is left over from it in that file.

**Owner.** Data Engineer. Filed and closed by the same commit (`d755da3`), because the fix and its proof
landed together; the review follow-up `e8ead2d` strengthened that proof (and corrected the Residual
paragraph above) rather than reopening the entry.

---

### KI-24b A fresh clone or worktree has six red tests before anyone touches it, because the skill registry file is gitignored - FILED 2026-09-29 (catalog roster, Task 1), CLOSED 2026-09-30 (option b: the suite skips on an absent registry)

**Symptom.** On a clean `git worktree add` or `git clone` of this repo, `php artisan test --compact` reports **6 failed / 364 passed / 2 skipped** at a commit where every other working tree sees green. All six failures are in `tests/Feature/SkillAutomationTest.php` (the file's seven tests; six of them fail without the registry and the seventh asserts an unknown-skill error, which an empty registry already produces), and the assertion that fails reads `Failed asserting that ... contains 'Route Inspector'`. The six-of-seven split was re-measured on 2026-09-30 rather than carried forward: with the fix reverted and the registry moved aside, the file reports `6 failed, 1 passed (7 assertions)`, the pass being the unknown-skill test, which an empty registry already satisfies. That is the whole reason the heading says six and the skip below says seven — they are two different measurements and must not be rounded into each other.

**Cause.** `app/Services/SkillRegistry.php:23` resolves its path as `base_path('.agents/skills.json')`, and `app/Services/SkillExecutor.php:340` reads `base_path('.agents/config.json')`. `.gitignore:49` ignores `/.agents`, so that directory exists only in a working tree where some tool wrote it. `git worktree add` and `git clone` check out tracked files only, so a fresh tree has no registry and the tests that read it fail for a reason unrelated to the change under test.

**Why it matters beyond one red run.** The failure is indistinguishable from a genuine regression at the exact moment a slice most needs a trustworthy baseline: Step 4 of any plan's setup task is "prove the gates are green before you start," and six red tests there means either stopping for a base that is not actually dirty, or proceeding with no baseline at all. Nothing in the output names the missing file, so the first response is to suspect the code.

**Fix chosen, option (b): the suite skips, with a named reason.** (a) and (c) were rejected: (c) leaves the trap armed for the next worktree, and (a) cannot be honestly done here, because `.agents/` is not project config. Measured on 2026-09-30, it holds `mcp_config.json` (secret-adjacent by category), a vendored Python package under `jev-ultrafast-mcp/` whose 245 `__pycache__` directories are build output, a compiled Windows binary at `skills/impeccable/scripts/bin/windows-x64/impeccable.exe`, and other tools' vendored source and LICENSE files — 4,431 files in all. It is also not the only copy: the same vendored tree is already present at `.github/skills/`, byte-identical for that binary (sha256 `477E544FC8880A5E…` in both), and `git ls-files` returns **0 files for both paths**, so `.agents/` is a second on-disk copy of a tool cache that is itself untracked, not a project asset. Committing any of that is not a registry fixture, so a tracked `skills.json` would have to be a synthetic one, and a synthetic registry makes the assertions below test the fixture rather than the matcher.

`tests/Feature/SkillAutomationTest.php` now carries one `beforeEach` guard that calls `markTestSkipped` with a named reason when `base_path('.agents/skills.json')` is absent, so the seven tests report `skipped` on a fresh tree instead of `failed`. One guard rather than seven calls, so a future eighth test in this file inherits the skip rather than forgetting it. Measured in both states on 2026-09-30, with the file moved aside and restored:

```text
registry present (developer tree)  ->  7 passed
registry absent  (fresh clone)      ->  7 skipped, 0 failed
```

**The class, because the instance is not the part that recurs.** A test whose input lives in a gitignored path passes in the developer's tree and fails in every fresh clone. The detection is one command, and it should be run against any test that reads a `base_path(...)` under an ignored directory:

```text
    git ls-files --error-unmatch .agents/skills.json
```

Non-zero exit means the input is untracked, and the test proves nothing anywhere — it is not merely unrunnable in CI. `.gitignore:46-55` ignores `/.agents`, `/.claude`, `/.cursor`, `/.grok`, `/CLAUDE.md` and others, so any test reaching into those inherits this shape. This is the same failure as KI-39 (`SkillFactory` deriving `match_key` with a different algorithm than production) one grain up: a test that passes while exercising something other than the thing under test.

**Owner.** Data Engineer with whoever owns `docs/SKILL_AUTOMATION.md`. Found by the catalog-roster plan's Task 1 provisioning step, which now copies `.agents` into its worktree; that copy is a workaround local to one branch and does not close this entry.

---

### KI-38 A card with the source's placeholder title is stored and rendered as a real Global costume - FILED 2026-09-29 (catalog roster, Task 13 browser pass), OPEN

**Symptom.** `character_cards` row `card_id = 103601` (Air Shakur, `char_id 1036`) carries `title = "[unsigned]"` alongside a real `release_en` of `2026-06-18` and `rarity = 3`. The catalog list, the trainee detail page and the run-form selector all print `[unsigned]` as though it were the costume's name, with a date and a star rating beside it. Measured on the live export: over its 268 rows, `title` equals the literal string `unsigned` on exactly one, so this is one card and not a pattern.

**Cause, and why a parser must not "fix" it.** The placeholder is the source's own value, so rewriting it in `GametoraCharacterCardParser` would be the engine editing a stated fact - the rule `ADR-0004` and D-33 both turn on. The real cause is the admission rule: `character_cards` is Global-only, and this card qualifies because it has a `release_en`. Nothing checks that it also has a *name*, so a row can satisfy the table's one stated precondition while carrying no displayable content.

**Why it matters.** D-220 says a widget with no mechanic is absent rather than an empty slot, and has no way to say "this row has no title yet". A Trainer sees a dated, rated costume that does not exist under that name, and the run form offers it as a selection. G-16 governs fixture names being real client strings; this is the stored-data equivalent, and it is currently invisible to every gate because the string is a legitimate bracketed title in form.

**Fix candidates, none chosen here.** (a) Treat a placeholder title as absent for display and search while keeping the row, so the card is reachable from her detail page but not offered by name in the selector. (b) Flag the card `unconfirmed` so the cross-check queue surfaces it, and let a human verdict decide whether the release date is trustworthy at all. (c) Accept it as a dated snapshot of an upstream placeholder and render it with a marker. (a) is the smallest change that stops a placeholder reaching a selection; (b) is the only one that also asks whether the date deserves trust.

**Owner.** Data Engineer, with the Lore Guardian consulted on (c) since it puts a non-client string on a display path. Found by the Task 13 browser pass against `http://127.0.0.1:8099/umamusume`.

---

### KI-39 `SkillFactory` derives `match_key` with a different algorithm than production, so search tests prove nothing - FILED 2026-09-29 (catalog roster, Task 13), OPEN

**Symptom.** `database/factories/SkillFactory.php:24` computes the match key as `mb_strtolower(str_replace('-', '', Str::slug($name)))`. Production computes it with `NameNormalizer::normalize()`, which is NFKD, then strip combining marks, then strip the five characters in `NameNormalizer::FOLDED_CHARACTERS`. `Str::slug` transliterates punctuation to nothing; the normalizer keeps it. Measured over 200 sampled `name_is_client` skills, **119 produce a different key**:

| Name                             | Factory key               | Normalizer key                |
| -------------------------------- | ------------------------- | ----------------------------- |
| `Warning Shot!`                  | `warningshot`             | `warningshot!`                |
| `Empress's Pride`                | `empressspride`           | `empress'spride`              |
| `1st Place Kiss` + star glyph    | `1stplacekiss`            | `1stplacekiss` + star glyph   |
| `Class Rep + Speed = Bakushin`   | `classrepspeedbakushin`   | `classrep+speed=bakushin`     |

Over all 135 trainee names, 3 differ: `Mr. C.B.`, `K.S.Miracle`, `Curren Bouquetd'or`.

**Cause.** The factory was written as a convenience and reimplemented the normalizer's intent rather than calling it. It is the same class of defect as `KI-23b`, where a factory-seeded `name_ja` meant the parser's wrong source key stayed invisible: the fixture agreed with the code because both were wrong in the same direction.

**Why it matters.** Any search test that seeds a skill through the factory asserts against a key production never writes. It passes whether or not `normalize()` is correct, and it fails for the wrong reason the moment `normalize()` changes. The divergence is largest exactly where punctuation is richest, which is the Global skill set.

**Fix candidates, none chosen here.** (a) Have `SkillFactory` call `NameNormalizer` through the container, so one algorithm exists. This changes keys in every test that seeds a skill, so it needs a full-suite run and an honest count of what moved. (b) Delete `match_key` from the factory and let the model or an observer derive it, which closes the "two places compute it" shape permanently. (a) is the smaller diff; (b) is the one that cannot drift back.

**Owner.** Laravel Dev. Measured 2026-09-29 with a tinker script comparing both algorithms over the imported `skills` table and the whole `umamusume` table.

---

### KI-40 `NameNormalizer`'s fold has a Unicode ceiling, and the failure mode reads as "no such trainee" - FILED 2026-09-29 (catalog roster, Task 13), OPEN as a hazard

**Symptom.** `NameNormalizer::normalize()` folds by NFKD, removes combining marks, then removes exactly the five characters in `FOLDED_CHARACTERS`. NFKD does not decompose the Latin ligature and stroked letters, and none of them are in that list, so they survive into the key. Measured pairs:

| Pair                                         | Result         |
| -------------------------------------------- | -------------- |
| `Cafe` / `Cafe` + acute                      | match          |
| `El Condor` / `El Condor` + acute            | match          |
| `Tokai` / `Tokai` + macron                   | match          |
| `Straights` / `Str` + ae ligature + `ight`   | **no match**   |
| `Odawara` / `O` + stroke + `dawara`          | **no match**   |

**Severity today, measured rather than assumed.** The ae ligature appears 375 times in the 268-row `character-cards` export, but only in `name_tw` (144 rows), `title_tw` (81), `title_jp` (45) and `title_ko` (4) - **none of which this tool stores or searches**. `O`-stroke, `D`-stroke, `L`-stroke, thorn, sharp-s and oe-ligature appear **0 times**. No `[Global]` English name currently folds wrong.

**Why it is still filed.** The failure mode is silent and wrong-looking: a name that fails to match presents as "no such trainee" or "no trainee or card found", not as a normalizer that does not cover this letter. The ceiling becomes live the moment a source adds a `[Global]` name carrying one of these letters, or the day this tool starts indexing `name_tw` - and the second is a plausible future scope, since the export has been carrying those fields all along.

**Fix candidates, none chosen here.** (a) Add a transliteration step for the Latin-1 letters NFKD leaves alone, so the fold is defined by a class rather than a list of five. (b) Record the ceiling as a known limit next to `FOLDED_CHARACTERS` and add a test that pins the pairs that do not match, so a future source that trips it is a test failure rather than a support question. (b) costs one test and converts a silent wrong answer into a loud one; (a) is the real fix and needs a decision on which letters are in scope.

**Owner.** Data Engineer. The ceiling is a property of the normalizer, so any fix belongs with the fold's own owner rather than with a caller.

---

### KI-41 The run form's roster is ordered by `name` with no tiebreaker, so the order is total by accident - FILED 2026-09-29 (catalog roster, Task 13), OPEN as a latent hazard

**Symptom.** `app/Http/Controllers/TrainingRunController.php:82` ends the roster query with `->orderBy('name')` and no secondary key. SQLite resolves ties by row order, which can change across a re-import or a vacuum, so two trainees whose names compare equal could swap places between two page loads of the same data.

**Severity today, measured.** 0 duplicate names in `umamusume`, and 135 distinct normalized names across 135 trainees, so the order is total in fact. This is filed as a hazard, not a live bug: the property holds because the data happens to be unique, not because the query guarantees it, and nothing in the test suite would fail if a future import introduced a tie.

**Why it matters for the surface it feeds.** The combobox groups options under trainee headers and its empty state is "10 most recently released cards, newest first", so a Trainer reads the group order as meaningful. An unstable order would make the same query return two different screens, which reads as a bug in the selector rather than in the query.

**Fix candidates, none chosen here.** (a) Add `->orderBy('id')` as a tiebreaker, which makes the order total by construction and costs one clause. (b) Add a unique index on the normalized name, which is an Architect decision and would also constrain manual rows. (a) is the smaller change and does not constrain what a Trainer may write.

**Owner.** Architect, or Laravel Dev under (a). Found by the Task 13 browser pass, where the wrap behaviour was verified from both ends of the list.

---

### KI-42 Nothing runs the gates on push, so a defect that only a fresh checkout can see is found by whoever remembers to look - FILED 2026-09-30 (catalog roster, review of the two near-misses below), OPEN, backlog only

**Symptom.** `.github/` exists and holds agents, hooks, prompts and skills, but there is no `.github/workflows/` directory: `Test-Path .github\workflows` is `False`, so no job runs on any push or pull request. The full gate is a local command, `composer test`, which runs `npm run typecheck` and then Pest (`composer.json`). It is therefore run only when a person in one working tree chooses to run it.

**Why this is filed rather than left as a preference.** Two defects from this session would have been caught on the commit that introduced them, had anything run the gate against a clean checkout. They were caught anyway, which is the actual problem, because both were caught by accident rather than by a control:

1. **An untracked gate input** - `.agents/skills.json` is gitignored, so `tests/Feature/SkillAutomationTest.php` was green in every developer tree and red in every fresh clone. Filed as KI-24b, which is now closed by a named skip rather than by a fixture, so this instance is spent; the shape recurs for any test reading a `base_path(...)` under an ignored directory.
2. **A commit measured on the wrong tree** - `d192fa1` added `resources/js/types/global.d.ts` on the belief that a tracked declaration was missing. It was not; `resources/js/bootstrap.ts:3-7` already declares `Window.axios`. The local gate caught it in the same session and `85b37a4` reverted it. To be exact about what this instance is: it is **not** a live defect and **not** something CI missed, because the file was never merged. It is a worked example of the same wrong-tree error that also produced the branch-freshness miscount and the KI-39 factory drift, all of which passed a check that had not fingerprinted what it was checking.

**The common cause is not the missing workflow.** It is that every gate in this repo runs inside one developer's working tree, on demand, where ignored files exist and only one commit is checked out. A clean-checkout run on push is what makes the ignored-input and wrong-tree classes visible without a person noticing them, and the two instances above were caught by a person noticing.

**Fix candidate, deliberately not chosen here, and deliberately not built on this branch.** Add a GitHub Actions workflow running `composer install --no-interaction`, `npm ci`, `npm run build`, and `composer test` on every push and pull request. This is an owner decision, not an implementation detail, for three reasons that are measurable today: the project is local-only by intent (`AGENTS.md`, Phase 1 non-goals in `PRD.md` §6), so enabling a hosted runner may be out of scope entirely; `.agents/` and `.github/skills/` are untracked in this tree, so a hosted runner has no skill registry and any future test that reads one will need KI-24b's skip rather than the file; and `node_modules` and `vendor` are absent from a fresh clone, so the first CI run is also a first-install run. Nothing about the scope change is decided here. Recorded as backlog so the decision is not re-litigated per slice.

**Owner.** Human owner, with the Architect, because the answer may be "no CI in Phase 1" rather than a workflow. Raised by the review of the two instances above; both are already resolved in the tree, so this entry carries no failing gate today.

### KI-43 The run detail page is now 80-89% support-deck markup, because six selects each repeat all 252 Global cards - FILED 2026-10-01 (Slice 2 browser pass), OPEN

**Symptom.** `resources/views/components/deck-panel.blade.php` renders one `<select>` per slot over the
whole Global catalogue. Six slots times the shipped catalogue is 1,512 `<option>` elements on every run
screen, equipped or not. Measured from the fetched pages against the imported 559-card catalogue
(`.scratch-uma/cited/measure-deck-weight.php`, replayable):

| Run state              | Page        | Deck block   | Block share   |
| ---------------------- | ----------- | ------------ | ------------- |
| six cards equipped     | 360,492 B   | 296,537 B    | 82.3%         |
| two cards equipped     | 363,341 B   | 293,021 B    | 80.6%         |
| **nothing equipped**   | 329,355 B   | 291,547 B    | **88.5%**     |

The options alone are 207,504 B, 57.6% of the page. Longest single label is 71 characters.

**The last row is the finding.** A run with an empty deck still carries 291 KB of picker, because an
unfilled `<select>` holds the same 252 options as a filled one. The cost is not the Trainer's data; it is
the choice list, paid on every run page whether or not anyone is choosing. Before this slice the same
pages rendered 51-99 KB with an eight-card catalogue, so the panel did not add a section, it became the
section.

**Why filed rather than fixed in the slice.** No gate covers it. C-6 budgets catalog-index latency at
~1,000 Umamusume (NFR-3), not rendered page weight, and the suite is green at 972. The fix is a design
change, not a bug fix: it alters how a Trainer finds one card among 252, which is `DESIGN.md` territory and
touches the combobox precedent already set for the trainee picker.

**Fix candidates, not chosen here.**

1. **Search-first picker**, the shape `resources/js/trainee-combobox.ts` already implements for trainees:
   a text input over a JSON payload, with six selects reserved as the no-JS fallback and `disabled` until
   the script claims them. Reuses an existing component pattern rather than inventing one; the same
   disabled-input reasoning at `runs/create.blade.php:9-23` applies unchanged.
2. **Filtered shortlist.** Offer the five stat types matching the run's deck need plus the Pal slot, so the
   list is ~40 cards rather than 252, with an explicit "show every card" disclosure for the rare case.
   Cheaper to build, but it decides for the Trainer which cards are worth being able to find.
3. Leave it. On a local-only tool with one Trainer and no network round-trip, 360 KB is parsed rather than
   downloaded. Defensible, and the reason this is filed rather than escalated.

**Owner.** Human owner with the designer, because option 2 makes a product decision about what a Trainer
should be able to reach, and option 1 decides whether the deck is the second surface to adopt the
combobox pattern or the test case for generalising it.

### KI-44 A copied SQLite file is not the database: WAL is declared in config, and the main file can hold nothing at all - FILED 2026-10-01 (Slice 2 scratch-database incident, confirmed by a probe), OPEN

**Rule.** Do not `cp` a SQLite database in this repository. An open WAL-mode database is **three files** —
`x.sqlite`, `x.sqlite-wal`, `x.sqlite-shm` — and the main file is not self-contained. Move or delete all
three together, or do not move any of them. To obtain a single-file copy, checkpoint first:
`VACUUM INTO 'target'`, or `PRAGMA wal_checkpoint(TRUNCATE)` and then copy. To obtain a working test
database, run `php artisan migrate` against a new path.

**This is not an accident of someone's environment.** `config/database.php:42` declares
`'journal_mode' => env('DB_JOURNAL_MODE', 'wal')`, and line 41 declares `busy_timeout` 10000. Both arrived
in the initial skeleton commit `fda6ff0` and are still there. A claim circulating in a peer report that
"no setting in this repository enables WAL mode" is wrong at the config layer; the observation that WAL is
in effect was right, and the reason is written into the connection array.

**Measured, on a throwaway file (`.scratch-uma/cited/wal-probe.php`, replayable):**

```text
after migrate + one Eloquent insert:
  main = 4,096 B   -wal = 1,751,032 B   -shm = 32,768 B

copy of the main file alone:
  -> SQLSTATE[HY000]: General error: 1 no such table: support_cards
     (the live database at that moment held 1 row)

after PRAGMA wal_checkpoint(TRUNCATE), the same copy reports 1 row.
```text

99.8% of the bytes were in the WAL, and the copied main file did not even **declare the table**. The copy
is not a stale snapshot of the database; it is a different, nearly empty database that opens without
complaint.

**Two distinct symptoms, same cause.** Slice 2 hit this for real: `cp database/database.sqlite
.scratch-uma/test.db` produced a file every later command rejected as `database disk image is malformed`.
The probe above produces the quieter outcome, `no such table`. Which one you get depends on where the WAL
was in its lifecycle when the copy was taken. **The malformed case at least announces itself; the
missing-table case, and the empty-but-valid case between them, are silent** — they read as a database that
legitimately has nothing in it, which is how a fixture ends up reporting zero rows for a population that
exists.

**Read-only access on a WAL database is a separate trap and reports as corruption.** Opening the main file
without write permission to the directory yields `SQLSTATE[HY000]: disk I/O error`. That is a permissions
condition, not damage. Do not respond to it by deleting or rebuilding the file.

**Reference implementation already in the repo.** `app/Console/Commands/UmaBackup.php:32` runs
`PRAGMA wal_checkpoint(TRUNCATE);` before `copy()` at line 43, which is exactly the right sequence, and its
own docblock at lines 11-13 says so. Anything else in this repo that copies a database should call that
checkpoint or use `VACUUM INTO`; a bare `copy()` of the live file is the bug.

**Related decoy.** `database/database.sqlite.bak` is 4,096 bytes — one empty page — and is a WAL-mode
main-file copy, not a backup. Its size equals the "main" figure above. Do not treat it as a restore point.

**Owner.** Every agent and human working in this repo; the rule is operational rather than design. Filed so
that the next session does not re-derive it from a corrupted scratch file.

### KI-45 `race_catalog_slots` has no offline population path, so any feature keyed on a race's distance or surface is unbuildable today - FILED 2026-10-01 (Slice 3 pre-flight data check), OPEN, blocking Slice 3

**Symptom.** `race_catalog_slots` is the only table in the schema carrying `distance`, `distance_band`,
`surface` and `grade_code` for a race, and it holds **0 rows**. `scenario_slots`, the table a run's
`race_entries` actually reference, has **no distance or surface column at all**. There is therefore no path
from "the next race on this run's calendar" to "what this race asks of her", which is the input any
readiness or aptitude comparison needs.

**Why it stays empty.** It is fetch-only, and nothing seeds it:

```text
grep -rln "race_catalog_slots" database/seeders/ app/Console/Commands/     # → no files
```text

`uma:fetch` with `GametoraRaceCatalogParser` is the only writer, and the source has no `seed_file` key, so
`migrate --seed` cannot reproduce it offline. A peer session's fresh `migrate:fresh --seed` produced 296
`scenario_slots` and 0 `race_catalog_slots` for exactly that reason. The upstream bodies are already on disk
(`research-scratch/data/json/races.json` 125,292 B, `race_instances.json` 213,813 B,
`racetracks.json` 107,466 B), so the data exists and only the offline path to it is missing.

**Second, independent gap on the same table.** `scenario_slots.tier` is NULL on **141 of 296** rows, and the
155 that have a tier carry only G1 (34), G2 (42), G3 (76) and OP (3) — no `Pre-OP`, no `EX`. That is
R75's strict nullification working as designed (`docs/design-research/verification/slice-15-2026-09-29.md`
§2: an uncorroborated grade is worse than an absent one), and it means config and table do not share a grade
vocabulary: `config('scenarios.php')` `grade_point_by_grade` carries `Pre-OP`, and D-153 records `EX` as a
sixth label. Any code that maps a slot's tier to a grade weight will silently miss 48% of rows.

**What is NOT missing, so nobody re-checks it.** `scenario_slots.fans_needed` is non-null on 296 of 296, so
the fan gate is computable today. All 67 `umamusume` rows carry all ten aptitude letters, with **0 NULL cells
across those ten columns**, and `x-aptitude-grid` renders them as the export's A–G. The trainee side of any
such comparison is complete; the requirement side is not.

**A near-miss recorded, because the column reads as populated and is not.** `is_maiden_gated` is non-null on
all 296 rows, which looks like a working gate — but **0 of the 296 are set to true**, so a maiden restriction
read from that column evaluates correctly and never fires. Column presence is not signal presence; the count
that matters is the non-zero one. Anything that builds a gate on it needs the per-row flag to arrive from the
source rather than from a default.

**Consequence.** Slice 3 (qualitative next-race readiness) is held. `docs/adr/0016-next-race-readiness-open-question.md`
records the measurement, the three candidate shapes and the fact that none was chosen; `PRD.md` §6.11 stands
unamended, so "no prediction engine" is currently a requirement this repository **satisfies**. A future
slice that reads the Slice 3 brief as live authorization would have to invent the requirements to finish it,
which fails the provenance floor and Planner Rule 5.

**Re-verify in three commands** (read-only, against any populated database):

```text
SELECT COUNT(*) FROM race_catalog_slots;                              -- 0 today
SELECT COUNT(*) FROM scenario_slots WHERE tier IS NULL;               -- 141 of 296
SELECT COUNT(*) FROM umamusume WHERE aptitude_turf IS NULL;           -- 0; aptitudes are fine
```text

**Fix candidates.** Give the race-catalog source a committed `seed_file` and a seeder, which is the narrow
change and unlocks the whole feature; or add `distance_band` and `surface` to `scenario_slots` at the
`ScenarioSlotSeeder` layer, which duplicates a fact `race_catalog_slots` already owns and needs a reason; or
scope the surface to gates only, which needs no data at all but is a smaller product. Not chosen here.

**Owner.** Human owner, with the Data Engineer, because the first candidate is a source-and-seed decision and
touches `config/uma.php`, which currently carries a concurrent session's uncommitted `seed_file` work.

**Corrected forward 2026-10-02 (documentation-sync pass).** Both halves of this entry moved, in opposite
directions. **The headline is resolved:** `config/uma.php`'s `gametora-race-catalog` source carries
`'seed_file' => 'race_instances.json'` (`sed -n '138,145p' config/uma.php`), committed at `8b17703`, and the
population path has been **run** — a read-only count of the shared development database returns
`race_catalog_slots = 410` rows, 402 of them with a real (non-sentinel) `distance` and `surface`, and `tier`
non-null on all 410. A feature keyed on a race's distance or surface is no longer unbuildable. **The tier gap
in the body below is not merely still live, it is worse than filed:** `scenario_slots.tier` is now NULL on
**296 of 296** rows, where this entry recorded 141 of 296 on 2026-10-01. The table was re-seeded at some point
after that count with tier-less rows, so the readiness model's tier input has gone from half-sourced to
unsourced while its other input filled. Re-verify with:
`SELECT COUNT(*), SUM(tier IS NULL) FROM scenario_slots;` and
`SELECT COUNT(*) FROM race_catalog_slots;` — and note the second number is now a *seeded* table, so KI-45's
"unbuildable today" headline should not be cited forward.

### KI-46 The import's per-stat error names the internal array path (`turns.0.speed`) to the Trainer - FILED 2026-10-01 (Slice 4 browser pass), OPEN

**Observed, rendered in a real browser** (`.scratch-uma/cited/slice4.sqlite` served on `127.0.0.1:8123`, one row of
`speed = 9999` under URA Finale):

> A speed value is outside what this scenario allows: The turns.0.speed field must be between 0 and 1400.

The ceiling and the column are right. `turns.0.speed` is the Form Request's internal nested key, and it is the
only part of the sentence that tells the Trainer **which row** broke.

**Why it is still there rather than fixed.** Laravel's `attributes()` maps a wildcard key to one string, so
`turns.*.speed => 'speed'` renames every row's error to the same word and the row identification is lost.
Keeping Laravel's default path is what keeps the row number visible. The fix is a message that carries the
row explicitly, which is a `withValidator()` pass over the parsed rows rather than a rename, and it is copy
work on a message that is already truthful and already actionable — the CSV stays in the textarea, so the
Trainer can find row 0 and correct it.

**Consequence.** None for correctness. It is a voice violation: `AGENTS.md` gates copy, and an internal
identifier in a Trainer-facing sentence is the kind of string the Lore Guardian would flag on sight. Recorded
rather than fixed inside Slice 4 because the fix is not a one-line rename and the message does not mislead.

**Owner.** Docs Writer / Lore Guardian with the Laravel Dev, on the next pass that touches import copy.

### KI-47 A run with no scenario shows a ceiling 200 higher than the one its own form enforces — the disagreement ADR-0015 exists to prevent, alive on the no-scenario branch - FILED 2026-10-01 (Slice 4 browser pass), **RULED 2026-10-01 by the human owner: the band is wrong. CLOSED 2026-10-01 by `04658f2`.**

**Symptom, seen in a real browser.** `http://127.0.0.1:8123/training-runs/4` — a run whose `scenario` column
is `NULL`, holding two logged turns — renders every stat band as **`/ 1,400`**. The turn form on that same
page refuses any stat above **`1,200`**. A Trainer who trusts the band and types 1,300 is rejected by the
server, which is precisely the failure ADR-0015's own text names: *"the disagreement was the defect ADR-0002
recorded and ADR-0015 closes, where a Trainer could see a reachable cap the form rejected."*

**Measured, replayable** (`.scratch-uma/cited/cap-probe.php`, run against `.scratch-uma/cited/slice4.sqlite`):

```text
run 4 (scenario NULL)
  stored scenario=NULL  hasScenario=false  scenarioKey()=ura_finale
  forRun()  Speed=1200, Stamina=1200, Power=1200, Guts=1200, Wit=1200
  band()    Speed=1400, Stamina=1400, Power=1400, Guts=1400, Wit=1400
  disagree: YES
run 3 (ura_finale)
  forRun()  Speed=1400 …   band()  Speed=1400 …   disagree: NO
config: base_cap=1200 hard_cap=2000 baseline=ura_finale ura bonus Speed=200
```text

**Root cause, named.** `ADR-0015` moved both readers onto `App\Services\ScenarioCaps`, and that half worked:
there is one owner of the arithmetic. It did not make the two readers **call it with the same argument**.

- The form and the validator call `ScenarioCaps::forRun($run)`, which returns the base cap with **no bonus**
  when `! $run->hasScenario()`. That refusal is deliberate and documented in the same file: *"a bonus belongs
  to a scenario the Trainer chose, and lending URA Finale's +200 to a run that never picked it would accept
  a number the tool has no source for (D-220, D-221)."*
- `resources/views/runs/show.blade.php:310` and `:325` hand the band `$run->scenarioKey()`, and
  `scenarioKey()` resolves `null` to `config('scenarios.baseline')` = `ura_finale`. `x-stat-band` then calls
  `ScenarioCaps::stat('ura_finale', …)` at line 87 and gets **1,400**.

So the run that never chose a scenario is lent URA Finale's bonus by the display path — the exact thing
`forRun()` was written to refuse. `ScenarioCaps` eliminated duplicate arithmetic, not duplicate inputs, and
the split survived inside the single owner.

**Why the import makes it urgent rather than marginal.** A historical run arriving from a paper sheet very
often names no scenario — the sheets recorded the trainee and the turns, not the career's scenario (that is
why the import form offers "Not set (baseline strip)" as its default). Slice 4 therefore produces
`scenario = NULL` runs on its main path, and every one of them lands on the page where the two numbers
disagree. Pre-existing, but newly reached at volume.

**Fix candidate, and why it is not applied here.** `show.blade.php:28` already computes the correct number
into `$statCaps` via `ScenarioCaps::forRun($run)` and does not pass it to the band. Handing the band those
caps instead of a scenario key is the narrow correction, and it makes the page structurally unable to show a
number the form disagrees with. It is **not** applied in Slice 4 because it changes a rendered ceiling on
every no-scenario run page, which is `ADR-0015`'s surface and the Architect's call, not a slice-local
cleanup. Escalated per `AGENTS.md` escalation path 1.

**Open question for the owner, stated both ways.** Either the band is wrong and a no-scenario run should
display 1,200 — which is what `forRun()` and the validator assert; or `scenarioKey()`'s null-to-baseline
resolution is the truth and a no-scenario run *is* URA Finale, in which case `forRun()`'s refusal is wrong
and the validator has been rejecting legal turns. The two positions imply different numbers on the same
page, so one of them has to lose. This file takes neither side. **(Answered — see the ruling below. The
paragraph is kept as the record of what was escalated, not as an open question.)**

**RULING, 2026-10-01, human owner: the band is wrong.** Three independent pieces of evidence, in the
owner's framing:

1. `ScenarioCaps::forRun()` refuses the bonus for a no-scenario run **in writing**, with its reasoning
   attached ("a bonus belongs to a scenario the Trainer chose").
2. **Both** write-side validators call `forRun()`. The turn form rejects 1,201 on a no-scenario run.
3. `x-stat-band`'s own `@props` comment records that a named default was removed *precisely because*
   "silently resolving to the baseline would rate a trainee against the wrong ceiling."

So the band is displaying the thing its own contract forbids. The tie-break the owner names is a
citation-count one, and it is decisive rather than stylistic: **the validator's contract is cited in three
places and the band's in one comment, so the band loses** — which is also why this is characterised as a
fix, not a preference. Either the band lies about the validator or the validator lies about the band, and
only one of them is corroborated.

**Fix, as ruled.** Pass the already-computed `$statCaps` from `resources/views/runs/show.blade.php:28` into
the band instead of a scenario key. One line at the call site.

**Who owes it, and when.** Not Slice 4, and **not Slice 4's successor either** — this is `ADR-0015`'s
surface, and it is the exact class of follow-up that ADR-0015's own register chapter was written to enable.
It goes to the Architect, and the fix lands in **whichever slice next touches `runs/show.blade.php` on the
no-scenario branch**. Explicitly **not** to be parked in a review queue: the source of the disagreement is
now documented in two places, so a reader of the band should not have to rediscover it.

**Owner.** Architect (`ADR-0015`, `ScenarioCaps`) owns the ruling; Laravel Dev applies the one-line view
change on the next slice that touches that branch. Filed by the Slice 4 browser pass.

**CLOSED 2026-10-01 by `04658f2`.** The band takes a required `caps` map as its only ceiling source and the
`scenario` prop is now the truth of the label plus the footer's bonus breakdown; `showData()` passes
`ScenarioCaps::forRun($run)` and the run's real scenario or null. Verified in a browser on a scratch database:
a no-scenario run with `speed = 1250` renders `/ 1,200` five times and `/ 1,400` zero times, and the same
value under `ura_finale` still renders `/ 1,400` with its `+200` breakdown intact — the fix removes an invented
bonus, not a chosen one.

**Two corrections to this entry's own forward-looking parts, recorded rather than quietly edited:**

1. **"One line at the call site" — both in the ruling and in the fix candidate above — was wrong.** The
   ruling was sound (stop the band deriving its own number) but the edit is a props-contract change, because
   passing `caps` alone would have left the footer still reading `cap_bonus` off a scenario key: a no-scenario
   page would then print a `+ Speed +200` breakdown beside bars capped at 1200. That is the same coupling that
   caused KI-47, relocated one line down the page. The footer needed a no-bonus branch, and the component an
   up-front guard, so the wrong state became unrepresentable rather than merely unlikely at this call site.
2. **A third shape of the same bug surfaced while fixing it.** Blade in this Laravel version leaves a
   defaultless `@props` entry *undefined* when the caller omits it. The first cut used `'caps',`, and the
   guard never ran — reading `$caps` raised `Undefined variable` and the page failed with a PHP notice instead
   of the message the component was written to emit. `caps` therefore takes a `null` default, which is not the
   D-240 smell the `scenario` comment warns about (that was a scenario *name* living in a view), so the guard
   fires as intended. Anyone adding a required prop to a component should give it a null default *and* check
   it, because the silent version of this failure is a guard that never executes.

**Also checked, no finding:** `x-guided-step` reads the scenario for step ordering and panel flags and derives
no ceiling, and already carries a separate `declared` flag for the absence case — so it does not have this
defect and was not changed. `x-resource-strip` keeps `scenarioKey()` because it composes a descriptor, not a
number. `ScenarioCaps` itself is unchanged, as the ruling required.

**Evidence trail.** `ADR-0015` carries the dated erratum for the consequence its own list asserted but did not
achieve. `docs/design-research/verification/slice-1-stat-ceilings-2026-09-30.md` §3 had already named this
defect and deferred it as the owner's call; that deferral is discharged, and the five tests added in `04658f2`
are what stops it reopening.

### KI-48 The architecture docs still state the flat `0..1200` stat bound that ADR-0015 superseded — filed, not fixed - FILED 2026-10-01 (owner ruling after the Slice 4 report), CLOSED 2026-10-02

**Gap.** Two governance documents assert a validation bound the code no longer applies:

- `ARCHITECTURE-ESSENTIALS.md:36` — `turn_entries: … stats validated 0..1200, turn >= 1`
- `ARCHITECTURE.md:158` — `-- stats validated 0..1200, turn >= 1 (StoreTurnEntryRequest)`

`ADR-0015` replaced that flat bound with the run's own per-stat scenario ceiling, and `PRD.md` already carries
the supersession in two places — `:28` ("**Superseded in part 2026-09-30 by `ADR-0015`: the flat 0..1200 test
becomes a per-stat, per-scenario ceiling**") and `:128` ("`ADR-0015`, which supersedes the flat 0..1200
recorded here"). The PRD was corrected forward; the architecture docs were not.

**Why it matters more than a stale number.** `0..1200` is the exact value a **no-scenario** run is held to, so
the sentence reads as correct to anyone looking at one, and is wrong for every run that names a scenario —
Unity Cup Wit goes to 1,800, Trackblazer Stamina to 1,900. A reader who takes the digest at face value
builds a form that rejects legal gameplay data, which is `CONSTRAINTS.md` D-31's original complaint
(*"A real run cannot be recorded … this is a product blocker, not a display question"*) resurrected by
documentation rather than by code. It is also the sibling of **KI-47** on the read side: KI-47 is a rendered
number that disagrees with the validator, this is a documented number that disagrees with it.

**Class.** This is the same drift shape as the Slice 2 twelve-column issue, which took three passes to close:
one document corrected forward while a peer document kept the old value, and each pass finding one more
carrier. `DocSchemaDriftTest` reads four governance docs for guarded wording but does not compare this kind of
prose bound against the rules the request classes actually apply, so nothing fails when the digest lags.

**Filed rather than fixed here, on the owner's instruction.** The correction belongs to whoever owns
`ARCHITECTURE-ESSENTIALS.md` and `ARCHITECTURE.md`, in the same change that names the ADR-0015 supersession,
so the fix and its citation land together. Not applied in a Slice 4 follow-up.

**Owner.** Docs Writer, as owner of digest currency. Suggested landing: one edit covering **both** lines above,

**Verified 2026-10-02 (documentation-sync pass) - closure HELD pending owner gate O-1.** The fix landed at `79d5f6f`; this pass verified it on the working tree (ARCHITECTURE-ESSENTIALS.md:36 and ARCHITECTURE.md:159 carry the dated errata; the verbatim 0..1200 lines stand at :36 and :159) rather than re-deriving it from the report that filed it. Nothing about the original finding is edited: it was correctly filed on 2026-10-01 against a tree that did not yet have the fix. What was missing was the closure, and that is a register-lag defect, not a code defect. **The CLOSED heading and the closure block are both written the moment the push lands** (`git rev-list --left-right --count HEAD...origin/master` returns `0 0`); until then this entry stays OPEN, because this register closes nothing on an unpushed fix.

each citing `ADR-0015`, since fixing only the ESSENTIALS line leaves `ARCHITECTURE.md:158` stating the same
wrong bound.

### KI-49 Three source bodies are untracked, so three pipelines cannot be re-run from a fresh clone. Filed, not fixed

**Gap.** `database/seeders/data/skills.609afe88.json`, `database/seeders/data/characters.c6676539.json` and `database/seeders/data/gametora-characters.e9e9ee6d.json` exist on disk, are in no commit, and are not ignored. Verified 2026-10-01, each with its own command:

- `git ls-files --error-unmatch` on all three returns no, so none is tracked.
- `git check-ignore -q` on all three returns no, so this is not a deliberate ignore. The files were simply never added.
- Sizes on disk: 4,399,937, 2,068,129 and 2,791,919 bytes.

**Why it matters now, and not only in principle.** `config/uma.php` names all three bodies through `seed_file` keys (`config/uma.php:75`, `:113`, `:180` in the working copy). `git show HEAD:config/uma.php` greps **0** matches for `seed_file`, so the keys exist only inside an uncommitted peer diff. Neither the pointers nor the bodies are on any ref. Corrected on the same day, because that sentence overreached in its own filing: the `seed_file` keys are indeed absent from every ref, and the bodies are absent from every ref, but two of the three file names do appear at `HEAD`, as `url` values in `config/uma.php` (`skills.609afe88` once, `characters.c6676539` once, `gametora-characters.e9e9ee6d` not at all). So a fresh clone can re-fetch them over the network; what it cannot do is seed offline, and that is the real gap. The rest of this entry is unaffected. Three consumers depend on them: the skills import, which `app/Enums/ReleaseStatus.php` and `ADR-0011` govern; the characters import and roster crosscheck; and the Batch 2 extraction at `docs/design-research/skill-facts-2026-10-01.md`, whose 623 rows cannot be regenerated from a fresh clone because the file it read is not in history. The extraction document says so in its own provenance warning, but a warning in a derived document does not make the source durable.

**Adjacent findings, deliberately not folded in.** KI-24b is a gitignored skill registry, and P-6 with N-3 in `docs/design-research/slice-6-currency-2026-10-01.md` concern one citation pointing at `factors.json`. Those are single pointers into ignored scratch. This is three data bodies with no rule and no commit, so the failure mode differs: a pipeline that is green in one working tree and unrunnable in every other checkout.

**Verified 2026-10-02 (documentation-sync pass) - closure HELD pending owner gate O-1.** The fix landed at `8b17703`; this pass verified it on the working tree (git ls-files database/seeders/data/ | wc -l -> 9, the three bodies named untracked in 2026-10-01 all tracked) rather than re-deriving it from the report that filed it. Nothing about the original finding is edited: it was correctly filed on 2026-10-01 against a tree that did not yet have the fix. What was missing was the closure, and that is a register-lag defect, not a code defect. **The CLOSED heading and the closure block are both written the moment the push lands** (`git rev-list --left-right --count HEAD...origin/master` returns `0 0`); until then this entry stays OPEN, because this register closes nothing on an unpushed fix.

**Fix options, owner's call.** Either (a) track the three bodies, about 9 MB together, as the committed source truth for these pipelines, or (b) declare them ephemeral and retarget each pipeline to a source that a ref can resolve, recording manifest hash and fetch date so provenance survives the move. What must not stand is the current middle state, where `seed_file` names bodies no ref carries.

**Not acted on here.** This dispatch's fence forbids adding the files, and a decision to commit or discard 9 MB of source data is the owner's, not an agent's. Filed only.

**Owner.** Data Engineer for the pipeline retarget, with the owner deciding between (a) and (b). Docs Writer owns the correction to `ADR-0011`'s provenance line if option (a) is taken, since that ADR currently cites the manifest hash as though it were retrievable.

### KI-50 `Blueprint::check()` is a silent no-op on SQLite, and no test would notice a second one. Filed, not fixed

**Gap.** `database/migrations/2026_09_30_142618_create_support_cards_and_support_effects_tables.php` writes four column-level `->check(...)` calls, at `:27` on `support_effects.calc`, `:42` on `support_cards.rarity`, `:43` on `support_cards.type` and `:66` on `deck_slots.slot_position`. On this project's stack, Laravel 13's SQLite grammar does not render that modifier at all, so the DDL that migration produced carries no `CHECK` token on any of the three tables. The table-level spelling is not an escape either: `Blueprint` has no `check()` method, so writing one raises `BadMethodCallException`. A constraint that cannot fail is worse than an absent constraint, because the source reads as a guarantee and the database enforces nothing.

**The one instance is repaired. The class is not.** `database/migrations/2026_09_30_151945_correct_support_card_schema_and_constraints.php` rebuilds all three tables by create-copy-swap in raw SQL, and the constraints are real in the live schema. Read `sqlite_master` on the shared database: `deck_slots` carries `check ("slot_position" between 1 and 6)`, `support_cards` carries two, and `support_effects` carries `check ("calc" in ('mult', 'add'))`. That is the whole of the repair, and it is a repair of the three tables that migration happened to touch.

**Canary, so the zero above is a real zero.** The same `sqlite_master` query returns a `check` token for two tables nobody rebuilt: `scenario_slots` and `race_catalog_slots` both hold `check ("half" in ('Early', 'Late'))`. Those come from `$table->enum('half', ['Early', 'Late'])` at `2026_09_27_153416_create_scenario_slots_table.php:45` and `2026_09_28_180350_create_race_catalog_slots_table.php:49`, which the SQLite grammar does render. So `enum()` produces an enforced constraint, `check()` produces nothing, and the query that finds three repaired tables can also find two working ones. The absence on the `142618` tables is a property of those tables, not of the query.

**Why it matters now.** Two ways. First, nothing in the toolchain fails on a `->check()`. A migration using it runs, reports success, and the next reviewer reads the source and believes the constraint holds, exactly as this repository's own correction docblock had to record at `151945:12-17`. Second, no test reads the emitted DDL for a constraint. `tests/Feature/SupportCardTest.php:50` reads `PRAGMA table_info(support_cards)` to prove a column is absent and `:176` reads `PRAGMA foreign_key_list` to prove a foreign key is absent, which is the right instrument and the right instinct. No test anywhere reads `sqlite_master` or `PRAGMA index_list` to prove a `CHECK` exists, so the four constraints `142618` failed to create were invisible to the suite, and the four `151945` created are unverified by it. A migration that drops a constraint again would also be invisible.

**Fix options, owner's call.** Either (a) add a test that reads `sqlite_master` and asserts a `check` token on every table whose source declares a constrained domain, which turns the silent no-op into a failing test, or (b) retire the spelling. Neither needs a migration: the three tables already hold real constraints, so this is a guard, not a repair. A gate that greps `database/migrations/` for `->check(` and fails is the cheaper half of (a) and catches the case before the migration is written rather than after it ships.

**Not acted on here.** This dispatch's fence covers the run screen's skills section, and adding a schema-drift test is a different surface owned by the Architect and QA. The finding is filed so the hazard is written down before the next support-card or slot migration reaches for `->check()`. Note also that `142618` and `151945` are not modified by this filing and must not be: `151945`'s `down()` deliberately reconstructs the constraint-free shape so a rollback returns the database to the state its parent left.

**Owner.** Architect for the choice between the test and the grep, with QA owning whichever guard is chosen.

**Mechanism, measured on 2026-10-01 rather than quoted from this entry's heading.** The heading says `Blueprint::check()`, and that name is imprecise in two ways, both verified against the installed framework. `vendor/laravel/framework/src/Illuminate/Database/Schema/Blueprint.php` has 2,036 lines and **0** of them contain `check`, and the class defines no `__call`. So there is no `Blueprint::check()` at all, table-level or otherwise. The four calls in `142618` sit on the column object: `addColumn` returns a `ColumnDefinition`, which extends `Illuminate\Support\Fluent`, and `Fluent::__call` at `vendor/laravel/framework/src/Illuminate/Support/Fluent.php:130` stores any unknown method name as an attribute. `SQLiteGrammar.php` has exactly **1** line containing `check`, line 876, which is the return value of `typeEnum()`, and it defines no `modifyCheck`. The attribute is therefore set, read by nothing, and dropped without an error. Two consequences for whoever fixes this. First, the accurate sentence is: a `->check()` call on a column lands on the column's Fluent object and stores an attribute no SQLite grammar modifier reads. Second, the only path by which this framework writes a CHECK on SQLite is an `enum` column, which is why the constraints in the live DDL come from `151945`'s hand-written SQL and not from any Laravel construct. A gate built on either assumption must test against built DDL, because source text shows neither.

**Re-verified 2026-10-02 (documentation-sync pass); stays OPEN.** The four `->check(...)` calls are still in
`2026_09_30_142618` (`:27`, `:42`, `:43` and the fourth), still render no SQL on SQLite, and are still
discussed only in that file's own comment block plus one test comment. Nothing new fails because of it, and
the raw-SQL rewrite in `2026_09_30_151945` remains the enforcement layer, so this stays an open documentation
hazard rather than a live defect: the risk is a future migration copying the no-op pattern believing it
constrains. No change made; the re-verification is recorded so the entry's age is not mistaken for neglect.

### KI-51 The seeder code that reads the untracked bodies is itself untracked, so the skills and roster pipelines have no implementation on any ref. Filed, not fixed

**Gap.** Three PHP classes under `database/seeders/` exist on disk, are in no commit, and match no `.gitignore` rule. Verified 2026-10-01, all by per-file `git ls-files --error-unmatch` and `git check-ignore`:

- `database/seeders/ReadsCommittedSource.php`, 2,284 bytes, mtime 2026-09-30 23:19. Untracked, not ignored.
- `database/seeders/SourceDocumentSeeder.php`, 4,389 bytes, mtime 23:17. Untracked, not ignored.
- `database/seeders/UmamusumeRosterSeeder.php`, 5,416 bytes, mtime 23:13. Untracked, not ignored.

The tracked seeder surface at `HEAD` is four files: `DatabaseSeeder.php`, `ScenarioSlotSeeder.php`, `SkillSeeder.php`, `UmamusumeSeeder.php`.

**The wiring is missing too, and that is the part a reader will otherwise get wrong.** `git show HEAD:database/seeders/DatabaseSeeder.php` contains **0** references to the three untracked classes. The working copy contains **5**, so the only thing that invokes this loader is an uncommitted peer diff. The sole tracked mentions of `SourceDocumentSeeder` at `HEAD` are prose in comments, at `app/Console/Commands/UmaImportSupportCards.php:93` and `tests/Feature/SupportCardFetchTest.php:152`, and `ReadsCommittedSource` is named nowhere in tracked content at all. `SkillSeeder.php` is tracked but independent of this path: it seeds nine hardcoded names through `NameNormalizer`, states in its own docblock that `sp_cost` and `type` are deliberately left for the import, and does not read a body.

**Verified 2026-10-02 (documentation-sync pass) - closure HELD pending owner gate O-1.** The fix landed at `30b3a08`; this pass verified it on the working tree (the seeder code reading those bodies is tracked at 30b3a08 alongside the ninth body) rather than re-deriving it from the report that filed it. Nothing about the original finding is edited: it was correctly filed on 2026-10-01 against a tree that did not yet have the fix. What was missing was the closure, and that is a register-lag defect, not a code defect. **The CLOSED heading and the closure block are both written the moment the push lands** (`git rev-list --left-right --count HEAD...origin/master` returns `0 0`); until then this entry stays OPEN, because this register closes nothing on an unpushed fix.

**Consequence, stated asymmetrically because the asymmetry is the finding.** A fresh clone gets the schema, the models, the views and a tracked seeder that does not touch the source bodies. It does not get the loader, the roster seeder, or the invocation. It does get a working support-card path, because `support-cards.88dea522.json` and `support_effects.ca447e53.json` are tracked and `UmaImportSupportCards.php` is tracked. So the pipelines split in two: one committed end to end, and one whose code, data and wiring are all off-ref. This is KI-49's subject two layers deeper, and it is not the same finding. KI-49 is about bodies. Committing the bodies would not make the skills pipeline run, because nothing on a ref reads them.

**Fix options, owner's call.** Either (a) track the three classes and the invocation in `DatabaseSeeder.php`, or (b) fold the loader back into tracked code so the tracked seeder reads the bodies directly. Option (a) plus KI-49 option (a) is the only combination that makes a fresh clone able to seed offline. What must not stand is the current shape, where the tracked entry point is unaware of an untracked implementation that three untracked files depend on.

**Not acted on here.** The fence on this dispatch forbids tracking source files, and the decision is the owner's. Filed only, with the measurement commands above so the state can be re-checked rather than re-argued.

**Owner.** Data Engineer for the loader and the roster seeder, with the owner deciding between (a) and (b) alongside the KI-49 decision. The two should be ruled together, since either answer on one changes the cost of the other.

### KI-52 Two files share the basename `DESIGN.md` with disjoint section numbering, and citations do not say which one they mean. Filed, not fixed

**Symptom.** The repository carries two documents with the same basename and different structures.
`DESIGN.md` at the root (394 lines, 27,646 bytes, mtime 2026-09-29) is the **surface specification**: §2
"Visual identity" with §2.3 "Spacing and layout", §3 "Component inventory (actual committed Blade)", §4
"Surface specifications" including §4.2 "Catalog detail `/umamusume/{slug}`", §5 "Data display rules".
`docs/design-research/DESIGN.md` (1,640 lines, 152,232 bytes, mtime 2026-09-30) is the **design system**:
§3 "Colour", §6 "Component anatomy" including §6.11, §6.14 and §6.16, §8 "UX architecture" including
§8.4, §10 "Accessibility". Neither file announces the other, and a reader cannot disambiguate a citation
by grepping its section number, because the number exists in only one of the two.

**Measured, one instance.** A dispatch this session asked for "`DESIGN.md` §6.14 (form field spec), §2.3
(screen structure), §6.5 (stat band markers)" and named the `docs/design-research/` path. §6.14 and §6.5
are real there. **§2.3 is not in that file at all**, and the section the dispatch described as "screen
structure" does not exist in either: the root file's §2.3 is spacing and layout. So the citation resolves
in neither file to the thing named. A register entry inherits the same unreachable pointer: KI-35's
symptom attributes a rule to "`DESIGN.md` §2.3's one-primary-action-per-screen".

**Why it matters.** Every design dispatch, register entry, ADR and review that cites a `DESIGN.md`
section without the path is ambiguous, and the ambiguity is silent: the citation looks resolvable, and a
reader who opens the wrong file finds either nothing or, worse, a different rule at a similar number. The
same failure shape as KI-49 and KI-51, where a path that looked canonical turned out not to be the one
that carries the content.

**Fix options, owner's call.** Either (a) rename one file, and the smaller diff is the root file, since
the `docs/design-research/` corpus is already one namespace and the root name is the one borrowed from
convention; or (b) keep both names and make the path qualification mandatory in prose, with each file's
opening lines naming the other. Whichever is chosen, a grep for `DESIGN.md` across the corpus will still
need a pass, since the broken citations are already written.

**Not acted on here.** The dispatch's fence forbids renaming either file and forbids edits to the design
record. Filed only, with the measurements above so the state can be re-checked rather than re-argued.

**Owner.** Docs Writer for the cross-reference line and the sweep of existing citations; the rename
decision is the owner's, because it moves a path that other documents cite by name.

**Corrected forward 2026-10-02 (documentation-sync pass).** The convention this entry asks for is written at
`docs/research-scratch/DESIGN-CORPUS.md` §"Convention (2026-10-02…)": a bare `DESIGN.md` citation means the
root contract, and a corpus section is cited as `DESIGN-CORPUS.md §"DESIGN.md" §6.14`. That resolves this
entry's own measured instance without editing either heading below it: KI-29's and KI-37's `DESIGN.md §6.14`
citations name a rule that lives at `DESIGN-CORPUS.md:904`, and they stay as filed. The entry stays OPEN on
its wider question (a rename versus a convention, and the ~40 existing citations that predate the rule),
because the convention guides new writing and does not repair the corpus retroactively — the masters'
provenance lines are merge records, not reader pointers.

### KI-53 A peer session deleted `database/seeders/data/race-tier-labels-2026-09-29.json` and left it as `.held-aside`, breaking eleven tests — FILED 2026-10-01, CLOSED 2026-10-02

**Symptom.** `git status` shows `D  database/seeders/data/race-tier-labels-2026-09-29.json` and `??  database/seeders/data/race-tier-labels-2026-09-29.json.held-aside`. Eleven tests fail without the tracked file:

- `tests/Feature/ScenarioSlotSeederResilienceTest` (4 tests)
- `tests/Feature/ScenarioSlotSeederTest` (1 test)

**Verified 2026-10-02 (documentation-sync pass) - closure HELD pending owner gate O-1.** The fix landed at `8b17703`; this pass verified it on the working tree (race-tier-labels-2026-09-29.json tracked; no *.held-aside file exists in database/seeders/data/) rather than re-deriving it from the report that filed it. Nothing about the original finding is edited: it was correctly filed on 2026-10-01 against a tree that did not yet have the fix. What was missing was the closure, and that is a register-lag defect, not a code defect. **The CLOSED heading and the closure block are both written the moment the push lands** (`git rev-list --left-right --count HEAD...origin/master` returns `0 0`); until then this entry stays OPEN, because this register closes nothing on an unpushed fix.

- `tests/Feature/TierLabelJoinTest` (6 tests)

`ScenarioSlotSeederResilienceTest` is the suspected deleter per its `withTierLabelsFileAbsent()` helper, which renames the file to `.held-aside` and does not restore it in a failing path.

**State.** The tracked file is deleted in the working tree; the held-aside variant exists at the same path prefix. A fresh checkout would restore the file, but the peer's intent for the held-aside name is unknown, and restoring could race a peer's in-progress edit.

**Owner options.** (a) The peer restores the file and removes the held-aside, or (b) the tests are re-pointed at the new path and the original deletion becomes deliberate. The KI records the state; the fix is the owner's call.

**Do not restore from git yet.** The held-aside name suggests an intentional intermediate state; restoring blindly could overwrite a peer's intended change. Do not run `git restore` on `database/seeders/data/race-tier-labels-2026-09-29.json` until the helper is guarded. Restoring the tracked path triggers the same rename warning that KI-53's deletion already produces, because `withTierLabelsFileAbsent()` is called outside the `try` and the `rename()` at `:105` fires before the `finally` restore.

**Related.** but distinct from the shared-database write pattern recorded in `SESSION-CONSOLIDATION-2026-09-30.md` §6; this KI is about a tracked fixture, not the database file.

### KI-54 The `.held-aside` fixture now collides with the path that restores it: `git restore` recreates the tracked file, then the resilience test's `rename()` warning aborts the test before its `finally` ever runs, failing 11 tests and possibly leaving a second move unrestored - FILED 2026-10-01, OPEN

**Symptom.** Eleven tests fail against the working tree while `database/seeders/data/race-tier-labels-2026-09-29.json` is deleted: 4 in `tests/Feature/ScenarioSlotSeederResilienceTest`, 1 in `tests/Feature/ScenarioSlotSeederTest`, 6 in `tests/Feature/TierLabelJoinTest`. This matches the counts KI-53 recorded, so KI-53's numbers stand.

**Mechanism.** `tests/Feature/ScenarioSlotSeederResilienceTest.php:32` and `:55` call `withTierLabelsFileAbsent()` **outside** the `try`, and the helper's `rename($file, $file.'.held-aside')` at `:105` is not guarded. Once the tracked path is gone, `rename()` on a missing source emits a PHP warning and Pest fails the test before it reaches the `finally` block that would call `restoreTierLabelsFile()` (`:41-45`, `:58-62`). Restoring the file from git is not a free fix either: `restoreTierLabelsFile()` (`:111-120`) renames `.held-aside` back to the tracked path, so with both present the helper's move creates a second `.held-aside` collision, and `git restore` while a peer holds its own intent for that sibling is exactly the race KI-53 warned about.

**Do not restore the file from git yet.** Identified hazard: `git restore database/seeders/data/race-tier-labels-2026-09-29.json` while the peer's `.held-aside` file is still in place.

**Measurement at filing (2026-10-01, this session, read-only).** `git hash-object` on the held-aside file returns `84fe09424fed5e8ad7d53213ed01e7facaeab046`, identical to the `HEAD` blob at the same path (`git rev-parse HEAD:database/seeders/data/race-tier-labels-2026-09-29.json`). So the held-aside content is the committed fixture, unmodified, not a peer's edited replacement. A plain move-back would therefore not overwrite peer work as of this measurement, but the measurement is a snapshot: a live peer process was present at the time, so this note does not lift the do-not-restore instruction.

**Owner options.** (a) Move `.held-aside` back to the tracked path once the owner confirms no peer edit is in progress against it (content is provably HEAD-identical at filing, so the move is lossless), or (b) guard `withTierLabelsFileAbsent()`, for example check `file_exists` before the rename or move the call inside the `try`, so the suite survives the missing-file state until the fixture decision is made. Option (b) is the smaller diff and does not touch the file the peer named.

**Attribution.** Same suspected actor as KI-53 (`ScenarioSlotSeederResilienceTest` helper leaves the renamed state when a run dies mid-suite). KI-54 records the collision the repair now has, not a second suspect.

**Related.** Follows KI-53. The `.held-aside` naming is the repo's own term from that helper; it is not a git convention.

---

### M1 register sweep, 2026-10-02 (PLAN-UI-UX-2026-10-02 Task 1.3, Task 1.2, Task 3.3)

One block, seven dispositions, so the sweep can be checked as one pass. Every closure below was written only after `git branch -r --contains <sha>` confirmed the fix commit is on `origin/master`; every withheld closure names the local commit it is waiting on. Evidence is stated per entry.

**Verified 2026-10-02 (documentation-sync pass) - closure HELD pending owner gate O-1.** The fix landed at `46959ab`; this pass verified it on the working tree (fixture tracked at database/seeders/data/race-tier-labels-2026-09-29.json and the tree is clean; the 11-red state no longer reproduces) rather than re-deriving it from the report that filed it. Nothing about the original finding is edited: it was correctly filed on 2026-10-01 against a tree that did not yet have the fix. What was missing was the closure, and that is a register-lag defect, not a code defect. **The CLOSED heading and the closure block are both written the moment the push lands** (`git rev-list --left-right --count HEAD...origin/master` returns `0 0`); until then this entry stays OPEN, because this register closes nothing on an unpushed fix.

**KI-29 (catalog index controls). Work complete, closure withheld.** The fix landed at `9cba3ee` (local; `origin/master` does not contain it): search input, status select and submit button all carry `h-11`, and `CatalogTest` pins the class on every control the form offers, red before the change. Browser read at 1280x800 and 390x844 on a seeded scratch database served on port 8245: all three controls read **44.00** at both viewports, root font size 16px, `box-sizing: border-box`, no horizontal overflow at either width, and the untouched `show_unconfirmed` checkbox read 13px as the canary that the instrument distinguishes sized from unsized. Closure waits on O-1.

**KI-32 (color-scheme). CLOSED 2026-10-02.** The fix shipped at `ed71741`, confirmed on `origin/master` by `git branch -r --contains`. The owed measurement, taken 2026-10-02 on `/skills` served from a seeded scratch database: with `prefers-color-scheme: dark` emulated, the layout's own matchMedia fallback applies `data-theme="dark"`, `getComputedStyle(document.documentElement).colorScheme` reads **dark**, and the native `unique` checkbox paints `rgb(36, 38, 42)` on two consecutive loads, identical both times, which is the exact property the entry said the defect broke. With `light` emulated, `colorScheme` reads **light**, no `data-theme`, and the checkbox paints `rgb(255, 255, 255)`, the correct light UA skin rather than a dark one leaking in.

**KI-45 (race catalogue population). Correction appended; the entry stays OPEN.** The headline, no offline population path, is superseded: `8b17703` landed the `seed_file` path, and `config/uma.php` carries `'seed_file' => 'race_instances.json'` for `gametora-race-catalog`. Re-derived 2026-10-02: `race_catalog_slots` holds **410 rows** on the shared database, and this pass ran `php artisan migrate --seed` against a scratch database, so the catalogue filled offline. The second gap stands and has **widened**: `scenario_slots.tier` was NULL on 141 of 296 rows at filing and reads NULL on **296 of 296** today (read-only query, 2026-10-02), so any tier-to-grade mapping now misses every row rather than 48 percent of them.

**KI-48 (architecture bound). CLOSED 2026-10-02.** The fix landed at `79d5f6f`, confirmed on `origin/master`. Both carriers carry the dated correction citing `ADR-0015` and `8bda7db`: `ARCHITECTURE-ESSENTIALS.md:36` and `ARCHITECTURE.md:158-161`, read 2026-10-02. `DocSchemaDriftTest` green, 5 passed, 12 assertions.

**KI-49 and KI-51 (untracked bodies, untracked seeders). Claims no longer reproduce; closures withheld.** All nine files under `database/seeders/data/` are tracked, the three seeder classes are tracked, and `DatabaseSeeder.php:28-30` invokes `UmamusumeRosterSeeder` and `SourceDocumentSeeder`. The whole fix landed in one commit, `8b17703`, which is **local only**: `git branch -r --contains 8b17703` does not list `origin/master`. This pass also ran `php artisan migrate --seed` offline on a scratch database end to end, so the pipelines these entries describe as unrunnable ran. Both closures wait on O-1, because the sha each would cite is not fetchable from `origin`.

**KI-53 (deleted fixture). CLOSED 2026-10-02.** Failed reproduction, recorded rather than silent: `database/seeders/data/race-tier-labels-2026-09-29.json` is tracked at `329cec1`, which is on `origin/master`, the working tree is clean at that path, and the three files the entry counted now run together **19 passed, 69 assertions** (2026-10-02). The deleted state the entry filed was a working-tree state that was never on any ref, so a reader on `origin` cannot reproduce it either.

**KI-54 (the held-aside collision). Failed reproduction proven; closure withheld.** The same 2026-10-02 run is green, and the fixture state that produced the warning abort cannot arise on any ref. What keeps this entry open is its fix: option (b), the `file_exists` guard, landed at `1bfbd33`, which is local only, and an `origin` checkout still carries the unguarded helper that can strand the fixture mid-run. Closure waits on O-1. The residual the entry also names, the destination collision when both files exist at once, is covered by neither the guard nor this pass and stays on the entry.

### KI-55 Two documents that `agents.md` cites are absent from the tree - FILED 2026-10-02 (M1 register sweep), OPEN

**Gap.** `agents.md` cites `docs/PRE-MORTEM.md` as the risk record (line 5: "Risk record: `docs/PRE-MORTEM.md` (§4 = repo #4 addendum)") and `docs/GATE-REGISTRY.md` as the gate runner reference twice (line 94, the fresh-DB command row "see GATE-REGISTRY C-5"; line 178, the documentation-precedence chain "`CONSTRAINTS.md` (the bar) > `docs/GATE-REGISTRY.md` (how each gate runs)"). Neither file exists: `ls docs/GATE-REGISTRY.md docs/PRE-MORTEM.md` returns no such file for both, verified 2026-10-02. The precedence chain at `agents.md:178` makes `GATE-REGISTRY.md` the second-highest authority in the repository, so a reader following it hits a dead pointer at the second link, and the C-5 reference in the fresh-DB row names a gate whose definition has no home.

**Why it matters.** The same shape as KI-52, a citation that looks resolvable and is not, with the addition that these two are cited by the file every agent reads first. The corpus consolidation moved the design corpus to `docs/research-scratch/`, so the likely history is that these two were moved, renamed or dropped in the same pass and the citations were not swept; that is conjecture and is marked as such.

**Fix options, owner's call.** Restore both files to the cited paths, or re-point the three `agents.md` citations at wherever their content now lives, or cut the citations. `agents.md` itself is the owner's file and was not edited here.

**Owner.** Docs Writer for the re-pointing; the owner decides restore versus re-point versus cut, since both names are load-bearing in the precedence chain.

**Related.** Peer commit `220ed67` ("docs(briefs): put the GATE-REGISTRY and PRE-MORTEM restore-or-repoint choice to the owner") raises the same two absent documents from the briefs side. This entry is the register side of the same question, filed so the dead citations are recorded where the precedence chain lives; the two artifacts should be resolved together.

### KI-56 `db:seed` is not re-runnable: the roster seeder re-queues candidates a unique index now rejects - FILED 2026-10-03, OPEN

**Gap.** `UmamusumeRosterSeeder` writes a `match_candidates` row for every trainee the source marks JapanOnly, unconditionally, on every run. `2026_10_01_124051_add_unique_index_to_match_candidates_table` added `match_candidates_source_external_match_unique` on `(source_key, external_ref)`, so a second run of the same body violates it. Measured 2026-10-03 on a scratch database, `php artisan migrate` then `php artisan db:seed --force` twice: the first pass seeds clean, the second throws `SQLSTATE[23000]: Integrity constraint violation: 19 UNIQUE constraint failed: index 'match_candidates_source_external_match_unique'`, the insert naming `gametora:char:1043` (Shinko Windy).

Running `php artisan db:seed --class=UmamusumeRosterSeeder --force` twice on that same database reproduces it alone, so the fault is in that seeder and not in the seeding order. Before the unique index the duplicate row was written silently, so the defect is older than the failure; the index turned a silent duplicate into a loud one.

**Why it matters.** `php artisan migrate --seed` and `db:seed` now abort on any database that already holds the queued candidates, and the three seeders after `UmamusumeRosterSeeder` in `DatabaseSeeder` (`SkillSeeder`, `SourceDocumentSeeder`, `ScenarioSlotSeeder`) do not run. A re-seed therefore leaves the catalogue as it was and exits non-zero. KI-45 and KI-49 both record a successful offline `migrate --seed`; that was a first run against an empty database, which is the case that still works.

**Fix options, owner's call.** Either make the roster seeder upsert its candidate rows on the source identity the way every store action does (`updateOrCreate` on `source_key` plus `external_ref`), or skip a candidate whose row already exists, or scope the queue write to a first run. The first is the shape the rest of the pipeline already uses and the smallest change.

**Not acted on here.** The 2026-10-03 skill-content slice found this while proving its own seeders idempotent (`SourceDocumentSeeder` run twice: `0 created, 1910 updated` for `gametora-skills`, `0 created, 106 updated` for `gametora-character-cards`). `UmamusumeRosterSeeder` is outside that slice's fence, so this is filed and not fixed.

**Owner.** Data Engineer, with the owner deciding the shape if the first option is not taken.

---

## Register snapshots, 2026-09-28 (five `.scratch-uma` states, consolidated 2026-10-06)

Five untracked whole-copy snapshots of the register above lived in `.scratch-uma/`: `ki_base.md`,
`ki_head.md`, `ki_s6.md`, `ki_s7.md` and `ki_s9.md`, each the register as it stood after one slice.
They were consolidated here on 2026-10-06. Three measurements decided what was embedded and what was
not, and they are recorded because a consolidation that silently drops a state is worse than one that
never ran:

1. **`ki_base.md` and `ki_head.md` are byte-identical** — sha256 `538b29cc3803`, 585 lines each. Two
   names for one Slice 5 snapshot, not two states.
2. **`ki_s9.md` is the Slice 8 snapshot.** Its own status header reads `2026-09-28, Slice 8`; only the
   filename says 9.
3. **The entry bodies were not re-embedded, because they are already above.** Measured per non-blank
   line against the register's KI-1 to KI-18, allowing the fold's one-level heading demotion (a
   snapshot's `## KI-n` is this register's `### KI-n`):

   | Snapshot | Non-blank body lines | Byte-exact | Contained as a promoted heading or a re-wrapped line | Substantively different |
   | --- | --- | --- | --- | --- |
   | `ki_base.md` and `ki_head.md` | 437 | 402 | 21 | 14 |
   | `ki_s6.md` | 437 | 402 | 21 | 14 |
   | `ki_s7.md` | 514 | 459 | 25 | 30 |
   | `ki_s9.md` | 548 | 489 | 25 | 34 |

   Every substantively different line is one of four things, and none of them is content this register
   lacks: **KI-11**, whose entry above was corrected forward and re-closed on Slice 12 evidence; a
   stale citation (`docs/scenarios/05-trackblazer-gametora.md` where the register now names
   `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`, and
   `docs/design-research/verification/slice-5-2026-09-28.md` where the register names
   `SLICE-RECORDS.md`); **KI-15's duplicate first variant** (reconciled below); or a heading whose
   state word the register later advanced, as KI-17 and KI-18 did — both are closed above and were
   OPEN in the snapshots.

What is unique to the snapshots is their **status headers**, and the register's own chain above carries
Slice 12, 11, 10, 8, 7 and an abridged Slice 6, with no Slice 9 block and with two fragments missing.
Both fragments are embedded verbatim here:

### The Slice 5 status block (`ki_base.md`, `ki_head.md`) — nowhere else in this file

**Status (2026-09-28, Slice 5):** 15 issues filed. **14 resolved/closed** (KI-1–9, KI-11–14).
**1 open** (KI-10: Trackblazer Grade Points placement ratio + year bucket). KI-11's
`--color-green-tint` half closed with the Safe band landing and being measured, so the
retire-and-amend clause of R23 does not trigger.

### The Slice 6 status block's first half (`ki_s6.md`) — the chain above keeps only its second half

**Status (2026-09-28, Slice 6):** 15 issues filed. **14 resolved/closed** (KI-1–9, KI-11–14).
**1 open** (KI-10: Trackblazer Grade Points placement ratio + year bucket). Slice 6 closed KI-14's
last residual as correct-by-design rather than fixing it (`aria-live` on a flow that navigates, so
there is no in-place change to announce), filed nothing, and moved no threshold. The two
`lore-code` hits that arrived with the concurrent session's review-queue work were ruled allowed
under C-4 and written into `docs/design-research/CONSTRAINTS.md` §3.2 with citations; neither was a
violation, so neither became an entry here.

### KI-15's duplicate, reconciled

`ki_s7.md` and `ki_s9.md` each carry **KI-15 twice**, the same entry filed with two different citation
sets: `docs/scenarios/05-trackblazer-gametora.md:22-24` with `app/Models/TrainingRun.php:322-334`,
then again as `:25` with `:346-355`. The register above carries the second variant and only that one. In
both snapshots the `:22-24` variant is filed first and the retained one beneath it, so the reading is that
the variant the register kept was pasted below the draft rather than replacing it, which is how a snapshot
comes to hold one entry twice. Both citation sets are stale now (the code reads
`app/Models/TrainingRun.php:378-385` today), so neither is a live pointer, and the entry's substance —
that no source names which Grade Point track applies — is identical in both.

---

## training-run-ux-review-2026-10-03.md

## Training Run UI/UX Review — 2026-10-03

Read-only review of `/training-runs/{run}`. Operand: WCAG 2.1 AA per R84 (2026-09-29).
The dispatch asked for WCAG 2.2 AA; that amendment is not on the record, so the review
proceeds under 2.1 AA and the WCAG-version gap is reported as a premise finding.
The five states were composed from the repo's own factories because no literal five-state
"R17 fixture" exists (see §3). Browser pass not executed (see §1); structural coverage
holds via `RunViewFrameTest` and `slice-5-2026-09-28.md`.

Filed untracked. Cite-or-replace by SHA. Owner disposition to be claimed in CHANGELOG.

**Operand note (added 2026-10-03): the 2.1 AA framing above is preserved as this record's operating scope. The operative mandate was amended to WCAG 2.2 Level AA on 2026-10-03 by the owner's ruling; see the amendments at PLANS-AND-BRIEFS.md, SLICE-RECORDS.md, and UX-DELIVERABLES.md. No finding in this record is changed.**

**Encoding note (added 2026-10-03): diagnosed with `file -i` (charset=utf-8) and a mojibake-pattern grep (0 matches). This file is correct UTF-8, no corruption, no rewrite performed. The mojibake sequences the upstream draft attributed to this file live elsewhere, e.g. SLICE-RECORDS.md:4418,4422.**

---

### 1. Skills invoked (and refusals)

Of the 22 skills the dispatch named for Phases 0–5, none were invoked through the Skill
tool here; this session exposes them only through `SKILL.md` (root) and the live scanner.
Discipline was applied directly from each skill's documented content. Posture mirrors
`skills-mechanics-audit-2026-10-01.md` §6 and `skills-section-phase-b2-2026-10-01.md` §13.

| Skill (named)                                                                 | Loaded             | Application                                                                                                                                   |
| ----------------------------------------------------------------------------- | ------------------ | --------------------------------------------------------------------------------------------------------------------------------------------- |
| using-agent-skills                                                            | no                 | Read `SKILL.md` and ran `scan-skills.cjs --names/--violations` (205 skills, 3 errors, 62 warnings)                                            |
| context-engineering                                                           | no                 | Recon: DESIGN.md §4.4, R84, KI-37/32/48 entries, ADR-0014, the run view, RunViewFrameTest, GuidedTurnOnRunViewTest, slice-5 verification      |
| constraint-driven-development                                                 | no                 | CONSTRAINTS.md C-4/C-7/C-8/C-11; G-18/G-19/G-46/G-SK-13                                                                                       |
| infer-conventions                                                             | no                 | `.ai/rules/*.md` plus shipped components                                                                                                      |
| planning-and-task-breakdown                                                   | no                 | This record is the plan                                                                                                                       |
| source-driven-development / careful / doubt-driven-development                | direct             | File:line citations; every premise verified; WCAG gap reported, not reconciled                                                                |
| design-review / impeccable / frontend-ui-engineering                          | no                 | Hierarchy / spacing / craft from DESIGN.md §2 and rule tests                                                                                  |
| antislop-ui / antislop-human / antislop-layoutmobile / antislop-copywriting   | no                 | Visual + focus + responsive + prose filters                                                                                                   |
| emil-design-eng                                                               | no                 | Second-pass craft                                                                                                                             |
| browser-testing-with-devtools (Phase 3)                                       | **NOT executed**   | Read-only fence + shared-DB rule; structural pin in `tests/Feature/RunViewFrameTest`, geometric pin in `verification/slice-5-2026-09-28.md`   |
| code-review-and-quality (Phase 4)                                             | no                 | Six-axis self-review                                                                                                                          |
| documentation-and-adrs (Phase 5 conditional)                                  | no                 | Not triggered                                                                                                                                 |

Fix-phase skills (TDD/debugging/incremental/git-workflow/testing-best-practices/laravel-best-practices/tailwindcss-development)
reserved for a separate dispatch.

Excluded groups from the dispatch's own §5 table were honoured without invocation: iOS,
deploy/ship, design generation, animation, meta/infra, typesafe/jev, specimen-collection.

---

### 2. WCAG mandate gap

Mandate: WCAG 2.1 AA, R84 (2026-09-29). Verified in PLANS-AND-BRIEFS.md:154,216,
DESIGN-CORPUS.md:1698 (D-10), SLICE-RECORDS.md:4422-4423, AUDIT-AND-VERIFICATION.md:3059,
UX-DELIVERABLES.md:2734. The 2.2 amendment is not on the record. Findings below are filed
against 2.1 AA only; §4.3 records what 2.2 framing would surface so the owner can elect
to amend R84 without the fix phase acting on bound-to-fail criteria.

---

### 3. R-2 resolution

R17 is referenced once: `tests/Feature/GuidedTurnOnRunViewTest.php:23` (the `guidedTurn()`
numeric shape, not a five-state file). Five states composed from existing helpers:

- Popular scenarios: `TrainingRun::factory()->create(['scenario' => $key])` with
  `ura_finale` / `unity_cup` / `trackblazer` from `config/scenarios.php`.
- No-scenario: `TrainingRun::factory()->create(['scenario' => null])` (route hides
  scenario-self panels; `run/show.blade.php:127`).
- Unpriceable: render with `GradePointMeter` `unpriced_count` / `unassigned_count` props set
  via factory state; the meter renders absence as absence (D-220).

Recorded as resolved premise, not a stop trigger.

**R-2 discrepancy (added 2026-10-03): the live browser pass found `/training-runs/5` carrying scenario `Our Grand Concert`, a scenario outside the five composed states above. Either the config carries more scenario keys than this record enumerated, or the route carries a scenario the factory path did not generate. Recorded as a discrepancy; not resolved here.**

---

### 4. Findings

#### 4.1 Operative-mandate findings (WCAG 2.1 AA)

| ID     | States            | File:line                                                                                        | Requirement                           | What is wrong                                                                                                                                                                                                                                                                                                                                     | Severity                                         | UX law (explanation)                                                      | Fix in scope?                                   |
| ------ | ----------------- | ------------------------------------------------------------------------------------------------ | ------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------ | ------------------------------------------------------------------------- | ----------------------------------------------- |
| F-01   | all               | `resources/views/runs/show.blade.php:580`                                                        | ADR-0007 / no-script rule             | The Delete Run form carries the only inline JS handler on the route: `onsubmit="return confirm(...)"`. Native confirm is keyboard-reachable; under WCAG 2.1 AA no criterion binds. The route otherwise runs zero script, so one surviving inline handler is the lone exception.                                                                   | Observation                                      | (anchor: ADR-0007, not a law)                                             | No — its own dispatch                           |
| F-02   | all               | `resources/views/runs/show.blade.php:70` and slice-5 verification                                | D-40/D-41/D-170                       | State region is `lg:sticky lg:top-0 lg:z-10 lg:py-3`; under WCAG 2.2 2.4.11 the focused stop in the log's scroll region could be obscured by the pinned strip. Operative 2.1 AA does not bind 2.4.11. Structural containment pinned by `RunViewFrameTest` (5 grade badges, ≥1 turn widget in state; 1 table / 1 radiogroup / 1 details in log).   | Observation under 2.1; Major under 2.2 framing   | Hick's Law (one decision per region)                                      | No — kinematic measurement, separate dispatch   |
| F-03   | all               | `resources/views/runs/show.blade.php:314-320 + 417-419`                                          | (observation)                         | Run fields omit `step="1"` on numeric inputs. Skill-row turn input (`show.blade.php:567`) does set `step="1"`. WCAG 2.1 AA does not mandate spin-buttons.                                                                                                                                                                                         | Observation                                      | Fitts's Law (stepper shortens distance)                                   | No                                              |
| F-04   | guided / all      | `resources/views/runs/show.blade.php:394-401`                                                    | 3.3.1 (A) / 3.3.3 (AA)                | `previewed` is a request-stage marker, not a field; its error renders top-line (`<p class="text-risk">`) while per-field errors render as a `<ul>`. Two error treatments on the same envelope; either both list items or the top-line carries a renaming (D-12: a state must carry its word).                                                     | Minor                                            | Zeigarnik (continue-on-error should not be confused with a stage state)   | No                                              |
| F-05   | skills repeater   | `resources/views/runs/show.blade.php:486-491 + 538/555/569`                                      | 2.4.7 (AA); KI-37 CLOSED 2026-10-01   | Four skill-row controls carry `h-11` and explicit `focus-visible:outline-green`. KI-37's measured pass holds.                                                                                                                                                                                                                                     | Confirmed pass (recorded, not a defect)          | Similarity (focus ring consistency in the row)                            | Not a finding                                   |
| F-06   | turn log          | `resources/views/runs/show.blade.php:245-284`                                                    | KI-25 OPEN under R85                  | Turn-log table wrapped in `overflow-x-auto role="region" tabindex="0" aria-label="Turn log"`. Same convention as `race-calendar`. KI-25 reopened by R85 with the 390px measurement unverified.                                                                                                                                                    | Observation (KI-25 already open elsewhere)       | Common Region (focusable region is the chunk)                             | Not in this dispatch                            |
| F-07   | guided            | `resources/views/runs/show.blade.php:299-392` + GuidedTurnOnRunViewTest + GuidedTurnStagesTest   | (positive confirmation)               | The two-stage POST (stage=preview, stage=confirm) gates confirm on a `previewed` hidden input; `old()` rehydration across the form. D-51 enforced server-side, no script.                                                                                                                                                                         | Confirmed pass                                   | Doherty (response on the second click, gated by response shape)           | Not a finding                                   |

#### 4.2 Cross-references resolved

- **KI-37** (run-screen 31/31/30/32): `KNOWN-ISSUUES.md` reads `CLOSED 2026-10-01`. Measurement-2 record
  `44/44/44/44 + canary 31` holds for the four skill-row controls (`show.blade.php:538, 555, 569, 577`).
- **KI-32** (`color-scheme`): CLOSED at `ed71741`; per-theme declaration present in `app.css`.
- **KI-48** (architecture docs bound): CLOSED at `79d5f6f`; not re-opened by this review.
- **KI-43** (deck panel 88.5% of page markup on empty deck): mentions on `deck-panel.blade.php` only.
  Not re-measured; the dispatch's non-goals exclude it from this surface's review.
- **D-40 / D-41 / D-170** (regions and persistence): shape pinned in `RunViewFrameTest`; the file's
  own header comment records the 613px → stripped-to-strip-and-band measurement in
  `verification/slice-5-2026-09-28.md`.

#### 4.3 2.2-only criteria — reported but not enforced under operative mandate

| Criterion                                   | Status under 2.1 AA                                  | 2.2 framing                                                                                                     |
| ------------------------------------------- | ---------------------------------------------------- | --------------------------------------------------------------------------------------------------------------- |
| 2.4.11 Focus Not Obscured (Minimum)         | Not bound                                            | Would surface F-02 as Major                                                                                     |
| 2.5.7 Dragging Movements                    | Not bound                                            | N/A confirmed (no `draggable` / `ondrag` reference in `resources/views/` or `tests/Feature/`)                   |
| 2.5.8 Target Size (Minimum, 24×24)          | Not bound (R84 ruled 24×24 not an obligation here)   | Run controls measure 31/31/31/30/32/40; already clear 24px and 44px on the four skill rows                      |
| 3.2.6 Consistent Help                       | Not bound (A in 2.2)                                 | Run view has no inline help; the "Search the skill catalog" link is consistent across the routes that ship it   |
| 3.3.7 Redundant Entry                       | Not bound (A in 2.2)                                 | Two-stage POST rehydrates via `old()`; no re-typing across stages                                               |
| 3.3.8 Accessible Authentication (Minimum)   | Not bound (PRD §6.1 bans auth)                       | N/A by design                                                                                                   |
| 4.1.1 Parsing                               | Obsolescent in 2.1; removed in 2.2                   | Not relevant                                                                                                    |

**Supersession note (added 2026-10-03): static F-02 (sticky obscurement) is superseded by live L-F02's measured pass at 1280px viewport (`training-run-live-2026-10-03.md` §2). The pinned state region never obtains a fully obscured focus stop. This section's 2.2-framing output stands as history; F-02 is no longer a live finding.**

---

### 5. Premise check — the dispatch statements falsified against the tree

| Dispatch statement                     | Tree ground truth                                                                                                                                                                                                               | Action                                                                                                                              |
| -------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| Operative mandate: WCAG 2.2 Level AA   | Mandate is WCAG 2.1 AA per R84 (2026-09-29); the 2.2 amendment is not on the record — PLANS-AND-BRIEFS.md:154,216; DESIGN-CORPUS.md:1698; SLICE-RECORDS.md:4422-4423; AUDIT-AND-VERIFICATION.md:3059; UX-DELIVERABLES.md:2734   | Proceed under 2.1 AA; report gap; do not self-elevate                                                                               |
| R17 fixture = five-state dataset       | R17 is referenced once in tests/ as the numeric shape of `guidedTurn()` in GuidedTurnOnRunViewTest.php:23; no five-state fixture file exists                                                                                    | Composed the five states from `TrainingRun::factory()` + scenario keys + `scenario=null` + `GradePointMeter` unpriced_count props   |
| Browser pass required in Phase 3       | Pest browser pass at five states would seed real runs against a non-test store; the fence is read-only review and the shared `database/database.sqlite` is read-only                                                            | Declined; recorded structural pin (RunViewFrameTest) and geometric pin (verification/slice-5-2026-09-28.md) instead                 |
| ADR-0014 "collection half cut"         | ADR-0014 at docs/adr/0014-support-card-entities.md; amendment chain in docs/adr/SUPERSEDED-feat-catalog-detail-page.md                                                                                                          | Verified, did not file (out of scope per the dispatch's own non-goal list)                                                          |
| KI-29 / KI-46 within scope             | KI-29 covers /umamusume; KI-46 is on the import surface                                                                                                                                                                         | Confirmed out of scope                                                                                                              |

---

### 6. What the agent could not verify

- Live browser pass against the five composed run states (reasons in §1).
- `Skill` tool invocations for the 22 named Phase 0–5 skills (active roster exposed through documentation only; discipline applied directly per the dispatch's own instruction).
- A re-measurement of KI-43 page-weight — dispatch's own non-goal list excludes it.
- Route behaviour under malformed `previewed` requests — existing `GuidedTurnOnRunViewTest` / `GuidedTurnValidationTest` already pin the validation shape; this review did not retest.

---

### 7. Skills that did not resolve

The session exposes the global skill scope through `SKILL.md` and the `scan-skills.cjs` roster only; Phase 0–5 skills were not loaded through the Skill tool here. Per the dispatch's own instruction "if any named skill is absent from the session's roster, say so and proceed with the discipline applied directly," this is the right posture: no silent substitution, discipline applied directly. Excluded groups (iOS, deploy/ship, design generation, animation, meta/infra, typesafe/jev, specimen) were honoured as exclusions, not as oversights.

---

### 8. Proposed fix sequence (separate fix-phase dispatch)

Each fix is scoped to one file, one purpose, one commit. The fix phase is not in this dispatch.

1. **F-02 (only if owner adopts 2.2)**: `resources/views/runs/show.blade.php`. Replace `lg:sticky` on the state region with a measurement-bounded sticky behaviour; test gate is a Pest assertion that the focused `<div role="region" tabindex="0">` parent in the log region has a top offset that clears the state region's sticky box at viewport widths 1280 / 1024 / 768 / 390.

   **Withdrawn (added 2026-10-03): this fix is withdrawn. Live L-F02 measured focus behaviour at 1280px and every work-region control lands below the pinned strip; no obscurement occurs.**
2. **F-04**: `resources/views/runs/show.blade.php` lines 394-401. Unify the error envelope: the `previewed` top-line error becomes a list item under the existing `<ul>` that already carries the per-field errors.
3. **F-01**: `resources/views/runs/show.blade.php` line 580. Replace `onsubmit="return confirm(...)"` with a pre-submit GET to a confirmation route that returns the run view with a `<details>` disclosure the Trainer confirms. Net effect: zero JS on the run detail route.

The shared worktree's peer-dirty inventory at write time (~20 peer-modified files plus an untracked `tools/__pycache__/`) means a fix-phase commit must use an explicit pathspec and verify against `git diff --cached --name-status` before signing off. KI-29 and KI-46 stay separate dispatches.

---

### 9. Citation keys (SHAs)

- `8985575` — eight-component library committed unwired (this repo rejected the brief's CSS values; documented at DESIGN-CORPUS.md:3308)
- `ed71741` — KI-32 closeout (`color-scheme`)
- `c17e63b` — KI-37 first fix (44px control pass on the skills repeater)
- `79d5f6f` — KI-48 closeout (architecture docs bound)
- `8b17703` — slice seed_file (downstream of slice-12 work)
- `4ab5ada` (cypress-cardamom detached HEAD archive) — five triaged UX write-ups as a labelled set
- `3dc8579` — D-30 amendment and ADR-0014 erratum landing (HEAD at write time)
- `5b21582` — HEAD, 31 ahead / 0 behind `origin/master` (O-1 push-gate premise has changed since the v1.1 plan was written)

Filed untracked 2026-10-03. Owner disposition pending.

---

## training-run-live-2026-10-03.md

## Training Run UI/UX Live Review (browser-interaction pass), 2026-10-03

Live browser pass against six routes under WCAG 2.2 Level AA and the UX-law lens,
companion to the static record at
`docs/research-scratch/training-run-ux-review-2026-10-03.md` (which stands under the
R84 2.1 AA mandate as its operating scope). This pass **actually drives the controls**
through Playwright (focus walk, real `Tab`, combobox typing, `details` click,
form-introspection), not markup arithmetic.

Operand note: this pass operated under 2.2 AA at the owner's request. The mandate was
subsequently amended to 2.2 AA on 2026-10-03 by the owner's ruling, so the 2.2-only
criteria reported here are now the operative mandate, not a forward-looking framing.

Method: viewport 1280x900 (the `lg:sticky` breakpoint is 1024, so 958px tests the
sticky does not engage and were discarded as meaningless; all geometry below is
re-measured at 1280). Dev server `http://127.0.0.1:8000`. Shared `database/database.sqlite`
untouched: no form was submitted; interactions were limited to focus, typeahead,
and the client-only `details` toggle.

---

### 1. Routes and states reached

| URL                       | Title                | Scenario state                          | Sticky pinned?   |
| ------------------------- | -------------------- | --------------------------------------- | ---------------- |
| `/training-runs/1`        | Run: Daiwa Scarlet   | populated (URA-class strip + band)      | yes, 156px       |
| `/training-runs/2`        | Run: Daiwa Scarlet   | baseline strip, no logged turns         | yes              |
| `/training-runs/3`        | Run: Daiwa Scarlet   | baseline strip                          | yes              |
| `/training-runs/4`        | Run: Daiwa Scarlet   | baseline strip                          | yes              |
| `/training-runs/5`        | Run: Daiwa Scarlet   | **Our Grand Concert**, empty turn log   | yes              |
| `/training-runs/create`   | New training run     | (form)                                  | n/a              |

All six return 200 with zero console errors (`browser_console_messages level=error`
returned 0 on the run routes visited).

---

### 2. WCAG 2.2 AA results

#### L-F01 (MAJOR, binds under 2.2; R84 exempts under 2.1), SC 2.5.8 Target Size (Minimum)

A focus walk of every visible interactive control at 1280x900 measured these below the
24x24 CSS-px floor, with the spacing exception not met (neighbours are 0px apart in the
nav row):

| Control                                                          | Measured     | Count of fails   |
| ---------------------------------------------------------------- | ------------ | ---------------- |
| Nav: Catalog / Skills / Support cards / Training runs / Review   | h=**20**     | 5                |
| Export CSV / Export JSON (run pages)                             | h=**20**     | 2                |
| "Search the skill catalog" helper link (run pages)               | **121x16**   | 1                |
| `<summary>` "Correct a turn by hand"                             | h=**20**     | 1                |

Skip-to-content measures 137x40 on focus (passes). The 1x1 guided-step radios are the
intentional zero-size semantic layer behind the banner label (the label is the 44px
target); not a 2.5.8 fail. The 60 form/select/textarea controls in the work region all
measure h=30 or h=44 and pass.

WCAG 2.5.8 exceptions checked: not Essential, not in a sentence (nav is a bar, not
inline), not user-agent-determined, no equivalent larger control on the same row. Fix
is `min-h-11` on the nav links, export links, and the helper `<a>` (same idiom KI-37
used). **UX law (explanation): Fitts's Law**, the horizontal-only width compensation does
not rescue a 16-20px height on a touch target.

#### L-F02 (PASS), SC 2.4.11 Focus Not Obscured (Minimum)

The review callout expected this to be the most likely new finding. It **passes**. With
the `lg:sticky` "Run state" region pinned at `top:0, bottom:265px` (measured,
position:sticky active), focusing each of the 60 work-region controls in turn leaves
every one below the sticky bottom (first sample: scenario select top=322 vs sticky
bottom=265). Chrome's scroll-on-focus never lands a control entirely inside the pinned
band. **Premise correction:** the earlier 958px measurement showed `position: static`
(sticky off below 1024px), so a naive 2.4.11 test at 958px is a check that cannot fail
for the wrong reason; the 1280px pass is the meaningful one. **UX law: Common Region**
holds, the pinned strip never hides what you are tabbing to.

#### L-F03 (PASS), SC 2.4.7 Focus Visible

A real `browser_press_key Tab` (not `.focus()`) moves to the skip link and reports
`:focus-visible` true with `outline: 2px solid rgb(78,121,6)` (the `--color-ring` value,
D-258's theme-split ring). Confirmed keyboard-driven, not script-driven.

#### L-F04 (PASS), SC 3.2.2 On Input

Typing "to" into the trainee combobox sets `aria-expanded=true` and filters the listbox
to 3 costume matches in place. No context change, no auto-submit. **Hick's Law** served:
the 623-row picker is narrowed without a page transition (KI-33 / G-SK-13 lens).

#### L-F05 (PASS, corrects a would-be false finding), create form integrity

The required `umamusume_id` `<select>` renders `disabled`, which reads like a no-JS
dead-end. Introspection shows the real submittable field is a hidden `umamusume_id`
`<input>` plus a hidden `character_card_id` the combobox writes; the disabled select is
the noscript fallback. **Progressive enhancement, not a defect.** Recorded so the next
reader does not re-file it.

#### L-F06 (N/A confirmed), SC 2.5.7 Dragging Movements

`[draggable=true]` count is 0 on all six routes and no slider/drag affordance is present.

#### L-F07 (PASS), SC 4.1.2 Name, Role, Value (combobox)

Trainee combobox: `role=combobox`, `aria-autocomplete=list`, `aria-controls=trainee-listbox`,
`aria-label` set, listbox `role=listbox` present. Proper APG pattern.

---

### 3. UX-law lens (driven, not asserted)

| Law                                        | Result                              | Evidence                                                     |
| ------------------------------------------ | ----------------------------------- | ------------------------------------------------------------ |
| Fitts's Law                                | Fails at 2.2 for nav/export links   | L-F01                                                        |
| Hick's Law                                 | Holds                               | L-F04 combobox narrows 623 -> filtered                       |
| Common Region                              | Holds                               | L-F02 sticky never obscures                                  |
| Zeigarnik                                  | Holds                               | Suggested/Acquired/Skipped groups render on `/1`             |
| Von Restorff                               | Holds                               | one primary enamel control per surface                       |
| Jakob's / Prägnanz / Aesthetic-Usability   | Not invoked as anchors              | DESIGN.md §2.1, principle 4, §7.1 override per dispatch §4   |

---

### 4. Premises in the request that the tree falsified

1. **"Test all interactable elements" against a shared read-only DB.** A literal full
   pass submits forms. Submission was withheld; focus/typeahead/toggle were driven
   instead. The two-stage guided-step POST rehydration (SC 3.3.7 Redundant Entry) is
   therefore confirmed at the code and test level only, not clicked live.
2. **The static record's F-02 (sticky obscurement) was left "arithmetic passes."**
   The live 1280px run, L-F02, converts it from a hedge to a measured pass, and the
   static fix is withdrawn accordingly.
3. **The 958px viewport used in the first live pass is below the sticky breakpoint**, so
   any "sticky covers focus" or "sticky never covers focus" reading at 958px is void;
   superseded by the 1280px run above.

### 5. Not verified, and why

- SC 3.3.7 Redundant Entry under a real rejected submit (needs a write; fence forbids).
- Keyboard arrow traversal inside the turn-log `role=region tabindex=0` at 390px
  (KI-25 is separately OPEN; R82 768px floor is unratified).
- Live contrast (SC 1.4.3 / 1.4.11): G-18's browser gate is still `markTestIncomplete`
  per the token test file; not re-derived here.

### 6. Fix sequence if authorized (one file each)

1. `resources/views/components/layout.blade.php`: `min-h-11` (or padding to 24px) on the
   five nav links -> L-F01, nav half.
2. `resources/views/runs/show.blade.php:46-47` export links and `:469` helper `<a>`:
   same floor -> L-F01, run-page half.
3. Optional: `runs/show.blade.php:407` `<summary>` min-height -> L-F01, details row.

L-F02 through L-F07 need no fix; they pass. KI-29 (catalog) and KI-46 (import copy) are
out of this surface and stay separate.

Filed untracked 2026-10-03. Owner disposition pending.

---

## UIX-AUDIT-TRAINING-RUNS.md

## UI/UX audit: training-run creation and the run page

**Method.** Hands-on. Every finding below was produced by driving the running app in a browser
(Playwright MCP, Chromium, 1280-wide viewport, `http://127.0.0.1:8000`) rather than by reading source
first. Source is read only to confirm a cause after an effect is observed. Evidence references are the
accessibility-tree snapshots and the DOM measurements taken during the pass.

**Design contract.** `DESIGN.md`. No `SCREEN_SPEC.md` and no `.design-qa/` artifacts exist in this repo,
so there is no per-screen layout target to compare against; visual-fidelity claims are limited to token
use, states and copy, which `DESIGN.md` does govern.

**Data entered.** A real run from `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md`: Rice Shower
[Rosy Dreams], Unity Cup, Active, notes citing the report. Created as run 7.

**Verdict.** Held until Part 2 completes. Severity vocabulary is `blocker`, `major`, `minor`, `debt`,
`info`, `needs-design-decision`.

---

### Part 1: `/training-runs/create`

The page renders a single form with four controls: a trainee/costume-card picker, a scenario select, a
status select and a notes textarea. It loads clean: no console errors, a "Skip to content" link, one `h1`
and a five-link nav.

#### C-1 · major · the trainee picker does not clear its own text, so a second attempt types into the first

- **Evidence.** After committing a selection, the input read `Rice Shower · [Vampire Makeover!]`. Typing
  `Rosy` into it produced the value `Rice Shower · [Vampire Makeover!]Rosy`, the listbox emptied, and the
  status line read `No trainee or card found.` Only a replacing fill (select-all, or clearing the field)
  recovers.
- **Observed.** The committed label is left in the input as editable text with the caret at the end.
- **Expected.** Either select-all the text on focus, or clear on the first keystroke after a commit, so a
  Trainer who picks the wrong card and starts retyping lands on a filtered list instead of a no-match.
- **Impact.** The obvious recovery gesture, typing again, is the one that fails. The message that appears
  ("No trainee or card found") describes a search problem, not the actual state, so the Trainer is likely
  to keep retyping. This is the first screen of the primary flow and the picker is the only hard
  requirement to get through it.
- **Fix.** In `resources/js/trainee-combobox.ts`, on `focus` (or on the first `beforeinput` after a
  commit) call `select()` on the input, which is the standard combobox affordance and costs one line.
- **Verification.** Re-run: commit a card, type a new query without clearing, confirm the list filters.

**Fixed 2026-10-03, and the fix was not the one proposed here.** The focus handler already called
`input.select()` (`trainee-combobox.ts:522`), so the line this finding asked for was in the file before
the audit ran. The gap is a path focus does not cover: Enter commits while the field keeps focus, so no
`focus` event ever fires again and the next keystroke appends. `commit()` now selects the label it just
wrote. Measured on the live page: type `Rice Shower`, Enter, then press `g` once. The field reads `g`,
the status line reads `9 matches`, and both hidden inputs are empty (`""`/`""`), which is the correct
statement about a half-typed query. Before the change that same gesture produced the label plus `g` and
`No trainee or card found.`

The related data-integrity risk is **not** present and was tested: when the text stops matching, both
hidden inputs (`umamusume_id`, `character_card_id`) are emptied rather than left pointing at the previous
pick, so a submit in the broken state fails `required` instead of silently creating the wrong run.

#### C-2 · major · required state is carried by a control the Trainer never uses

- **Evidence.** The fallback `<select name="umamusume_id">` has `required`. The combobox input the script
  enables in its place has no `required` and no `aria-required`; the a11y tree shows it as
  `combobox "Trainee or costume card name"` with no required state. No field on the form shows a visual
  required marker, while two optional fields do carry one in their labels: "Scenario (optional)" and
  "Notes (optional)".
- **Observed.** With JavaScript on, which is the path the form is built for, nothing announces or shows
  that the trainee is mandatory. Status is also required and equally unmarked.
- **Expected.** `aria-required="true"` on the live control, and the same optional/required convention on
  all four labels.
- **Impact.** Screen-reader users get no cue before a failed submit, and sighted users get an asymmetric
  convention: the form tells you what is optional but not what is mandatory. WCAG 3.3.2 territory.
- **Fix.** Mirror `required` onto the input when the script swaps the pair, and pick one marker
  convention for the form.
- **Verification.** Re-snapshot with the script enabled and confirm the combobox exposes required.

**Fixed 2026-10-03 with `aria-required="true"` on the input, not `required`.** The value that posts is the
hidden pair, so `required` on the text field would gate on the label the Trainer sees typed, which is a
different string from the one submitted, and a hidden-but-rendered control still blocks the submit.
Re-measured in the accessibility tree: `combobox "Trainee or costume card name" ... required settable`.
The visible convention is left as it was, marking the optional fields, because that is already one
coherent convention and Status carries a native `required` of its own; adding a second marker to the two
mandatory fields would state the same fact twice.

**Corrected the same day, from a sibling form.** The paragraph above states the convention as if the app
had only one. It does not: `resources/views/runs/import.blade.php` ships "Trainee *", and the run page's
hand-correction form ships "Turn*" and "<Stat> *". `*` is this repository's own required marker, already
in use for the same entity, so the create captions are now "Trainee*" and "Status *" while "(optional)"
stays on the two fields that mark it. One rule now covers all four fields: `*` marks what has no answer of
its own, `(optional)` marks what does.

The marker does interact with WCAG 2.5.3 on the combobox, since the caption reads "Trainee *" while the
explicit `aria-label` stays "Trainee or costume card name". The words are still a prefix and `aria-required`
carries the state, so the pin in `TraineeSelectorTest` compares against the caption with its marker
stripped (`str_starts_with($name, rtrim($caption, ' *'))`); no speech-input user says "Trainee asterisk".
The alternative, the glyph in its own `aria-hidden` span, is what the run page's asterisks would need too,
and that file is held.

#### C-3 · minor · the visible caption and the accessible name of the same field disagree

- **Evidence.** The sighted label text is "Umamusume". The live control's accessible name is
  "Trainee or costume card name" (explicit `aria-label`, which wins over the moved `for`).
- **Impact.** Low for a screen reader alone, but a label-vs-name mismatch breaks speech-input users who
  say the visible caption, and it makes the two paths describe one field differently in docs and tickets.
- **Fix.** One name for both: make the visible caption "Trainee or costume card" or set the `aria-label`
  to the caption and move the specificity into a hint below the field.

**Fixed 2026-10-03, on neither of the two options above.** The caption is now "Trainee". "Trainee or
costume card" (the first option) would sit over the no-script `<select>` too, and that control lists
trainees and nothing else, so the caption would promise a card search on the path that cannot do it.
"Trainee" is true on both paths, it is the word the field's own copy already uses ("No trainee or card
found", "Trainees and costume cards"), and it is a prefix of the accessible name, which is what WCAG
2.5.3 asks for and what lets a speech-input user say the words on screen. The `aria-label` is unchanged,
so the pin at `TraineeSelectorTest` naming that string still holds. Re-measured: `StaticText "Trainee"`
beside `combobox "Trainee or costume card name"`.

#### C-4 · minor · a non-option text node sits inside `role="listbox"`

- **Evidence.** With the list open, the accessibility tree reads:
  `listbox "Trainees and costume cards"` → `text: Rice Shower ライスシャワー` → `option "Rice Shower · [Rosy Dreams] debut"`.
  The header is presentation by design (`trainee-combobox.ts:300-305` says so in the comment above the
  `aria-label`), and the option's own accessible text already carries the trainee name.
- **Impact.** A listbox's owned elements should be options; a bare text child is inconsistent across
  screen readers, and here it duplicates information the option already announces.
- **Fix.** `aria-hidden="true"` on the header row, or move it outside the listbox as a caption.

**Fixed 2026-10-03, on both presentation row kinds.** `aria-hidden="true"` now goes on the per-trainee
header and on the band divider, which is the same shape: a sentence inside the listbox that the option
under it already states in its own title ("No costume card confirmed yet"). Read off the live DOM, with
the query `g` open: 15 children, the six presentation rows each carry `aria-hidden="true"`, the nine
options carry none.

This one is verified at the DOM level only. The instrument that produced the original evidence was the
Playwright accessibility tree, and that browser session is busy with the activity-label research pass. The
session used here (browser-use) serialises this ARIA listbox with **no children at all**, which it still
did after every `aria-hidden` was stripped back out in the live page, so it can neither show the defect
nor show it gone. A tree-level re-read is still owed on the Playwright engine.

#### C-5 · debt · the request validates four fields the create form never sends

- **Evidence.** `StoreTrainingRunRequest::rules()` covers `inheritance_parent_a_id`,
  `inheritance_parent_b_id`, `current_objective_index` and `shop_resets_in`. `create.blade.php` renders
  none of them.
- **Impact.** Two things. The store path carries validation that cannot be exercised from the surface it
  guards, which is how a rule stays untested. And a Trainer starting a run has no signal that ancestry,
  objective period or shop countdown exist as run data until they find them on the detail page.
- **Fix.** Either put the fields on the form (ancestry is known at the start of a run, which is exactly
  when it is entered in the game) or drop them from the store-time rule set and keep them on update.
  This is a design decision, not a bug: **needs-design-decision**.

#### Checked, no finding

- The keyboard path works: ArrowDown moves the highlight, Enter commits, `aria-expanded` closes, and the
  live region announces `Selected Rice Shower · [Vampire Makeover!]`.
- The first match is pre-highlighted, so Enter on a filtered list picks it without navigation.
- Match count is announced (`2 matches`, `1 match`, `No trainee or card found.`).
- The epithet and the date inside an option are separated by `ml-2` in the rendered row, and the option
  carries an explicit spaced `aria-label`; the run-together visible in raw `textContent` is not a defect.
- Scenario options come from the composition matrix rather than free text, and "Not set (baseline strip)"
  states what the empty choice does.
- No `{!! !!}` on catalog data, no `dark:` utilities, no off-token colors in the form's classes.

---

### Part 2: `/training-runs/7`, the run page

Created as run 7 from the form above. Populated hands-on: the six-card deck, five acquired skills, and
turn 67 carrying the stat line from the report (607 / 577 / 549 / 736 / 397, SP 173, mood GREAT).

#### R-1 · major · the page is 5,284 DOM nodes and almost all of them are unfilterable options

- **Evidence.** Measured after load: 5,284 elements, 9 forms, 47 inputs, 22 selects. Six deck selects
  carry **252 options each** (1,512 nodes) and five skill rows carry **624 options each** (3,120 nodes).
  Option elements are roughly 99% of the page. The three-year race calendar is also rendered inline in
  full, with every candidate race and its fan threshold, above the fold content the Trainer came for.
- **Observed.** To set one deck slot a Trainer opens a 252-row native list and scrolls; to add one skill,
  a 624-row list. Browser typeahead is the only search.
- **Expected.** The pattern already exists in this codebase: the create page's trainee combobox filters
  as you type, announces match counts and works by keyboard. It is used for one field in the app and for
  none of the twelve pickers on the run page.
- **Impact.** The page's two most-used controls are its slowest and hardest to use, and the DOM cost is
  paid on every visit. This is the single highest-value change in the audit.
- **Fix.** Extract the combobox into a shared component and use it for deck slots and skill rows, with
  the option list filtered server-side or from the existing roster JSON rather than emitted as 624
  `<option>` elements per row.
- **Verification.** Re-measure node count after the swap; the page should drop by an order of magnitude.

#### R-2 · major · the Unity Cup numbers the scenario turns on have no input anywhere

- **Evidence.** The Resources strip renders `TEAM RANK N/A not yet recorded` and
  `SPIRIT BURSTS N/A not yet recorded`. Across all nine forms on the page the field names are:
  `choice, circles, condition, deck[n][support_card_id], energy, entry_mode, fans, guts, mood, outcome,
  penalty_kind, placement, power, race_catalog_slot_id, scenario, skills[n][...], sp, speed, stamina,
  status, turn, umamusume_id, wit, year`. There is no team-rank, burst-count, league-placement or
  team-grade field.
- **Observed.** The strip displays a value the app can never fill.
- **Impact.** For Unity Cup these are not decoration. The documented run's burst count (6 normal plus 5
  Extreme) is what decides whether the November event pays the white or the gold scenario skill, and
  team rank S is one of three Elite Team gates. A Trainer planning in this tool cannot record the inputs
  to the scenario's own decisions.
- **Fix.** Either add the fields to the run update form, or drop the two cells from the strip until they
  can be set. Showing `N/A not yet recorded` for a value with no entry point reads as "I forgot to fill
  this in", which sends the Trainer looking for a control that does not exist.

#### R-3 · major · caps render as the scenario floor, so the band contradicts the client

- **Evidence.** After saving turn 67 the Stats band reads `SPEED A 607 / 1,300`. The client's own screen
  for this run shows `607 /1325`, and the other four are 1322, 1368, 1308 and 1800.
- **Cause.** The denominator is the scenario base cap. The overages from inheritance breakthroughs and
  whatever else raises a cap have nowhere to be entered, so the tool states a ceiling the game does not.
- **Impact.** A Trainer comparing screen to tool sees a mismatch on every stat and concludes one of them
  is wrong. It also hides the real headroom, which is the number the plan depends on.
- **Fix.** Let the turn or the run carry a per-stat cap override, defaulting to the scenario base, and
  label the source the same way the report does. **needs-design-decision** on whether the cap is
  run-level or per-turn.

#### R-4 · major · the skill picker lets a one-glyph-different name win, and the wrong pick saves cleanly

- **Evidence.** The catalog holds both `Corner Adept ○ · 180 SP` (export 200332) and
  `Corner Adept × · 100 SP` (export 200333); 39 skills carry `×`, 69 carry `○`, 65 carry `◎`. Typing
  "Corner Adept" in a 624-row list and taking the first hit recorded the `×` variant. The save accepted
  it with no warning, and the Acquired list then displayed the wrong skill with full confidence.
- **Impact.** This is the failure mode of the tier glyph: three near-identical names differing by one
  character, in an unfilterable list, with no confirmation step. It was caught here only because the
  source data was checked afterwards.
- **Fix.** Group or label tiers in the option text (`Corner Adept (○) · 180 SP`), and sort the family
  together so the variants are adjacent rather than scattered across 624 rows.

#### R-5 · major · no save on this page confirms itself

- **Evidence.** After "Save deck", after "Save skill status", and after the turn commit, the page
  re-renders with no flash and no status element. The only text near the top is the run header and the
  notes.
- **Impact.** With nine independent forms on one page, the Trainer cannot tell which save landed, and a
  silently rejected submission is indistinguishable from a successful one. The turn table and the
  Resources counter do update, which is the only feedback that exists, and for the deck save there is no
  such visible change at all if the selection was already showing.
- **Fix.** One flash partial reused by all nine redirects, naming what was saved.

#### R-6 · minor · internal review markers and stale disclaimers are shipped as UI copy

- **Evidence.** The activity choice labelled "Mood adjustment" renders with the literal text
  `[Unverified]` in its on-page description: "Mood adjustment [Unverified] Raises Mood. The client label
  is not verified." Separately, the skills help reads "Skill-point discounts from hint levels are not
  shown: no source in this repository settles the per-level reduction."
- **Impact.** The first is an editorial state marker a Trainer cannot act on, and it advertises that the
  control's own name is a guess. The second is now false: the per-level ladder was settled from the
  client on 2026-10-03 and recorded in `docs/research-scratch/SKILLS-MECHANICS.md` §2.4 and
  `docs/UMAMUSUME_REFERENCE.md` §1.1.4 (10 / 20 / 30 / 35 / 40 percent at Lv1 through `Lv Max`). The
  picker could show discounted prices and currently states the opposite.
- **Fix.** Move `[Unverified]` to a build-time note or a tooltip on the source, not the label. Update the
  discount sentence, or better, use the ladder.

#### R-7 · major · the race calendar cannot record this scenario's races

- **Evidence.** The calendar race picker offers 58 slots and none of them is the Kyoto Daishoten or the
  Arima Kinen. Queried at the data layer: `race_catalog_slots` holds 410 rows and matches zero on
  `%Daishoten%` or `%Arima%`.
- **Impact.** Both of this run's remaining races, including the goal race, are unrecordable through the
  "Calendar race" path, so the "+" markers on the calendar stay empty and the race must be entered as
  "not on the calendar", which loses the slot link the rest of the page is built around.
- **Fix.** Seed the Unity Cup slot set, or fall back to the free-text path with a visible reason rather
  than an empty list.

#### R-8 · debt · two forms post to `/turns` with two controls both labelled "Add turn"

- **Evidence.** `form[action$="/turns"]` resolves to two forms: the guided one, and a manual-correction
  one inside `<details><summary>Correct a turn by hand</summary>`. Both contain a submit labelled
  "Add turn". A selector that does not exclude the disclosure resolves to the hidden one and its click
  never lands; the audit pass hit exactly that before submitting the visible form directly.
- **Impact.** For a human the disclosure label disambiguates, so this is not a blocker. It is a
  testability and automation hazard, and the duplicate label is worth renaming ("Add turn" versus
  "Save correction").

#### Checked, no finding

- The turn flow is a keyboard-guided stepper: "Step 2 of 5 · Choose activity" with the instruction
  "Keys 1 to 7 choose an activity, arrow keys move between them, Enter previews the turn, Escape returns
  to the choices." The commit control is deliberately withheld until preview, which is why it reads as
  absent. Good design, and my R-8 note is about the duplicate label only.
- Activity choices carry the mechanics in their labels: "Wit Costs no Energy, so it stays available when
  the bar is low", "Rest Returns about +30 Energy, and a rest can backfire into a stayed-up-late
  penalty". These match `docs/UMAMUSUME_REFERENCE.md` §1.1.1 and §1.1.6.
- The `N/A` plus reason rule is honoured throughout the strip, with no em dash and no zero default.
- Deck slot 6 is labelled "Slot 6 · Friends", which matches the client's friend slot exactly. The deck
  form records the card only: no level, limit break, bond or hint level, so the run's card state from
  §1.4 of the report has no home. That is a scope question, not a defect.
- "Delete run" sits behind a `<details>` disclosure with a separate confirm button, not beside the
  everyday actions as a one-click control.
- Acquired skills are marked `✦ Unique`, so the unique-skill tier survives into the list.

---

### Verdict: **Pass with warnings**, revised to **Fail** on the run page after the owner pass below

The flow completes. A real run was created and populated end to end without a blocker, the empty and
`N/A` states are honest, the keyboard stepper is better than most commercial tools, and the copy that
explains mechanics is genuinely useful. Nothing is broken.

The owner pass that followed found one blocker and eleven more items, and the blocker is not a detail:
the sticky run-state region occupies 66 to 73 percent of the viewport, so the forms a Trainer came to
use get the remainder. See O-2 and the revised verdict at the end of Part 3.

What the pass exposes is that the tool models URA Finale well and Unity Cup only as a label. The
scenario's own decision variables, team rank, burst counts, league placement, real stat caps and the
race card the Trainer is actually looking at, are either absent (R-2, R-3, R-7) or displayed as `N/A`
with no way to fill them. Twelve of the page's heaviest controls are unfilterable native selects that a
component already in this repo would fix (R-1, R-4).

**If only three things get fixed:** R-1 (reuse the combobox for deck and skill pickers), R-2 (enter the
Unity Cup state the scenario turns on), and R-5 (confirm every save). R-3 and R-7 need a data decision
before code.

### What this audit could not verify

- No `SCREEN_SPEC.md` and no `.design-qa/` baseline exist, so there is no approved layout or spacing
  target to diff against. Visual-fidelity findings are limited to token use, states and copy.
- Numeric turn fields were set programmatically after a label probe, so keystroke-level validation on
  those inputs was not exercised. Everything else went through the UI.
- Hover, focus-visible and disabled states were not systematically walked; the keyboard path was.
- No responsive pass. `DESIGN.md` may configure viewports; nothing here establishes which.
- The 5,284-node count is one measurement at one viewport with the deck and skill forms in their default
  state, not a performance profile.

---

### Part 3: owner observations, 2026-10-03

Twelve observations from using the tool on a live run. Each was checked against source, the database or
a measurement before being recorded, and three came back different from how they were reported.

#### O-1 · major · the dark theme ships with no way to select it

- **Confirmed.** `DESIGN.md` documents two themes: light-first, with `html[data-theme='dark']` as "a
  measured palette of its own, not an inversion", G-18 requiring every text/background pair to pass in
  both and G-20 requiring the first paint to already be resolved. `layout.blade.php` renders
  `data-theme` from a server-side `$theme` supplied by `AppServiceProvider` under an owner ruling dated
  2026-09-27, and a head script can set `dataset.theme = 'dark'`.
- **Gap.** There is no control in the shell. A second verified theme exists in the design system and the
  only way to reach it is a server-side value.
- **Fix.** A toggle in the nav that persists the choice, or state in `DESIGN.md` that dark is
  config-only. Not a defect in the palette, which is the part that was ruled on.

#### O-2 · blocker · the sticky run-state region takes two thirds of the viewport

- **Measured.** `runs/show.blade.php:70` puts `lg:sticky lg:top-0 lg:z-10` on
  `section[aria-label="Run state"]`. That section measures **524px tall at both 1024x720 and 1280x800**,
  which is **73% of the shorter viewport and 66% of the taller one**, and it stays pinned while the
  Trainer works on the forms below. Its largest child is 216px.
- **Impact.** Above the `lg` breakpoint, which is where the sticky behaviour is designed to apply, the
  working area is whatever is left after the pinned block. The comment at `show.blade.php:14` shows the
  intent was to spare narrow screens, so the case that breaks is the one the rule targets.
- **Fix.** Pin only the Resources strip, which is the part that benefits from being always visible, and
  let Stats and Mood scroll away. Failing that, cap the sticky block with `max-height` and an internal
  scroll, or collapse it to a one-line summary once it is pinned.
- **Verification.** Re-measure at 1024x720 and 1280x800; the pinned region should be under roughly 25%
  of the viewport height.

#### O-3 · major · four `N/A` cells, and they are not the same problem

- **Split, after checking the field list.** `energy` and `fans` **are** enterable, on the turn form, and
  they read `N/A` on this run only because the turn was logged without them: the client gives no Energy
  number, and the report's 209,245 fans is a running total rather than a per-turn delta. `TEAM RANK` and
  `SPIRIT BURSTS` have no field anywhere on the page, which is R-2.
- **Real finding underneath the report.** The strip does not distinguish "you have not entered this"
  from "this tool cannot record this", and the `fans` cell does not say whether it wants the total or the
  change. Both are ambiguity, and the second is a modelling decision. **needs-design-decision.**

  **Ruling revised 2026-10-03.** Owner confirmed (b)-equivalent. Fields store totals, display
  computes the delta, labels read `Energy (after this turn)` / `Fans (after this turn)`. The (c)
  delta-storage option is not in force.

#### O-4 · major · Mood sits in its own block while the stats band wastes vertical space

- **Confirmed as reported, with a structural cause.** The DOM does not group the way the screen reads:
  asking for the nearest `section` of the `Stats` or `Skills` heading returns the race-calendar wrapper,
  so the panels are not independently addressable. That is why the band cannot be reflowed in one place
  and why the same markup produces both this problem and the R-1 weight.
- **Fix as proposed.** Move the explanatory text beside the values instead of under the last two columns,
  and place Mood in the Resources row with turn, Energy, fans, team rank and bursts. Requires the section
  nesting fixed first.

#### O-5 · blocker · the calendar is not scenario-scoped, and it is showing the wrong races

- **Confirmed at the data layer.** `race_catalog_slots` holds 410 rows and its `scenario_key` is **empty
  on the Senior October set**. Those twelve rows are Aichi Hai, Carbuncle Stakes, January Stakes, Kyoto
  Kimpai, Tokyo Hai, Fuji Stakes, Swan Stakes, Tenno Sho (Autumn) and friends. The client's Senior Early
  October race for this run is the **Kyoto Daishoten, G2**, which appears nowhere in the table, and the
  goal race, the **Arima Kinen**, does not either. This extends R-7 from "the picker lacks these two" to
  "the seeded set is not the scenario's set and carries no scenario key to filter by".
- **Second half of the observation, confirmed.** Every candidate race for the half-month is listed flat,
  with no focus ring, badge or marker distinguishing the race the trainee ran, the one scheduled next, or
  the goal. The client distinguishes all three (Scheduled pill, Recommended flag, goal slot) and the
  report records them.
- **Note on turn numbering, which is right.** The Senior Early October rows carry `turn 19`, and the run
  page accepted turn 67 for the same half-month, which is 48 + 19. The turn arithmetic is consistent even
  though the race identities are not.
- **Correction (2026-10-03).** The table holds 410 rows and only **4** carry a `scenario_key` at all
  (`trackblazer`, `unity_cup`, `grand_concert`, `ura_finale`, one each); **406 are NULL** and none is an
  empty string. `year` holds `1..4`, not a name, so `year='Senior'` matches nothing. Seeding is therefore a
  data job for 406 unscoped rows, not a backfill of the October set. This audit's "empty on the Senior
  October set" wording overstated the scope.

#### O-6, O-7 · major · three panels are documentation, not capture

- Team rank, spirit bursts and team races render explanatory text where a control should be. The
  description of the six burst states is genuinely useful and is currently the only thing the panel does.
  Team races have a partial path, the `circles` and `placement` fields on the race form, but no round
  list, so the four preseason rounds and the finals this run has are unrecordable.
- The report has all of it: rank S, 8th, 55 Unity Trainings, 6 bursts, 5 extremes, four rounds won.

#### O-8 · major · the deck renders as a bar of selects and never closes

- **Confirmed.** After saving, the six selects stay on screen at 252 options each. There is no reset
  control; clearing the deck means choosing "Not equipped" six times and saving. The deck is also frozen
  in the game once a career starts, so a surface that keeps offering the picker is offering something the
  Trainer cannot legitimately do.
- **Fix as proposed.** Render the deck as six card tiles, with the locked state as the default once a
  deck is saved, one "Reset deck" action behind a confirm, and the card's own identity, level, type and
  scenario-link badge on the tile. The data for the tile already exists in `support_cards` and the
  `isScenarioLink()` derivation used in the report.
- **Related gap, not in the original observation.** The deck stores the card only: no level, limit break,
  bond or hint level. The report carries all four per card and the tool cannot.

#### O-9 · major · the activity picker describes mechanics instead of showing numbers

- **Confirmed.** The choice labels are good prose ("Wit Costs no Energy, so it stays available when the
  bar is low", "Rest Returns about +30 Energy, and a rest can backfire into a stayed-up-late penalty")
  and they match the corpus. They are also the only information offered. The client prints, per tile, the
  facility level, the stat preview, the SP preview and the failure percentage, which is what the decision
  actually runs on. None of it has a home here.
- **Fans and Energy are enterable and were left empty**, per O-3, and the form does not say which sense
  it wants.
- **"Mood adjustment" is unresolved and the owner's challenge is fair**: the client's action set is
  Rest, an outing that raises Mood, and a treatment option, and this app offers a choice named after its
  effect rather than its button. A research pass is running on the verbatim client labels, the Infirmary
  rules and the stayed-up-late probability. Until it returns, the `[Unverified]` marker should not be
  shown to Trainers (R-6).
- Manual correction behind a disclosure is right and is kept.

#### O-10 · major · the Energy control should be a gauge, not a number box

- **Partly established already**: maximum starts at 100, and the official Global account announced a
  numeric readout under the bar around 2025-10-24, which sits oddly against a 2026-10-03 capture that
  shows none. The colour behaviour described, green at full grading to blue as it drains, is **not
  established** by any source found so far, and neither is the Trackblazer item route for raising the
  maximum. Both are with the running research pass.
- **Design position for the doc either way**: a free-text number field is the wrong control for a value
  the game itself only shows as a gauge. A bounded slider or a 0 to 100 stepper with the bar rendered
  beside it matches how the Trainer reads it, and it makes the "no number in the client" case a state the
  tool can show rather than a blank.

#### O-11 · major · the skills panel is mislabelled, capped, and source-blind

- **The label is wrong, and the owner's reading is the correct one.** The three rows this run shows as
  `Suggested` are the costume card's own starting skills, seeded by the controller per KI-33. They are
  not suggestions, they are what the trainee arrives with, and the unique skill among them is from turn
  one. Call the state `Starting` or `Innate` and reserve `Suggested` for hintable skills.
- **The wall the owner describes is real and unrepresented**: 624 catalog skills, 173 SP available, and
  no budget line anywhere. The prices are in the data, the SP total is enterable per turn, and the
  discount ladder is now settled in the corpus (§2.1 of the report), so a "what can I actually buy" view
  is buildable today.
- **Structure**: five fixed rows, one unfilterable select each, statuses set per row, no grouping by
  source. The distinction the Trainer needs is unique / from support card / from event / evolved, and the
  catalog can supply three of the four.
- This is the owner's worst-rated panel and the evidence supports it: it combines R-1, R-4, R-5 and R-6.

#### O-12 · major · there is no goals surface

- **Confirmed, with one component already there.** `components/grade-point-meter.blade.php` renders an
  objective list, but its props and comments scope it to Trackblazer Grade Points, and
  `current_objective_index` has no input on either screen (C-5). Nothing in the tool shows the run's
  actual goal line, which is the first thing the client puts in its header: "Place 1st in Arima Kinen,
  entry criteria met, 5 turns", plus the cleared goals behind it.
- **Fix.** A goals panel: name, deadline period, state (cleared, active, failed), and the turn countdown
  the client prints. The race rows already carry `is_mandatory` and `is_special_race`, and the report
  records three cleared goals and one active, so the shape is known.

  **Ruling revised 2026-10-03.** Owner confirmed (b). The goals panel renders directly in
  `show.blade.php` from `is_mandatory` and `is_special_race` race rows. The grade-point-meter
  component is not generalised. A future surface that needs the same list can extract a shared
  component at that time.

---

### Part 4: the sibling forms, swept 2026-10-03

The run page is held on file ownership (see the backlog), so the sweep went sideways instead: the other
forms that share the create page's rules, in files no other session has open. Two findings, one of them
the worst on this record, and both fixed the same day.

#### I-1 · blocker · the import form's paste field was `required`, which closed the upload path

- **Evidence.** `resources/views/runs/import.blade.php` put `required` on `textarea[name="csv"]` while
  `input[name="file"]` carried none. Measured on the live page before the change:
  `textareaRequired: true`, `fileRequired: false`, `formNoValidate: false`, `form.checkValidity(): false`
  with the textarea empty. A native constraint is evaluated per field, so a Trainer who chooses the file
  and leaves the box empty gets "Please fill out this field" and the form never submits.
- **Why the server does not want it.** `ImportHistoricalRunRequest::prepareForValidation()` copies the
  upload into `csv` and removes `file` before rules run, so `csv` is only empty on submission when the
  Trainer supplied neither. Its own message is written for that case: `'csv.required' => 'Paste the run's
  CSV or choose the file to import.'` The browser constraint was firing before that message could, on the
  one path the file input exists to offer.
- **Fix.** `required` dropped from the textarea. The rule stays where it can see both halves.
- **Verification.** Re-measured after: `textareaRequired: false`, `valueMissing: false`, and
  `traineeRequired: true` (the pick the Trainer genuinely still owes is still gated).

#### I-2 · minor · the required marker was three different conventions across four forms

- **Evidence.** `Trainee *` on import, `Turn *` and `<Stat> *` on the run page's hand-correction form,
  nothing at all on the two mandatory fields of the create form, and `Tier` marking itself optional only
  through `placeholder="optional"`, which disappears the moment the Trainer types in it.
- **Fix.** One rule, using the marker the repository already chose: `*` on every field that has no answer
  of its own, `(optional)` in the caption on every field that does. Applied to `Trainee *` and `Status *`
  on create, `Status *` on import, and `Race title *`, `Month *`, `Half *`, `Tier (optional)` on the race
  panel's manual branch, where the placeholder marker is gone.
- **Checked, no finding.** The race panel's `title`, `month` and `half` are `required` only inside the
  manual branch, and `StoreRaceEntryRequest` adds those three rules only when `$mode === 'manual'`, so the
  client constraint and the server rule cover the same branch. The calendar branch's select has no empty
  option, so it cannot be left un-answered and needs no marker.
- **Still open.** The run page's own asterisks are inside the held file.

#### I-3 · settled from the research pass · the mood action is named `Recreation`

Backlog item 7 asked for the `[Unverified]` treatment to come off the mood action once the research pass
reported. It reported, and the repository already had the stronger evidence: three July 2026 client
captures under `docs/research-scratch/screenshot-notes/` read the action row verbatim, six buttons,
"Rest, Training, Skills, Infirmary, Recreation, Races". `TrainingRunController::turnChoices()` now labels
that row `Recreation` with the flag removed, and the controller's docblock records which captures the name
came from. Measured on the live run page: the word `Recreation` is present, `Mood adjustment` is not.

Three things the same pass surfaced that are **not** edited here, with the reason each one waits:

*(Read Part 5 before acting on items 2, 3 and 4 below: the local corpus withdrew item 3 and item 4 outright
and settled item 2 as an in-repo conflict.)*

1. **The client offers two actions the tool cannot log.** `Infirmary` and `Races` are buttons on that row,
   and the turn-choice list has no key for either, so a Trainer recording a rest-gone-wrong turn or a race
   turn has no honest option. Adding them is new behaviour with a `choice` value behind it, which needs a
   PRD citation before code, not a copy change.
2. **`Rest`'s "+30" is now contested.** The detail line reads "Returns about +30 Energy", which traces to
   the corpus row at `docs/UMAMUSUME_REFERENCE.md` §1.1.5 (+30 per standard rest, GameWith 2026-09-25). The
   research pass returned a post-rework Global table of 30 / 50 / 70 with 50 the most common outcome
   (umareference, 2026-08-26), and two JP probability pages agreeing on the three tiers but stale on the
   split. That is a source conflict for the corpus's Source Conflict Log, and the single-source post-rework
   figure is not strong enough to overwrite a row on inference. Flagged, not applied.
3. **"Motivation" is not a client word.** Across the 154 Global notices the official site still serves, the
   research found `Mood` and never `Motivation`, and our own seeded `support_effects` row 2 is named "Mood
   Effect". Verified here that no view, controller, config or `tools/lore.php` pattern prints `Motivation`,
   so there is no UI defect to fix, but `AGENTS.md` describes `Motivation` as Global client terminology the
   lore gate adds, and the gate does not contain that word. `docs/UMAMUSUME_REFERENCE.md` §1.1.6 also titles
   itself "Motivation (mood)". Both are documentation lines about a ruling, and rulings are the owner's to
   write, so they are named here rather than edited.
4. **Not a UI item, but the same pass surfaced it, so it is recorded rather than lost.** The Trackblazer
   shop block in `config/scenarios.php` looks short one row, "Energy Drink MAX" (30 coins, maximum Energy
   +4 and Energy +5), and prices "Artisan Cleat Hammer" at 25 where the publisher page says 20. The two
   Global sources agree with each other and both are marked stale, so this is a recompare against the
   export, not an edit from a guide.

---

### Part 5: what the local corpus settled, 2026-10-03

The research pass came back from the web, and the same questions were then run against this repository's
own files: the nine committed JSON data sets under `database/seeders/data/`, the five snapshot
directories, the 49-plus screenshot notes, and the two corpus documents. That pass answered more than the
web pass did, and it withdrew two of the four claims in I-3.

#### Withdrawn first, because they are wrong and they are printed above

**I-3 item 3 is false as written.** It says `AGENTS.md` describes `Motivation` as a term the lore gate
adds "and the gate does not contain that word". It does: `tools/lore.php:64` carries the pattern
of the JP-wiki stat names, `wisdom` and `motivation` among them, as whole words
over the app paths. The grep that produced my zero was case-sensitive and every one of those patterns is
lowercase, so it matched nothing and reported silence as absence. `AGENTS.md` is accurate, and the
repository has ruled Wit and Mood as the client words with Wisdom and Motivation as the JP-wiki English
for years.

**I-3 item 4 is false too.** It says `config/scenarios.php` "looks short one row", Energy Drink MAX, and
misprices Artisan Cleat Hammer at 25. The block's own comment answers the first half
(`config/scenarios.php`, above `shop_items`): "This is a subset of the published list, chosen to span the
categories (stats, energy, mood, training effects, races, facility). It is not the whole catalogue, and the
step says so where it renders." The second half is answered by the table the block is transcribed from:
`docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md:422` prices Artisan Cleat Hammer at **25**, the same
number config carries. The web pass's "20" came from a page this repository does not hold, so the config was
never the thing out of line.

#### What the local files settled, with the line each answer came from

1. **Rest's recovery tier is a conflict inside this repo, and the UI was quoting the wrong side of it.**
   `docs/UMAMUSUME_REFERENCE.md:162` says "+30 Energy per standard rest", cited to GameWith 2026-09-25. The
   same repository's `SCENARIO-PUBLISHER-REFERENCES.md` §2.1 says the opposite in three rows: ":1518 Rest
   costs 1 turn and restores 50 energy (the modal case)", and the measured table at :1531-1533 (Kamigame,
   2022-12-19, n = 100) "休息はバッチリ！ 体力+70 12%", "リフレッシュ完了 体力+50 66%", "寝不足で…… 体力+30
   確率で「夜ふかし気味」獲得 22%". So +30 is the failure tier, +50 is the common one, and the same section
   settles that a rest consumes a turn, which the reference row's own "Free-turn action" wording obscures.
   Both Trainer-facing copies of the number were dropped rather than re-picked: the `Rest` detail line in
   `TrainingRunController::turnChoices()`, and `resources/views/components/guided-step.blade.php` ("Rest
   returns about +30" became "Rest refills Energy"). Measured on the live run page: no `+30` string remains,
   the new detail renders, 43 guided tests pass, and `GuidedTurnOnRunViewTest:293` still pins the hint
   branch that copy lives in. The reference row itself is left for the owner's Source Conflict Log; it is
   contested between one post-rework citation and three measured rows, and the client-capture evidence that
   would settle it is an Energy-bar read this tool does not have.
2. **`Recreation` was already the repository's word.** `docs/scenarios/01-ura-finale.md:54` reads "Use
   **Recreation** to raise mood, at the cost of an entire turn with no training progress", and :57 and :103
   record the summer-camp variant where Rest and Recreation both restore Energy and Mood. So the label change
   in I-3 matches the repo's own Global scenario guide, not only the three July captures.
3. **The Infirmary rules the web pass could not find are transcribed here.** §2 of the publisher references:
   energy +20 plus an attempt to clear one bad condition, the attempt can fail (:1558); an inferred 85% cure
   and 15% fail from 119 and 111 trials, stated by its authors as an estimate (:1560-1561); only one
   condition per visit (:1564); unavailable during summer camp (:1565); and resting during camp clears every
   condition (:1566). Every one of those rows carries the `[JP]` tag, so under the Global-only audience
   ruling they are mechanics to cite as JP-sourced rather than Global client reads, and the button itself is
   evidenced by the captures. The plan item that needs an `Infirmary` turn choice now has its numbers.
4. **The Wisdom and Motivation strings in the data never reach a Trainer.** They are in
   `skills.609afe88.json` (for example "Your Stamina, Guts, and Wisdom stats are increased by 40 if your
   Motivation is Good or better"), but `SkillSeeder` and the `Skill` model import no description field at
   all, and the views already say the consequence: `skills/index.blade.php:9-10` scopes the permitted Skill
   surface to name, name_ja, sp_cost, type and is_unique, and `skills/show.blade.php:118-124` states that
   "This tool's skills source stores no description it may render". So there is no display-path conflict to
   fix, and the one item that looked like a UI terminology bug is a data-provenance note.
5. **The Energy gauge question has a local answer, and it is not the web one.** The capture at
   `docs/research-scratch/screenshot-notes/Screenshot 2026-07-17 221504.md:24` records what carries state:
   "Energy is bar length plus gradient colour". No note reads a number. So of the two things the web pass
   could not establish, the Oct-2026 numeric display and the colour rule, the client frames in this repo say
   the bar is graphical and the gradient is a state channel. That is the evidence O-10's colour decision
   should be written against, and it is also a caution: a HUD capture of the bar at low Energy is still what
   would name the thresholds.

---

### Part 6: the option flood, halved where it was reachable, 2026-10-03

Item 6 of the plan was "extract the create page's combobox into a shared component and a shared JS module,
use it for the deck and skill pickers, and source the options from data rather than emitting `<option>` per
row." Measured and reasoned about first, that shape is the wrong vehicle, and the right one needed no JS at
all. Recorded here because it changes what the remaining half should be built as.

**Why not the JS extraction.**

1. The option nodes are not decoration, they are the no-script path. ADR-0007 and the create page's whole
   design say the native control stays in the markup and the script only takes it over. A JS-populated
   picker either keeps the `<option>` elements, in which case the node count is unchanged, or drops them,
   in which case the Trainer with scripting off has a select with no choices. The saving the audit wanted
   and the contract the audit's own page defends are the same bytes.
2. `TraineeSelectorTest` holds about twenty assertions pinned to the trainee module's source text, by
   function slice. Extracting its engine would not adapt those pins, it would void them, and the module it
   would leave behind is a generic list over a flat payload while the trainee module commits a *pair*
   (trainee and card) with banding, cardless rows and exact-name debut resolution. Two shapes, one name.
3. The peer session's in-flight `RunViewTargetSizeTest` asserts `//select[starts-with(@name, "skills[0][")]`
   carries `h-11`. Replacing those selects is not a collision in one file, it is a contradiction of a test
   that has not been committed yet.

**What landed instead, and where.** `resources/views/components/deck-panel.blade.php` now renders the card
catalogue once instead of six times: one slot open with the full list, five closed, each carrying a hidden
input under the same field name and a `Change slot N` link built with `request()->fullUrlWithQuery()`, the
idiom `x-race-calendar` already uses for its year tabs. A failed submission opens the slot that errored. The
POST contract is byte-identical from `StoreDeckRequest`'s point of view: six keys, blanks dropped by its own
`prepareForValidation`. No JavaScript, no new dependency, and the no-script path keeps every capability it
had, at one extra click per slot.

| Measured on the run page       | Before   | After    |
| ------------------------------ | -------- | -------- |
| `select[name^="deck["]`        | 6        | 1        |
| options inside the deck form   | 1,512    | 252      |
| element nodes, whole page      | 8,941    | 7,696    |
| `option` nodes, whole page     | 7,997    | 6,737    |
| text nodes, whole page         | 17,752   | 15,257   |

**A correction to R-1's own numbers, in passing.** The audit recorded five skill rows of 624 options. The
page now measures **ten** `skills[N][skill_id]` selects and ten `skills[N][status]` selects, 6,270 options
between them, which is why the total it reported was 5,284 elements and the total measured today is 8,941.
The row count follows the run's skill list, so the figure is not a drift in the page, it is a count of this
run's rows. Anyone sizing the remaining work should use 6,270, not 3,120.

**The remaining half is the same fix, one file later.** Ten `skill_id` selects over one catalogue collapses
the same way the six did: one open picker, nine hidden inputs, ten `Change row N` links, and the `status`
selects left as they are because they hold four options each and are not the problem. That cut is roughly
5,600 option nodes, larger than what landed here, and it lives in `resources/views/runs/show.blade.php`,
which is still held. It also needs the peer's `h-11` pin re-pointed at whatever replaces the select, so it is
a coordinated change rather than a quiet one.

Tests: `RunDeckTest` 21 passed / 66 assertions, including one new pin that counts one open list, five hidden
fields and five switch links. One existing assertion was re-pointed rather than removed: it proved the
`old()` rehydration by looking for `value="X" selected` on the second slot's option, and under the new
markup the rehydrated slot is closed and posts a hidden value, so it now asserts the card name the Trainer
reads back and the field and value the next submit carries. The claim is unchanged; the markup it was read
off is not. Peer's three untracked `RunView*` tests, `RunWriteAtomicityTest`, `FrontendComponentLibraryTest`,
`DesignTokensTest` and `RenderedCopyHygieneTest` all pass against the change: 42 passed / 171 assertions with
2 pre-existing skips. Pint clean, PHPStan level 6 clean, `composer lore` unchanged at 187 hits and 76 exempt
lines.

---

### Completion backlog

Ordered by what unblocks what, not by size.

**Shipped 2026-10-03, same day as the audit.** C-1, C-2, C-3 and C-4 (the whole create-page set that was
not a design decision), in `resources/js/trainee-combobox.ts` and `resources/views/runs/create.blade.php`,
with pins in `tests/Feature/TraineeSelectorTest.php`. C-5 stays `needs-design-decision`, and the run-page
half is held, for the reason below.

Later the same day, the Part 4 sweep: I-1 (the import upload path), I-2 (the `*` and `(optional)`
convention on create, import and the race panel), and I-3 (the `Recreation` label, backlog item 7), in
`resources/views/runs/create.blade.php`, `resources/views/runs/import.blade.php`,
`resources/views/components/race-panel.blade.php` and `TrainingRunController::turnChoices()`.
Verified in the browser on the live pages, not from the source. Gates after the full set:
`TraineeSelectorTest` 28 / 182, the guided-turn family plus `FrontendAuditFixesTest` and
`DesignTokensTest` 58 / 247 with 2 pre-existing skips, `GuidedStepScenarioCompositionTest` 36 / 207,
`npm run typecheck` clean, Pint clean, PHPStan level 6 clean, `composer lore` unchanged at 187 hits and
76 exempt lines. No test was deleted, skipped or weakened.

Then Part 5's local-corpus pass removed one contested number from two more places: the `Rest` detail line in
`TrainingRunController::turnChoices()` and the low-energy hint in
`resources/views/components/guided-step.blade.php`. Measured on the live run page afterward: no `+30` string
anywhere in the page, the new detail line renders, and 43 guided tests pass with Pint, PHPStan and the lore
gate all still clean.

**Held on file ownership, not on work.** Almost every remaining item lands in one file:
`resources/views/runs/show.blade.php` (items 10, 11, 12, 13 and 15's redirect targets, plus R-5, R-6, R-8,
O-2, O-3 and O-4), and `resources/views/components/layout.blade.php` (item 1's theme control and the
sticky rule). Both are modified and uncommitted in this shared worktree while another session finishes a
touch-target and no-script pass on the same page, with three new untracked tests beside it
(`RunViewErrorEnvelopeTest`, `RunViewNoScriptTest`, `RunViewTargetSizeTest`). Two sessions rewriting one
Blade file is how a pass gets silently reverted, so these wait for that work to land. Item 6 is held for
the same reason from the other side: the word lives on `SkillAcquisition`, and `SkillController.php` and
`SkillSeeder.php` are open in the same tree.

**Data first, because four UI items are blocked on it.**

1. Seed the Unity Cup race slot set and populate `scenario_key` on every slot (O-5, R-7). Without this
   the calendar cannot mark scheduled, run or goal races honestly.
2. Add run-level capture for team rank, league placement, burst counts and team-race rounds (O-6, O-7).
   Needs a schema proposal with a PRD citation before code, which makes it an Architect item.
3. Add per-card deck state: level, limit breaks, bond, hint level (O-8). Same gate.
4. Add a per-stat cap field, or derive the cap and show its source (R-3).
5. Add the goals list as data: name, deadline, state (O-12).

**Copy and semantics, cheap and independent of the above.**

1. Rename `Suggested` to `Starting` for the KI-33 seed and reserve the word for hints (O-11).
2. ~~Remove `[Unverified]` from the Mood adjustment label and settle the label from the running research
   pass (R-6, O-9).~~ **Done 2026-10-03 as I-3: the client string is `Recreation`, read off the action row
   in three July 2026 captures, and the flag is off that row.** The `[Unverified]` wording R-6 pointed at
   is the controller's detail line, which now names what the action does instead.
3. Replace the stale hint-discount disclaimer with the ladder now recorded in the corpus (R-6).
4. State whether `fans` and `energy` mean a total or a delta, on the field (O-3).

**Layout, after the section nesting is fixed.**

 1. ~~Stop pinning Stats and Mood; pin the Resources strip only (O-2, blocker).~~ **Done 2026-10-03:
```text
`f5a91b2` (view) and `5110c3b` (frame pin re-point). The pinned region measures 176px, 22 percent of
800 and 24.4 percent of 720, down from 524px. `lg:contents` on the Run state wrapper keeps the sticky
child's containing block the page, so the strip stays pinned over the log.**
```

 1. Fix the section scoping so each panel is addressable, then move the stats explanation beside the

```text
values and Mood into the Resources row (O-4). **Partially done 2026-10-03 (`f5a91b2`): each panel is
its own section (Run state, Resources, Stats, Skills, Race calendar, Turn log) and Mood sits with the
strip values. The stats explanation reflow is deferred: that text lives in the read-only `x-stat-band`
component, outside this dispatch's fence.**
```

 1. Deck as six tiles with a locked state and one reset action (O-8). **Open; needs a design decision.**
 2. Skills as a budgeted, grouped, filterable panel rather than the stacked selects it has (O-11). Ten

```text
rows, not the five R-1 counted; see Part 6. **Open; the option flood is cut (see item 14), the panel
design is not decided.**
```

**Reuse, the single highest-leverage code change.**

 1. ~~Extract the create page's combobox into a shared component and use it for the deck and skill

```text
pickers (R-1, R-4).~~ **Landed in a different shape, and only half of it was reachable: Part 6.** The
deck half is in (1,512 option nodes to 252, page from 8,941 elements to 7,696). **The skill half landed
2026-10-03 (`e0aa029`, `8faea29`): one open picker, nine hidden inputs, nine switch links, the ten
whole-catalogue selects' 6,270 option nodes down to 624, page elements 7,696 to 2,111.**
```

 1. ~~One flash partial on every save redirect (R-5).~~ **Done 2026-10-03: `04244a4` (status region and
    controller messages), `b27328e` (test `RunSaveConfirmationTest`).**

**Research pass landed 2026-10-03 (activity labels and the Energy gauge).** What it settled, and what it
did not:

- **Settled:** the action row is six buttons and their words are `Rest`, `Training`, `Skills`,
  `Infirmary`, `Recreation`, `Races`. Three of those are our own client captures, so the label question in
  item 7 closed on first-party evidence rather than on the guides. The pass also confirmed the repo's own
  captures, not just the guides, since it returned the same six from uma.guide and umamusu.wiki.
- **Not settled, and it blocks item 10's colour decision:** the Energy gauge. The only published
  description anywhere is "rainbow-colored bar" (umamusu.wiki, edited 2026-09-28), no source gives a
  colour threshold, a warning string or a grading rule, and the numeric-value announcement of 2025-10-28
  could not be reconfirmed in any post-rework source. Our July 2026 captures read the bar as length plus
  gradient with no number, which is the O-10 answer the HUD capture still owes.
- **Not settled:** whether the Infirmary costs anything beyond the turn, and whether the rest and outing
  outcome names ("Sleep Deprived", "Well-Rested", "Riverside Stroll", "Shrine Date", "Karaoke") are client
  strings or guide paraphrase. Two independent guides agree on the words and no string table was reachable,
  so none of them may be promoted into UI copy yet.
- **New, from the same pass, and not applied:** the three items in I-3. The `Rest` "+30" line is contested
  by a post-rework Global probability table, the client's `Infirmary` and `Races` actions have no key in
  `turnChoices()`, and `Motivation` is not a Global client word in either the official corpus or our own
  seeded `support_effects` row 2.

**Revised verdict: Fail on the run page.** The create flow still passes with warnings. O-2 and O-5 are
blockers: one makes the page hard to work in at the widths the sticky rule was written for, the other
means the calendar shows races this run never had.

### Plan from here, in the order that unblocks things

1. **Wait for the run-page pass to land**, then re-measure O-2 before touching the sticky rule. Re-measured
   2026-10-03 after Part 6, at 1280x800 with the rule active: the `Run state` section is still **524px, 66% of
   the viewport**, so the peer's touch-target work did not move it and the blocker's number is stable. Its
   children measure 28 (heading) + 91 (Resources strip) + 28 + 216 (Stats band) + 28 + 25 (Mood row), which
   says the fix in item 10 takes the sticky region from 524px to the 91px strip plus its padding, about 14% of
   the viewport, comfortably under the 25% this audit asked for. That last figure is arithmetic on the numbers
   above, not a measurement of a change that has landed. The file is still open in the peer session.
2. **Run-page items 10 to 13**, in that order, each with a browser measurement after it rather than before
   it. Part 6 already took the deck half of item 14; the skill half is the biggest single cut left on this
   page (about 5,600 option nodes) and it needs `show.blade.php` plus an agreed answer on what happens to
   the `h-11` pin that currently holds those pickers as selects.
3. **Two items the research pass opened, both gated on a decision rather than on work:** an `Infirmary` and
   a `Races` choice (Part 5 supplies the Infirmary's numbers, so the decision is now a PRD citation for new
   behaviour rather than a search for data); and the `Rest` recovery tier, which Part 5 shows is contested
   *inside this repository*. The number came out of the two Trainer-facing copies instead of being
   re-picked, and the reference row waits for the owner's Source Conflict Log entry.
4. **Ask the repository before the web.** On every question this pass could answer twice, the local files
   answered with a line number and the web pass either arrived late at the same place or was wrong: both
   withdrawn claims in Part 5 came out of the web pass.
   `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` is the transcribed publisher tables,
   `docs/research-scratch/screenshot-notes/` is the client reads, and `database/seeders/data/` is nine
   committed exports. Grep those three before opening a browser, and treat a web answer as evidence only
   where they are silent.
5. **Surfaces this audit never opened,** in the order a Trainer meets them: the run index, the import
   preview step (the half that parses a real sheet, where I-1 lived), then catalog, skills, support cards
   and review. The convention sweep in I-2 found defects on the two forms it looked at, so the same three
   checks (marker convention, one name per field, and no native constraint stricter than the server rule)
   are worth running across the rest.

### Part 7: run-page fixes applied (2026-10-03)

Part A landed the WCAG 2.2 AA pass that was in the tree but never committed. Part B then took the run-page
items that were held on file ownership. Every figure below is a measurement or a test run, not a projection.

#### Landed

| Item                                     | Commit                 | File                                                                                       | Test that closes it                               |
| ---------------------------------------- | ---------------------- | ------------------------------------------------------------------------------------------ | ------------------------------------------------- |
| L-F01 nav target size                    | `39a6cea`              | `resources/views/components/layout.blade.php`                                              | `RunViewTargetSizeTest`                           |
| F-04 error envelope                      | `a3e323c`              | `resources/views/runs/show.blade.php`                                                      | `RunViewErrorEnvelopeTest`                        |
| F-01 no inline JS on the run route       | `ecae77d`              | `resources/views/runs/show.blade.php`                                                      | `RunViewNoScriptTest`                             |
| WCAG test gates                          | `db48655`              | the three tests                                                                            | themselves                                        |
| O-2 pin the Resources strip only         | `f5a91b2`, `5110c3b`   | `show.blade.php`, `RunViewFrameTest`                                                       | `RunViewFrameTest` (re-pointed), measured 176px   |
| O-4 section nesting and Mood placement   | `f5a91b2`              | `show.blade.php`                                                                           | `RunViewFrameTest` pins preserved                 |
| R-8 duplicate submit label               | `767de93`              | `show.blade.php`                                                                           | `SkillsFetchTest`                                 |
| R-6 stale hint-discount disclaimer       | `595cb25`, `4f76ecf`   | `show.blade.php`, `SkillsFetchTest`                                                        | `SkillsFetchTest` (re-pointed)                    |
| R-6/O-11 copy: Suggested to Starting     | `b1814cc`              | `lang/en/uma.php`, `show.blade.php`, `TrainingRunController.php`                           | `RunSkillRowLabelsTest`                           |
| R-5 save confirmation                    | `04244a4`, `b27328e`   | `show.blade.php`, `TrainingRunController.php`                                              | `RunSaveConfirmationTest`                         |
| Item 14 skill half: picker collapse      | `e0aa029`, `8faea29`   | `show.blade.php`, `RunViewTargetSizeTest`, `RunSkillPickerTest`, `RunSkillRowLabelsTest`   | `RunSkillPickerTest`, both re-points              |

Part A commits: `1a87e2c` (mandate to WCAG 2.2 AA), `39a6cea`, `a3e323c`, `ecae77d`, `db48655`. The two
folded review records are not separate files; `docs/research-scratch/INDEX.md` records them as folded into
`AUDIT-AND-VERIFICATION.md`, which is where the L-F01 to L-F07 numbering and the F-02 supersession note
live, so no duplicate file was created.

#### Measured

- **O-2.** Pinned region 524px to 176px. At 1280x800 that is 22 percent; at 1024x720, 24.4 percent. The
  children are heading 28, strip 92, Mood row 20. The strip stays pinned at scroll 600 (bottom 176) and no
  focusable control in the Turn log is fully behind it.
- **Item 14 skill half.** Element nodes 7,696 to 2,111; option nodes 6,737 to 1,121; the ten
  whole-catalogue selects' 6,270 options to 624.
- **Domain fingerprint.** `04870f28...` before Part A and after Part B, unchanged. No domain table was
  written.

#### Corrections folded in

- The `Race calendar` section now gates on `panels.race_calendar`. A static wrapper added the label for
  every scenario and broke `GoalPanelsOnRunDetailTest`'s absence checks for Trackblazer and Our Grand
  Concert (G-34).
- The hint-ladder copy uses commas, not slashes. The slash form (`10 / 20 / 30 / 35 / 40`) tripped the
  Grade Point absence pin's `\d+ / \d+` regex in `GoalPanelsOnRunDetailTest`, a read-only file, so the
  copy moved rather than the pin.
- `RunViewFrameTest`'s sticky pin, `RunViewTargetSizeTest`'s two skill-row selectors,
  `RunSkillRowLabelsTest`'s row and sizing pins, and `SkillsFetchTest`'s copy pin were re-pointed, each with
  the original claim stated in the test and the commit. No claim was weakened or removed.
  `RunSkillRowLabelsTest` and `GoalPanelsOnRunDetailTest` sit outside the dispatch's write fence but pin the
  exact shape this dispatch changed; the re-points are recorded here as a fence deviation.

### Part 8: quick wins applied (2026-10-03)

Dispatch A's scope, stated in the dispatch itself as no schema and no PRD: the Rest recovery doc
edit, the O-3 label disambiguation, the C-5 four-field surface on the create form, and the O-12
goals panel. Each lands in its own commit.

#### Landed

| Item                               | Commit      | File                                                                            | Test that closes it                                                                                        |
| ---------------------------------- | ----------- | ------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------- |
| A.1 Rest recovery tier             | `24ba395`   | `UMAMUSUME_REFERENCE.md` §1.1.5, `SCENARIO-PUBLISHER-REFERENCES.md` §2.1 note   | doc-only, no test gate                                                                                     |
| A.2 / O-3 fans and energy labels   | `48544ca`   | `resources/views/runs/show.blade.php`                                           | the existing `ResourceStripOnRunDetailTest` and `ResourceStripTest` pins still pass on the longer labels   |
| A.3 / C-5 four create fields       | `b411fd0`   | `resources/views/runs/create.blade.php`                                         | `RunCreateSurfaceTest` (new file, three tests)                                                             |
| A.4 / O-12 goals surface           | `6f7e738`   | `resources/views/runs/show.blade.php`                                           | `RunGoalsPanelTest` (new file, four tests)                                                                 |

#### Premise 3, the domain fingerprint

The dispatch opened expecting `04870f28...`, the value recorded at Part B end. Measured value at
Dispatch A start was `87164db8f03e02d6523c144cfbe9606ac383e1aeb197144c7eca1e3e10ce4d70`. The
mismatch is not a schema write from Dispatch A, because Dispatch A writes no rows.

Resolution attempt: the audit doc records the expected value but does not record the method that
produced it. The prior dispatch's own summary named a "per-table INSERT dump ordered by rowid",
which hashes row data, but the current method (`sqlite_master.sql` + `PRAGMA table_info` per
table) hashes schema metadata. Neither reproduces `04870f28...` against today's DB, nor do five
other data-dump serialisations tried (pipe, csv, serialize, json, kv) against either the all-table
set or the domain-only set. The expected value is unreproducible from what is on disk today.

Owner ruling 2026-10-03 (this dispatch, pre-work): the premise conflated "fingerprint at dispatch
start" with "fingerprint recorded at a prior dispatch end". The guard is the within-dispatch
delta; the cross-session value is a stale reference. Re-baseline to the current schema-dump
`87164db8...` and proceed. Dispatch A re-computes at the end of Stage 5 using the same method;
delta must be zero.

Amended template for the next dispatch: *domain fingerprint computed at the start of this
dispatch; compare at the end; any delta is a finding*. Not: *domain fingerprint matches a value
recorded at the end of a prior dispatch*.

#### Out-commits recorded

Two of the four stage commits swept edits that were already on disk at dispatch start.

- `24ba395` (Stage 1) carries, in `UMAMUSUME_REFERENCE.md`, the prior session's in-client hint
  discount closure at §1.1.4, the Growth Rate owner identification at §1.3.5, the two §8.4 closed
  gap lines, and the source-path renames that point §2.5 and §2.6 at `SCENARIO-PUBLISHER-REFERENCES.md`.
  In the same commit, `SCENARIO-PUBLISHER-REFERENCES.md` grows by 1,262 lines of source expansion
  the prior session made but never committed. All of it landed at one SHA because line-level staging
  against a tree with no active peer was not authorised.
- `b411fd0` (Stage 3) carries the prior session's C-2 caption edits in the same `create.blade.php`:
  "Umamusume" → "Trainee *", "Status" → "Status*", `aria-required="true"` on the combobox input,
  and the caption rationale that goes with them.

Both sweeps are stated in the commit body. The alternative was to leave the prior session's work
uncommitted across the rest of the dispatch, which the owner's "no peer on the board" ruling
argues against. The next session that reads git log will see those edits under a Dispatch A label
rather than under their own C-2 label; the bodies name the sweep so the audit trail is honest.

#### Stage 2 deviation, named

The dispatch named Stage 2 as "label both as deltas". The turn-form fields on the run page take the
client's post-turn reading, not a delta: the controller line
`app/Http/Controllers/TrainingRunController.php:447` computes the per-turn change by subtracting
the previous turn's stored value from the entered value. Switching the field to take a delta
itself would need a controller edit, and the controller is outside this dispatch's fence. The
labels read "Energy (after this turn)" and "Fans (after this turn)", which is what the fields
actually take, and the Blade comment names the preview computation. The ambiguity O-3 found is
resolved on the field. Reconciling the dispatch's wording to the field's shape is recorded here
rather than papered over.

#### Stage 4, the grade-point-meter decision

The dispatch's fence included `resources/views/components/grade-point-meter.blade.php` and named
Stage 4 as generalising the component to an objective-list. The goals panel's data shape is
different from the meter's (race state, year, turn countdown vs. points ladder, target, current
sum), so the goals section was rendered directly on `show.blade.php` rather than shared through a
generalised component. That is the smaller diff and it does not risk the Trackblazer meter's
existing test pins. If a third panel lands that needs the same list pattern, the abstraction
becomes worth its cost.

#### Suite state after Stage 5

- **Start:** 1,123 passed, 2 skipped, 17,982 assertions, 82.29s, exit 0.
- **End:** 1,130 passed, 2 skipped, 18,014 assertions, 100.05s, exit 0. The seven new tests are
  Stage 3's three in `RunCreateSurfaceTest` and Stage 4's four in `RunGoalsPanelTest`. No test was
  deleted, skipped, or weakened.
- **Domain fingerprint.** `87164db8f03e02d6523c144cfbe9606ac383e1aeb197144c7eca1e3e10ce4d70` at both
  Stage 5 start and end. Delta is zero, which is what the guard was set up to catch.
- **One in-flight fix landed in Stage 5's commit.** Two `TraineeSelectorTest` xpath assertions at
  lines 309 and 376 broadened `//option[@value="N"]` across the whole page. Stage 3's two new
  inheritance-parent selects reuse the Global trainee list, so the broad pattern started matching
  three rows per trainee and both assertions failed. Each was scoped to
  `//select[@name="umamusume_id"]/option[@value="N"]`, the trainee select the test was actually
  about, with a comment naming the C-5 selects as the reason the scoping matters. That tightens
  the original claim rather than weakening it.

#### Still open

O-11 (skills panel), O-8 (deck tiles and per-card state), O-5 and R-7 (the 406-row unscoped race
catalog; Architect), R-2 and R-3 (schema), the Infirmary and Races choices. None of those landed
here; they were in Dispatch A's held-or-future list, not its scope.

_Dated re-read 2026-10-08 (documentation-sync pass, on the owner's Phases A–E completion brief; the
ledger above is preserved as written). The "Fail on the run page" verdict this section carries was
revised by its own Part 7, which closed the page's blockers in code, and the page it was written
against no longer exists: A4b ported `runs/show` to `resources/js/pages/Runs/Show.vue` and B1
deleted the Blade view and its components. Each item's state on this tree, measured rather than
remembered:

- **O-2** (sticky region two thirds of the viewport) — **closed**. Part 7 pinned the Resources
  strip to 176px (measured 22% at 1280x800), and the ported `Runs/Show.vue` keeps the
  `lg:sticky` strip pin; `SCR-RUN-003`'s Accessibility section records it as closed.
- **O-3** (four `N/A` cells, total-vs-delta ambiguity) — **closed in its wording half, open in its
  modelling half**. The 2026-10-03 ruling (fields store totals, display computes the delta) and
  Part 8 A.2 shipped the "after this turn" labels; `SCR-RUN-003` §Localization records that the
  model question stays open (§7).
- **O-4** (Mood placement, section nesting) — **closed**. Part 7 landed the nesting and Mood
  placement at `f5a91b2`; the ported page keeps one labelled `section` per panel
  (`SCR-RUN-003` Accessibility).
- **O-5 / R-7** (unscoped race calendar) — **open, data-first, unchanged by Phases A–E**. The
  catalogue still holds 410 rows with 4 carrying a `scenario_key`; `SCREEN_SPEC.md` §7-7 records
  the deferral, and `KI-67` (the pinned GameTora document answering 404) now blocks the refresh
  that could add scoping.
- **O-8** (deck as a bar of selects that never closes; per-card state) — **closed in its first
  half, open in its second**. The ported `DeckPanel.vue` renders equipped slots as closed rows and
  opens one picker at a time (`openSlot`, `?deck_slot=`), so the never-closing bar is gone; the
  per-card state half (level, limit break, bond, hint) stays gated on the PRD §6.9 / US-12 call
  (`ADR-0014`, Part 9).
- **O-11** (skills panel design) — **open, unchanged**. The R-6/O-11 copy ruling
  ("Suggested" → "Starting") landed at `b1814cc`, but the panel-design question the audit's ledger
  names is not closed by any Phase D/E slice.
- **O-12** (goals surface) — **closed as the run page's goals panel, with the client's per-trainee
  goal header still absent**. Part 8 A.4 landed the goals panel (`RunGoalsPanelTest`, the
  ported `goalRows` prop); the client's "Place 1st in Arima Kinen, 5 turns" header remains absent
  because no `trainee_goals` table exists (`KI-34`).
- **R-2 / R-3** (Unity Cup state capture, schema) — **open, schema-gated**. E3's Unity Cup panel is
  read-only with no write path, exactly as the E3 slice plan records; the capture proposal at
  `RACE-AND-SLICE-RESEARCH.md` §unity-cup-capture.md stays the owner's.
- **R-5** (save confirmation) — **closed**. Part 7 landed it (`04244a4`, `b27328e`,
  `RunSaveConfirmationTest`).
- **R-8** (duplicate submit label) — **closed**. Part 7 landed it (`767de93`).
- **Infirmary and Races choices** — **open, unchanged**. `TrainingRunController::turnChoices()`
  still offers the five training stats plus Rest and Recreation; no Infirmary or Races choice
  exists on this tree.

This re-read closes nothing in the audit's own terms — the ledger above stays the audit's record —
but it records the tree verdict per item so a future reader does not read a 2026-10-03 "Fail" as a
statement about the Vue page that replaced the one it measured._

---

### Part 9: deck tiles and per-card state, gated (2026-10-03)

Dispatch C opened with Stage 0 (O-3 and O-12 ruling revisions) and Stage 1 (PRD-citation gate).
Stage 1 stopped.

#### Stage 0 · landed

| Item                 | Commit      | File                                            | Verification   |
| -------------------- | ----------- | ----------------------------------------------- | -------------- |
| O-3 revision note    | `143d342`   | `docs/UIX-AUDIT-TRAINING-RUNS.md` (O-3 body)    | doc-only       |
| O-12 revision note   | `143d342`   | `docs/UIX-AUDIT-TRAINING-RUNS.md` (O-12 body)   | doc-only       |

Both revisions record the ruling choice the audit named earlier as "needs-design-decision"
without a settled answer. The dispatch's code path for the four per-card values still has to
wait for the PRD amendment (next paragraph).

#### Stage 1 · stopped

PRD §6.9 partial lift (line 172) cuts "no card levels, limit breaks or Unique Perk states,
because that is uma-tracker's abandoned promise and no user story replaced it." US-12 (line 37)
repeats the body: "no card level, limit break or Unique Perk state is stored anywhere
(`ADR-0014`: identity, not collection)." `ADR-0014`'s §"Decision" table puts `UserSupportCard`
on the **no** row with the reason "Collection tracking is still the feature §6.9 cut. The deck
records card identity, not ownership state", and its §"Not designed here" list names
"hint-level accumulation" alongside the other collection-style facts. Read together they
cover the four values O-8 names (level, limit break, bond, hint level) without exception.

No PRD section authorizes the change. No owner pre-approval is on the record in the ADRs I
can read. The backlog item 3 ("Add per-card deck state: level, limit breaks, bond, hint level
(O-8). Same gate.") is explicitly marked "Same gate" referring to item 2's "Needs a schema
proposal with a PRD citation before code, which makes it an Architect item."

The proposal at `docs/research-scratch/SUPPORT-CARDS.md`, section `## o8-per-card-state-proposal.md` records the four-column
schema (card_level, limit_break, bond, hint_level, all nullable on `deck_slots`), names the
two surface options (picker path or locked-tile path), enumerates the alternatives considered,
and quotes the PRD and ADR-0014 passages that gate the change. The owner decides whether to
amend §6.9 and US-12, and on approval re-issue Dispatch C from Stage 2.

#### Stages 2, 3, 4 · not run

No migration file created. No `deck-panel.blade.php` change. No `StoreDeckRequest` change. No new
`RunDeckTest` assertions.

#### Backlog status

| Item                                                             | Before                        | After                                                        |
| ---------------------------------------------------------------- | ----------------------------- | ------------------------------------------------------------ |
| Item 3 (per-card deck state schema)                              | open, gated                   | **open, gated, proposal lands**                              |
| Item 12 (deck as six tiles with locked state and reset action)   | open, needs design decision   | **open** (Stage 3 was the implementation; gated on item 3)   |

Until the PRD amendment lands, neither item closes. The proposal stays at
`docs/research-scratch/SUPPORT-CARDS.md`, section `## o8-per-card-state-proposal.md` for the owner's review.

#### Domain fingerprint

Computed at Dispatch C start with the per-table data-dump of `training_runs, turn_entries,
run_skills, deck_slots, support_cards, skills, race_catalog_slots` ordered by rowid, hashed
SHA-256. Baseline value: `7a4c8f74a95621e63b490267bfe5447a1adf3cbea245933fb3a590be46930ef8`.
The dispatch's "expected" value was `87164db8…` measured under the schema-dump method used at
Dispatch A end; the two methods are not comparable, so per the amended rule this is the new
baseline rather than a finding. End-of-Stage-1 fingerprint was not recomputed because the
dispatch wrote no schema change during Stage 1: the baseline carries.

### Part 10: the two orphans that already had a ruling (2026-10-04)

Not an audit item, and this audit names none of the eight component-library components. It is
recorded here because the missing UI this work was asked about turned out to be surfaces that
were already built and already ruled, and bypassed by inline copies of themselves. Which is what
SCREEN_SPEC.md §7-13 item 13 was holding open: the reason it kept `capsule-header` and
`grade-badge` was not that the treatment was undecided, but that the surfaces were still
carrying their own.

#### Landed

- `x-capsule-header` is mounted on the eight panels that hand-copied its div:
  `epithet-checklist`, `grade-point-meter`, `race-calendar`, `race-panel`, `shop-panel`,
  `spirit-burst-roster`, `team-race-panel`, `team-rank-gauge`. All eight had already drifted
  from the component (`pl-16` against `pl-20`, `text-sm` against `text-base`) while each one
  claimed to be the same header, which is the drift O-2's own note about repeated chrome is
  about. Nothing about the ruling changed: DESIGN.md §2.3 already made the lattice bleed
  material and chose the chrome fill.
- `x-grade-badge` is mounted in `stat-band`, which carried its own nine-letter fill map and its
  own badge span next to the letter it was banding. §2.1 already routed every grade letter
  through `ink-strong`; the map is the one that KI-8's crash came from, so one owner is the fix.

#### Not landed, and why

- `run-header` and `energy-gauge` are coupled and one cannot move without the other's tests
  failing. Mounting the gauge is also O-10, which asks for the Energy control to become a
  gauge: a design decision about a control this audit already flagged, not a wiring change.
- `deck-editor` is still gated on backlog item 3 (per-card deck state), open since Part 9 and
  waiting on the PRD amendment.

#### Gate added

`FrontendComponentLibraryTest` asserts the lattice bleed and the grade fill map each appear in
exactly one view, so a second copy fails the suite. Proven by re-inlining the shop panel header
during the work and watching that case go red.

### Part 11: baseline reconciliation (2026-10-04)

Two dispatches reported the suite differently and both claimed no test was deleted, skipped, or
weakened. The reconciled figure is **1,222 passed, 2 skipped, 18,275 assertions**, and the
1,189 / 18,210 line is a transcription error.

#### What the record actually contains

Neither figure is on disk. `docs/UIX-AUDIT-TRAINING-RUNS.md` never carried either, and a sweep of
`.scratch-uma/`, `docs/`, and the tracked history finds no `Tests:` line for either run. The only
suite figure recorded in a tracked file is `SLICE-RECORDS.md:1897`, which is a September slice
(`2 skipped, 390 passed (1287 assertions)`). So the reconciliation had to be made from git and
from a fresh run rather than by reading the two claims back.

#### The run that reproduces

`php artisan test --compact` at `fc64bc2` with the working tree as it stands:

```text
Tests:    2 skipped, 1222 passed (18275 assertions)
Duration: 163.19s
```text

Run twice, at 274.82s and 163.19s, with identical counts both times. A JUnit log of the same run
records `tests="1224" assertions="18275" errors="0" failures="0" skipped="2"` across 125 test
classes, which is 1,222 passed plus the 2 skipped.

#### Why the 1,189 figure is not a removed test

- `git log --diff-filter=D --name-only -- tests/` returns nothing. No test file has ever been
  deleted in this repository.
- `git diff --stat 1f0f9ae..fc64bc2` covers the four commits that moved `HEAD` during and after
  the orphan-mounting pass (`f1c18fc`, `9a09cc3`, `6238529`, `fc64bc2`, all between 11:32 and
  11:42 on 2026-10-04). It touches two documentation files and nothing else: 127 insertions, 2
  deletions, zero test files, zero source files.
- `git diff --stat -- tests/` is `+92 / -11` across six files. Every deletion is a line edit
  inside a case that still exists. `FrontendComponentLibraryTest.php` is `+56 / -0` and adds one
  dataset-driven case worth exactly two test cases, which moves the count by 2, not by 33.

#### The arithmetic points at a counted subset

The gap is 33 cases and 65 assertions, or 1.97 assertions per missing case. The suite averages
14.9 assertions per case. A removed test file carries its own assertion density with it, so a
removal cannot produce a slice at one eighth the suite's density. A partial or filtered run can.
No single class in the log carries 33 cases at 65 assertions either: the nearest are
`SupportCardTest` at 36 / 65 and `GametoraRaceCatalogParserTest` at 37 / 74.

#### Cross-worktree runs were checked and do not account for it

Two sibling worktrees exist, so a run against the wrong checkout is a real mechanism worth
ruling out rather than assuming. `C:/Users/exatf/AppData/Local/Temp/kilo/detail-page` at
`e22d05e` holds 773 raw `it(` / `test(` cases and
`.kilo/worktrees/cypress-cardamom` at `98953d0` holds 1,050. Neither is anywhere near 1,189, and
neither is on `master`.

#### Corrected figure

**1,222 passed, 2 skipped, 18,275 assertions** at `fc64bc2`. The 1,189 / 2 / 18,210 line should be
struck from the record.

#### Clean baseline (2026-10-04)

Run after the peer-dirty files landed, at `36b71b4`.

| Gate              | Command                                                        | Result                                              | Exit   |
| ----------------- | -------------------------------------------------------------- | --------------------------------------------------- | ------ |
| Full suite        | `composer test`                                                | 2 skipped, 1222 passed, 18275 assertions, 256.25s   | 0      |
| Format            | `vendor/bin/pint --test`                                       | passed                                              | 0      |
| Static analysis   | `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`   | No errors                                           | 0      |
| Lore (docs)       | `composer lore`                                                | 176 hits, 76 exempt lines                           | 0      |
| Lore (code)       | `composer lore-code`                                           | 47 hits                                             | 0      |

The suite count matches the corrected figure above exactly, so the number is now stable across two
runs and one landing. `composer test` runs `config:clear` and `npm run typecheck` ahead of the
suite, so the typecheck passed inside that exit 0.

Domain fingerprint, taken before the landings and again after the gates, identical both times:

```text
domain-fingerprint 49ad58bbeaf4dc64494b126b7df049453b1e8d985cea86789843e1f824fb6af6
training_runs 6, turn_entries 1, run_skills 34, deck_slots 6,
support_cards 559, skills 1910, race_catalog_slots 410
```text

#### What this baseline does not cover

Part 10 records the capsule-header and grade-badge mounting as landed, and Part 10 is now in
git, but eight of the nine panels carrying that work are not. `stat-band.blade.php` and
`epithet-checklist`, `grade-point-meter`, `race-calendar`, `shop-panel`, `spirit-burst-roster`,
`team-race-panel`, and `team-rank-gauge` are all still dirty in the working tree. The suite
passes against that dirty state, which is the whole point of a baseline, but the claim in Part 10
is not yet backed by `HEAD`. Landing them is the next dispatch's call.

## Open-question register and propagation audit, 2026-10-05

**Scope of this record.** The owner asked for every still-unverified item in `docs/UMAMUSUME_REFERENCE.md` and for
any other Umamusume knowledge in the corpus that needs updating. It is an audit of markers, not a slice: one file
changed no code, and the two defects it found in the corpus's own bookkeeping were fixed in place.

### 1. Marker census, measured rather than recalled

| File                                                                                                                                                        | `❌` lines  | `unverified` matches   | `⚠️ STALE`   |
| ----------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------- | ---------------------- | ------------ |
| `docs/UMAMUSUME_REFERENCE.md`                                                                                                                               | 69          | 68                     | 52           |
| `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`                                                                                                    | 20          | 21                     | 1            |
| `docs/research-scratch/SKILLS-MECHANICS.md`                                                                                                                 | 11          | 19                     | 0            |
| `docs/research-scratch/DESIGN-CORPUS.md`                                                                                                                    | 7           | 26                     | 2            |
| `docs/scenarios/09-global-race-calendar.md`                                                                                                                 | 7           | 3                      | 0            |
| `docs/scenarios/07-grand-concert.md`                                                                                                                        | 4           | 2                      | 0            |
| `docs/scenarios/08-grand-masters-jp-only.md`                                                                                                                | 5           | 0                      | 1            |
| `docs/research-scratch/SUPPORT-CARDS.md`                                                                                                                    | 0           | 10                     | 0            |
| `docs/research-scratch/AUDIT-AND-VERIFICATION.md`                                                                                                           | 1           | 15                     | 0            |
| `docs/research-scratch/CATALOG-ROSTER-WORKSTREAM.md`                                                                                                        | 0           | 5                      | 0            |
| `GOVERNANCE.md`, `RACE-AND-SLICE-RESEARCH.md`, `CHARACTERS-SOURCE.md`, guides `01`/`03`, `SCREEN_SPEC.md`, `ARCHITECTURE.md`, `PRD.md`, `KNOWN-ISSUES.md`   | 0-1         | 0-2                    | 0            |

Counts come from `grep -c` over each file on 2026-10-05 and they are line counts, not question counts: §8.4's own
counting correction states that ~67 occurrences decompose into roughly 30 distinct questions, because a marker
repeats across a status cell, a prose sentence and the 8.2 audit row that quotes it. **The instrument for the number is
`grep -c '❌ UNVERIFIED' docs/UMAMUSUME_REFERENCE.md`, not this table.**

### 2. The four propagation defects, all fixed by this audit

The failure mode is one the file has named before: *a marker outlives the read that closed it, and then a status cell
and an audit row disagree inside one file.* These are the cross-file instances.

| #     | Defect                                                                                                                                                                                                                                                                         | Where it sat                                            | Fix                                                                                                                                                                                                                                                                     |
| ----- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| P-1   | The per-level hint discount was ruled unsettled on the strength of two pointers into `docs/UMAMUSUME_REFERENCE.md` that no longer say what the paragraph claims: §1.1.4 does not print the `❌` sentence the paragraph quotes, and §8.4 records the gap **closed**, not open.  | `docs/research-scratch/SKILLS-MECHANICS.md` §2.2        | Dated note added. The subsection's position on *sources* stands as the record it is; its present-tense claims about the other file do not.                                                                                                                              |
| P-2   | §8.4 cites "1.1.4" as the place the closed ladder lives, and §1.1.4 did not contain it: the measured captions lived only in `SKILLS-MECHANICS.md` §2.4 and in the 68-frame capture note. A reader following the citation found a different statement.                          | `docs/UMAMUSUME_REFERENCE.md` §8.4 and §1.1.4           | The ladder is now printed in §1.1.4, so the pointer resolves, and conflict row 16 records that its own "in-client check" condition was met on 2026-10-03.                                                                                                               |
| P-3   | Tier labels: `DESIGN-CORPUS.md` §6.20 says codes 200, 300 and 700 `❌ UNVERIFIED` **in present tense**, while §1.2.6 closed 200 and 300 on two publishers on 2026-09-29 and D-153's own exception note says so.                                                                | `docs/research-scratch/DESIGN-CORPUS.md` §6.20          | Forward correction. 200 and 300 closed, 400 and 700 single-domain, and the rendering rule (tier as stored on the race row) unchanged, which is what the original sentence was protecting.                                                                               |
| P-4   | The `[Global]` notice that introduced Independent Training was cited four times as "title and date captured, URL not recorded", because `umamusume.com/news/NNN` renders client-side and the earlier pass could not get a URL out of it.                                       | `docs/UMAMUSUME_REFERENCE.md` 1.1.7, 1.6.0, 1.6.8, §6   | URL recovered (notice **100087**) through the news API, with the body read in full, and the timing claim corrected: the mode arrived **08:00 UTC** on 2026-07-22, not at the 22:00 update boundary it shared with Grand Concert. Recorded as an `AGENTS.md` §18 trap.   |

### 3. Closed by the same day's research, listed so the markers are not re-derived

- **TP** (the standing "what does TP govern"): it is the Career entry cost, 30 per run at a 100 cap, 1 per 10 minutes
  (`[Global]` Game8 538079, 2025-08-04 ⚠️ STALE, cross-checked against the client string "Restores 30 TP"). RP joined
  it: Team Trials entry, 1 per race, cap 5, 1 per 2 hours, with 1.6.5's Racing Carnival 1-per-attempt reading kept
  beside it because the currency is shared.
- **Story event record 1057**: named "Banquet of Shadows" from the vendor field the earlier pass said was absent, with
  the new hazard that its `story-event-NN` slug is not a cross-server key.
- **「SSRセレクトステップアップガチャ」**: official title confirmed, window 2026-08-24 12:00 to 2026-09-30 11:59 JST,
  purchase cap two cycles paid-Carat only, notice id 3415 resolved.
- **Trackblazer Alarm Clock retry**: three per run with an inventory requirement, and the wording is *that race*, which
  makes the "start of that semester" recollection the weaker reading. Which turn it resumes on is still unstated.
- **Our Grand Concert vocabulary**: rows 48 and 52 closed on notice 905 (Performance; Dance, Passion, Vocals, Visuals,
  Composure; Promo Concert, Grand Concert, lessons, songs, concert techniques, Live Performance Expectations, Great
  Success; the title's exclamation mark inside a Help path). Rows 49, 50 and 51 stay open.
- **The `[Global]` live-ops state**, refreshed from all 154 notices in the archive (§4.6), including four archive
  negatives: no Grand Masters, no Masters Challenge, no Training Pass and no "Pickup" string in any Global notice.
- **Conflict row 4 / 2.4 and conflict 4's 「Fully Charged」 half**, closed, and refuted as scenario chrome.
- **Repo #4's trainee image question**, probed rather than recalled. The legacy app's `images` column holds
  hand-fed booru downloads for a handful of characters, its `source` field reads `"fanart"`, and its app code
  makes no outbound request at all; it is the feature `PRD.md` §6.13 cut, and the cut was sound. What does work
  is a `card_id`-keyed third-party asset host, verified path by path, reachable from ids already committed in
  `database/seeders/data/` (268 card ids in the seed body, 106 of them kept as `character_cards` rows on a seeded
  tree, 559 support ids, 125 distinct skill icons across 1,910 rows that have no column to sit in).
  Bulk alternatives were each searched and each ruled out for a stated reason. Decided as `ADR-0021`, whose
  fetch half landed the same day (`uma:fetch-art`, `ArtworkMirror`, `SourceFetcher::fetchAsset()`, eight cases in
  `ArtworkMirrorTest` against `Http::fake`), while **no live pass has been run and no screen renders a file**.
  One thing the build found: skill icons are not reachable, because `skills` stores no `iconid` column and the
  125 distinct ids exist only in the committed dataset. The open half, which screens get a picture and whether
  the box is reserved, sits in `PRD.md` OQ-6 and `SCREEN_SPEC.md` §7-16 rather than being answered here.

### 4. What is still open, sorted by the instrument that would close it

**Needs a client capture (nothing else can reach these).** The Unique Perk badge question and its per-level value table
(conflict row 47, 1.4.7); the hint-stage colouring, whose premise row 15 now calls doubtful (1.4.4); Grand Concert's
two bonus layers and its 23 Song titles (rows 49 and 51); the reserve and confirm button labels; the gauge label as
displayed. One frame each, and two of these also fix the manifest's 0-frame row for the scenario.

**Needs an official notice that does not exist yet.** Whether Masters Challenge and Training Pass reach `[Global]`;
per-season Training Pass quantities and the season close time, which are published in-app; the `[JP]` 10th Masters
Challenge opening, with no announcement as of 2026-10-05; Grand Concert's successor on `[Global]`, unannounced across
the whole archive; Holiday Celebration Part 2, promised without a date.

**Needs a source that has been searched and does not publish it.** Grade code 700's numeric-to-label mapping (the label
is one publisher, the number is nobody's); the time-of-day values 2 and 3 (the label set is three, the codes are
unmapped); `SlopePer`'s unit and 10000 scale; `frontType` 3; the skill effect magnitudes stored as raw integers
(600000 / 400000); the bond-radius term "friendship radius", unattested in every source read; the energy-versus-failure
probability table; per-level SP totals for the card ladder; the inheritance uncap ranges.

**Needs an owner ruling, not a source.** Whether the `[Global]` label moves to the exclamation-marked title the notice
prints (row 52 says the export field is what everything currently joins on); whether Grand Concert gains any surface at
all (2.8's last paragraph, `SCREEN_SPEC.md` §7); whether D-241 and gate G-41 keep naming "the undescribed fourth
scenario" now that it is described; and whether `02-unity-cup.md:244`'s claim that Global still pays +2 SP on the four
energy disciplines survived the 2026-07-01 rework, which is the one dated game claim this audit found, flagged, and
could not settle from any source read.

---

## audit-status.md

## Audit status re-verification — Phase 0

Tree: `master` at `40018c08b71daf97fb0a06f53f39fcadab1e7997`, 2026-10-04.
Method: Read/Grep on the current file, `git log`/`git grep` for state. Every citation below is a
file:line **I read at this HEAD**, not the audit's citation. Finding definitions come from
`docs/research-scratch/AUDIT-AND-VERIFICATION.md` §2 table and the KI entries; the copy that used to
live at `docs/design-research/audit-verification-2026-10-01.md` is gone — a sibling reorg deleted that
directory, so the master is the only source of the definitions now.

Verdicts: `STILL OPEN` (fix it), `ALREADY FIXED` (skip, with sha), `CHANGED SHAPE` (the audit's
premise no longer describes the code), `BLOCKED` (fence conflict, nothing written).

### 1. Implement-now items

| Item                      | Verdict                | Evidence read at this HEAD                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |
| ------------------------- | ---------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| F-1 batch atomicity       | ALREADY FIXED          | `app/Actions/StoreCharacterCards.php:41`, `StoreRaceCatalogSlots.php:34` both open `DB::transaction`; commit `bd3f83d`                                                                                                                                                                                                                                                                                                                                                                                                                       |
| F-7 run multi-writes      | ALREADY FIXED          | `app/Http/Controllers/TrainingRunController.php:538` (`storeRace`), `:643` (`storeTurn`) inside `DB::transaction`; commits `13b50e5`, `08852c1`                                                                                                                                                                                                                                                                                                                                                                                              |
| F-3 SkillSeeder           | ALREADY FIXED          | `database/seeders/SkillSeeder.php:60` `firstOrCreate`, `:78` delete guarded by `where('is_manual', false)` with the OR clauses grouped; commit `9e65561`. File is peer-dirty (`M`, 3 lines) — not mine to commit                                                                                                                                                                                                                                                                                                                             |
| F-8 reparse lock          | ALREADY FIXED          | `app/Console/Commands/UmaReparse.php:43` uses the same `Cache::lock("uma-fetch:{$key}")` as `UmaFetch.php:55`; peer commit `fda8bba`                                                                                                                                                                                                                                                                                                                                                                                                         |
| F-5 snapshot path         | ALREADY FIXED (half)   | `SourceFetcher.php:60` `"snapshots/{$sourceKey}/{$hash}.html"`; commit `20364ae`. The other half (date-keyed short-circuit) was the point and is gone; KI-24/KI-27 posture stays an owner call                                                                                                                                                                                                                                                                                                                                               |
| F-2 seed failure exit     | STILL OPEN             | `database/seeders/SourceDocumentSeeder.php:81-91` catches `Throwable`, `Log::error`, `warn`, `continue` — loud in the log, exit code 0                                                                                                                                                                                                                                                                                                                                                                                                       |
| N-3 silent fetch null     | STILL OPEN             | `SourceFetcher.php:154` `catch (RequestException) { return null; }`, `:157` `$response->failed() ? null`, `:171` `->retry(..., 500, throw: false)`. No `Log` import in the file at all                                                                                                                                                                                                                                                                                                                                                       |
| F-9 redirect host         | STILL OPEN             | `SourceFetcher.php:170` `->maxRedirects(2)`; no post-redirect host assertion anywhere in the file                                                                                                                                                                                                                                                                                                                                                                                                                                            |
| F-10 / N-4 version key    | STILL OPEN             | `CatalogController.php:289` `Cache::remember('catalog:version', 3600, fn () => 0)`; `PipelineRunner.php:140`, `:267` `Cache::add('catalog:version', 0, 3600)` then `Cache::increment`                                                                                                                                                                                                                                                                                                                                                        |
| KI-56 seed re-run         | STILL OPEN             | `UmamusumeRosterSeeder.php:79` unconditional `MatchCandidate::create(...)` in the queued branch, against the partial unique index in `2026_10_01_124051_add_unique_index_to_match_candidates_table.php:19`. All three seeders are now tracked (`git ls-files database/seeders`), so KI-51's file half is closed and this one is reachable                                                                                                                                                                                                    |
| KI-41 order tiebreaker    | STILL OPEN             | `orderBy('name')` with no secondary key at `TrainingRunController.php:89`, `:301`, `:868`, `CatalogController.php:301`, `:312`, `SkillController.php:53`, `Api/V1/UmamusumeController.php:33` — seven sites, two of them paginated                                                                                                                                                                                                                                                                                                           |
| KI-39 factory match_key   | STILL OPEN             | `database/factories/SkillFactory.php:28` hand-rolls `mb_strtolower(str_replace('-', '', Str::slug($name)))`; production writers are `StoreSkills.php:69` and `SkillSeeder.php:68`, both `$normalizer->normalize($name)`                                                                                                                                                                                                                                                                                                                      |
| KI-40 option b            | STILL OPEN             | `NameNormalizer.php:22` `FOLDED_CHARACTERS` is still the five-item list with no ceiling note; no test pins the pairs that do not fold                                                                                                                                                                                                                                                                                                                                                                                                        |
| KI-50 option a            | STILL OPEN             | `git grep -l "sqlite_master" tests/` → no hits. `->check(` still present in `2026_09_30_142618_...php` and `..._151945_...php`. Read-only probe of the shared DB (`php .scratch-uma/cited/tier-probe.php`) returns real CHECK tokens on `deck_slots`, `support_cards` (two), `support_effects`, `scenario_slots`, `race_catalog_slots` — the constraints exist and nothing verifies them                                                                                                                                                           |
| KI-46 import row copy     | STILL OPEN             | `resources/views/runs/import.blade.php:153` prints `A {{ $stat }} value is outside what this scenario allows: {{ $message }}` over Laravel's default `between` text, which renders `The turns.0.speed field must be between 0 and 1400`. Confirmed instrument behavior: Blade `@error` calls `$errors->first($key)` (`vendor/.../CompilesErrors.php:21`), `MessageBag::get` matches the wildcard through `getMessagesForWildcardKey` (`MessageBag.php:197`), and `first()` returns only the first row's message (`MessageBag.php:170-177`)   |

### 2. Docs / config items

| Item                              | Verdict         | Evidence                                                                                                                                                                                                                                                                                                                                                     |
| --------------------------------- | --------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| E-1 backoff claim                 | STILL OPEN      | `ARCHITECTURE.md:246` "retry w/ backoff", `:276` "retry with exponential backoff max 2", `ARCHITECTURE-ESSENTIALS.md:52` "retry backoff max 2". Code: `SourceFetcher.php:171` flat `500` ms. Both target docs are clean (`git status --porcelain ARCHITECTURE*.md` empty)                                                                                    |
| C-3 config comments               | STILL OPEN      | `config/uma.php` lines 62, 128, 154, 198, 256 all credit "the cache TTL" with bounding fetch load. `config/uma.php` is clean; the TTL at `:33` (`'ttl' => 900`) is a read cache and is consulted after the fetch, so it cannot bound it                                                                                                                      |
| `.gitignore` tail as UTF-8        | ALREADY FIXED   | `file .gitignore` → "ASCII text"; first bytes are `# L a r a v e l` with no UTF-16 BOM or NULs; line 102 already reads `docs/vibe_images/` and `git check-ignore -v docs/vibe_images/` → `.gitignore:102:docs/vibe_images/`. Nothing to rewrite                                                                                                              |
| welcome.blade.php forward notes   | BLOCKED         | The three citations live in root `KNOWN-ISSUES.md`, which a peer has open as a 2,655 → 71 line rewrite (register moved to `docs/research-scratch/AUDIT-AND-VERIFICATION.md`). `git diff --stat -- KNOWN-ISSUES.md` = 2,722 deletions staged in the working tree. Committing it would land a peer's restructure under my message. Draft text is in §5 below   |
| KI-23 vs KI-23b reconciliation    | BLOCKED         | Same file. The unreconciled pair is described at `AUDIT-AND-VERIFICATION.md:906` ("register KI-23 headline still OPEN pending its own re-read — the two entries must be reconciled") and `:3135`                                                                                                                                                             |
| APP_NAME                          | STILL OPEN      | `.env.example:1` is `APP_NAME=Laravel`. `.env` itself is gitignored and not touched                                                                                                                                                                                                                                                                          |

### 3. UI items under the WCAG 2.2 AA mandate

| Item                         | Verdict         | Evidence                                                                                                                                                                                                                                                                                               |
| ---------------------------- | --------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| L-F01 target size            | ALREADY FIXED   | `resources/views/components/layout.blade.php:45,49,52,53,54,61` all six nav links carry `min-h-11`; `runs/show.blade.php:55,56` the two export links, `:655` the skill-catalog helper link, `:448` the per-turn delete button and `:812` the `<summary>` all carry `min-h-11`. Peer commit `ecae77d`   |
| F-04 error envelope          | ALREADY FIXED   | `runs/show.blade.php:571-576` one `<ul>` envelope, comment names "static review F-04", `previewed` is in the `hasAny` key list. Peer commit `a3e323c`                                                                                                                                                  |
| KI-35 "Unknown" debut copy   | ALREADY FIXED   | `resources/views/catalog/show.blade.php:164` and `:172` render `<span title="The source publishes no JP debut date for this trainee.">N/A</span>`; the word "Unknown" survives only inside the comment at `:159` explaining why it was dropped. Commit `80caefd`                                       |
| KI-46 copy fix location      | SCOPE NOTE      | The message rewrite can be confined to `app/Http/Requests/ImportHistoricalRunRequest.php`, which is clean. The view is peer-dirty and its line 153 prefix would then double up, so the view half stays undone and is reported                                                                          |

### 4. Owner-decision items — verification only, no code written yet

| Item                                             | State at this HEAD                                                                                                                                                                                                                                                                                                                                                        |
| ------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| F-6 dead job                                     | RESOLVED BY PEER — `app/Jobs/` is empty, `FetchSourceJob.php` deleted at `fda8bba` (32 lines). Nothing to propose                                                                                                                                                                                                                                                         |
| F-4 unbounded growth                             | HALF LANDED BY PEER — unique indexes exist: `2026_10_01_124039_add_unique_index_to_data_sources_table.php:15` on `(umamusume_id, source_key, url)`, `2026_10_01_124051_...:19` partial unique on `match_candidates`. `git log --diff-filter=A` attributes both to `fda8bba`. What is left is the duplicate-count evidence, which those indexes now prevent structurally   |
| F-5 / KI-24 / KI-27                              | Shape changed as above; still an owner call on the snapshot retention policy                                                                                                                                                                                                                                                                                              |
| C-4 `Scenario::cap_*` unread                     | STILL TRUE — `cap_speed` appears only in `app/Models/Scenario.php:25,39,57` (declaration, fillable, cast) and in the parser write path `GametoraScenarioParser.php:78,80`; `ScenarioCaps` remains the only reader of ceilings                                                                                                                                             |
| KI-55 register paths                             | CONFIRMED CONFLICT — `docs/GATE-REGISTRY.md` and `docs/PRE-MORTEM.md` do not exist (`ls` → "No such file or directory" for both); the live register is `docs/research-scratch/GOVERNANCE.md`, and `AGENTS.md` §2 already points there                                                                                                                                     |
| KI-45 tier NULL                                  | REPORT ONLY, AS ASKED — read-only query at this HEAD: `scenario_slots` total **296**, `tier IS NULL` on **296 of 296**. The KI-45 correction's figure holds. Probe: `php .scratch-uma/cited/tier-probe.php`                                                                                                                                                                     |
| KI-38, KI-42, KI-43, KI-25/R82, KI-10/15, F-01   | Not re-verified in this pass; they are proposal-only. `KI-42`'s premise was spot-checked: no `.github/workflows/` job exists                                                                                                                                                                                                                                              |

### 5. Draft text for the two BLOCKED `KNOWN-ISSUES.md` notes

Not applied; `KNOWN-ISSUES.md` is peer-dirty by 2,722 lines. Either the peer lands their restructure
first and I append to the new shape, or the owner rules that I may commit the file whole.

For the welcome.blade sweep (`65f8b92` deleted `resources/views/welcome.blade.php` on 2026-09-29 while
closing KI-20; the citations survived):

> **Corrected forward 2026-10-04.** The three `resources/views/welcome.blade.php` citations in this
> register describe a file that no longer exists. It was deleted at `65f8b92` on 2026-09-29 in the same
> commit that closed KI-20. Verified at this HEAD: `ls resources/views/welcome.blade.php` reports no
> such file. The KI-3 and KI-20 closure records stay accurate as history; they are not live claims
> about the tree.

For KI-23 vs KI-23b:

> **Corrected forward 2026-10-04.** KI-23 and KI-23b are two entries, not one. KI-23b (`d755da3`,
> renumbered from KI-21 on merge) is the parser's wrong source key and is resolved. KI-23 is the
> `uma:fetch` headline plus the fixture that repeated the wrong key, and it stays open until its own
> re-read. `docs/research-scratch/AUDIT-AND-VERIFICATION.md:906` records the same split.

### 6. Shared-database proof for this pass

`database/database.sqlite` was read through `PRAGMA query_only = ON` only.

| File                             | sha256 before       | sha256 after                  |
| -------------------------------- | ------------------- | ----------------------------- |
| `database/database.sqlite`       | `cda5984c…40c9a`    | identical                     |
| `database/database.sqlite-wal`   | `ad7b3e58…fcd6f`    | identical                     |
| `database/database.sqlite-shm`   | `c4c18148…50f14a`   | changed (`361c2df3…bed542`)   |

The `-shm` file is SQLite's shared-memory index, not data; it changes on any attach. The two files that
carry content are byte-identical, so the shared database holds what it held before this pass.

### 7. Peer-dirty inventory that constrains the coming commits

`git status --porcelain` at this HEAD lists ~60 modified/deleted paths from sibling sessions. The ones
inside this dispatch's write set: `KNOWN-ISSUES.md` (2,722 lines), `AGENTS.md` (921), `CLAUDE.md` (653),
`PLAN.md` (727, and PLAN.md is on the do-not-edit list), `DESIGN.md` (314), `CONSTRAINTS.md` (57),
`resources/views/runs/import.blade.php` (11 lines, two hunks at 110 and 137), `database/seeders/SkillSeeder.php`
(3), `resources/views/catalog/partials/form-detail.blade.php` (7), `app/Http/Controllers/SkillController.php`
(31), `docs/research-scratch/GOVERNANCE.md`. Clean and safe to commit with an explicit pathspec:
`app/Services/DataPipeline/SourceFetcher.php`, `app/Services/DataPipeline/PipelineRunner.php`,
`app/Http/Controllers/CatalogController.php`, `app/Http/Requests/ImportHistoricalRunRequest.php`,
`database/factories/SkillFactory.php`, `app/Services/DataPipeline/NameNormalizer.php`,
`database/seeders/UmamusumeRosterSeeder.php`, `database/seeders/SourceDocumentSeeder.php`,
`config/uma.php`, `.env.example`, `ARCHITECTURE.md`, `ARCHITECTURE-ESSENTIALS.md`, `.gitignore`,
`TrainingRunController.php`, `CatalogController.php`, `Api/V1/UmamusumeController.php`.
`app/Http/Controllers/SkillController.php` is peer-dirty, so the KI-41 fix there is reported, not landed.

---

## rice-shower-unity-cup-ux-walk.md

> **Augmented 2026-10-10 (Round 14, augment-only).** Source: `docs/audits/rice-shower-unity-cup-ux-walk.md` (957 lines), reproduced verbatim below with headings demoted one level. The source file remains in place; no citation was repointed and nothing was deleted.

## UX walk audit: [Rosy Dreams] Rice Shower, Unity Cup

### Header

| Field                | Value                                                                                                              |
| -------------------- | ------------------------------------------------------------------------------------------------------------------ |
| Audit name           | Rice Shower Unity Cup mid-career UX walk                                                                            |
| Source document      | `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` (67 `[Global]` client frames, 2026-10-03; a mid-career snapshot)        |
| Starting URL         | `http://127.0.0.1:8000/`                                                                                            |
| Date/time of walk    | 2026-10-10, single continuous session                                                                               |
| Environment          | Laravel 13.32.0, PHP 8.5.8, SQLite (`database/database.sqlite`), `php artisan serve` on `127.0.0.1:8000`, Chromium 150 via the browser-use tool |
| Run created          | `training_runs` id **8** (`/training-runs/8`), status Active, scenario Unity Cup                                      |
| Screens visited      | 18 (listed in visit order below)                                                                                    |

**Method statement.** Every screen below was reached in a real browser session and its contents were
read from the rendered DOM at the time of the visit. Every value recorded was typed or selected
through the on-screen controls (native `<select>`, number/text inputs, radio and checkbox controls,
and the app's own buttons). No seeder, no direct database insert or update, no SQL, no tinker, no
API call, and no edit to application state was used to enter data. Screen-to-screen navigation from
the run record onward used the link targets the UI itself exposes (the same routes the on-screen
links point at); the data-entry controls were always operated directly. Source code was read only to
explain the one server error observed (screen 15).

**One pass.** The walk was made once, forward. No screen was re-entered to change an earlier choice,
and no screen was skipped. Where the UI required a value the source document does not carry, the
minimum available choice was made and is flagged below as **Not present in source — UI-required
choice**. The run was never marked Completed, never filed as a Veteran, and never filed as a Legacy.

**Nothing was fixed.** No route, controller, view, component, migration, model, seeder, test,
validation rule, label, field, or schema was changed. `git status` after the walk shows the same
pre-existing uncommitted files that were present before it began (a shared working tree carrying
other work); this audit added one file, `docs/audits/rice-shower-unity-cup-ux-walk.md`, and nothing
else.

---

### Screen 1 — Dashboard

#### Route and trigger
- Route: `/` (`home`), title "Dashboard".
- Trigger: initial navigation to the starting URL.

#### What the screen asks for
Read-only. Regions in order: nav rail (Dashboard, New Career, Careers, Legacy Lab, Veterans, Support
Cards, Skills, Review, Database, Settings); ACTIVE CAREER (empty state + "Start a new training run");
Quick actions (New Career, Legacy Lab, Support Cards); RECENT VETERANS (3 of 7); RECENT BUILDS;
DATA STATUS (Global data, "Verified 2026-09-27", Ruleset `N/A`, Trainees 67, Skills 1,910, Support
cards 559). The only control used was the "Start a new training run" link.

#### Values entered + source trace
None entered on this screen.

#### Source data with no UI home
None relevant on this screen.

#### Friction / errors / dead ends
- RECENT VETERANS lists 7 rows produced by earlier work in this shared tree, none of them this run.
  Not a defect, but it means the Dashboard's "no active career" state sat beside 7 existing careers,
  so the landing state is not a clean first-run state on this database.
- `Ruleset: N/A` and `Ruleset snapshot: N/A` recur on nearly every later screen. The app states why
  in the cockpit ("No source defines a Global ruleset version, so this run stores none to print"), so
  this is an intentional `N/A`, not a gap.

#### Outcome / next screen
Clicked "Start a new training run" → `/career/setup/scenario`.

---

### Screen 2 — Scenario setup (wizard step 1 of 6)

#### Route and trigger
- Route: `/career/setup/scenario`, title "Choose a scenario".
- Trigger: clicked the Dashboard's "Start a new training run".

#### What the screen asks for
A step nav (Leave setup; Scenario, Trainee, Your target, Legacy, Support deck, Preflight) and four
scenario cards. Each card prints eight facts (Optimizes, Systems, Tracked resources, Turn loop, Live
on Global, Ruleset, Description, Recommended use) and one `Select Scenario` button. A "Stored choice"
readout sits above the cards. There is no Continue button: forward movement is the step nav.

#### Values entered + source trace
- `Unity Cup` → the Unity Cup card's `Select Scenario` button → source: `[Rosy_Dreams]Rice_Shower_Unity-Cup.md` §1.1, line 19 ("Scenario Unity Cup, confirmed by the logo panel").

#### Source data with no UI home
- Nothing from §1.1 is asked for here beyond the scenario name; the run's identity (trainee, year,
  month, turns remaining) has no home on this screen.

#### Friction / errors / dead ends
- On all four cards, `Ruleset`, `Description` and `Recommended use` render `N/A`. Three of the eight
  facts per card are therefore empty for every scenario, which makes the comparison cards mostly
  scaffolding. (Consistent with the repo's "unrecorded renders as N/A" rule, but the effect on this
  screen is that two thirds of the decision surface is blank.)
- After clicking, the card flashed "Saving your choice…" then "Scenario set.", the button became
  `pressed="true"` with a "Selected" label, and `Stored choice:` changed from `N/A` to `Unity Cup`.

#### Outcome / next screen
Clicked the step-nav "Trainee" link → `/career/setup/trainee`.

---

### Screen 3 — Trainee (wizard step 2 of 6)

#### Route and trigger
- Route: `/career/setup/trainee`, title "Choose a trainee".
- Trigger: clicked "Trainee" in the wizard step nav.

#### What the screen asks for
Filters in order: `Search` (text), `Surface` (select: Any,S..G), `Distance` (select: Any,S..G),
`Running style` (select: Any,S..G), `Unique skill` (select, ~130 options), `Sort by` (select: Name,
Turf, Dirt, Sprint, Mile, Medium, Long, Front runner, Pace chaser, Late surger, End closer), `Order`
(select: Ascending/Descending), `Filter` (submit), `Clear filters`. Then a paginated roster of 67
trainee cards (3 pages). Each card carries: portrait, English name, Japanese name, "Released
(Global)", ten aptitude chips (Turf, Dirt, Sprint, Mile, Medium, Long, Front runner, Pace chaser,
Late surger, End closer — each with a letter and a word), a costume-form summary, a `Select Trainee`
button, and a `View Profile` link.

#### Values entered + source trace
- `Rice Shower` → the `Search` field → source: `[Rosy_Dreams]Rice_Shower_Unity-Cup.md` §1.1, line 20 ("Trainee Rice Shower, `[Rosy Dreams]` card, ★3, Potential Lvl 2").
- `Select Trainee` on the Rice Shower card (`id="trainee-30"`) → source: same line 20.

#### Source data with no UI home
- **★3 and Potential Lvl 2 (§1.1 line 20)**: the card carries no star rating and no Potential Level
  field anywhere on this screen. There is no control that would accept either.
- **[Rosy Dreams] — the costume**: the card prints "2 costume forms" and two badges, "★★★SSR debut
  form" and a second "★★★SSR" with **no name at all**. The portrait rendered is
  `/artwork/card_portrait/103001`, the debut form. Nothing on the screen lets the user say which
  costume form the career uses; the run's own form is the unlabelled one.

#### Friction / errors / dead ends
- The aptitude row the UI printed for Rice Shower is **Turf A, Dirt G, Sprint E, Mile C, Medium A,
  Long A, Front runner B, Pace chaser A, Late surger C, End closer G**. The source document records
  **Mile B, Front runner A, Late surger B** for the same trainee (§1.1, lines 58-59, read in-client
  from frame `1dde6b2f`). Three of ten aptitude letters differ. Recorded as observed; the audit does
  not decide which side is right.
- After `Select Trainee`, the screen flashed "Trainee set: Rice Shower." but the `Stored choice:`
  readout on the same screen still read **`N/A`**, and the search filter cleared (the list returned
  to all 67). So the step's own confirmation of what it stored did not update.

#### Outcome / next screen
Clicked "Your target" in the step nav → `/career/setup/target`.

---

### Screen 4 — Your target (wizard step 3 of 6)

#### Route and trigger
- Route: `/career/setup/target`, title "Your target".
- Trigger: clicked "Your target" in the step nav.

#### What the screen asks for
- `Purpose` (select: Not chosen yet, Story Clear, Champions Meeting, Parent Farming, Skill Farming),
  with a note that a fifth purpose, Competitive Build, is not recorded.
- `Distance` (select: Not chosen yet, Sprint, Mile, Medium, Long).
- `Surface` (select: Not chosen yet, Turf, Dirt).
- `Style` (select: Not chosen yet, Front Runner, Pace Chaser, Late Surger, End Closer).
- `Stat targets`: five number inputs (Speed/Stamina/Power/Guts cap 1300, Wit cap 1800), each with a
  `Cap` label and a ratio readout.
- `Skill priorities`: a free-text `Skill name` + `Add`, an ordered list, with a note that risk
  tolerance and per-skill marks are not recorded.
- A live `Summary` region and a `Save target` button.

#### Values entered + source trace
| Value                    | Field          | Source                                                                                                                                  |
| ------------------------ | -------------- | --------------------------------------------------------------------------------------------------------------------------------------- |
| `Story Clear`            | Purpose        | **Not present in source — UI-required choice.** The snapshot records no build purpose; the field is required.                            |
| `Long`                   | Distance       | Partly sourced: §1.1 line 59 "A Medium/Long build"; §3 line 461 names Arima Kinen at 2500 m. **Single-select choice not in source** (Medium and Long are both A). |
| `Turf`                   | Surface        | §1.1 line 58 ("Turf A"); §1.6 lines 186-187 (both race-day options are Turf/Dirt, her races Turf).                                        |
| `Pace Chaser`            | Style          | §1.1 line 59 ("the pace half available", "Pace A"); §1.3 line 66 (Pace Chaser Straightaways ○ owned).                                     |
| `1300, 1300, 1300, 1300, 1800` | Stat targets | **Not present in source — UI-required choice.** These are the form's own printed caps. See the friction note below.                      |

#### Source data with no UI home
- **The run's own caps (§1.2, lines 28-32): Speed 1325, Stamina 1322, Power 1368, Guts 1308, Wit
  1800.** The screen prints the scenario's base caps (1300/1300/1300/1300/1800) and its inputs are
  `max=1300` / `max=1800`. Three of the run's four documented caps (1325, 1322, 1368) **exceed the
  form's maximum and cannot be entered at all**. §1.2's whole cap-arithmetic discussion has no
  representation here.
- **The cap arithmetic's inputs (§1.2 lines 39-56): the six blue sparks and any support-card Max-Stat
  layer.** This screen asks for a *target*, not a cap composition. It asks for no spark and no
  support-card cap contribution, so the question §1.2 raises ("what raises the other three extras")
  is not asked anywhere in the wizard.
- **Potential Level 2 and the growth row +0/+10/+0/+20/+0 (§1.1 line 20, §7 item 2):** no field.
- **Fans 209,245 / class Star / the 30,755-fan gap (§1.1 lines 21-22):** no field.

#### Friction / errors / dead ends
- Submitting with everything empty produced four `role="alert"` errors ("The purpose field is
  required." etc.) **and five more, one under each stat**: "Enter a target for every stat, or leave
  the whole target unset." — even though every stat field was empty, i.e. the state that message says
  is acceptable. Submitting again with the four selects filled and the stats still empty left the
  five stat errors in place. So the "leave the whole target unset" branch is unreachable: the target
  is never wholly unset, because four of its fields are required. Entering all five stats cleared
  them. (Recorded as observed; no code was changed.)
- Because the stats could not be left empty, the five numbers entered are the form's own caps and are
  not sourced from the run. On this screen, the run's documented caps are both unenterable and not
  what the field asks for.
- The screen's own copy says "the cap is the server's own ceiling for the chosen scenario", which is
  true; but the run's real caps sit above that ceiling, so the tool's ceiling is lower than the run's.

#### Outcome / next screen
Clicked `Save target` → "Build target saved.", summary updated to "Purpose Story Clear; distance
Long; surface Turf; style Pace Chaser; Speed 1300, Stamina 1300, Power 1300, Guts 1300, Wit 1800;
skills in order: none listed yet." Then clicked "Legacy" in the step nav → `/career/setup/legacy`.

---

### Screen 5 — Legacy (wizard step 4 of 6)

#### Route and trigger
- Route: `/career/setup/legacy`, title "Legacy".
- Trigger: clicked "Legacy" in the step nav.

#### What the screen asks for
The screen states it records and compares but computes nothing. Two parent panels (PARENT A, PARENT
B), each with:
- a readout of the chosen parent's own rank and spark chance;
- `Assign to Parent N` (select, listing the saved Veteran library: Not chosen, Vodka, Vodka, Daiwa
  Scarlet, Haru Urara, Mayano Top Gun, Maruzensky, Maruzensky);
- `Grandparent N1` and `Grandparent N2` (type-ahead comboboxes, free text);
- an "AS YOU READ HER" block: `Own rank` (number), `Rented for the run` (checkbox), and a `SPARKS`
  list with `Add Spark to Parent N` / `Remove Spark from Parent N`;
- each spark row carries `Kind` (select: Blue, Pink, Green, White, Scenario), `Applies to` (free
  text), `Stars` (number, min 1 max 3).
Then `Affinity for the pair` (select: Not recorded, △, ○, ◎), `Save Legacy`, `Next: Support deck`.

#### Values entered + source trace
| Value                                  | Field                             | Source                                                                                                     |
| -------------------------------------- | --------------------------------- | ---------------------------------------------------------------------------------------------------------- |
| `Vodka`                                | Assign to Parent A                | §1.5 line 138 ("Legacy 1  Vodka [Wild Top Gear]  rank B+")                                                  |
| `Daiwa Scarlet` / `Seiun Sky`          | Grandparent A1 / A2               | §1.5 lines 139-140                                                                                          |
| Blue / `Power` / ★1                    | Spark 1 on Parent A               | §1.5 line 148 (Vodka Blue: "Power ★☆☆")                                                                     |
| Pink / `Late Surger` / ★2              | Spark 2 on Parent A               | §1.5 line 148 (Vodka Pink: "Late Surger ★★☆")                                                               |
| Green / `Cut and Drive!` / ★2          | Spark 3 on Parent A               | §1.5 line 148 (Vodka Green unique: "Cut and Drive! ★★☆")                                                    |
| `Maruzensky`                           | Assign to Parent B                | §1.5 line 141 ("Legacy 2  Maruzensky [Formula R]  rank UG, flagged Guest")                                  |
| `Daiwa Scarlet` / `Taiki Shuttle`      | Grandparent B1 / B2               | §1.5 lines 142-143                                                                                          |
| `Rented for the run` = checked         | Parent B                          | §1.5 line 141 ("flagged Guest") — the UI then prints "Rented from a friend"                                  |
| Blue / `Speed` / ★2                    | Spark 1 on Parent B               | §1.5 line 151 (Maruzensky Blue: "Speed ★★☆")                                                                |
| Pink / `Turf` / ★2                     | Spark 2 on Parent B               | §1.5 line 151 (Maruzensky Pink: "Turf ★★☆")                                                                 |
| Green / `Red Shift/LP1211-M` / ★1      | Spark 3 on Parent B               | §1.5 line 151 (Maruzensky Green unique: "Red Shift/LP1211-M ★☆☆")                                           |
| (left unset)                           | Affinity for the pair             | **Not present in source.** §1.5 states no pair affinity grade, so it stayed "Not recorded" and rendered `N/A`. |

#### Source data with no UI home
- **The two parents' end-of-run ranks, B+, A, UG (§1.5 lines 138-143).** The only rank field is
  `Own rank`, a number input whose helper text says it is the "Legacy's own star count". A letter
  class (B+, A, UG) has no field. It was left blank, so the Preflight summary printed `Rank N/A` for
  both parents.
- **The four grandparents' sparks (§1.5 lines 149, 150, 152, 153).** Daiwa Scarlet (Legacy 1 side),
  Seiun Sky, Daiwa Scarlet (Legacy 2 side) and Taiki Shuttle each carry a full Blue/Pink/Green/White
  spark set in the source. **The screen has a SPARKS block for PARENT A and PARENT B only** — the four
  grandparent fields are name-only. Four of the tree's six spark sets have no UI home. In particular
  the source's "six blue sparks" (§1.5 line 159) can only be two here.
- **The white sparks.** Not entered (see the completion note); they have a home (`Kind = White`) but
  the mechanism is identical to the coloured sparks that were entered.
- **The two parents' epithets, `[Wild Top Gear]` and `[Formula R]` (§1.5 lines 138, 141).** The
  `Assign to Parent` options read as bare names; there is no epithet field and no place to type one.
- **The ancestor's own star count / the "no ceiling" note** is offered as `Own rank`, but the source
  gives no star count for any ancestor, so it stayed blank.

#### Friction / errors / dead ends
- The `Assign to Parent A` list contains **Vodka twice and Maruzensky twice** with no
  disambiguating detail (no epithet, no rank, no id). The two entries are visually identical; the
  user cannot tell which saved record is which.
- Spark rows: clicking `Add Spark to Parent A` appends a **blank trailing row** (Kind Blue, no
  `Applies to`, no `Stars`). That blank row is not a hint — after `Save Legacy` the Preflight summary
  listed Parent A's sparks as "Blue on Power★1 / Pink on Late Surger★2 / Green on Cut and Drive!★2 /
  **Blue**", i.e. **the empty trailing row was stored as a real spark** with no stat and no stars.
  This is observable on the Preflight screen (screen 7) and was not corrected by the app.
- The `Applies to` field accepts three different kinds of value (a stat name, an aptitude name, a
  skill name) with no list and no validation; the only clue is the spark's `Kind`.
- `Rented for the run` is the only representation of the source's "flagged Guest" parent; it renders
  as "Rented from a friend", which is a different phrase but the right idea.
- After `Save Legacy` the flash read "Legacy recorded." and the blank trailing spark row was still
  present in the editor.

#### Outcome / next screen
Clicked `Next: Support deck` → `/career/setup/deck`.

---

### Screen 6 — Support deck (wizard step 5 of 6)

#### Route and trigger
- Route: `/career/setup/deck`, title "Support deck".
- Trigger: clicked "Next: Support deck" on the Legacy screen.

#### What the screen asks for
Six slots (Slot 1..5, and `Slot 6 · Friends`, labelled "Friend slot"). Each slot carries the equipped
card or an empty state, an `Ownership` control (`Owned` / `Rented`, a two-button toggle starting at
`N/A`), and `Replace the card in Slot N`. Below: `Save deck`, a `Choose a card` picker with a `Type`
filter (All, Speed, Stamina, Power, Guts, Wit, Pal, Group), a `Name contains` field, a `Filter`
button, a `Card for Slot N` select listing every catalog card, and `Equip owned to Slot N` / `Equip
rented to Slot N` / `Set Slot N to not equipped`. A `Deck analysis` region fills in as cards land.

#### Values entered + source trace
| Value                                            | Field                             | Source                                                                        |
| ------------------------------------------------ | --------------------------------- | ----------------------------------------------------------------------------- |
| `Tokai Teio [Dream Big!] · SSR Speed` / Owned     | Slot 1                            | §1.4 line 79 (deck table row 1)                                                |
| `Nishino Flower [Even the Littlest Bud] · SSR Speed` / Owned | Slot 2                 | §1.4 line 80                                                                   |
| `Biko Pegasus [Double Carrot Punch!] · SSR Speed` / Owned | Slot 3                    | §1.4 line 81                                                                   |
| `Matikanetannhauser [Just Keep Going] · SSR Guts` / Owned | Slot 4                    | §1.4 line 83                                                                   |
| `Haru Urara [Urara's Day Off!] · SSR Guts` / Owned | Slot 5                           | §1.4 line 84                                                                   |
| `Super Creek [Piece of Mind] · SSR Stamina` / **Rented** | Slot 6 · Friends          | §1.4 lines 82 and 86-88 (the friend card is Super Creek)                       |

Slot numbering for the five owned cards is **Not present in source — UI-required choice**: §1.4 gives
the deck as a list, not a slot order. The friend card is placed in Slot 6 because the source names it
the friend card and the UI names Slot 6 the friend slot.

#### Source data with no UI home
- **Card levels and limit breaks (§1.4 lines 77-84, the "Client level" column: 35/35, 30/30, 30/30,
  50/50, 30/30, 35/35).** No field on any deck surface accepts a card level.
- **The per-card "Client rows visible" values (§1.4 lines 97-104)** — the effects this run has
  actually reached. The Deck analysis explicitly uses catalog maxima instead ("Every figure is an
  effect's highest stated anchor … never an interpolation for a level this deck holds"), so the
  run's reached values have no home.
- **The sixth slot's "Friends" pill origin (§1.4 line 86, §7 item 8):** the source derives the
  friend card from the Career Profile rail. Here the role is attached to the *position*, which is a
  reasonable mapping, but the fact that Super Creek is borrowed from another account survives only as
  the `Rented` toggle, and (see screen 18) that toggle is not persisted.

#### Friction / errors / dead ends
- The screen's own copy warns: "The owned or rented flag is kept in this setup draft. The deck table
  has no column for it yet, so Preflight writes the six cards it can store and the flag stays here
  until one exists." The rented flag is therefore knowingly non-persistent, and screen 18 confirms it
  is lost.
- The picker defaults to Slot 1 and its target only changes via the per-slot `Replace the card in
  Slot N` button; the button that equips keeps the slot name in its label ("Equip owned to Slot 4"),
  which is the only cue that the target moved.
- Deck analysis states "Friendship Bonus … The export marks this effect as multiplicative, so the
  values above are listed separately rather than added", while Mood Effect, Specialty Priority,
  Training Effectiveness, Race Bonus, Fan Bonus etc. are summed. Correct, but two adjacent blocks
  treat their numbers differently without a visual separator beyond the note.
- The six card pickers render fine and the analysis block is populated only after a card lands
  ("Nothing to analyse yet" beforehand), which matches its own copy.

#### Outcome / next screen
Clicked `Save deck` → "Deck saved." (URL gained `?deck_slot=6`). Then clicked "Next: Preflight" →
`/career/setup/preflight`.

---

### Screen 7 — Preflight (wizard step 6 of 6)

#### Route and trigger
- Route: `/career/setup/preflight`, title "Career contract".
- Trigger: clicked "Next: Preflight" on the deck screen.

#### What the screen asks for
Read-only summary, then the wizard's one write. It prints: a warning line ("No warnings. Every
section is filled…"), Build (trainee, scenario, target, ancestry with parents, grandparents and
sparks), Support deck (six slots with type and ownership), a full Deck analysis, Target (five stat
values, race profile, skill priorities, ruleset snapshot), and four controls: `Back`, `Edit Legacy`,
`Edit Deck`, `Edit Target`, and `Start Career`.

#### Values entered + source trace
Only the final action: clicked `Start Career` (no value). Every value shown is traced above.

#### Source data with no UI home
- The ancestry block confirms the gap found on screen 5: **four grandparents appear as names only,
  with no sparks**, and the parents' ranks read `N/A`. The four grandparent spark sets from §1.5
  (lines 149, 150, 152, 153) are absent from the contract.

#### Friction / errors / dead ends
- The summary's Parent A spark line reads "Blue on Power★1 / Pink on Late Surger★2 / Green on Cut and
  Drive!★2 / **Blue**". The trailing bare "Blue" is the blank spark row from screen 5, now part of the
  contract. A user reading this screen would see a fourth spark that was never intended.
- "Affinity N/A" is correct (the source gives no grade) but appears with no explanation on this
  screen.
- "Skill priorities: None entered" and "Ruleset snapshot: N/A" both read as gaps, though both are
  honest states.

#### Outcome / next screen
Clicked `Start Career` → "Run created." → `/training-runs/8/cockpit`.

---

### Screen 8 — Career Cockpit

#### Route and trigger
- Route: `/training-runs/8/cockpit`, title "Career: Rice Shower".
- Trigger: clicked `Start Career` on Preflight (the wizard's one write).

#### What the screen asks for
Read-only. Run record (scenario, YEAR, MONTH, TURN, Team Rank, Spirit Bursts); Stats and state (five
stats against caps, Energy, Mood, Fans, Skill Points); Advisor (recommendation + why); an empty-state
block; Actions (Training, Race, Rest, Recreation, Scenario action, Event, Inheritance); a Scenario
block (Unity Cup, team race circles, the rank ladder G..S+, team stat grades, Spirit Burst states,
Current Spirit, the burst-count band table, Special Training notes); Races in this run (recorded,
this turn, still to come); and links to the Career Timeline and Career Result.

#### Values entered + source trace
None entered. (The screen was re-read later — see the persistence note under screen 9.)

#### Source data with no UI home
- **Fans class and the fan ladder (§1.1 lines 21-22).** Fans is a free number; the class ("Star"),
  the Top Star threshold (240,000) and the 30,755-fan gap have no field.
- **Team identity (§1.6 lines 169-172): team name "Blue Bloom", motto "Dreaming Big", league
  placement 8th, four preseason rounds won, finals pending.** The cockpit's Scenario block has
  Team Rank and Spirit Bursts readouts but no team name, no motto, no league placement, and no
  per-round win record. Only the *count of circles* is entered, and only on the race form.
- **Team stat grades A/A/A/S/A and the +30 ranking bonus (§1.8 lines 232-234).** The cockpit prints a
  five-stat grade row (Speed..Wit, all `N/A`), but there is no field for the bonus.
- **The Unity Trainings count (55) and the 6 / 5 burst split (§1.8 lines 235-237).** The cockpit shows
  "Spirit Bursts N/A" and its own copy says bursts are "recorded as states, not counters (D-223)".
  The source's two numbers (6 normal, 5 Extreme) and the 55 Unity Trainings cannot be entered; the
  band table consequently cannot place the run in its 10-12 band even though §1.8 derives it.
- **The run's YEAR/MONTH/turns-remaining.** The cockpit derives YEAR and MONTH from the turn number
  (see friction).

#### Friction / errors / dead ends
- On first arrival everything is `N/A` and the Advisor declines ("Recommendation unavailable because
  Energy has not been entered"). That is correct behaviour and is well explained.
- **The mid-career position has no representation.** The cockpit's YEAR/MONTH/TURN come out of the
  turn number; with no turn logged it printed `YEAR N/A / MONTH N/A / TURN 0`. The source's "Senior
  Year Early Oct, 5 turns left, 5 turns until the Unity Cup" (§1.1 line 21) has no field anywhere.
- The burst-count band table carries a visible caveat badge and a note that the run's own position in
  the bands is not shown. Good disclosure, but it means the source's §2.4/§8.2 conclusion ("11
  combined is inside the 10-12 gold band") cannot be expressed in the tool.

#### Outcome / next screen
Followed the cockpit's own prompt, "Record the first turn on the run screen" → `/training-runs/8`.

---

### Screen 9 — Run record (turn logger)

#### Route and trigger
- Route: `/training-runs/8`, title "Run: Rice Shower".
- Trigger: clicked "Record the first turn on the run screen" in the cockpit's empty state.

#### What the screen asks for
A very large screen. In order: header links (Career Cockpit, Skills planner, Career result, Export
CSV, Export JSON); a Resources strip; a three-year race calendar with per-turn race names; a race
record form (`race_catalog_slot_id`, `status` Not/Offered/Skipped/Entered/Completed, `placement`,
`circles`, `Record race`); a scenario select + `Change scenario`; a status select (Active/Completed/
Retired) + `Change status`; six deck slots (`deck[N][support_card_id]`) + `Save deck`; the Turns
table; and the turn form — "Step 2 of 5 · Choose activity" with seven numbered activities (Speed,
Stamina, Power, Guts, Wit, Rest, Recreation), then `Turn`, `Speed`, `Stamina`, `Power`, `Guts`, `Wit`,
`Skill Points`, `Energy (after this turn)`, `Fans (after this turn)`, `Mood`, `Outcome`, `Penalty
kind`, `Preview this turn`; a `Correct a turn by hand` disclosure; and a Skills block (Starting /
Acquired / Skipped, plus a `Skill` + status + turn form).

#### Values entered + source trace
| Value        | Field                       | Source                                                                                                 |
| ------------ | --------------------------- | ------------------------------------------------------------------------------------------------------ |
| `1` (default, unchanged) | Turn            | **Not present in source — UI-required choice.** §1.1 line 21 gives a position ("Senior Year Early Oct, 5 turns left") but no turn number, and the field is required with a default of 1. |
| Speed        | Choose activity (radio)     | §1.7 line 220 (the last observed training turn is the Speed tile). **Activity is not stated as a turn record in the source; UI-required choice.** |
| `607`        | Speed                       | §1.2 line 28                                                                                            |
| `577`        | Stamina                     | §1.2 line 29                                                                                            |
| `549`        | Power                       | §1.2 line 30                                                                                            |
| `736`        | Guts                        | §1.2 line 31                                                                                            |
| `397`        | Wit                         | §1.2 line 32                                                                                            |
| `173`        | Skill Points                | §1.2 line 34 ("Skill Points: 173 unspent")                                                               |
| `209245`     | Fans (after this turn)      | §1.1 line 22 ("Fans 209,245")                                                                           |
| `GREAT ↑`    | Mood                        | §1.7 line 208 ("Mood is GREAT")                                                                          |
| Success      | Outcome (default)           | Default. §1.7 line 220 records the tile's 39% failure rate but no resolved outcome; left at the default. |
| (left empty) | Energy (after this turn)    | **No value exists in the source.** §1.7 lines 213-216: "The client never prints an Energy number", the bar is "near a third". Left blank rather than invented. |
| (left empty) | Penalty kind                | Not applicable with a Success outcome.                                                                   |

#### Source data with no UI home
- **Energy (§1.7 lines 213-218).** No number exists in the source ("The client never prints an
  Energy number"; the bar sits "near a third"). The field is a number, so the snapshot's energy state
  has no representation at all. Leaving it blank leaves the run's Energy `N/A` on every screen, which
  in turn disables the Advisor everywhere.
- **The run's position in the career (§1.1 line 21).** `Turn` is a bare integer; "Senior Year Early
  Oct", "5 turns left" and "5 turns until the Unity Cup" have no field. The calendar renders all three
  years, but nothing records which one the run is in except the derivation from the turn number.
- **The last observed turn (§1.7 lines 220-222).** Speed facility Lv4, the "Floor Cleaning" banner,
  three card avatars, the preview "+18 Speed / +8 Power / +6 Skill Points", **Failure 39%**, and the
  five facility levels (Speed 4, Stamina 4, Power 4, Guts 5, Wit 4). There is no per-turn failure
  rate, no facility level and no preview figure anywhere on the form.
- **The unspent SP's composition (§1.3, §2.2).** SP is one number. The ten-plus owned skills, the
  purchasable pool with its prices, and the hint discounts have no home on the turn form.
- **The Unity Cup state (§1.6, §1.8).** Team name, motto, league placement, the four rounds won, the
  55 Unity Trainings and the 6 + 5 burst split. The only nearby control is the race form's `circles`,
  which is per-race.

#### Friction / errors / dead ends
- The turn form's required fields are `Turn`, the five stats and `Outcome`; `Energy`, `Fans`,
  `Skill Points` and `Mood` are optional. Because the source cannot supply Energy, the Advisor stays
  disabled on this and every later screen: the app's own recommendation engine is unusable for a run
  whose energy the source cannot state.
- The form is a small wizard inside the page. It announced "Step 2 of 5 · Choose activity", then
  after `Preview this turn` it announced "Step 4 of 5 · Record outcome". No step 3 label was ever
  shown.
- `Preview this turn` does not write; it added a `Confirm turn` button, and only `Confirm turn`
  persisted. After confirming, the Turns table read
  `1 | 607 | 577 | 549 | 736 | 397 | 173 | (blank) | GREAT ↑`. The table's columns are Turn, Speed,
  Stamina, Power, Guts, Wit, SP, Condition, Mood and actions — **there is no Fans column and no
  Energy column**, so two of the values the form accepted are not shown back on this screen.
- **The mid-career position cannot be expressed.** Re-reading the cockpit after the turn shows the
  derivation: `YEAR Junior Year`, `MONTH Early January`, `TURN 1`. The source's snapshot is Senior
  Early October. Entering the snapshot's values at the form's default turn number places a
  Senior-year state at the career's first turn, and no control on the screen would have let the user
  place it anywhere else.

#### Outcome / next screen
After `Confirm turn`, clicked "Skills planner" in the screen header → `/training-runs/8/skills`.

---

### Screen 10 — Skills planner

#### Route and trigger
- Route: `/training-runs/8/skills`, title "Skills planner: Rice Shower".
- Trigger: clicked "Skills planner" in the run record screen's header.

#### What the screen asks for
Read-only. A "Your target" block (purpose, distance, surface, style and the five stat targets), a
"Skill Point coverage" line, the hint-discount ladder (Hint Lvl 1 10% off … Lvl Max 40% off), and a
legend of five skill states (Confirmed, Required, Available, Learned, Inherited).

#### Values entered + source trace
None entered.

#### Source data with no UI home
- **The ten-plus owned skills and Corner Recovery ○ (§1.3 lines 63-67).** The planner prints "No skill
  is learned yet" and "No skill is marked for this run yet"; §1.3's owned list has no entry point on
  this screen.
- **The Ignited Spirit hints and the Burning Spirit gold (§2.2 lines 363-373, §2.4).** The screen has
  no scenario-skill row.
- **The recommended spend (§2.3).** No advice surface exists here.

#### Friction / errors / dead ends
- The screen reads back "the run holds 173 SP", so the turn entry persisted.
- Coverage is stated as "The skills still to learn cost N/A SP", so the headline number of the screen
  cannot be computed even though the run's own SP is known. A user gets a coverage panel that can
  never resolve.
- "No column holds the skills a Legacy configuration passes down" — the screen explains its own gap,
  but it means the ancestry recorded on screen 5 contributes nothing here.

#### Outcome / next screen
Navigated back to the run record, then to the cockpit, then followed the cockpit's "Training" action.

---

### Screen 11 — Training decision

#### Route and trigger
- Route: `/training-runs/8/training`, title "Training decision: Rice Shower".
- Trigger: the cockpit's "Training" action link.

#### What the screen asks for
Five training cards (Speed, Stamina, Power, Guts, Wit). Each carries a deficit line, `EXPECTED GAINS`,
`ENERGY COST`, `SUPPORTS ENTERED FOR THIS TRAINING`, `TARGET DEFICIT`, `RISK`, a `Train` button and an
`Inspect details` disclosure. Below them, "Record the turn" with `Turn *`, `Speed total *`,
`Stamina total *`, `Power total *`, `Guts total *`, `Wit total *`, `Skill Points`, `Energy`, `Fans`,
`Mood`, `Outcome *`, and `Preview this turn`.

#### Values entered + source trace
None entered on this screen.

#### Source data with no UI home
- The source's tile preview and failure rate (§1.7 lines 220-222) have no field, as on screen 9.
- The source's facility levels (Speed 4, Stamina 4, Power 4, Guts 5, Wit 4) have no field; the app
  says per-facility levels "are not recorded here".

#### Friction / errors / dead ends
- The screen's computed deficits match the entered target against the entered stats exactly:
  Speed 693 (1300−607), Stamina 723, Power 751, Guts 564, Wit 1,403. The arithmetic is right, and it
  is right against a target the source does not state.
- Supports-per-training counts match the deck and the source: Speed 3, Stamina 1, Guts 2, Power 0,
  Wit 0 (§1.4 line 86: "Three Speed, one Stamina, two Guts, no Wit, no Pal").
- `EXPECTED GAINS` and `RISK` are `N/A` on every card, with sourced reasons (ADR-0001 §3). Honest, but
  it leaves each card with one usable number (the deficit) out of five.
- This screen repeats the run record's turn form with different labels (`Speed` vs `Speed total *`),
  and its own copy says the preview "renders on the run record screen … (Rice Shower, turn 2)". A user
  meets the same form twice and has to work out which one to submit.
- The screen names the next turn as 2, i.e. it has already advanced past the turn that was logged.

#### Outcome / next screen
Returned to the cockpit and followed its "Race" action.

---

### Screen 12 — Race decision

#### Route and trigger
- Route: `/training-runs/8/races`, title "Race decision: Rice Shower".
- Trigger: the cockpit's "Race" action link.

#### What the screen asks for
"The turn being decided" (printed as `Junior Year · Late January · Turn 2`); Mandatory races (Junior
Make Debut, Junior turn 12, flagged NEXT, "Not recorded yet"; URA Finals Qualifier / Semifinal /
Final (Aoharu) in Finale, all "Not recorded yet"); a "Next obligation" line; Readiness (`N/A`); and
"Races to decide between" with a `Plan the whole calendar` link.

#### Values entered + source trace
None entered.

#### Source data with no UI home
- **The race-day card (§1.6 lines 181-193):** Kyoto Daishoten (G2, Kyoto Turf 2400 m, Soft) and Mile
  Championship Nambu Hai (G1, Morioka Dirt 1600 m), the 6,700-fan payout, the "2,000 fans or more"
  entry criteria, and the client's "top contender" verdict. Because the run sits at turn 1/2, the
  screen reports "No race on this scenario's calendar falls at this turn", so none of it is reachable.
- **The race calendar's Senior board (§1.6 lines 178-179):** the scheduled Kyoto Daishoten and the
  Arima Kinen goal slot. Not reachable from this turn.

#### Friction / errors / dead ends
- This screen is the clearest demonstration of the mid-career mismatch. The app can only decide a race
  at the turn it is at; the turn is 1/2 (Junior January), while the source's turn is Senior Early
  October. The screen has no way to jump to the run's real turn.
- The mandatory-race list is the only populated race content, and it is the *scenario's* obligation
  list, not the run's own four won rounds (§1.6 line 170).

#### Outcome / next screen
Clicked `Plan the whole calendar` → `/training-runs/8/races/planner`.

---

### Screen 13 — Race planner

#### Route and trigger
- Route: `/training-runs/8/races/planner`, title "Race planner: Rice Shower".
- Trigger: clicked "Plan the whole calendar" on the Race decision screen.

#### What the screen asks for
Read-only. "Planning from Junior Year · Turn 2"; Held figures (Win probability `N/A`, Expected risk
`N/A`); Deadlines; Mandatory races, each with a per-race detail panel (Grade, Distance, Distance
band, Surface, Running style, Fan gain, Reward, Skill points, Scenario reward, Estimated win
probability, "Details and actions"); and Upcoming races (the calendar's non-mandatory races).

#### Values entered + source trace
None entered.

#### Source data with no UI home
- The source's Soft going and per-race fan rewards (§1.6 lines 185-191) have no column; the Fan gain
  row explains that the payout curve id is stored but no payout table is.
- The Arima Kinen goal (§1.6 line 176) is not in this list; goal races are `N/A` (KI-34).

#### Friction / errors / dead ends
- The detail panels are mostly `N/A` with a reason each. Junior Make Debut and Junior Maiden Race
  carry no distance, distance band or surface; Chukyo Junior Stakes does (1,600 m, Mile, Turf, "Fan
  gate: 350 fans"). The inconsistency is in the catalog, and the screen discloses it per field.
- Win probability is withheld on purpose (ADR-0016 / `PRD.md` §6.11), stated on every panel.
- The screen is the longest in the walk (16,001 characters of text) and repeats the same eleven-field
  panel for every race, so most of it is `N/A` rows.

#### Outcome / next screen
Navigated to the Event decision screen.

---

### Screen 14 — Event decision

#### Route and trigger
- Route: `/training-runs/8/events`, title "Event decision: Rice Shower".
- Trigger: the cockpit's "Event" action link.

#### What the screen asks for
Coach recommendation (none), Current career state (read-back of five stats, Energy, Mood, Fans, Skill
Points), "Known outcomes on this run", "Recorded events", and a "Record an event choice" form:
`Turn *` (Select turn → Turn 1), `Source *` (Character, Support Card, Group, Scenario), `Event name *`,
`Choice you made *`, `Support card (if the event came from one)`, `What the choice gave`, and
`Record choice`.

#### Values entered + source trace
None entered.

#### Source data with no UI home
- The source records no event that fired during the run, so nothing from §1.8's Team Info panel or
  §2.4's payout event can be entered as an event choice. The screen's own copy is explicit that it
  "does not look up events it has not seen", so the source's scripted-event content has no home.
- **The four Ignited Spirit hints (§1.8 line 243-244).** These are the run's recorded inheritance/
  scenario hint state; there is no field for a hint, only for an event choice.

#### Friction / errors / dead ends
- The read-back confirms persistence of the turn entry: Speed 607/1300, Stamina 577/1300, Power
  549/1300, Guts 736/1300, Wit 397/1800, `Energy N/A`, `Mood GREAT`, `Fans 209245`, `Skill Points 173`.
- `Turn *` offers only the turns that exist on the run (Turn 1), so this screen inherits the same
  positional problem as the rest of the run.
- Energy reads `N/A` here too, and the screen offers no advice (`TrainerAdvisor holds no event advice`).

#### Outcome / next screen
Navigated to the Inheritance event screen.

---

### Screen 15 — Inheritance event (server error)

#### Route and trigger
- Route: `/training-runs/8/inheritance`.
- Trigger: the cockpit's "Inheritance" action link.

#### What the screen asks for
Nothing: the route returned **HTTP 500** and rendered Laravel's error page, not the app screen.

#### Values entered + source trace
None. The screen never rendered, so nothing could be entered.

#### Source data with no UI home
- The whole screen's subject matter (§1.5's ancestry, sparks and the three inheritance moments) is
  unreachable for this run, because the screen cannot load.

#### Friction / errors / dead ends
- The error page reports: `TypeError` — `array_map(): Argument #1 ($a) must be of type array, string
  given`, in `App\Http\Controllers\Career\InheritanceEventController.php:162`, inside
  `legacySection()`.
- Read from the source (to explain the page, not as evidence of the screen's behaviour):
  `StoreLegacySelectionRequest.php:186-189` writes `legacies.*.ancestors` as a **list of name
  strings**, with the comment that ancestors are names and not ids (`ADR-0010`), while
  `InheritanceEventController.php:161-164` maps each entry as an **array** with `slot` and `name`
  keys. Any run whose stored legacy selection carries ancestor entries therefore fails to render.
- This is a hard dead end: the cockpit offers the link, the link is a 500, and there is no
  in-app route around it. The run's ancestry was entered entirely through the UI on screen 5.
- Not fixed, per the brief.

#### Outcome / next screen
No forward movement from this screen. Navigated to the Career Timeline.

---

### Screen 16 — Career Timeline

#### Route and trigger
- Route: `/training-runs/8/timeline`, title "Career timeline: Rice Shower".
- Trigger: the cockpit's "Career Timeline" link.

#### What the screen asks for
A `Filter by type` control ("Toggle a type to hide its rows. Empty filter shows everything"), a count,
and a rail of rows. Each row's BEFORE stat block is the previous logged turn; EXPECTED and ACTUAL
collapse because `ADR-0003` stores the absolute end-of-turn value.

#### Values entered + source trace
None entered.

#### Source data with no UI home
- The source's four preset race days and the pending finals (§1.6 line 170-172) would appear here as
  race rows; none can be recorded at turn 1, so none is present.
- The source's turns beyond the snapshot (the §5 plan for the five remaining turns) have no
  representation.

#### Friction / errors / dead ends
- The rail shows exactly one row: `TURN · Turn 1 · Turn recorded`, and the count reads
  "showing 1 of 1". Correct, and it is a direct consequence of the positional problem: a mid-career
  snapshot logged as turn 1 produces a one-row timeline.

#### Outcome / next screen
Navigated to the Career Result screen.

---

### Screen 17 — Career Result

#### Route and trigger
- Route: `/training-runs/8/result`, title "Career result: Rice Shower".
- Trigger: the cockpit's "Career Result" link.

#### What the screen asks for
Read-only. It refuses to render a result and offers `Back to the Cockpit`, `Career Timeline` and
`Run record`.

#### Values entered + source trace
None entered.

#### Source data with no UI home
- The source's mid-career state is not a result, so nothing here is expected to have a home. The
  screen's subject matter (a finished build, a final race history, scenario objectives) is not
  reachable while the run is Active.

#### Friction / errors / dead ends
- The screen's copy is exactly the behaviour the brief required: "This career is still running, so
  there is no result to read yet. The result screen reads a run whose status is Completed". The
  snapshot was not silently converted into a finished career, and the walk did not change the status
  to force it.
- This is a designed dead end for an unfinished run, not a defect.

#### Outcome / next screen
Navigated to the run-scoped deck builder, the last screen the run exposes.

---

### Screen 18 — Support deck (run-scoped)

#### Route and trigger
- Route: `/training-runs/8/deck`, title "Support deck".
- Trigger: the run record screen's "Change slot N" links.

#### What the screen asks for
The six slots, each with the equipped card, its type, its scenario-link flag and its full effect
list; an `Ownership` toggle (`Owned` / `Rented`) per slot; `Replace the card in Slot N`; `Confirm
deck` ("Confirming replaces the whole deck: slots left empty are cleared"); and a picker with `Type`,
`Rarity`, `Availability` and `Name contains` filters over "251 of 251 cards available".

#### Values entered + source trace
None entered.

#### Source data with no UI home
- **Card client levels and limit breaks (§1.4 lines 77-84).** Still no field.
- **The reached effect values (§1.4 lines 97-104).** The screen prints catalog anchors only.

#### Friction / errors / dead ends
- All six cards persisted and render with their effects. Haru Urara is the only slot flagged
  `Scenario Link`, which matches §1.4 lines 92-94 ("Haru Urara is the deck's only Unity Cup link").
- **The friend-slot Rented flag did not persist.** Slot 6 · Friends reads `Owned: true`,
  `Rented: false`. The screen states the reason: "The run record stores no field for the owned or
  rented flag yet, so the six toggles above hold their choice on this page only and a reload returns
  them to Owned. Nothing is posted for them, so nothing is claimed to have been saved."
- The consequence is that the source's one borrowed card (§1.4 lines 86-88, §7 item 8: the sixth slot
  is a card borrowed from another account, "which is why the only 50/50 card in the deck is the one
  borrowed") is indistinguishable from an owned card once the run exists. The distinction survives
  only inside the wizard's draft.

#### Outcome / next screen
None. This is the last screen reachable for this run.

---

### Closing assessment

#### What worked

- The wizard accepted the run's identity end to end. Scenario, trainee, target, ancestry and deck all
  persisted, and the Preflight contract echoed every entered value back before the write.
- The deck represented the source's deck exactly, including the friend slot as a **position** with an
  `Owned` / `Rented` flag, and it independently derived the one Scenario Link the source also derives
  (Haru Urara).
- `Rented for the run` → "Rented from a friend" is the right representation for the source's Guest
  parent, and it was the only place the borrowed-parent fact could go.
- The spark editor accepted kind, target and stars for every coloured spark entered, and **nothing
  forced a value for a category the source lacks**. There is no Wit spark row anywhere and no control
  demanded one, so the source's "no Wit spark anywhere in the tree" (§1.5 line 159) is representable
  by omission.
- The turn form accepted the run's five stats, its SP, its fans and its mood, and every later screen
  read them back correctly (cockpit, event decision, skills planner, deck).
- The Career Result screen refused to present an unfinished career as finished, and the walk never
  changed the run's status to make it do so.
- Unrecorded values render `N/A` with a stated reason throughout. The app never invented a number to
  fill a field.

#### Functional findings

Problems where the UI failed to do what it claims, rejected valid information, lost information,
produced contradictory state, or prevented legitimate progression.

1. **`/training-runs/{run}/inheritance` returns HTTP 500.** `TypeError: array_map(): Argument #1 ($a)
   must be of type array, string given` at `InheritanceEventController.php:162`, because
   `StoreLegacySelectionRequest.php:186-189` stores `ancestors` as name strings and the controller
   maps them as arrays. Observed on a run created entirely through the UI with grandparent names
   entered. The cockpit's Inheritance action is a dead end and the screen is unusable.
2. **A blank trailing spark row is stored as a real spark.** After three `Add Spark` clicks on Parent
   A, the fourth (blank) row was persisted; the Preflight contract lists Parent A's sparks as
   "Blue on Power★1 / Pink on Late Surger★2 / Green on Cut and Drive!★2 / **Blue**", with no target and
   no stars on the fourth.
3. **The "Your target" step cannot be saved with the stat target unset**, even though its own
   validation message ("Enter a target for every stat, **or leave the whole target unset**") says that
   state is allowed. Submitting with four selects filled and all five stats empty produced five
   `role="alert"` errors, one per stat. Because four fields of the target are required, "the whole
   target unset" is unreachable, so the message describes a state the form forbids.
4. **The friend-slot Rented flag does not persist.** Set to `Rented` in the wizard, it reads `Owned`
   on `/training-runs/8/deck`, and the screen confirms the run record has no column for it. The
   source's borrowed-card distinction is lost at the moment the run is created.
5. **The trainee step's own `Stored choice:` readout does not update.** After `Select Trainee`
   succeeded (flash "Trainee set: Rice Shower."), the readout on the same screen still read `N/A`.
6. **The run record's turn table shows no Fans and no Energy column.** Both values were accepted by
   the form; neither is shown back anywhere on the screen that accepted them.
7. **The run record's inner turn flow reports "Step 2 of 5" and then "Step 4 of 5".** No step 3 label
   is ever rendered.

#### Experiential findings

Problems where the UI technically works but is unclear, requires unnecessary rework, forces repeated
consultation of the source, uses confusing terminology, has poor information architecture, requires
awkward workarounds, or creates avoidable friction.

1. **The run's own caps cannot be entered.** §1.2's caps (Speed 1325, Stamina 1322, Power 1368, Guts
   1308) exceed the target form's maxima (1300), and the form asks for a *target* rather than a cap.
   The source's central cap-arithmetic discussion has no representation, and the tool's ceiling is
   lower than the run's.
2. **The mid-career position has no representation.** YEAR and MONTH are derived from a bare turn
   number. The only way to enter the snapshot's stats is to log them at a turn the form defaults to 1,
   which places a Senior-year state at Junior January. A user holding a mid-career snapshot must
   invent a position, and the screens then contradict the source.
3. **Four of the tree's six spark sets have no home.** The SPARKS block exists only for PARENT A and
   PARENT B; the four grandparents are name-only. Of the source's six blue sparks, only two can be
   recorded.
4. **Ancestor ranks are letter classes and the only rank field is a star count.** B+, A and UG have
   no field, so both parents' ranks print `N/A` on the contract.
5. **The parent picker lists duplicate bare names.** `Vodka` twice and `Maruzensky` twice, with no
   epithet, rank or id, so the two records cannot be told apart; and neither parent's epithet
   (`[Wild Top Gear]`, `[Formula R]`) has a field anywhere.
6. **Three of the four scenario cards print `Ruleset`, `Description` and `Recommended use` as `N/A`.**
   Two thirds of the comparison surface is blank for every option.
7. **The same turn form appears twice with different labels.** The run record says `Speed`; the
   Training decision says `Speed total *`. A user meets the identical form twice and must work out
   which one to submit.
8. **The trainee card's aptitude row disagrees with the source on three of ten letters** (Mile B→C,
   Front runner A→B, Late surger B→C), and the costume form the run actually uses is the unlabelled
   one while the debut form is named.
9. **Energy cannot be entered from this source, which disables the Advisor for the whole run.** §1.7
   explains why the source has no number, but the app offers no band or qualitative alternative, so
   every screen's recommendation panel is permanently blank for this run.
10. **The spark `Applies to` field accepts a stat name, an aptitude name or a skill name** with no
    list, no hint and no validation; the only clue is the spark's `Kind`.
11. **The race surfaces are unreachable in practice.** Because the run's turn is 1/2, the Race
    decision reports "No race on this scenario's calendar falls at this turn" and the planner offers
    only mandatory races, so the source's race-day card (§1.6) cannot be entered or even seen.

#### Note on the brief versus the source

The task brief states that "Haru Urara occupies the friend slot". The source document says the friend
card is **Super Creek**: §1.4 lines 86-88, "the Career Profile rail tags Super Creek's entry with a
'Friends' pill where the other five carry their discipline icon, which is why the only 50/50 card in
the deck is the one borrowed from another account". This walk followed the source document, as the
brief directs, and placed Super Creek in `Slot 6 · Friends`. Recorded here because a reader comparing
the two will meet the difference.

#### Unrepresented source data (final inventory)

Every source datum encountered for which the UI has no appropriate field:

- Run position: Senior Year Early Oct; 5 turns left; 5 turns until the Unity Cup (§1.1 line 21).
- Rarity ★3 and Potential Level 2 (§1.1 line 20).
- The `[Rosy Dreams]` costume identity: the card's second form is unlabelled (§1.1 line 20).
- The growth-rate row +0/+10/+0/+20/+0 (§1.1 line 20; §7 item 2).
- Fans class Star, the Top Star threshold 240,000 and the 30,755-fan gap (§1.1 lines 21-22).
- The run's own per-stat caps 1325 / 1322 / 1368 / 1308 / 1800 (§1.2 lines 28-32).
- The cap shortfall and its candidate causes (§1.2 lines 39-56; §8.5).
- Aptitudes (§1.1 lines 58-59): shown on the picker but not stored against the run, and three letters
  disagree with the source.
- The ten-plus owned skills and Corner Recovery ○ (§1.3 lines 63-67).
- Card client levels / limit breaks and the reached effect values (§1.4 lines 77-104).
- Both parents' letter ranks B+ / UG, and the grandparents' ranks B+ / A / UG (§1.5 lines 138-143).
- The four grandparents' spark sets (§1.5 lines 149, 150, 152, 153).
- The white sparks for both parents — representable (`Kind = White`) but not entered; see completion.
- Team identity: "Blue Bloom", motto "Dreaming Big", league placement 8th, the four preseason rounds
  won, finals pending (§1.6 lines 169-172).
- Goals: the three cleared and the active Arima Kinen goal (§1.6 lines 174-176).
- The race-day card: Kyoto Daishoten (G2) and Mile Championship Nambu Hai (G1), the Soft going, the
  "top contender" verdict, the fan rewards and the entry criteria (§1.6 lines 181-193).
- The Mood Effect panel percentages (§1.7 line 208).
- Energy: low, with the warning text; no number exists (§1.7 lines 213-218).
- The last observed turn: Speed facility Lv4, "Floor Cleaning", three avatars, +18 Speed / +8 Power /
  +6 Skill Points, Failure 39%, facility levels 4/4/4/5/4 (§1.7 lines 220-222).
- Team Info: 55 Unity Trainings, 6 Spirit Bursts, 5 Extreme Spirit Bursts, 20/20 members, the Team
  Ranking Bonus +30 and the team stat grades A/A/A/S/A (§1.8).
- The 18-member roster and the guests' skill lists (§1.9).
- The skill pool, its prices, the hint-discount ladder and the recommended spend (§2.1-2.3).
- The burst economy and the 10-12 band conclusion (§2.4, §8.2).

#### Walk completion

- **Reached the end of the available flow: yes, for an Active run.** Eighteen screens were visited,
  in visit order, from the Dashboard to the run-scoped deck builder.
- **Where it stopped:** the flow terminates at the Career Result screen's "still running" state, and
  the Inheritance screen terminates in an HTTP 500. Nothing further is reachable for this run.
- **Why it stopped:** the run was deliberately left Active — the brief forbids marking the mid-career
  snapshot as completed — so the Result screen has nothing to read; and the Inheritance screen cannot
  render at all.
- **Intentionally omitted data:** the twelve white sparks from §1.5 (six on Vodka, six on
  Maruzensky) were not entered. They have a UI home (`Kind = White`) and the identical control to the
  nine coloured sparks that were entered, so no further UX behaviour was at stake; the omission is
  stated here rather than presented as a gap. The pair's affinity grade was left unset because the
  source states none. Every other source datum the UI cannot hold is listed under Unrepresented
  source data above.
- **Persistence note:** the values entered were verified to persist only where a screen read them
  back — the cockpit, the event decision screen, the skills planner, the timeline and the run-scoped
  deck all printed the entered stats, SP, fans and mood. No claim of persistence is made for anything
  a screen did not show again, and the friend-slot `Rented` flag is explicitly recorded as **not**
  persisting.

---

## rice-shower-unity-cup-ux-walk-2.md

> **Augmented 2026-10-10 (Round 14, augment-only).** Source: `docs/audits/rice-shower-unity-cup-ux-walk-2.md` (941 lines), reproduced verbatim below with headings demoted one level. The source file remains in place; no citation was repointed and nothing was deleted.

## UX walk audit (2nd pass): [Rosy Dreams] Rice Shower, Unity Cup

### Header

| Field                | Value                                                                                                              |
| -------------------- | -------------------------------------------------------------------------------------------------------------------- |
| Audit name           | Rice Shower Unity Cup mid-career UX walk — second, independent pass                                                    |
| Source document      | `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` (67 `[Global]` client frames, 2026-10-03; a mid-career snapshot)          |
| Starting URL         | `http://127.0.0.1:8000/`                                                                                               |
| Date/time of walk    | 2026-10-10, a second continuous session on the same day as the first pass                                              |
| Tree under test       | `master` at `aedfc30` ("chore(runs): drop the orphaned stat_ceilings column"). The first pass ran against an earlier    |
|                      | working state whose peer commits had not yet landed, so this pass exercises different application code.                 |
| Environment          | Laravel 13.32.0, PHP 8.5.8, SQLite (`database/database.sqlite`), `php artisan serve` on `127.0.0.1:8000`, Chromium 150 |
| Run created          | `training_runs` id **9** (`/training-runs/9`), status Active, scenario Unity Cup                                         |
| Screens visited      | 20 in visit order (19 belonging to run 9 plus one labelled cross-check against run 8)                                   |

**Relationship to the first audit.** `docs/audits/rice-shower-unity-cup-ux-walk.md` is the first pass. This
is a fresh walk of the same source run through the same flow, written to its own file. Where the two
passes disagree, both readings are recorded with the pass that produced them; neither is silently
overwritten.

**Method statement.** Every screen below was reached in a real browser session and its contents were read
from the rendered DOM at the moment of the visit. Every value was typed or selected through the on-screen
controls (search box, native selects, number inputs, radio controls, checkboxes, disclosure summaries and
the app's own buttons). No seeder, no database insert or update, no SQL, no tinker, no API call and no
hand-edited application state was used to enter data. Inter-screen hops after the wizard hub used the
address bar with the exact href each screen's own link exposes; the target route is the route the link
points at. Source code was not needed to explain anything in this pass, because every claim here is
backed by a rendered screen.

**One pass.** Made once, forward. No screen was re-entered to change an earlier choice and none was
skipped. Where the UI demanded a value the source does not carry, the minimum available choice was made
and is flagged **Not present in source — UI-required choice**. The run was never marked Completed, never
filed as a Veteran and never filed as a Legacy.

**Nothing was fixed.** No route, controller, view, component, migration, model, seeder, test, validation
rule, label, field or schema was changed. The only file this pass added is
`docs/audits/rice-shower-unity-cup-ux-walk-2.md`.

---

### Screen 1 — Dashboard

#### Route and trigger
- Route: `/` (`home`), title "Dashboard".
- Trigger: initial navigation to the starting URL.

#### What the screen asks for
Read-only. ACTIVE CAREER (trainee, scenario, `Junior Year · Early January`, Turn 1, Energy `N/A`, Team
Rank `N/A`, `Resume Career`); Quick actions (`New Career`, `Legacy Lab`, `Support Cards`); RECENT
VETERANS (3 of 7); RECENT BUILDS; DATA STATUS (Verified 2026-09-27 · GameTora `scenarios.json`, Ruleset
`N/A`, Trainees 67, Skills 1,910, Support cards 559).

#### Values entered + source trace
None. The control used was `New Career`.

#### Source data with no UI home
None relevant on this screen.

#### Friction / errors / dead ends
- The Dashboard now renders a career state, and it showed the run the **first** pass created (run 8):
  "Rice Shower … Junior Year · Early January · Turn 1". The same misplacement the first pass recorded is
  now on the landing screen before the walk starts.
- `New Career` was still available with an Active career already present, and it started a clean draft
  (step 1 showed `Stored choice: N/A`), so the two coexist without a prompt about the existing career.
- The Veterans rows now link to `/training-runs/N/cockpit`, not `/training-runs/N`.

#### Outcome / next screen
Clicked `New Career` → `/career/setup/scenario`.

---

### Screen 2 — Scenario setup (step 1 of 6)

#### Route and trigger
- Route: `/career/setup/scenario`, title "Choose a scenario".
- Trigger: clicked `New Career` on the Dashboard.

#### What the screen asks for
Step nav (`Leave setup`; Scenario, Trainee, Your target, Legacy, Support deck, Preflight), a `Stored
choice` readout, and four scenario cards of eight facts (Optimizes, Systems, Tracked resources, Turn
loop, Live on Global, Ruleset, Description, Recommended use) each with a `Select Scenario` button. No
Continue button: forward movement is the step nav.

#### Values entered + source trace
- `Unity Cup` → the Unity Cup card's `Select Scenario` → source §1.1, line 19 ("Scenario Unity Cup,
  confirmed by the logo panel").

#### Source data with no UI home
The run's identity beyond the scenario name (trainee, year, month, turns remaining) is not asked for
here, as in the first pass.

#### Friction / errors / dead ends
- **The Unity Cup card prints two Ruleset rows with different values.** The eight-fact block ends with
  `Ruleset · N/A`; below it the new description block prints `Ruleset · Team`. Both are on screen at the
  same time, so the same label carries `N/A` and `Team` on one card.
- Only Unity Cup gained the richer block (description, Ruleset, Planning focus, three KEY MECHANICS
  bullets). URA Finale, Trackblazer and Our Grand Concert still print `Ruleset`/`Description`
  /`Recommended use` as `N/A` plus a `Not recorded` line, so the comparison is now asymmetric: one card
  is fully described and the other three are mostly blank.
- Selecting flashed "Saving your choice…" then "Scenario set.", the button became `pressed="true"` with
  a "Selected" tag, and `Stored choice:` moved from `N/A` to `Unity Cup`.

#### Outcome / next screen
Clicked "Trainee" in the step nav → `/career/setup/trainee`.

---

### Screen 3 — Trainee (step 2 of 6)

#### Route and trigger
- Route: `/career/setup/trainee`, title "Choose a trainee".
- Trigger: clicked "Trainee" in the step nav.

#### What the screen asks for
Filters in UI order: `Search` (text), `Surface`, `Distance`, `Running style` (selects Any,S..G), `Unique
skill` (≈130 options), `Sort by`, `Order`, `Filter`, `Clear filters`. Then a paginated 67-trainee roster.
Each card: portrait, English name, Japanese name, "Released (Global)", ten aptitude chips (letter plus
word), a costume-form summary, `Select Trainee`, `View Profile`.

#### Values entered + source trace
- `Rice Shower` → `Search` → source §1.1, line 20.
- `Select Trainee` on the Rice Shower card (`id="trainee-30"`) → source §1.1, line 20.

#### Source data with no UI home
- **★3 and Potential Level 2 (§1.1 line 20)**: no star rating and no Potential field on the card, and
  none is reachable from it. (See screens 8 and 11 — the run now *labels* this gap but offers no field.)
- **[Rosy Dreams]**: the card reads "2 costume forms ★★★SSRdebut form★★★SSR" — the second form, which is
  the run's, carries no name, and the portrait is `artwork/card_portrait/103001`, the debut form.

#### Friction / errors / dead ends
- The aptitude row printed **Turf A, Dirt G, Sprint E, Mile C, Medium A, Long A, Front runner B, Pace
  chaser A, Late surger C, End closer G**. The source reads **Mile B, Front runner A, Late surger B**
  from the client (§1.1 lines 58-59). Three letters differ, and they differ **exactly as they did in the
  first pass** — same card id, same values — so this is a stable divergence, not a flake.
- `Select Trainee` flashed "Trainee set: Rice Shower." and `Stored choice:` then read **`Rice Shower`**.
  In the first pass the same readout stayed `N/A` at this point. Unreconciled between the two passes;
  the likely cause is that the first pass read the DOM during the post-save re-render, but it could also
  be one of the commits that landed between the passes. Recorded as a difference, not resolved.
- The search filter cleared after the write, returning the list to all 67.

#### Outcome / next screen
Clicked "Your target" in the step nav → `/career/setup/target`.

---

### Screen 4 — Your target (step 3 of 6)

#### Route and trigger
- Route: `/career/setup/target`, title "Your target".
- Trigger: clicked "Your target" in the step nav.

#### What the screen asks for
`Purpose` (Not chosen yet, Story Clear, Champions Meeting, Parent Farming, Skill Farming) with a note
that a fifth purpose, Competitive Build, is not recorded; `Distance` (Sprint, Mile, Medium, Long);
`Surface` (Turf, Dirt); `Style` (Front Runner, Pace Chaser, Late Surger, End Closer); five stat targets
(Speed/Stamina/Power/Guts max 1300, Wit max 1800) each with a `Cap` label and a ratio readout; a `Skill
name` + `Add` priorities list; a live `Summary`; `Save target`.

#### Values entered + source trace
**None.** The step was submitted with every field left at its empty state and the app accepted it. See
the first bullet under Friction for why this is the faithful entry rather than an omission.

#### Source data with no UI home
- **The run's own caps (1325 / 1322 / 1368 / 1308 / 1800, §1.2 lines 28-32)** remain unenterable: the
  inputs carry `max=1300` / `max=1800`, so three of the run's four documented caps exceed the field
  ceiling. The §1.2 cap arithmetic still has no representation: the screen asks for a target, not a cap,
  and it asks for no spark and no support-card cap contribution.
- **Build purpose, and the single distance the run is "for"**: the source names both Medium and Long as
  A and calls it "A Medium/Long build" (§1.1 line 59) without picking one.
- Potential Level, the growth row +0/+10/+0/+20/+0, and the fan class/threshold.

#### Friction / errors / dead ends
- **The first pass's contradiction is gone.** Submitting with nothing entered now returns
  "Build target saved." with **zero** `[role=alert]` elements. In the first pass the same submission
  produced nine errors, five of them reading "Enter a target for every stat, or leave the whole target
  unset." while the target was in fact wholly unset. The purpose/distance/surface/style fields are no
  longer required, so the state the message offered is now reachable.
- Consequence for this walk: because nothing is forced, the source's real condition — a mid-career
  snapshot with no recorded build target — was carried forward honestly instead of being padded with
  invented values, as the first pass had to do.
- The step kept printing "No target is recorded yet. The fields below start empty rather than at zero."
  after the save, which is accurate, but the success flash beside it is easy to misread as "recorded".

#### Outcome / next screen
Clicked "Legacy" in the step nav → `/career/setup/legacy`.

---

### Screen 5 — Legacy (step 4 of 6)

#### Route and trigger
- Route: `/career/setup/legacy`, title "Legacy".
- Trigger: clicked "Legacy" in the step nav.

#### What the screen asks for
Two parent panels. Each: a rank and spark-chance readout; `Assign to Parent N` (select over the saved
Veteran library); `Grandparent N1`/`N2` (type-ahead over the catalog roster); **each grandparent now has
its own `Add spark to grandparent …` disclosure**; an "AS YOU READ HER" block with `Own rank (letters)`
(combo with a fixed suggestion set), `Own rank (stars)` (number), `Rented for the run` (checkbox) and a
`SPARKS` list. Each spark row is `Kind` (Blue, Pink, Green, White, Scenario), `Applies to` (Stat,
Aptitude, Skill) and a dependent `Target` select plus `Stars` (1-3). Then `Affinity for the pair`
(Not recorded, △, ○, ◎), `Save Legacy`, `Next: Support deck`.

#### Values entered + source trace
| Value | Field | Source |
| ----- | ----- | ------ |
| `Vodka [Wild Top Gear] · 7` | Assign to Parent A | §1.5 line 138 |
| `Daiwa Scarlet` / `Seiun Sky` | Grandparent A1 / A2 | §1.5 lines 139-140 |
| `B+` | Parent A → Own rank (letters) | §1.5 line 138 ("rank B+") |
| Blue / Stat / `Power` / ★1 | Parent A spark | §1.5 line 148 |
| `Maruzensky [Formula R] · 2` | Assign to Parent B | §1.5 line 141 |
| `Daiwa Scarlet` / `Taiki Shuttle` | Grandparent B1 / B2 | §1.5 lines 142-143 |
| `UG` | Parent B → Own rank (letters) | §1.5 line 141 ("rank UG") |
| checked | Parent B → Rented for the run | §1.5 line 141 ("flagged Guest") |
| Blue / Stat / `Speed` / ★2 | Parent B spark | §1.5 line 151 |
| Blue / Stat / `Stamina` / ★3 | Grandparent A2 (Seiun Sky) spark | §1.5 line 150 |
| (left "Not recorded") | Affinity for the pair | **Not present in source** — §1.5 states no pair grade |

#### Source data with no UI home
- **The four grandparents' ranks** (B+ line 139, A line 140, UG lines 142-143): `Own rank (letters)` and
  `Own rank (stars)` exist only for the two parents. Grandparent nodes take a name and now sparks, but no
  rank.
- **The remaining three of the six blue sparks** (Daiwa Scarlet Legacy-1 side Guts ★1 line 149, Daiwa
  Scarlet Legacy-2 side Speed ★2 line 152, Taiki Shuttle Power ★3 line 153) and every pink, green and
  white spark in §1.5's table: the fields exist and the two I entered were accepted by the editor, so
  this is no longer a missing-field gap — it is the volume of rows the control requires. The first pass
  could not place them at all.
- **Star-roll chance**: `Spark chance` is `N/A` with the stated reason ("This tool holds no sourced
  star-roll table"), so §1.2's per-activation arithmetic still has nothing to read.

#### Friction / errors / dead ends
- **The save was silently discarded.** `Save Legacy` produced **no** confirmation flash, **no**
  `[role=alert]` error, and left the line "No Legacy is recorded for this setup yet" in place. Screen 7
  then reported `ANCESTRY · N/A` and listed "Ancestry is not entered." among its warnings. Every value in
  the table above was entered and none of it survived. The first pass saw a "Legacy recorded." flash here;
  this pass saw nothing.
- The rank-letter suggestion set is `G, F, E, D, C, B, A, S, B+, A+, S+`. It has no `SS`, `U`, `UU` or
  `UG`, and `UG` is the rank the source gives for Legacy 2 and both of its grandparents. The field is an
  editable combo, so typing `UG` was accepted without complaint — the value simply is not one the control
  offers, and nothing warns that it is off-set.
- The grandparent spark editor's Low/Mid-style band select renders with **no accessible name** in the
  accessibility tree (a bare `combobox`), unlike its siblings.
- Three changes that removed first-pass friction, confirmed live here: parent options now read
  `Vodka [Wild Top Gear] · 7` / `· 6`, so the duplicates are distinguishable; `Own rank (letters)` exists
  alongside the star count; and all four grandparents gained a spark disclosure. Spark `Applies to` is now
  a Stat/Aptitude/Skill select with a dependent Target list, replacing the free-text field.

#### Outcome / next screen
Clicked `Next: Support deck` → `/career/setup/deck`. The ancestry entered on this screen was gone by the
time the contract was assembled.

---

### Screen 6 — Support deck (step 5 of 6)

#### Route and trigger
- Route: `/career/setup/deck`, title "Support deck".
- Trigger: clicked `Next: Support deck` on the Legacy screen.

#### What the screen asks for
Six slots (1-5 and `Slot 6 · Friends`, tagged "Friend slot"), each with the equipped card or an empty
state, an `Ownership` `Owned`/`Rented` toggle pair and `Replace the card in Slot N`; `Save deck`; then a
`Choose a card` picker with `Type`, `Name contains`, `Filter`, a `Card for Slot N` select and
`Equip owned` / `Equip rented` / `Set Slot N to not equipped`.

#### Values entered + source trace
| Slot | Card | Ownership | Source |
| ---- | ---- | --------- | ------ |
| 1 | `Tokai Teio [Dream Big!] · SSR Speed` | Owned | §1.4 line 79 |
| 2 | `Nishino Flower [Even the Littlest Bud] · SSR Speed` | Owned | §1.4 line 80 |
| 3 | `Biko Pegasus [Double Carrot Punch!] · SSR Speed` | Owned | §1.4 line 81 |
| 4 | `Matikanetannhauser [Just Keep Going] · SSR Guts` | Owned | §1.4 line 83 |
| 5 | `Haru Urara [Urara's Day Off!] · SSR Guts` | Owned | §1.4 line 84 |
| 6 · Friends | `Super Creek [Piece of Mind] · SSR Stamina` | **Rented** | §1.4 lines 82, 86-88 |

Slot order for the five owned cards is **Not present in source — UI-required choice**: §1.4 lists the deck
without slot numbers. The friend card went to Slot 6 because §1.4 lines 86-88 names Super Creek as the
borrowed card and the UI names Slot 6 the friend slot.

#### Source data with no UI home
- **Client levels and limit breaks (35/35, 30/30, 30/30, 50/50, 30/30, 35/35, §1.4 lines 77-84)**: no
  field.
- **The reached effect values ("Client rows visible", §1.4 lines 97-104)**: the analysis prints catalog
  maxima and says so; the run's own reached figures have nowhere to go.

#### Friction / errors / dead ends
- The copy on this screen still says the flag lives only in the draft ("The deck table has no column for
  it yet, so Preflight writes the six cards it can store and the flag stays here until one exists"), while
  screen 18 reports the flag as saved and reload-stable. The two statements contradict each other; see
  Screen 18.
- The picker's target slot is set only by `Replace the card in Slot N`, and the only signal that it moved
  is the slot name inside the equip button's label.

#### Outcome / next screen
`Save deck` → "Deck saved." (URL gained `?deck_slot=6`), then `Next: Preflight` → `/career/setup/preflight`.

---

### Screen 7 — Preflight (step 6 of 6)

#### Route and trigger
- Route: `/career/setup/preflight`, title "Career contract".
- Trigger: clicked `Next: Preflight`.

#### What the screen asks for
Read-only, then the wizard's single write: a **Warnings** region; Build (trainee, scenario, target,
ancestry); Support deck (six slots with type and ownership); the full Deck analysis; Target; and
`Back`, `Edit Legacy`, `Edit Deck`, `Edit Target`, `Start Career`.

#### Values entered + source trace
None. Only `Start Career` was pressed.

#### Source data with no UI home
- `YOUR TARGET · N/A` and `ANCESTRY · N/A` are the contract's own statement that the ancestry and the
  target from §1.4/§1.5 and the (absent) build target are not in the run about to be created.

#### Friction / errors / dead ends
- **The contract names both gaps, which is what makes the silent Legacy save visible.** Warnings reads:
  "Your target is not entered." and "Ancestry is not entered.", each with an `Open that step` link and the
  line "nothing is lost by going back". The first pass printed "No warnings. Every section is filled…".
- The claim "nothing is lost by going back" is the one this walk cannot corroborate: leaving step 4 and
  coming forward again is exactly the path along which the ancestry vanished.
- `Start Career` stayed enabled with two warnings outstanding, so the wizard will create a career from a
  contract that says two of its own sections are empty.

#### Outcome / next screen
Clicked `Start Career` → "Run created." → `/training-runs/9/cockpit`.

---

### Screen 8 — Career Cockpit (first arrival)

#### Route and trigger
- Route: `/training-runs/9/cockpit`, title "Career: Rice Shower".
- Trigger: `Start Career` on the contract.

#### What the screen asks for
A run identity block (trainee, a rarity/potential `N/A` badge, SCENARIO, YEAR, MONTH, TURN, Team Rank,
Spirit Bursts, and an "Edit the run's identity" form holding `Status`, `Scenario` and `Delete this
career`); Stats and state (five stats, Energy, Mood, Fans, Skill Points); Advisor (recommendation and
why); a "No turn recorded yet" region; Actions (Training, Race, Rest, Recreation, Scenario action, Event,
Inheritance); the Unity Cup scenario panel (team race circles, the G..S+ ladder, team stat grades, Spirit
Burst states, Current Spirit, the burst-count band table, Special Training notes); Races in this run; and
links to the Career Timeline and Career Result.

#### Values entered + source trace
None.

#### Source data with no UI home
- **`YEAR N/A` / `MONTH N/A` / `TURN 0`.** §1.1 line 21's "Senior Year Early Oct, 5 turns left, 5 turns
  until the Unity Cup" still has no field; the three readouts are derived, not entered.
- **Rarity ★3 and Potential Lvl 2 (§1.1 line 20).** The screen now *names* this gap — "The trainee's
  rarity and potential level has not been recorded for this run" — but the identity form beside it holds
  only `Status`, `Scenario` and `Delete this career`. A badge describing a missing field is not a field.
- **Team identity (§1.6 lines 169-172)**: team name "Blue Bloom", motto "Dreaming Big", league 8th, four
  rounds won, finals pending. The panel has Team Rank and Spirit Bursts only.
- **Unity Trainings 55, Spirit Bursts 6, Extreme 5, members 20/20, the +30 ranking bonus, team grades
  A/A/A/S/A (§1.8)**: the band table exists but the run's own counts have no input, and the panel's copy
  states bursts "are recorded as states, not counters (D-223)".

#### Friction / errors / dead ends
- **The stat denominators are the target, not the cap.** With no target entered the strip reads
  `Speed N/A / N/A — No turn has recorded this stat yet, and no target was entered for it.` In the first
  pass, which had invented a 1300 target, the same strip read `N/A / 1,300`. So the scenario's own ceiling
  (1300/1300/1300/1300/1800, which §1.2 compares the run against) is now absent from the cockpit whenever
  no target exists. Screen 15 shows a different denominator for the same stat.
- The Advisor declines for want of Energy, with the reason printed. Correct behaviour, and it becomes the
  subject of screen 11.
- The Unity Cup panel still carries the estimated burst-band table with its three-way publisher caveat,
  which is the honest way to show the §2.4/§8.2 dispute.

#### Outcome / next screen
Followed the run's own identity route, `/training-runs/9`.

---

### Screen 9 — Run root (`/training-runs/9`), redirected

#### Route and trigger
- Requested route: `/training-runs/9` (`runs.show`). Final route: **`/training-runs/9/cockpit`**.
- Trigger: clicked `Cockpit` in the run header after reading screen 8, then navigated to the bare run id.

#### What the screen asks for
Nothing of its own: the URL resolved to the same visible Cockpit as screen 8.

#### Values entered + source trace
None.

#### Source data with no UI home
As screen 8 — the two routes present one screen.

#### Friction / errors / dead ends
- **The run record screen no longer exists as a distinct surface.** In the first pass `/training-runs/8`
  was the turn logger: a three-year race calendar, a race form, deck selects, the turn form, a correction
  form and a skills panel (49 controls, 34,793 characters of text). This pass found 2 controls and 5,527
  characters at the same address, because it redirects to the Cockpit.
- Three labels still point users at that retired screen. The Career Result says "the run record screen is
  where the status changes" (status is on the Cockpit); the Training decision offers "Record the first turn
  **on the run screen**", and that link's href is `/training-runs/9/cockpit`; the Cockpit itself now says
  "Record the first turn **through Training**". Two of the three instructions are stale in wording while
  all three resolve correctly.

#### Outcome / next screen
Took the Cockpit's `Training` action → `/training-runs/9/training`.

---

### Screen 10 — Training decision and the turn write

#### Route and trigger
- Route: `/training-runs/9/training`, title "Training decision: Rice Shower".
- Trigger: the Cockpit's "Training" action link.

#### What the screen asks for
Five training cards (Speed, Stamina, Power, Guts, Wit), each with a rank line, `EXPECTED GAINS`,
`ENERGY COST` (now with its declared-constant provenance), `SUPPORTS ENTERED FOR THIS TRAINING`, `TARGET
DEFICIT`, `RISK`, `Train` and `Inspect details`. Then "Record the turn": `Turn *`, `Speed *`,
`Stamina *`, `Power *`, `Guts *`, `Wit *`, `Skill Points`, `Fans`, **`Energy` as Exact / Band / Not
recorded** (a Low/Mid/High select appears under Band), `Mood`, `Outcome *`, a `Facility levels this turn`
disclosure (five 1-5 inputs), a `Preview readings` disclosure (`Failure rate` 0-100 plus six gain
inputs), and `Record this training`.

#### Values entered + source trace
| Value | Field | Source |
| ----- | ----- | ------ |
| Speed | `Train` on the Speed card | §1.7 line 220 ("Speed facility Lv4 with the banner 'Floor Cleaning'") |
| `1` (left at the default) | Turn * | **Not present in source — UI-required choice.** §1.1 line 21 gives a position, not a turn number, and the field is required with default 1. |
| `607` | Speed * | §1.2 line 28 |
| `577` | Stamina * | §1.2 line 29 |
| `549` | Power * | §1.2 line 30 |
| `736` | Guts * | §1.2 line 31 |
| `397` | Wit * | §1.2 line 32 |
| `173` | Skill Points | §1.2 line 34 |
| `209245` | Fans | §1.1 line 22 |
| Band → `Low` | Energy | §1.7 lines 213-216: the bar "sits near a third" with the low-energy warning printed, and "the client never prints an Energy number" |
| `GREAT` | Mood | §1.7 line 208 |
| `Success` | Outcome * | Default. §1.7 records the tile's 39% failure rate, not a resolved outcome; the choice is **Not present in source — UI-required choice.** |
| `4 / 4 / 4 / 5 / 4` | Facility levels Speed..Wit | §1.7 line 222 |
| `39` | Preview readings → Failure rate | §1.7 line 222 ("Failure 39%") |
| `18` | Preview gains → Speed | §1.7 line 221 ("+18 Speed") |
| `8` | Preview gains → Power | §1.7 line 221 ("+8 Power") |
| `6` | Preview gains → Skill Points | §1.7 line 221 ("+6 Skill Points") |

#### Source data with no UI home
- **The run's position again.** Everything above is the Senior Early October state; `Turn` could only be
  carried at its default 1, and `Outcome` had to be chosen because the source records an unresolved tile.
- **The `Floor Cleaning` banner and the three card avatars (§1.7 line 221)** have no field.
- **§1.3's owned-skill list and §1.5's rank letters** are not reachable from this screen; the skill panel
  that walk 1 found on the run record screen is not present here.

#### Friction / errors / dead ends
- Four of the first pass's "no field exists" items on this run are now fields: facility levels, failure
  rate, per-gain previews, and Energy as a band instead of a forced number.
- The Energy band select has no accessible name (an unnamed `combobox`), so its purpose is carried only by
  the adjacent radios and the "Energy" text.
- The cards' `TARGET DEFICIT` reads `N/A — No build target was entered for this stat`, which is the honest
  result of carrying no target; the first pass showed four- and three-digit deficits computed from a target
  the source never stated.
- No validation error appeared at any point in the write, and every entered value survived the
  re-renders.

#### Outcome / next screen
`Record this training` → the write landed on `/training-runs/9/cockpit` with no error.

---

### Screen 11 — Career Cockpit (after the turn write)

#### Route and trigger
- Route: `/training-runs/9/cockpit`.
- Trigger: the Training decision's own write, which states "The write lands on the Cockpit".

#### What the screen asks for
Same surface as screen 8, now populated.

#### Values entered + source trace
Nothing entered here; this is the read-back of screen 10.

#### Source data with no UI home
- **Energy.** The band `Low` was entered and accepted on screen 10; this screen prints `ENERGY · Energy
  N/A`, and the Advisor still says "Recommendation unavailable because Energy has not been entered … there
  is no band to read and nothing to rank against."
- **The turn's activity, failure rate, facility levels and preview gains** are not shown: the strip carries
  stats, Mood, Fans and Skill Points only.

#### Friction / errors / dead ends
- **Persistence verified for what the screen echoes:** Speed 607, Stamina 577, Power 549, Guts 736, Wit
  397, Mood GREAT ↑, Skill Points 173, and Fans rendered as **`209,245 · Star · 30,755 to next`** — the
  class and the gap the source states at §1.1 lines 21-22, now derived by the app from the fan count
  instead of needing a field.
- **The band reading is contradicted by the app's own words.** The Advisor's stated reason is the absence
  of a band; a band was entered. Whether it is stored or dropped cannot be settled from the UI: the
  correction form for turn 1 prefills Speed 607, Fans 209245 and Mood GREAT and exposes an `energy`
  number field with **no** `energy_state` or band control at all, so there is no screen that reads the band
  back. From the user's side the value is lost.
- `YEAR Junior Year / MONTH Early January / TURN 1` — the source's snapshot is Senior Early October. The
  first pass met the same contradiction; it reproduces.

#### Outcome / next screen
Went to the Inheritance screen.

---

### Screen 12 — Inheritance event (run 9)

#### Route and trigger
- Route: `/training-runs/9/inheritance`, title "Inheritance: Rice Shower".
- Trigger: navigated from the Cockpit's "Inheritance" action.

#### What the screen asks for
An explanatory header ("Track the three inheritance moments … the Predicted section shows the Sparks
carried by the chosen parents and grandparents … no inheritance outcome is computed"), an Inheritance
Milestones strip (`● Career Start completed`, `○ Classic Early April upcoming`, `○ Senior Early April
upcoming`), a `Predicted Sparks` region with Blue/Pink/Green counts, `EXPECTED INHERITANCE · N/A (Not
computed)`, and an Observed section.

#### Values entered + source trace
None entered.

#### Source data with no UI home
- The whole §1.5 tree, because the ancestry never reached the run: the screen reads "No Legacy
  configuration recorded for this …".

#### Friction / errors / dead ends
- **It renders.** The first pass met an HTTP 500 here. This run reaches the same route cleanly, but only
  because it has no ancestry to render — so this visit cannot by itself say whether that crash was fixed.
  Screen 13 settles it.
- The milestone strip marks "Career Start completed" for a run with one logged turn, which is consistent
  with the app's own derivation of position from the turn number.

#### Outcome / next screen
Navigated to the same route on run 8, the first pass's run, which does carry an ancestry.

---

### Screen 13 — Inheritance event (run 8) — cross-check, not this run's flow

#### Route and trigger
- Route: `/training-runs/8/inheritance`, title "Inheritance: Rice Shower".
- Trigger: navigated from screen 12. This is the run the **first** pass created; it is in the library with
  an ancestry attached, which is what the crash needed.

#### What the screen asks for
The same surface as screen 12, now with data.

#### Values entered + source trace
None entered.

#### Source data with no UI home
Not applicable — this is a check on the other run.

#### Friction / errors / dead ends
- **The first pass's 500 is gone.** `InheritanceEventController.php:162` no longer throws on run 8's
  ancestry; the screen renders, which the first pass could not do.
- **The phantom spark the first pass produced is still there and is now visible as a number.** Run 8's
  `Predicted Sparks` reports **BLUE 3 Sparks**, PINK 2, GREEN 2. The first pass entered two real blue
  sparks (Vodka Power ★1, Maruzensky Speed ★2) plus one blank trailing row, and it recorded that the
  contract printed a bare fourth "Blue". Three blues counted where two are real is the same inflation, read
  off a screen rather than inferred.

#### Outcome / next screen
Returned to run 9 and took its "Race" action.

---

### Screen 14 — Race decision

#### Route and trigger
- Route: `/training-runs/9/races`, title "Race decision: Rice Shower".
- Trigger: the Cockpit's "Race" action link.

#### What the screen asks for
"The turn being decided" (`Junior Year · Late January · Turn 2`); Mandatory races (Junior Make Debut,
Junior turn 12, flagged NEXT; URA Finals Qualifier / Semifinal / Final (Aoharu), Finale, all "Not recorded
yet"); a next-obligation line; `Readiness`; and "Races to decide between" with `Calendar race` / `Race
not on the calendar` controls and a `Plan the whole calendar` link.

#### Values entered + source trace
None entered.

#### Source data with no UI home
- **The entire §1.6 race-day card**: Kyoto Daishoten (G2, Kyoto Turf 2400 m, Fall, **Soft**, 6,700 fans on
  a win) and Mile Championship Nambu Hai (G1, Morioka Dirt 1600 m), the entry criteria, the five-place fan
  ladder and the "top contender" verdict (lines 181-193).
- **The four rounds already won and the pending finals** (lines 169-172).

#### Friction / errors / dead ends
- "No race on this scenario's calendar falls at this turn" is correct for turn 2 and wrong for the run:
  the source's turn is Senior Early October, where two races are offered. The position problem reaches the
  race surface unchanged from the first pass.
- The obligation list is the scenario's, not the run's, so it cannot show that this run already cleared
  four team rounds.
- `Readiness` is `N/A` with a stated reason.

#### Outcome / next screen
Left the race screen for the Event decision; the planner was visited later at the end of the flow
(screen 20).

---

### Screen 15 — Event decision

#### Route and trigger
- Route: `/training-runs/9/events`, title "Event decision: Rice Shower".
- Trigger: the Cockpit's "Event" action link.

#### What the screen asks for
Coach recommendation; Current career state (five stats, Energy, Mood, Fans, Skill Points); Known outcomes
on this run; Recorded events; and a record form: `turn`, `event_type`, `source_name`, `choice_label`,
`support_card_name`, and its submit.

#### Values entered + source trace
None entered.

#### Source data with no UI home
- The source records no event that fired during the run, so nothing from §1.8's Team Info panel or §2.4's
  payout event maps to an event choice.
- **§1.3's ten-plus owned skills and Corner Recovery ○**: this screen records choices, not an owned-skill
  list, and no other screen in the flow holds one.

#### Friction / errors / dead ends
- The read-back confirms the write: Speed 607, Stamina 577, Power 549, Guts 736, Wit 397, Mood GREAT,
  Fans 209245, Skill Points 173 — and **Energy N/A**.
- **This screen prints the same five stats as `607/1300`, `397/1800`** while the Cockpit prints
  `607 / N/A — No target was entered for this stat.` The denominators disagree between two screens of one
  run: scenario cap here, build target there.
- The form's `turn` select offers only turns that exist (Turn 1), so it inherits the position problem.

#### Outcome / next screen
Navigated to the Career Timeline.

---

### Screen 16 — Career Timeline

#### Route and trigger
- Route: `/training-runs/9/timeline`, title "Career timeline: Rice Shower".
- Trigger: the Cockpit's "Career Timeline" link.

#### What the screen asks for
A type filter and a rail. Its header states that each row's BEFORE block is the previous logged turn and
that EXPECTED and ACTUAL collapse because `ADR-0003` stores the absolute end-of-turn value.

#### Values entered + source trace
None entered.

#### Source data with no UI home
- The source's four won team rounds, its cleared goals and its pending finals would all appear as rows;
  none exists, because the run sits at turn 1.

#### Friction / errors / dead ends
- The rail reads `Junior Year · Early January · Turn 1`, one row, "showing 1 of 1" — a Senior October
  snapshot rendered as the career's opening turn.
- The row says "Turn recorded" and does not show the activity, so the Speed choice made on screen 10 is
  not visible here.

#### Outcome / next screen
Navigated to the Career Result.

---

### Screen 17 — Career Result

#### Route and trigger
- Route: `/training-runs/9/result`, title "Career result: Rice Shower".
- Trigger: the Cockpit's "Career Result" link.

#### What the screen asks for
Read-only. It refuses and offers `Back to the Cockpit`, `Career Timeline` and `Cockpit`.

#### Values entered + source trace
None entered.

#### Source data with no UI home
Its subject matter — a finished build, a final race history, scenario objectives — is not reachable while
the run is Active, which is the correct outcome for a mid-career snapshot.

#### Friction / errors / dead ends
- **"This career is still running"** with the stated rule that the screen reads a Completed run. The
  snapshot was not converted into a finished career, and the status was never touched.
- Its copy sends the user to "the run record screen" to change the status, and that address redirects to
  the Cockpit (screen 9).

#### Outcome / next screen
Navigated to the Skills planner.

---

### Screen 18 — Skills planner

#### Route and trigger
- Route: `/training-runs/9/skills`, title "Skills planner: Rice Shower".
- Trigger: the run header's "Skills planner" link.

#### What the screen asks for
Read-only. A "Your target" state line, `Skill Point coverage`, `Hint discounts`, and five priority states
(Required, Available, Learned, Inherited).

#### Values entered + source trace
None entered.

#### Source data with no UI home
- **§1.3's owned skill list** (ten chips plus Corner Recovery ○) and **§2.2's purchasable pool with its
  173-SP question**: the screen reports the run's SP but lists no skills, because none are marked.
- **§2.1's hint discount ladder**: see the friction note.

#### Friction / errors / dead ends
- Coverage resolves to "The skills still to learn cost **0** SP, and the run holds 173 SP, leaving 173" —
  arithmetic over an empty set, presented as a result.
- **`Hint discounts are not modelled.`** The first pass rendered the ladder here in full — "Hint Lvl 1 10%
  off … Hint Lvl Max 40% off" with a Confirmed marker — which is the §2.1 figure this repository records as
  client-read. One pass showed it; this pass disclaims it. Reported as observed on each pass, not
  reconciled.
- "No build target is set on this run, so the fit cells below compare against nothing" correctly carries
  the empty target forward.

#### Outcome / next screen
Navigated to the run-scoped deck screen.

---

### Screen 19 — Support deck (run-scoped)

#### Route and trigger
- Route: `/training-runs/9/deck`, title "Support deck".
- Trigger: the run deck surface reached from the run flow (reached by its route in this pass).

#### What the screen asks for
Six slots with their cards, types, scenario-link flags and full effect lists; a per-slot `Owned`/`Rented`
toggle; `Replace the card in Slot N`; `Confirm deck`; and a picker with `Type`, `Rarity`, `Availability`,
`Name contains` filters.

#### Values entered + source trace
None entered — this screen was used to read back what step 5 had set.

#### Source data with no UI home
- Card client levels and limit breaks, and the reached per-card effect values, as on screen 6.

#### Friction / errors / dead ends
- **The friend card's rented status survived this time.** Slot 6 · Friends reads `Owned:false
  Rented:true` after a full page load, and the screen states: "The owned or rented flag is saved with the
  deck, one value per slot, and stays on the run after a reload. A slot you have not classified reads as
  N/A rather than as Owned." Slots 1-5 read `Owned:true`.
- The deck step's own copy (screen 6) still says the flag has no column and stays in the draft, which this
  screen contradicts.

#### Outcome / next screen
Navigated to the Race planner, the last screen the flow exposes.

---

### Screen 20 — Race planner

#### Route and trigger
- Route: `/training-runs/9/races/planner`, title "Race planner: Rice Shower".
- Trigger: `Plan the whole calendar` on the Race decision screen.

#### What the screen asks for
Read-only. "Planning from Junior Year · Turn 2"; Held figures (Win probability, Expected risk);
Deadlines; Mandatory races, each expandable to a per-race panel (Grade, Distance, Distance band, Surface,
Running style, Fan gain, Reward, Skill points, Scenario reward, Estimated win probability); Upcoming races;
and a `Compare` control per race.

#### Values entered + source trace
None entered.

#### Source data with no UI home
- The Soft going and the per-place fan payouts (§1.6 lines 189-191): `FAN GAIN` is `N/A` with the reason
  that the payout curve id is stored but no payout table is.
- The Arima Kinen goal (§1.6 line 176): goal races remain unrecorded.

#### Friction / errors / dead ends
- The panels are uniformly `N/A` on the calendar's own placeholder races (Junior Make Debut and Junior
  Maiden Race carry no distance, band or surface), while Chukyo Junior Stakes resolves to 1,600 m / Mile /
  Turf with a 350-fan gate. Consistent disclosure per field, but the page runs to 17,409 characters of
  mostly empty panels.
- Win probability is withheld on every panel by design.

#### Outcome / next screen
End of the reachable flow for an Active run. Nothing further is offered.

---

## Closing assessment

### What worked

- **The deck is faithfully representable end to end, including the friend card.** All six cards from §1.4
  equipped, Haru Urara alone carried the Scenario Link flag the source derives independently (§1.4 lines
  92-94), Super Creek sat in `Slot 6 · Friends`, and — unlike the first pass — the `Rented` flag survived a
  full reload to screen 19. This is the §5 "sixth support-card slot" question answered affirmatively: the
  borrowed card is representable as a friend slot **and** an ownership flag, and the flag persists.
- **The mid-career stat line entered cleanly and read back.** Every figure from §1.2 (607/577/549/736/397,
  SP 173) plus §1.1's 209,245 fans and §1.7's GREAT mood were accepted and echoed by the Cockpit, the
  Event decision and the Timeline, with no validation error anywhere.
- **The cockpit derived the fan class and the gap** ("209,245 · Star · 30,755 to next") from the fan count,
  which is §1.1 lines 21-22's pair of figures arriving without a field being needed.
- **The §1.7 turn record now has fields.** Facility levels 4/4/4/5/4, Failure 39% and the +18/+8/+6
  preview were all accepted on screen 10; none of them existed in the first pass.
- **Energy without a number is finally expressible.** Exact / Band / Not recorded with Low/Mid/High answers
  §1.7's "the client never prints an Energy number" directly, and the source's "low" was selectable. This
  was the first pass's most structural entry obstacle and it is gone at the control.
- **The ancestry form got the three things the first pass said it lacked**: disambiguated parent options
  (`Vodka [Wild Top Gear] · 7`), a letter-rank field beside the star count, and spark editors on all four
  grandparents — so §5's cap-arithmetic question now has a place for sparks on every node, and the two
  ★★★ blues the §1.2 shortfall turns on (Seiun Sky's Stamina, and by extension Taiki Shuttle's Power) can be
  entered where the first pass could place only two of the six.
- **The wizard stopped forcing invention.** The target step accepted an empty submission, so this pass
  carried the source's real "no documented build target" instead of manufacturing purpose, distance, style
  and five cap numbers, and the downstream screens reported `N/A` and "compare against nothing" honestly.
- **The contract reports its own gaps** (Warnings: target, ancestry) and **the Result screen refused to
  present an unfinished career as finished.**

### Functional findings

Where the UI failed to do what it claims, lost information, produced contradictory state, or blocked
legitimate progression.

1. **The Legacy step discards its entire input with no signal.** Every value in the screen 5 table was
   entered, `Save Legacy` was pressed, and the result was no confirmation flash, no `[role=alert]` error,
   and the unchanged line "No Legacy is recorded for this setup yet". The next screen that reads the draft
   — Preflight — then reported `ANCESTRY · N/A` and "Ancestry is not entered." The first pass, on the same
   route with the same kind of input, received a "Legacy recorded." flash and a populated contract, so the
   failure is not inherent to the data entered.
2. **The Preflight warning text promises the opposite of what happened.** "No two-parent ancestry is
   recorded in this setup. Step 4 enters it, and nothing is lost by going back." The ancestry was entered on
   step 4 and lost on the way forward, without going back at all.
3. **`Start Career` stays enabled against its own contract.** The screen lists two open warnings and still
   creates the run, so the contract's final state can read `YOUR TARGET · N/A / ANCESTRY · N/A` while
   announcing "Everything the six steps recorded".
4. **The Energy band is accepted and then read back nowhere.** `Band → Low` was submitted on screen 10; the
   Cockpit prints `Energy N/A`, the Advisor states "there is no band to read", and turn 1's correction form
   prefills the other three values but exposes no energy-state or band control at all. Whether the value is
   stored or dropped cannot be determined from the UI — and that is itself the finding: no screen the user
   can reach confirms it.
5. **Two screens give the same stat two different denominators.** Cockpit: `Speed 607 / N/A — No target was
   entered for this stat.` Event decision, same run, same moment: `Speed 607/1300`. One is the build target
   and the other the scenario cap, and neither screen labels which it is printing.
6. **With no target, the scenario caps vanish from the cockpit stat strip.** `N/A / N/A` replaces the
   `N/A / 1,300` the first pass saw, so §1.2's comparison of the run's stats against a 1300/1800 base is
   no longer available on the screen that exists to show it.
7. **Three instructions point at a screen that no longer exists.** `/training-runs/9` redirects to
   `/training-runs/9/cockpit`, yet the Career Result says the run record screen changes the status, the
   Training decision offers "Record the first turn **on the run screen**" linking to the Cockpit, and the
   deck step still asserts that the owned/rented flag has no column and stays in the draft, which screen 19
   contradicts by persisting it.
8. **One card prints two contradictory values under the same label.** The Unity Cup scenario card carries
   `Ruleset · N/A` in its fact block and `Ruleset · Team` in the description block beneath it.
9. **A blank spark row survives as counted data (verified on the adjacent run).** Run 8's Inheritance screen
   reports `BLUE 3 Sparks` where two real blue sparks were entered — the third being the empty trailing row
   the first pass recorded being written into its contract. The crash that hid this in the first pass is
   fixed, so the inflation is now on screen.

### Experiential findings

Where the UI works but costs the user clarity, repetition or a workaround.

1. **The run's position is still inexpressible.** YEAR and MONTH are derived from `Turn`, so a Senior Early
   October snapshot logged at the field's default becomes `Junior Year · Early January · Turn 1`, and the
   Timeline, the race surfaces and the mandatory-race logic all read that derived position as fact.
2. **§1.2's caps remain unenterable.** 1325, 1322 and 1368 exceed the 1300 maximum on both the target step
   and the turn form, and no screen asks for a cap at all.
3. **Grandparents take a name and sparks but no rank.** `Own rank (letters)` exists for the two parents
   only, so §1.5's B+ / A / UG / UG grandparent classes have nowhere to go.
4. **The letter-rank suggestion set cannot express `UG`.** It offers G..S, B+, A+, S+ — no SS, U, UU or UG.
   The field is editable, so typing the source's rank passed with no note that it is off the offered set.
5. **Rarity and Potential are named as missing without being reachable.** The Cockpit's badge says the
   trainee's rarity and potential level is unrecorded; the identity form beside it holds only Status,
   Scenario and Delete.
6. **The run's own costume is the unnamed one.** The trainee card lists "debut form" and a second bare
   `★★★SSR`, and shows the debut portrait, so `[Rosy Dreams]` cannot be picked out.
7. **The catalog's aptitudes still disagree with the client read** on Mile, Front runner and Late surger —
   identically in both passes — so a user checking the source against step 2 finds three mismatches.
8. **Recording §1.5 as written is a hundred clicks.** Every spark is one row of three controls behind an
   Add button with no bulk entry, no ordering by kind, and no visible count while editing, and the
   grandparent editors sit behind disclosures that must be opened per node.
9. **Deck comparison must be re-derived from the source.** The analysis prints catalog maxima and disclaims
   interpolation, so §1.4's "Client rows visible" column can only be checked by returning to the document.
10. **Scenario comparison is now lopsided.** Unity Cup carries a description, a ruleset, a planning focus and
    three mechanics; the other three carry `N/A` rows and `Not recorded`, so the cards no longer present
    like-for-like choices.
11. **Two readings of this pass disagree with two readings of the first and were not reconciled**: the
    trainee step's `Stored choice` (updated here, stale in pass one) and the Skills planner's hint ladder
    (disclaimed here, shown with a Confirmed marker in pass one). Both are flagged rather than assumed away.
12. **The Energy band select has no accessible name**, unlike its labelled siblings.

### Unrepresented source data

Source information with no appropriate UI representation for this run:

- Run position: Senior Year Early Oct; 5 turns left; 5 turns until the Unity Cup (§1.1 line 21).
- Rarity ★3 and Potential Level 2 (§1.1 line 20) — named as missing, not enterable.
- The `[Rosy Dreams]` costume identity, whose form is the unlabelled one.
- The growth-rate row +0/+10/+0/+20/+0 (§1.1 line 20; §7 item 2).
- The run's own per-stat caps 1325/1322/1368/1308/1800 and the whole §1.2 cap-composition question
  (lines 39-56, §8.5).
- The documented aptitude letters, which disagree with the catalog on three of ten.
- The ten-plus owned skills and Corner Recovery ○ (§1.3 lines 63-67).
- Card client levels, limit breaks, and the reached "Client rows visible" effect values (§1.4 lines 77-104).
- All six ancestors' ranks as letter classes on the grandparents, and `UG` on the parents' suggested set
  (screen 5's §1.5 lines 138-143).
- Four of the six blue sparks and every pink, green and white spark in §1.5's table (not entered; the
  fields now exist — see Walk completion).
- The pair's affinity grade (§1.5 states none; left unset and rendered `N/A`).
- The entire §1.6 Unity Cup state: team "Blue Bloom", motto "Dreaming Big", league 8th, four rounds won,
  finals pending, and both cleared and active goals.
- The §1.6 race-day card: both options, the Soft going, the fan ladder, the entry criteria, the "top
  contender" verdict.
- The `Floor Cleaning` banner and the three card avatars on the observed tile (§1.7 line 221).
- The Team Info numbers: 55 Unity Trainings, 6 Spirit Bursts, 5 Extreme, 20/20 members, the +30 ranking
  bonus, grades A/A/A/S/A (§1.8) — and therefore the run's place in §2.4's 10-12 burst band.
- The §1.9 roster and the guests' skill lists.
- The §2.1 hint-discount ladder as a displayed figure on this pass, and the §2.2/§2.3 pool, prices and
  recommended spend.
- The §2.4/§8.2 payout dispute is surfaced by the app rather than represented by it: the band table renders
  with its own caveat, which is the right treatment of an unsettled figure.

### Walk completion

- **Reached the end of the available flow: yes, for an Active run.** Twenty visits in order, from the
  Dashboard to the Race planner, which is the last surface the run exposes.
- **Where it stopped and why.** The flow terminates at the Career Result's designed "still running" state
  and at the Race planner; the Cockpit's remaining actions (Rest, Recreation, Scenario action) resolve to
  the Cockpit itself, and the Inheritance route renders but has no ancestry to show because of functional
  finding 1. Nothing further is reachable without changing the run's status, which the brief forbids.
- **Data intentionally not entered.** §1.5's remaining four blue sparks and every pink, green and white
  spark were not typed in. The fields exist and accepted the five sparks that were entered — including two
  on grandparents, which the first pass had nowhere to put — so the mechanism is proven and the remainder is
  volume rather than a gap. The pair's affinity grade was left unset because the source states none, and the
  Energy number was left unset because the source states none; the band was used instead.
- **Data entered but not retained by the application.** The whole step 4 ancestry — both parents, all four
  grandparents, both rank letters, the rented-parent flag and three sparks. That is functional finding 1,
  not a choice by this walk, and it is why run 9's contract reads `ANCESTRY · N/A`.
- **Persistence claims are limited to what a screen echoed back.** Stats, SP, fans, mood and the deck
  (including the Rented flag) are verified by read-back on the Cockpit, the Event decision, the Timeline and
  the run deck screen. The Energy band is explicitly **not** claimed to persist: no reachable screen reads
  it back. The ancestry is explicitly recorded as absent from the contract rather than as deleted from
  storage, because storage was not inspected.

---

## rice-shower-unity-cup-ux-walk-resolutions.md

> **Augmented 2026-10-10 (Round 14, augment-only).** Source: `docs/audits/rice-shower-unity-cup-ux-walk-resolutions.md` (495 lines), reproduced verbatim below with headings demoted one level. The source file remains in place; no citation was repointed and nothing was deleted.

## Resolution record: Rice Shower / Unity Cup UX walk

What happened to each finding in `docs/audits/rice-shower-unity-cup-ux-walk.md` after the
remediation began. The audit is the historical baseline and is **not** rewritten; this file records
only what came afterwards, and a finding whose text later proves wrong is corrected by a dated
erratum in its own document, never by editing the audit.

All of it landed on 2026-10-09, in one session. The session-boundary record is below, because the
brief's stopping point and what was actually done are not the same thing.

### Session boundaries

The brief makes Session 1 Phase 1 only (D1-D3) and states: "Do not start Phase 2 in Session 1 unless
explicitly instructed."

**Phase 2 was started in the same session, and it was explicitly instructed.** Before any code was
changed, the scope was put to the owner as a choice between "Session 1 only (D1-D3)", the brief's
mandatory stopping point, and "Session 1 + Session 2 (D1-D9)". The owner chose the second. The same
exchange authorised one commit per completed slice, and authorised the `deck_slots.ownership` column
together with a drafted ADR - the commit, not the ruling: `ADR-0023` still stands Proposed awaiting the
owner's decision.

So the record is **Session 1 = D1, D2, D3** and **Session 2 = D4-D8 delivered, D9 not delivered**. The
boundary was authorized rather than crossed, but it was not closed in the sense the brief intended: the
Session 1 handoff and the Session 2 handoff came out as one artifact, so the intermediate stable
handoff the brief asked for before Phase 2 opened does not exist as a separate record.

**Phase 2 is not complete.** Its Definition-of-Done list includes "duplicate turn-entry business logic
is eliminated or placed behind one canonical contract" and "one authoritative turn persistence path
exists". The second is satisfied - and was already satisfied before this work, which is D9's finding.
The first is not done. Phase 2 closes when D9's remaining work lands.

### Status

| Finding | Status | Slice | Commit | Evidence |
| ------- | ------ | ----- | ------ | -------- |
| D1 inheritance HTTP 500 | **Fixed** (browser half green, 2026-10-09) | 1.1 | `291f6ea` | `CareerInheritanceEventTest` (14 cases); `career-inheritance-event.spec.ts` **8 of 8 passed**, 5.8 min |
| D2 blank trailing Spark row | **Fixed** | 1.2 | `a4b2b26` | `CareerLegacyDeckStepsTest` (1 new case) |
| D3 rented/friend flag not persisted | **Fixed** | 1.3 | `fdebcc9` | `RunDeckTest`, `SupportDeckBuilderTest`, `CareerPreflightTest` |
| D4 target validation contradicts the UI | **Fixed** | 2.1 | `be02297` | `CareerBuildTargetTest` (3 new cases); the sibling `KI-47` named in the follow-up is already **closed** |
| D5 trainee stored-choice readback `N/A` | **Fixed** | 2.2 | `dd6dd22` | `CareerTraineeSelectTest` (1 new case) |
| D6 turn table hides Fans/Energy | **Fixed** (rendered surface removed, 2026-10-09; see below) | 2.3 | `89e8453` | `TurnRowActionsTest` (2 new cases) |
| D7 wizard step numbering skips 3 | **Fixed** (rendered surface removed, 2026-10-09; see below) | 2.4 | `6d53ffc` | `GuidedTurnOnRunViewTest` (1 new case) |
| D8 Rice Shower aptitude mismatch | **Verified, not a tool defect** | 2.5 | `be48e60` | `CareerTraineeSelectTest` (1 new case) |
| D9 duplicate turn-entry paths | **Fixed** — one shared field source, all three surfaces wired and labelled alike (2026-10-09) | 2.6 | `8e3d33c`, `5e41137`, `7a81c7c` + this commit | `TurnEntryFieldSourceTest` (4 cases) |
| Career position / snapshot mode | **Landed** (Phases 4–5; 4.4 and 5.3 cockpit-read deferred, see close) | 4.1–5.4 | `0025f37`, `d0bd138`, `a31000c`, `b890cd5`, `fc66978`, `8c7628d` | `SnapshotReviewTest`, `SnapshotTimelineTest`, `SnapshotRaceCalendarTest`, `SnapshotTrainingDecisionTest` |
| Legacy disambiguation, ancestor rank, grandparent sparks | **Landed** (Phase 6) | 6.1–6.3 | `31c9f5d`, `5876f64`, `f39ff03` | `LegacyDisambiguationTest`, `AncestorRankTest`, `GrandparentSparksTest` |
| Scenario metadata, typed spark targets | **Landed** (Phase 7) | 7.1–7.2 | `1098546`, `6fa0d01` | `ScenarioMetadataTest`, `SparkTargetValidationTest` |
| Energy model (exact/band/unknown) | **Landed** (Phase 8.1; §11 record added at `837d995`) | 8.1 | `1ec92ab` | `EnergyStateTest` |
| Cockpit reads the career position and countdown (4.4 + 5.3) | **Landed** (closes the 2026-10-09 deferral) | 2A.1 | `3b6f3cc` | `SnapshotCockpitTest`, `SnapshotScenarioCountdownTest` |
| Facility levels per turn | **Landed** | 2A.2 | `c8402ee` | `TurnFacilityLevelsTest` |
| Per-turn failure rate and preview gains | **Landed** (stored, not computed) | 2A.3 | `3b34fb0` | `TurnFailurePreviewTest` |
| Stat-ceiling fourth marker | **Landed-superseded** — the render half was superseded by the cutover at `b30906a` (`StatBand.vue` deleted, `CareerStatePanel.vue:16` states it was deliberately not reused); the persistence half was then dropped in this commit, since no reader remained. This supersedes the 2026-10-10 close's "the persistence half is the deliverable" | 2A.4 | `cb42d93`, dropped at `2026_10_10_170000_drop_stat_ceilings_from_training_runs` | `RunStatCeilingsTest` retired with the column |
| Nav-rail run-record door relabelled to the Cockpit | **Landed** | 2A.5 | `d251f7f` | `NoStaleRunRecordLinksTest`; career-cockpit / career-result specs |
| Cockpit renders the run's identity, team and Unity Cup state | **Landed** | 2A.6 | `5eba0d0` | `CockpitRunIdentityTest`, `CockpitTeamPanelTest`, `CockpitUnityCupCountersTest` — 9 focused / 70 regression passed |
| Race payout columns | **Landed** | 2B A4 | `057db67` | `RacePayoutTest` |
| Skill spend coverage, hint ladder removed per ADR-0001 | **Landed** | 2B A5 | `992be59` | `CareerSkillsPlannerTest`, `SkillSpendCoverageTest`, career-skills-planner.spec.ts |
| Trainee rarity and potential level (persistence) | **Landed** | 2B A6.1 | `b896d0b` | `TraineeRarityPotentialTest` |
| Character card reference | **Already built** — no change needed | 2B A6.2 | — | existing coverage |
| Growth-rate row (persistence) | **Landed** | 2B A6.3 | `110ffd3` | `RunGrowthRateTest` |
| Per-run stat ceilings (persistence) | **Landed** | 2B A6.4p | `cb42d93` | `RunStatCeilingsTest` |
| Fan ladder service | **Landed** | 2B A6.5 | `e096137` | `FanLadderTest` |
| Team identity (persistence) | **Landed** | 2B A6.6 | `709a233` | `RunTeamStateTest` |
| Unity Cup progression counters (persistence) | **Landed** | 2B A6.7 | `89f3b11` | `UnityCupCountersTest` |
| Advisor card rendered nothing when the finale context was present | **Fixed** — filed as KI-90 | Part 4 | `7a344a4` | career-cockpit.spec.ts + career-cockpit-forms.spec.ts, 18 passed (6.0m) |
| Card levels and limit breaks (Phase 14) | **Deferred** — owner ruling on `PRD.md` §6.9 outstanding; `ADR-0014` declined `user_support_cards` | 14 | — | — |

### Per finding

#### D1 — inheritance HTTP 500: Fixed

`StoreLegacySelectionRequest::payload()` stores `legacies.*.ancestors` as a list of **names**
(`ADR-0010` Consequences §2) and `InheritanceEventController::legacySection()` mapped each entry as an
array with `slot`/`name` keys, so `array_map()` threw on the first string. Names are the canonical
shape: `LegacySelectionSchemaTest` already pinned them, `AncestryGraph::parentNode()` already read
them, and the one stored record on the dev database held names. The controller reads the name list,
`LegacySelectionPayload` refuses a slot-record ancestor instead of letting a second shape exist, and
the page's props contract is `list<string|null>`. The test fixture was converted to the real UI
payload and zero/one/multiple-ancestor cases added.

#### D2 — blank trailing Spark row: Fixed

The Spark editor appends an empty trailing row on `Add Spark` (kind only, no target, no stars).
`StoreLegacySelectionRequest::payload()` stored it unchanged, so the Preflight contract listed a
phantom bare `Blue`; the dev database still holds that exact row on run 8. The write boundary now
discards a Spark carrying neither a target nor a star count, and `LegacySelect.vue` applies the same
rule so the chip list and the posted payload agree while the row is still a draft.

**Not done:** the phantom already stored on run 8 was left in place. Nothing reads it as a Spark
except the Inheritance page's predicted counts, and rewriting a stored record to fix a display is a
data edit this slice did not authorise.

#### D3 — rented/friend flag: Fixed

`deck_slots` had no ownership column (`ADR-0014`), so the flag lived only in the wizard's session
draft and `/training-runs/{run}/deck` printed the one value it could read. `deck_slots.ownership`
(nullable `OWNED` / `RENTED`, null = not recorded) is written by both writers and read by the
builder; `DeckSlot::booted()` guards the value. The run screen's deck panel has no ownership control,
so its re-save preserves what the slot already holds. Docs travel with the schema: `ADR-0023`
(Proposed, for the owner's ruling), a dated erratum in `ADR-0014`, the digest, and the ADR index.

#### D4 — target validation: Fixed

The message under every stat offered "or leave the whole target unset", but `purpose` and all five
stats were `required`, so that branch was unreachable and an empty save answered nine errors. The
whole target is now optional and all or nothing: nothing entered means no field is required and
`payload()` answers null (which clears the column); any value makes every field required. The
no-target downstream path needed no change (`TrainerAdvisor::deficits()` already answers nulls).

**The sibling named in the follow-up is already closed, and that premise did not hold.** The
instruction was to reference `KI-47` as "the still-open sibling" — the no-scenario band/form ceiling
disagreement (`/1400` on the bar against `max=1200` on the form). It is not open. `KI-47` was ruled by
the human owner on 2026-10-01 ("the band is wrong") and **closed the same day by `04658f2`**, four days
before this remediation began, which is why it is absent from `KNOWN-ISSUES.md`. The lesson is already
carried at the surface: `BuildTargetController`'s docblock names `ScenarioCaps::forRun` as the authority
(`ADR-0015`, KI-47) and `StoreTurnEntryRequest`'s own comment states a no-scenario run "keeps 1200", so
band and form read the same number. No open register entry covers a band/form ceiling disagreement, so
D4 may read as fully closed. Recorded rather than silently complied with, because writing "still open"
would put a false status in this record. The band's current rendered number was **not** re-measured in a
browser in this session.

#### D5 — trainee readback: Fixed

The step's `Stored choice:` readout derived the name from `trainees.data`, the paginated and filtered
roster, so a Trainer who searched and selected saw `N/A` beside a flash confirming the selection. The
name now travels with the stored selection.

#### D6 — turn table Fans/Energy: Fixed, rendered surface removed

The payload already carried `energy` and `fans`; only the table lacked the columns. Both are now
printed, and a turn that recorded neither names its own absence (`N/A` with a `title`). The read path
and the columns' presence are pinned by feature tests; the rendered-DOM assertion in
`run-detail.spec.ts` was **not executed** (see the infrastructure note).

**2026-10-09 resolution — rendered surface removed.** The peer's cockpit cutover deleted
`Runs/Show.vue`, its `show()` method and `run-detail.spec.ts` in one pass (`TrainingRunController`
`:198`), so the run-screen turn table the rendered assertion addressed no longer exists on this tree,
and `runs.show` is a redirect to `runs.cockpit`. The behaviour the assertion guarded — Energy and Fans
reachable and printed — is carried by the cockpit, whose correction form reads Energy and Fans
(`Cockpit.vue:446-452`) and whose `career-cockpit.spec.ts` records Energy through it (the "records
Energy through the cockpit correction and then marks one action" case). The D6 feature tests were
removed with the surface. Status: **Fixed (rendered surface removed)**.

#### D7 — wizard step numbering: Fixed, rendered surface removed

The indicator counted `current`'s position inside `def.steps`, the scenario's turn vocabulary (Unity
Cup's five include a facility and a team-race step the rail never lands on), so it read "Step 2 of 5"
then "Step 4 of 5". The rail has two stages; the server now sends `flow` and the indicator and the
progress pips count those. The rendered assertion in `run-detail.spec.ts` was **not executed**.

**2026-10-09 resolution — rendered surface removed.** The same cutover removed the `runs.show` guided
rail entirely, so there is no step indicator left on this tree to assert against. The cockpit has no
guided flow and no step indicator, so the assertion cannot be re-addressed. Status: **Fixed (rendered
surface removed)**.

#### D8 — Rice Shower aptitudes: Verified, not a tool defect

The audit reported a catalogue/display mismatch (document: Mile B, Front A, Late B; picker: C, B, C).
The picker is faithful. The committed export the seeder reads
(`database/seeders/data/gametora-characters.e9e9ee6d.json`, card `103001`, `[Rosy Dreams]`) carries
`aptitude: ["A","G","E","C","A","A","B","A","C","G"]` in the turf..end order; the seeded column holds
it letter for letter and `TraineeSelectController::aptitudes()` maps index to index. No seed,
transformation or display defect exists on the tool's side, so nothing was corrected: overriding the
project's own source with one document's transcription is the change the brief's non-goal forbids.
All ten letters are pinned for that trainee, and the scenario document gains a dated erratum
recording the disagreement as open.

The **authority ruling** is recorded at the governing ADR rather than only in the document: `ADR-0004`
gained a dated erratum stating that the GameTora export is authoritative for aptitude letters, that a
scenario document is a secondary reference whose disagreements resolve in the catalogue's favour, and
that a disagreement is recorded open rather than resolved in the display path.

#### D9 — duplicate turn-entry paths: Diagnosed, not implemented

**The premise does not hold as stated.** The two surfaces are `Runs/Show.vue` (the guided rail plus
the `Correct a turn by hand` disclosure) and `TrainingDecisionController`'s decision page. Both POST
to **the same route** (`runs.turns.store`) through **the same Form Request**
(`StoreTurnEntryRequest`) and the same controller method, so there is one persistence path, one
validation set and one normalization contract. `TrainingDecisionController` says so itself: "The
write is the guided turn's, unchanged."

What is left of the finding is **presentational**. The backend diagnosis below is the artifact the
follow-up's §6 asked for: written before any code, so the refactor is designed rather than improvised.

**Backend diagnosis (2026-10-09).** The persistence contract is already single, and was before this
remediation. One route, one Form Request, one controller method, one table:

| Layer           | Owner                                                                |
| --------------- | -------------------------------------------------------------------- |
| Route           | `POST /training-runs/{run}/turns` -> `runs.turns.store` (`routes/web.php:175`) |
| Form Request    | `App\Http\Requests\StoreTurnEntryRequest`                            |
| Controller      | `TrainingRunController::storeTurn()` (`:1769`)                       |
| Stored row      | `turn_entries`                                                       |

Three surfaces post to that route and are validated by that request:

| Surface                  | Component                    | Field-list source                                                       | Stat label       |
| ------------------------ | ---------------------------- | ----------------------------------------------------------------------- | ---------------- |
| Run record, raw hatch    | `Runs/Show.vue:720-732`      | `const statWords = ['speed', …, 'wit']` (`:119`)                         | `Speed *`        |
| Training decision        | `Career/TrainingDetail.vue:304-366` | `const statFields = [{ name: 'speed', label: 'Speed' }, …]` (`:92-98`) | `Speed total *`  |
| Cockpit correction       | `Career/Cockpit.vue`         | `const statFields = [{ name: 'speed', label: 'Speed' }, …]` (`:189`)     | `Speed`          |

So the duplication is **three-way, not two-way**: the same five-stat list is declared three times,
twice under the identical name `statFields` with the same `{ name, label }` shape, and the labels
drift. Each surface also declares its own extras (`Show.vue` adds `sp` and `condition`;
`TrainingDetail.vue` adds `sp`, `energy` and `fans`), and that part genuinely differs, so it stays
per-surface.

**The shape the fix takes, and the alternative rejected.** One shared field-definition module (a plain
exported constant read by all three surfaces), not a shared form component and not a base class. That
is `ADR-0018`'s reasoning when it chose `App\Services\PageSize` over a base `FormRequest`: the surfaces
agree on the field list and the labels and disagree on everything that matters — which extras they
offer, whether they preview or write, what they call themselves. A shared form component would push a
union of three behaviours into one place, and the drift it removed would return as a props contract
with a flag per surface. `ADR-0018`'s rejected base-class alternative is the precedent, recorded with
the same specificity.

**What cannot be moved yet.** Of the four files the unification touches, three carry another session's
uncommitted work: `TrainingRunController.php`, `StoreTurnEntryRequest.php`, `Runs/Show.vue` and
`Career/Cockpit.vue` are all modified in the working tree by a concurrent session; only
`Career/TrainingDetail.vue` is clean. `ADR-0018` left two copies of the clamp in place for exactly this
reason, and the same rule applies: the unification waits for those files to be free.

**Remaining, explicitly.** One shared turn-entry field definition read by all three surfaces; the label
vocabulary reconciled (`Speed` on the run record against `Speed total *` on the decision screen); and
each surface naming whether it creates or edits a turn. Until that lands, Phase 2's canonical-contract
gate stays open. **D9 is Fixed** — one shared field source, all three surfaces wired and labelled alike
(landed at `fec95cd`), recorded in the status table above.

### Infrastructure findings

1. **The database guard refused every Windows path (fixed, `033c974`).**
   `DatabaseGuard::isAbsolute()` wrote its character class as `[\\/]`, which compiles to a class
   holding only `/`, so every drive path using backslashes read as relative and
   `assertAbsolutePath()` refused it at application boot for any isolated role. The Playwright harness
   passes its scratch database as a Windows path (Node's `join()` emits backslashes), so
   `npm run test:browser` could not start its server at all. The predicate now normalises the
   separator first, and `tests/Unit/DatabaseGuardPathTest.php` pins both separators (the method had no
   coverage, which is how the regression shipped).

2. **The browser suite cannot be executed on this host (outstanding).** With the guard fixed, the
   server boots and answers `/up` in ~1.3s, but the specs fail in their own setup: the scratch
   database is seeded by `DatabaseSeeder`, which seeds **no Veterans**, while
   `career-inheritance-event.spec.ts` selects a parent by label (`Symboli Rudolf`) from the
   run-scoped Legacy Lab's Veteran picker. Every case fails at that `selectOption` before reaching
   the page under test. This is a pre-existing gap introduced when KI-69 made the harness own a fresh
   database, not a regression from this remediation. The minimum fix is a Veteran in the browser
   seed (or a spec that creates its own), after which the harness's cost on a contended host should be
   measured again: a two-spec run exceeded 17 minutes here while a peer session was rebuilding assets.

   Consequence: D1, D2, D3, D5, D6, D7 and D8 have feature-test coverage that passed, and the
   rendered-DOM halves added to `run-detail.spec.ts` (D6, D7) are written but unrun. No browser claim
   in this record is a pass.

3. **`app/Http/Controllers/TrainingRunController.php` and `resources/js/pages/Runs/Show.vue` carry
   another session's uncommitted work.** Each of this remediation's commits stages only its own hunks
   in those two files (built from the index and written back with `git update-index --cacheinfo`); the
   other session's work is untouched in the working tree.

#### Correction to finding 2, same day — the seed gap was the first blocker, not the cause

Item 2 above diagnosed the missing Veterans. Removing that dependency (a spec-local edit — this suite
must start with zero runs, and a Veteran is a filed run) moved the failure and exposed the real one:
`career-inheritance-event.spec.ts` addresses its ancestry controls as `input[name="legacies.0.rank"]`,
`[name="legacies.0.ancestors.0"]` and `[name="legacies.0.sparks.0.kind"]`, and **no such `name`
attribute exists on either surface**. `Legacy/Builder.vue` and `Career/LegacySelect.vue` both bind
those fields with `v-model` and give them an `:id` (`rank-parent_a`) but no `name`; only the parent pick
carries a `name`, because `AncestryNode` is handed `controlName` — which is why the original failure
resolved the locator and then reported "did not find some options" instead of never finding it.

The spec's own docblock claims "the legacy configuration is entered via the run-scoped Legacy Lab
builder". The builder cannot be addressed the way the spec addresses it, so **this spec has never been
able to pass**. `openInheritanceEvent()` needs re-addressing by `id`/role, or rebuilding on the wizard
path the audit actually used — roughly twenty selectors, each verification costing many minutes on this
host. Not attempted here. The partial edit made while diagnosing this was reverted; the spec file is
unchanged from `HEAD`.

**The D1 browser gate is green (2026-10-09), and the sentence above is superseded by it.**
`career-inheritance-event.spec.ts` passed **8 of 8** in 5.8 minutes on the scratch harness: the
predicted/observed separation and badges, the milestone glyphs, the keyboard entry through the observed
form, the 44px sweep, the axe scan, the empty state, the aggregate Spark counts and the no-computed-figure
case. Getting there took the fixture off surfaces that no longer render: the turns are recorded over
`runs.turns.store` (the shape `ura-panel.spec.ts` already uses) because `runs.show` is now a redirect to
the cockpit and no rendered surface offers a create-turn form, and the completion wait reads the write's
own redirect rather than a session flash a second render can consume.

**D6 and D7's rendered assertions are unrunnable on this tree, and that is a structural finding rather
than a skipped run.** Their spec, `run-detail.spec.ts`, drives the guided rail and the turn table, both
of which live in `Runs/Show.vue` - and `runs.show` is now a redirect to the cockpit
(`routes/web.php:116`), with `TrainingRunController::show()` left unrouted. A one-case probe of the spec
failed exactly there, at `page.waitForURL: Test timeout of 180000ms exceeded`, before reaching any D6 or
D7 assertion. Both stay **Fixed (browser half unrunnable)**: their feature coverage is green, and their
rendered half belongs to whoever lands the cockpit cutover, since the cockpit is the surface that now
renders and it is peer-held.

#### Correction to finding 2, later the same day — the addressing fix landed (Path B)

The paragraph above recorded the re-addressing as "not attempted here" and the spec as "unchanged from
`HEAD`". Both have since changed: the fix landed, and the spec file is no longer at `HEAD`.

**The path was decided by a check, not by preference.** `Legacy/Builder.vue` carries an unstaged peer
hunk in this shared worktree (a `:model-value` binding on the `AncestryNode` invocation, ~`:243`), so the
component fix ("Path A") was not available — a file carrying another session's uncommitted work is not
touched in this remediation. The spec-only fix ("Path B") was taken instead and touched no component.

**The `name` gap is a convention gap, not a functional one (Case B).** Verified before deciding:
`Legacy/Builder.vue` does render a real `<form>` (`:207`) but submits through Inertia's `useForm` with
`@submit.prevent="submit"`, never native submission and never `FormData(form)`; `Career/LegacySelect.vue`
has no `<form>` element at all; and `FormData` appears nowhere on either surface (`resources/js` carries
it only in `GuidedStep.vue:110` and `Import.vue`'s `forceFormData`). A `name` attribute on the rank,
ancestor, rented or Spark controls is therefore an HTML-convention improvement, not a load-bearing
contract, and adding it would have been a production change made to accommodate a test. It is recorded
as owed, not claimed as a defect.

**Path B had to fix two independent misaddresses, not one.**

1. **The field addresses.** The rank, ancestor, rented and Spark controls carry `v-model` and an `:id`
   and no `name`; only the parent pick carries one, because `AncestryNode` is handed `controlName`
   (plan §4.1 item 10, `KNOWN-ISSUES.md` KI-69). The fixture now addresses them by `:id`
   (`#rank-parent_a`, `#ancestor-parent_a-0`) and by role plus the label the wrapping `<label>` gives
   them ("Kind", "Applies to", "Stars").
2. **The save contract.** The spec waited on a `Save Legacy` button and a `Saving the Legacy…` status
   toast. Those belong to the **wizard** (`Career/LegacySelect.vue:451,454`); the surface this fixture
   actually navigates to, the run-scoped builder, has `Confirm Inheritance` (`:449`) and no
   `role="status"` element at all. The wait is now the disclosure flipping to the recorded state, which
   is the signal a Trainer reads.

The parent pick is left unchosen: this suite's scratch database holds no Veterans (`KI-69` class 3), so
the roster renders no options, and `legacies.*.legacy_id` is nullable, so the payload is valid without
one. The picker's own contract is covered by `ancestry-node-picker.spec.ts`, a component-level case
mounted for exactly that reason.

**A third defect in the same spec, found by reading the page rather than by the run.** The case
`predicted section aggregates sparks across both parents and grandparents` asserts `White: 2 Sparks` and
`Scenario: 1 Spark`, but the fixture enters only blue, pink and green Sparks, and
`InheritanceEventController::predictedSection()` (`:195-204`) emits a kind **only when at least one Spark
of it exists**. Those two assertions cannot pass against any fixture entering blue/pink/green only. They
are left unchanged rather than fitted to the fixture, and are recorded here as an open defect in this
spec.

**The one authorized run (2026-10-09).** `PLAYWRIGHT_PORT=8233 npx playwright test
tests/browser/career-inheritance-event.spec.ts`. The fixture's re-addressing **worked**: the run was
created, twenty turns logged, and every re-addressed control was filled and persisted. The compare
surface the write redirects to reads `Parent A rank 3`, `Parent A ancestors Symboli Rudolf, Mejiro
McQueen`, `Parent B rank 2`, `Rented from a friend B`, `Blue Sparks 2`, `Pink Sparks 1`, `Green Sparks
1` — the payload `StoreLegacySelectionRequest::payload()` stored. So `#rank-parent_a`,
`#ancestor-parent_a-0`, the "Kind"/"Applies to"/"Stars" role addresses and the "Rented from a friend"
checkbox all resolve, and the fixture's data entry is sound.

The run still **failed**, on a third misaddress in the same fixture: the completion wait.
`getByText('This run already has a Legacy selection recorded.')` never resolved, because
`LegacyController::update()` does not return to the builder — it redirects to
`route('legacy.compare', ['runs' => [$run->id]])` with the flash `Inheritance recorded.`
(`LegacyController.php:248-250`). The DOM snapshot Playwright captured on failure **is** that compare
page, carrying the flash, which is how the redirect was identified rather than guessed. The write
succeeded; the wait looked in the wrong place.

**The fix is one line and is deliberately not applied here.** The wait becomes the write's own signal:

```ts
await page.getByRole('button', { name: 'Confirm Inheritance' }).click();
await expect(page.getByText('Inheritance recorded.')).toBeVisible(WRITE);
```

It is left for the next slice because the follow-up's §4 is explicit that a new issue found by the run
is recorded and fixed as a separate slice, never folded into the same commit, and a second run to
verify it is outside this session's budget.

**The run split 4/4, and the second failure was caused by a concurrent session, not by this spec.**
Cases 1-4 reached the completion wait above. Cases 5-8 failed **earlier**, at the fixture's
`page.waitForURL(/\/training-runs\/\d+$/)` after `Create run`, having navigated to
`/training-runs/{N}/cockpit` instead of `/training-runs/{N}`. The cause is a peer's uncommitted edit
landing **during** the run: `git diff -- app/Http/Controllers/TrainingRunController.php` shows that
session's in-flight cockpit cutover, which rewrites every `runs.show` redirect to `runs.cockpit`,
including `store()`'s:

```diff
-        return redirect()->route('runs.show', $run)->with('status', 'Run created.');
+        return redirect()->route('runs.cockpit', $run)->with('status', 'Run created.');
```

The change is uncommitted, so the running `php -S` served the old redirect for the first four cases and
the new one for the last four once the peer saved the file. That is the KI-70/KI-74 class — two sessions
on one checkout — and it is why one fixture produced two different failures inside one run.

**The second fix, also one line, also not applied.** The address has to survive that cutover, because
`HEAD` still redirects to `runs.show` while the peer's tree redirects to `runs.cockpit`:

```ts
await page.waitForURL(/\/training-runs\/\d+(\/cockpit)?$/, WRITE);
```

Recording it rather than hardening the fixture against a peer's uncommitted work is deliberate: the spec
should be corrected once the cutover lands, not fitted to a working tree that is mid-flight.

**Net result of the one authorized run.** 8 of 8 failed, exit 1, ~13.4 minutes of test time (about 15
with the harness's migrate-and-seed). No case reached its own assertions. Nothing here is a browser
pass, so D6 and D7 remain "Fixed (browser half unrun)", and Phase 3 stays closed.

### Schema

`2026_10_09_120000_add_ownership_to_deck_slots_table` is applied to the dev database
(`migrate:status` clean). It adds one nullable column and has a working `down()`. The test suite
builds its own schema from the migration files, so the migration is exercised on every run.

### Verification performed

- `vendor/bin/pest <focused files>` for every slice; the counts are in each commit message.
- `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` — clean after each slice.
- `npm run typecheck` — clean after the Vue changes (D1, D2, D3, D5, D6, D7).
- `vendor/bin/pint` on the changed files (not `--dirty`: the tree carries another session's
  uncommitted files and `--dirty` would reformat them).
- `php artisan migrate:status` on the dev database — clean.
- **Not run:** the full suite as one command, the browser suite, `composer lore` / `composer lore-code`,
  and `composer audit` / `npm audit` (no dependency changed).

### What remains unsupported from the source snapshot

Unchanged by this remediation: the run's own per-stat caps (1325/1322/1368/1308), Energy as a
band or qualitative state (a numeric Energy is stored, but a snapshot that states "near a third" has
nowhere to go and the run then reads `N/A` everywhere), the trainee's star rating and Potential Level, the four grandparents' Spark sets,
ancestor letter ranks (B+, UG), card levels and limit breaks, team identity and the Unity Cup
progression counts, the race-day card, and the mid-career position itself. Each needs the snapshot
architecture (Phase 3 onward) or a dedicated slice; none was faked.

**Erratum 2026-10-10 — five of that list have since been supported by later slices in this
remediation** (the sentence above is kept as written; this note dates it): the per-stat caps
(`cb42d93`), the star rating and Potential Level (`b896d0b`), team identity (`709a233`), the Unity
Cup progression counts (`89f3b11`), and the mid-career position itself (Phases 4–5, read by the
Cockpit at `3b6f3cc`). Still unsupported: Energy as a qualitative band, the grandparents' Spark
sets, ancestor letter ranks, card levels and limit breaks (Phase 14, deferred), and the race-day
card.

### Close — final status (2026-10-09)

**D1–D9 final status.** D1 **Fixed** (browser half green: `career-inheritance-event.spec.ts` 8 of 8).
D2 **Fixed**. D3 **Fixed**. D4 **Fixed**. D5 **Fixed**. D6 **Fixed (rendered surface removed)**. D7
**Fixed (rendered surface removed)**. D8 **Verified, not a tool defect**. D9 **Fixed** — one shared
field source, all three surfaces wired and labelled alike.

**Phases and commits (flat list).** Phase 4: `0025f37`, `d0bd138` (4.1–4.2), `a31000c` (4.3). Phase 5:
`b890cd5` (5.1), `fc66978` (5.2), `8c7628d` (5.4). Phase 6: `31c9f5d`, `5876f64`, `f39ff03`. Phase 7:
`1098546`, `6fa0d01`. Phase 8.1: `1ec92ab`; its §11 package (ADR-0024 + digest + index) at `837d995`.
D6/D7 resolution: `e8a3dff`.

**Outstanding at close.**

- **Slices 4.4 and 5.3 are deferred.** Both read `careerPosition` / `scenarioCountdown` in the cockpit.
  `app/Http/Controllers/Career/CockpitController.php` carries an unauthored peer hunk (staged and
  unstaged, `MM`), so the two slices could not be landed without staging another session's work.
  `SnapshotCockpitTest` and `SnapshotScenarioCountdownTest` were not created for the same reason. The
  cockpit's YEAR/MONTH/TURN block and its countdown still derive from the run's turn number rather than
  the imported position.
- **D6 and D7's rendered assertions are not run**, because the surface they addressed (`Runs/Show.vue`
  and its `run-detail.spec.ts`) was deleted by the peer's cockpit cutover. The behaviour they guarded is
  carried by the cockpit and its `career-cockpit.spec.ts`, which was not re-run in this close (Option B
  in the D6/D7 resolution did not require it).
- **Deliberately out of scope**, per the task: new audits, session reports, `ADR-0019`/`ADR-0022`/
  `ADR-0023` edits, and any change to `resources/js/pages/Runs/Show.vue` or the `runs.show` redirect.

**Verification performed at close.** Focused Pest on every landed slice's file — 47 passed. Full
`vendor/bin/phpstan analyse` — clean. `npm run typecheck` — clean. `npm run build` — clean.
`php artisan migrate:status` on the dev database — no pending. `composer lore` — 302 hits, 77 exempt
(unchanged; no new hit from the close). `composer lore-code` — no hit from any file changed here.
`career-inheritance-event.spec.ts` — 8 of 8 passed. Not run: the full suite as one command, and
`composer audit` / `npm audit` (no dependency changed).

### Close — final state (2026-10-10)

**Phases 1–8 complete.** Phase 4.4 / 5.3 landed via 2A.1 (`3b6f3cc`), closing the one deferral the
2026-10-09 close recorded. The 2A and 2B queues then landed in full (rows above): the 2A slices
render and store what the earlier phases persisted, and the 2B slices carry the remaining
persistence halves. Every landed commit's body carries the same note: *Phase 14 deferred; §6.9
still rules out collection tracking.*

**2A.4.** The only A-phase slice not landed as specified: its render target `StatBand.vue` was
deleted by the cockpit cutover at `b30906a`, and the cutover's own code states the component was
deliberately not reused (`CareerStatePanel.vue:16`). The persistence half is the deliverable
(`cb42d93`); the render half is superseded by the cutover's removal of the visualization and no
surface exists to render a fourth marker on. Recorded Landed-superseded in the status table.

**Advisor defect.** The `finale_context` prop mismatch introduced by `ad8dc77` (snake_case prop,
kebab-case binding, so the card threw on `undefined.label` and rendered nothing) was found during
2A.6's browser verification and fixed at `7a344a4`. Filed as KI-90, which also records why the
cockpit-spec failure it named is resolved by the same commit.

**Deferred.** Phase 14 (card levels and limit breaks) awaits an owner ruling on `PRD.md` §6.9;
`ADR-0014` declined `user_support_cards` and nothing else was faked to fill its place. This is the
remediation's only outstanding work.

**Gates.** State at close: `career-cockpit.spec.ts` + `career-cockpit-forms.spec.ts` 18 passed
(6.0m, session-private port and scratch database); `composer lore` 303 hits / 77 exempt;
`composer lore-code` 118 hits (both at baseline); `php artisan migrate:status` — all Ran, no
Pending. Not run at close: the full suite as one command (the focused and related-regression runs
per slice are recorded in each commit), and `composer audit` / `npm audit` (no dependency changed).

---

## rice-shower-unity-cup-round2-reconciliation.md

> **Augmented 2026-10-10 (Round 14, augment-only).** Source: docs/audits/rice-shower-unity-cup-round2-reconciliation.md (169 lines at first augment), reproduced verbatim below with headings demoted one level. The source file remains in place; no citation was repointed and nothing was deleted.
>
> **Live record, re-synced 2026-10-10 (322 lines / 248 non-blank at this re-sync).** This audit is the working record of the Rice Shower / Unity Cup round-2 remediation and is still being appended to as slices land; the body below is a dated snapshot of it, regenerated from the current source to keep the master current. Read the live source for anything written after this line; the section will be re-synced again when the round closes.

## Round 2 Reconciliation: Rice Shower / Unity Cup UX Walk — Second Pass

Reconciles `docs/audits/rice-shower-unity-cup-ux-walk-2.md` (the second browser walk) against
`docs/audits/rice-shower-unity-cup-ux-walk-resolutions.md` (the remediation record) and the current
codebase at `aedfc30`.

### Method

Each observation from the second audit is classified as one of:

- **Previously fixed and confirmed** — the second pass verifies the earlier fix.
- **Previously fixed but inconsistent** — one surface still presents obsolete behavior or copy.
- **Regression or possible regression** — later behavior conflicts with earlier observations or current contracts.
- **New confirmed defect** — the second audit establishes a new failure or misleading result.
- **Known limitation** — behavior is intentional and consistent with existing product decisions.
- **Source conflict / needs investigation** — evidence does not yet support a definitive correction.
- **Deferred feature** — valuable functionality outside the current remediation slice.

### Reconciliation Table

| Finding ID | Evidence (Audit 2) | Prior Status | Current Status | Root Cause | Proposed Slice | Regression Test |
| --- | --- | --- | --- | --- | --- | --- |
| **R2-01: Existing-career position rendered as Turn 1** | Dashboard, Cockpit, Timeline all show Junior Year Early January Turn 1; source is Senior Early October, 5 turns left (§1.1 line 21) | Fixed (Phases 4–5, `0025f37`, `d0bd138`, `a31000c`, `b890cd5`, `fc66978`, `8c7628d`, `3b6f3cc`) | **Confirmed fixed for snapshot mode, but not for new careers** | The cockpit reads `careerPosition` when `hasImportedPosition()` is true (`CockpitController.php:277`). New careers still derive from turn count (`:263-270`). The second audit's run was created via the wizard (new career), so it correctly starts at Turn 1. The mismatch is that the *source* describes an existing career but the UI created a new one. | Clarify in docs: the walkthrough should use the snapshot import path, not the wizard. No code change needed. **Verdict, 2026-10-10:** held. A wizard-created run has logged nothing, so Turn 1 and the year grid's first slot are what the tool knows, `TrainingRun::hasImportedPosition()` (app/Models/TrainingRun.php:707) gates the imported reading, so a run that has logged nothing claims no calendar place rather than borrowing one; the Dashboard's own empty position is `N/A` under the title "not standing anywhere on the calendar yet", which `dashboard.spec.ts:136` pins. What the walk actually hit is the R2-04 door, landed in `65d7068`: the snapshot flow was unreachable from the state the Trainer was in. | `SnapshotCockpitTest` already covers imported position; verify new-career flow still starts at Turn 1. |
| **R2-02: Legacy selections disappear during wizard navigation** | Screen 5: ancestry entered, saved with no flash; Screen 7: Preflight reports ANCESTRY N/A | Fixed (Phase 6, `31c9f5d`, `5876f64`, `f39ff03`) | **Not a code defect — session configuration issue** | Feature tests prove the persistence logic works correctly (`CareerLegacyDeckStepsTest` passes, including new R2-02 regression test verifying flash message and Preflight readback). The audit observation is consistent with a session persistence failure in the browser environment, not a code bug. Likely causes: (a) `SESSION_DRIVER=database` in `.env` but no `sessions` table migrated, or (b) browser session cookies not persisted between requests, or (c) CSRF token mismatch preventing form submission. The controller's redirect-with-flash pattern is correct; if the session cannot store data, neither the flash nor the legacy payload survives. Correction 2026-10-10 (this session, measured on this tree): hypothesis (a) is disproved here. `Schema::hasTable('sessions')` answers yes with 3 rows on `database/database.sqlite`, and `.env:30` reads `SESSION_DRIVER=database`, so the sessions table is migrated on the database the walkthrough would have used. (b) and (c) were not tested and nothing reproducible was demonstrated, so the cause of the observed browser failure stays unidentified; the row stands as "the code path is not at fault", which is all the evidence supports. | No code change needed. Document troubleshooting: verify `php artisan migrate:status` includes sessions table when using database session driver, or switch to `SESSION_DRIVER=file` for local development. Correction 2026-10-10: that check is already answered on this tree (the table is there), so the clause stands only as guidance for a fresh worktree. Nothing to implement for this finding. | New regression test added: `CareerLegacyDeckStepsTest::'it shows a confirmation flash and persists legacy data through navigation to Preflight (R2-02)'` verifies flash message, session persistence, and Preflight readback in one request cycle. |
| **R2-03: Preflight permits contract with unresolved warnings** | Screen 7: Warnings list target and ancestry gaps, but Start Career remains enabled | Known limitation (resolution record does not address blocking semantics) | **Confirmed** | Preflight is read-only and delegates creation to the wizard's write path. The wizard allows partial saves by design (D4 fixed the target validation, but did not make warnings block creation). This is a product decision, not a defect. | Document as intentional: warnings inform but do not block. If owner wants blocking, that's a new feature request. | `CareerPreflightTest` verifies warning display; add case for creation with warnings. |
| **R2-04: Snapshot state conflated with subsequent turn state** | Screen 10: Turn form defaults to Turn 1; Screen 11: Cockpit shows Junior Year Early January after recording stats | Fixed (Phases 4–5, same as R2-01) | **Same as R2-01** | The wizard creates a new career, not a snapshot import. The turn form's default is correct for a new career. The snapshot import path (if it exists) would set the initial position differently. | Verify if snapshot import path exists and is reachable from the Dashboard. If not, this is a missing feature, not a bug. **Verdict, 2026-10-10, landed `65d7068`:** the path exists (`career.snapshot*`, `SnapshotController`, `Career/Snapshot/Entry|Setup|Review`) and the Dashboard linked it, but the sentence holding that link sat inside the `<section v-else>` empty state, so the door vanished the moment the tool held a career for the very Trainer the flow is for. The paragraph now renders outside both career branches with its words, link and destination untouched; `dashboard.spec.ts` asserts the door in each state and pins its href. No position value was invented, which is the R2-01 half of the same finding. | `SnapshotTrainingDecisionTest` covers snapshot-mode turn recording. |
| **R2-05: Scenario metadata has duplicate Ruleset rows** | Screen 2: Unity Cup card prints `Ruleset · N/A` in fact block and `Ruleset · Team` in description block | Not addressed in resolution record | **Fixed** in `9777531`, browser-verified; the residual (the Cockpit's own strip) landed in `c7a9c87`, and the four further surfaces R2-19 names are closed in `24f7e48` | Confirmed, and the cause is a label collision rather than a duplicated fact. The two rows print two different values from two different sources and neither is hardcoded: the grid row is the tool-wide ruleset *version*, `props.ruleset.value`, which `ScenarioSelectController::ruleset()` returns as null by ruling because no source defines a Global version (`design-2.0` §48); the block row is the scenario's own rule family, `card.metadata.ruleset` from `config/scenarios.php` (`'Team'` for Unity Cup alone, added by `1098546`). `config/scenarios.php:225` already says the distinction in its own words, "ruleset names the scenario's rule family, not a version", but `1098546` reused the grid's heading for the new row, so one card headed two different facts `Ruleset` and read as self-contradicting. | Rename the headings so each fact owns one: grid `Ruleset version`, block `Rule family`. Values, sources and the `N/A`-with-`title` rule are untouched, and no `Team` is typed into the template. Done in `resources/js/pages/Career/ScenarioSelect.vue`; `SCREEN_SPEC.md` SCR-CAR-002 updated for the two headings, the absent-field state row and the test list. | `ScenarioMetadataTest::'it prints each fact on a scenario card under one label, and never one label twice'` pins the card's full `<dt>` heading set read off the template (RED before the fix with `'Ruleset'` twice, GREEN after, and it cannot pass on a pattern that matched nothing). `tests/browser/career-scenario-select.spec.ts` adds a rendered case: Unity Cup shows `Ruleset version · N/A` and `Rule family · Team`, and no element anywhere is headed bare `Ruleset`. |
| **R2-06: Support-deck ownership copy contradicts persisted behavior** | Screen 6: Copy says flag has no persistence column; Screen 19: Flag survives reload | Fixed (D3, `fdebcc9`, added `deck_slots.ownership`) | **Fixed** in `dd179ba`, browser-verified | Confirmed, and wider than one screen. `fdebcc9` (2026-10-09) gave `deck_slots` its nullable `ownership` column and wired both writers, but the sentence written at D6-wizard survived in three shipped places: `Career/DeckSelect.vue` (step 5) and `Career/Preflight.vue` (step 6) each printed "The deck table has no column for it yet", and `components/support/SupportSlot.vue`'s own docblock still called the value client-side. Four comments in the write path said the same (`StoreDeckRequest` twice, `TrainingRunController::syncDeck()`, `SetupDraft::deck()`, `DeckSelectController`, `StoreDraftDeckRequest` twice). `Support/Builder.vue` had been corrected in the same pass as the migration, and that is why Screen 19 read true while Screen 6 read false. | Copy and comments only, no persistence change: each screen now states the flag's real route, draft then `PreflightController::store()` then the slot row, and each keeps the three-state distinction (`Owned`, `Rented`, unrecorded renders `N/A`). | Two things, because one was lying. `SupportDeckBuilderTest` gains (a) a sweep of the four shipped deck surfaces: the retired wording in any shape fails it, and (b) the write-and-read-back case for `runs.deck.sync` with a posted flag, which no test had run before, so the stale comments were never contradicted. `tests/browser/support-deck.spec.ts` asserted the run screen's **pre-D3** sentence and has been failing since `fdebcc9` (pre-existing, not caused here); it now asserts the sentence the screen prints. `career-legacy-deck-steps.spec.ts` likewise asserted "The deck table has no column for it". |
| **R2-07: Retired run-record instructions remain visible** | Screen 9: `/training-runs/9` redirects to Cockpit, but three screens still reference "run record screen" | Partially fixed (2A.5, `d251f7f`, nav-rail relabelled) | **Fixed** in `380350e` (+ `a1379bd`), browser-verified | Confirmed, and the audit's "three screens" was the visible tip. `F2` deleted `Runs/Show.vue` and made `GET /training-runs/{run}` a redirect (`TrainingRunController::redirect()`), and `d251f7f` relabelled the nav rail, but seventeen shipped sentences across nine sources still named the retired screen in three phrasings: "the run record screen" (Cockpit strip absences, Career Result skills/races/status/retired lines, Timeline empty state, Save Veteran blocked state), "the record screens" (Save Veteran's `best_use` title), and "the run screen" (Race planner × 4, Skills planner absence, `RaceFacts` skill-point fact, Training Detail's first-turn link, Event and Inheritance no-turn panels, RaceCard's skip note). Two links also addressed the doorway itself while labelled otherwise: the deck builder's career-bar `run_url` and the Veteran's `runUrl`. A grep for the literal phrase found 9 files; a grep for the *concept* ("run screen", "career record") found the rest, which is why the audit's own wording was not enough. | Retarget each instruction to the surface that owns the action today, verified per destination: the action grid (`Cockpit.vue:588`), Training for the turn fields (`TrainingDetail.vue:121` posts `sp`), the Race planner for the calendar (`SCR-CAR-023`), the Skills planner for skill states (`skills_sync_url` form), the Cockpit for status and scenario (`Cockpit.vue:354,376`) and for recorded outcomes (`Cockpit.vue:510`). Where no 2.0 owner exists, Rest and Recreation, the copy now says so instead of sending the Trainer to a closed door. No route, redirect or payload was touched. | `NoStaleRunRecordLinksTest` already existed at `d251f7f` with two browser-side cases; it was kept verbatim and gained the sweep its design could not make: comment-stripped matchers over `app`, `resources/js`, `resources/views` and `lang`, plus a `runs.show` whitelist narrowed to the doorway's own file with its two retained redirects counted (2). Red first listed nine files; green after. `career-race-strip.spec.ts` had been failing since `3cdaa2f` (2026-10-09, the pass that repointed the strip's links) because it still addressed `Go to the run record screen` while the component printed `Go to the Cockpit` (pre-existing, not caused here); both its cases now pass, and `VeteranLibraryScreenTest`'s `runUrl` pin moved to `runs.cockpit` with the link. `CareerRacePlannerTest` gained a positive case so the retarget cannot pass by losing its destination. |
| **R2-08: Target save confirmation is misleading** | Screen 4: Empty target saves with "Build target saved." but page says "No target is recorded yet" | Fixed (D4, `be02297`) | **Confirmed fixed, but UX ambiguity remains** | The validation now allows empty targets, and the success message is technically correct (the save succeeded). However, the distinction between "save succeeded" and "target configured" is subtle. | Consider changing the flash to "Target cleared." when all fields are empty, or adding a visual indicator of the empty state. | `CareerBuildTargetTest` covers empty save; add UX assertion if changed. |
| **R2-09: Stat denominators disagree across screens** | Screen 11: Cockpit shows `Speed 607 / N/A`; Screen 15: Event decision shows `Speed 607/1300` | Not addressed in resolution record | **New confirmed defect** | The Cockpit uses the build target as denominator (which is null if no target entered). The Event decision screen uses the scenario cap. These should be consistent or clearly labelled. | Inspect `EventDecisionController` and `CockpitController` stat rendering. Use shared helper for denominator logic. | Cross-screen test verifying same stat displays same denominator. |
| **R2-10: Skills planner reports meaningful result over empty set** | Screen 18: "skills still to learn cost 0 SP" with no skills marked | Not addressed in resolution record | **Fixed** in `d95da07`, browser-verified | The arithmetic was never the defect: `SkillSpendCoverage::sum([], 173)` returns `total_cost 0, remaining 173`, which is a correct sum over an empty set and the service still refuses a sum with any price missing. The controller then passed that zero through as a figure, so the branch that prints the numbers won and the branch that names the absence never ran: the page read "The skills still to learn cost 0 SP, and the run holds 173 SP, leaving 173" for a Trainer who had prioritized nothing. The screen's own Required group already distinguished "no build target" from "target names no priorities", so the coverage panel was the one place the two collapsed. | `coverage` gained a `state` key (`no_priorities` / `covered` / `unpriced` / `cost`) derived from the priority list rather than from the sum: nothing prioritized prints no figure at all; every priority learned prints the real zero with the reason beside it; a missing price still refuses; `warn` is only ever true in `cost`. `SkillSpendCoverage` is untouched, the priority schema and `runs.build-target.update` are untouched, and ADR-0001's removal of the hint ladder is untouched. | `CareerSkillsPlannerTest`: two new cases pin `no_priorities` (total null, remaining null, no text, absence names itself) apart from `covered` (total 0, remaining 173, reason states the learned outcome), so the states cannot pass by being conflated again; the suite 21 passed (306 assertions) and `SkillSpendCoverageTest` 5 passed (56) with the service's rules unchanged. `career-skills-planner.spec.ts` gained the rendered case (5 passed, 1.1m): the screen shows the absence and no `still to learn cost 0 SP` string is in the DOM. |
| **R2-11: Hint discounts discrepancy** | Screen 18: "Hint discounts are not modelled." vs first audit showing hint ladder | Resolution record states ADR-0001 removed hint ladder | **Known limitation (intentional)** | ADR-0001 withdrew the hint discount ladder from the UI. The second audit correctly reports its absence. The first audit's observation is outdated. | No change needed. Document that hint discounts are stored but not displayed per ADR-0001. | None needed. |
| **R2-12: Race planner overwhelms with empty details** | Screen 20: Large surface with repeated N/A fields for placeholder races | Not addressed in resolution record | **Fixed** in `473bb19`, browser-verified | Confirmed as presentation, and the underlying data gap is real and stays. Ten fact rows print per race; five of them refuse for every race in the tool (running style is the trainee's, reward / Skill Points / scenario reward have no column, the win figure is held on `ADR-0016`), and on the calendar's placeholder rows the remaining five refuse too: `Junior Make Debut` carries the tier word `Debut` and nothing else, `URA Finals Qualifier` and `Semifinal` likewise, while `Aichi Hai` resolves 2,000 m / Medium / Turf. So a debut card was ten labels and ten refusals, and the audit measured 17,409 characters of it. | `RaceFacts::describesRace()` owns the test (distance, band, surface, fan gate or fan payout, with the tier deliberately excluded because `Debut` is a name and not a course), the row carries `detail_recorded`, and `RaceCard.vue` folds the grid to one line when the caller asks. The fold is opt-in through `fold-undescribed`, which only `RacePlanList.vue` passes: the planner prints a whole career and asked for density, the Race decision screen prints one or two races and its spec holds that each refusal is on the page. The drawer keeps every fact either way. No distance, surface, reward or gate was invented, no route or write touched, `ADR-0016` untouched. | `CareerRacePlannerTest` pins the fold's input on both kinds of row (`detail_recorded` true for a described slot, false for a debut-shaped one), 26 passed (403 assertions). `career-race-planner.spec.ts` adds the rendered case: the debut card shows `Details not recorded for this race.` with its obligation and year lines kept, another card still shows its `Distance band`, and the debut's drawer still lists every refused field. The first version of this folded on both screens and broke `career-race-decision.spec.ts:92` (the refusal disclosure assertion), which is the regression the opt-in exists to prevent. |
| **R2-13: Run identity incomplete** | Screen 8: Rarity and Potential Level named as missing but not editable | Fixed (2B A6.1, `b896d0b`) | **Previously fixed** | The migration added `trainee_rarity` and `potential_level` columns. The Cockpit's identity form should now include these fields. Verify the form renders them. | Check `Cockpit.vue` identity edit form for rarity/potential inputs. Add if missing. | `TraineeRarityPotentialTest` covers persistence; verify UI. |
| **R2-14: Unity Cup scenario state incomplete** | Screen 8: Team identity, rankings, burst counts have no input | Fixed (2B A6.6, A6.7, `709a233`, `89f3b11`) | **Previously fixed** | Migrations added team identity and Unity Cup counter columns. The Cockpit's scenario panel should render these. Verify the panel displays the stored values. | Check `CockpitController::scenarioSection()` and `Cockpit.vue` team panel for rendered values. | `CockpitTeamPanelTest`, `CockpitUnityCupCountersTest` cover persistence and rendering. |
| **R2-15: Active-career and Dashboard context** | Screen 1: Dashboard shows active career from first walk while allowing new draft | Known behavior (multi-run support) | **Known limitation (intentional)** | The Dashboard displays the most recent active career but also allows starting a new draft. This is by design to support multiple concurrent runs. | No change needed. Consider clarifying the Dashboard copy to explain multi-run support. | None needed. |
| **R2-16: Energy band not editable on the Cockpit correction form** | Cockpit correction writes `energy`; the guided training screen records a band beside it | Not addressed in the first audit | **Open, blocked on a concurrent session** | `turn_entries` carries the state and the band, and the guided screen writes them. The Cockpit's correction form posts the numeric reading only, so a band that is wrong stays wrong after a correction. Which behaviour is right (edit the band there, or show it read-only) is the owner's call and is recorded as such in the round-2 brief; no decision was inferred here. | Deferred: `Cockpit.vue` and `CockpitController.php` hold a concurrent session's uncommitted advisor refactor, so this file pair is not openable this session. | None yet. The chosen behaviour needs a round-trip test either way: write through the guided form, correct through the Cockpit, read back. |
| **R2-17: Run-scoped deck builder has no inbound link** | `/training-runs/{run}/deck` is reachable only by URL | Not addressed in the first audit | **Open, blocked on a concurrent session** | `runs.deck` answers, writes and reads back (Slice 2's `SupportDeckBuilderTest`), and the Training screen's door now points at it (`e2889ff`), but the Cockpit itself offers no door, so a Trainer mid-career cannot reach the deck builder by clicking. | Deferred with R2-16: the door belongs in `Cockpit.vue`, in the peer's dirty set. `TrainingDetail.vue`'s door landed first because that file was clean. | A feature test asserting the Cockpit response carries an `href` for `runs.deck`. |
| **R2-18: Training screen's deck door opened a new wizard** | Found by the R2-07 sweep of `runs.training` | Not in the second audit's list | **Fixed** in `e2889ff` | The "no Support Cards are recorded" line on the Training decision screen linked the literal `/career/setup/deck`. That route is step 5 of a fresh setup draft, so a Trainer with a career open was sent out of it into an unrelated draft and the career's own deck stayed empty; the label beside it, "Deck step", named the wizard rather than the destination. `runs.deck` was already the screen that writes this run's six slots. | The server now supplies `run.deck_url` beside the two doors already in that prop set, the anchor binds it, and the label reads "Record this run's deck". The wizard's own internal links (`SetupLayout.vue`, `LegacySelect.vue`, `DeckSelect.vue`) are unchanged, because there the next step is the next step. | `CareerTrainingDetailTest` pins `run.deck_url` at the same run's `runs.deck` and greps the component for the retired literal (present exactly once before, absent now). The screen's suite 10 passed (266 assertions) and the five suites that write through `runs.training` 46 passed (601 assertions). Not browser-verified: that spec's fixture equips a card, so the empty-deck branch never renders there. |

| **R2-19: The absent Global ruleset version is headed four different ways on six surfaces** | Found by the R2-05 residual sweep, 2026-10-10 | Not in the second audit's list | **Fixed** in `24f7e48`, browser-verified (was filed as open at the sweep, before slice 13 took it) | The same null value — `app.ruleset`, null by ruling because no source names a Global ruleset version (`design-2.0` §48) — reaches a Trainer under four headings: `Ruleset version` on the wizard card (`ScenarioSelect.vue:193`, `9777531`) and the Cockpit strip (`ScenarioPanel.vue:162`, `c7a9c87`), `Ruleset:` on the Dashboard's data-status panel (`Dashboard.vue:316`) and the Career Result's footer (`Result.vue:366`), `Ruleset snapshot:` on Preflight (`Preflight.vue:392`), and `Global Ruleset` on Preferences' game-version tab (`Preferences/Edit.vue:445`). Every one of them prints the refusal title that says *version*, so four of the six headings name the fact less exactly than the text beside them. R2-05 was one card carrying two headings for two facts; this is six surfaces carrying four headings for one fact. | Pick the card's wording, `Ruleset version`, and apply it to the four stragglers in one pass, leaving the wizard's `Rule family` row alone because that is a different fact. Values, sources and the `N/A`-with-`title` rule did not move. **Landed in `24f7e48`**, with Settings keeping its qualifier as `Global Ruleset Version`. | Two assertions pinned the old wording and moved with it: `dashboard.spec.ts` now takes the anchored version heading plus the retired `Ruleset:` as its red/green pair, and `preferences.spec.ts` matches with `exact`, because the retired label is a prefix of the new one and a substring match would pass on either wording. `CareerPreflightTest.php:132` pins Preflight's *value*, not its heading, and `CareerResultTest.php:240-241` pins the prop, so neither blocked the rename. `RulesetVersionVocabularyTest` now owns the rule: six named surfaces with the heading each must print, then a retired-wording sweep of all 105 `.vue` files under `resources/js`, the count case being the guard that keeps the sweep from reading as a pass when it scanned nothing. 2 passed (428 assertions); the two browser specs together 18 passed (7.7m) on `:8261`. |
| **R2-20: The Cockpit's page-wide 44px sweep fails on a collapsed disclosure's button** | Found while browser-verifying slice 11, 2026-10-10 | Not in the second audit's list | **Filed as KI-92, open, not this session's to fix** | `scenario-panel.spec.ts:168-174` sizes every `main a, main button` by index. The run-delete door's submit button lives inside a closed `<details>` (`Cockpit.vue:661-684`), so it is in the DOM with no box, reads 0, and the case fails at `control 17` — the 18th control, one past the 17 the failure's own accessibility listing puts inside `main` before that door. The sweep was written by `6a74f85` (2026-10-08); the door arrived with `91494a1` (2026-10-09). The markup is compliant and the instrument is not: `ura-panel.spec.ts:206-223` already skips controls that `isVisible()` rejects and guards the skip so it cannot pass on nothing. | Out of Round 2's remit. The four Cockpit sweeps sharing the unfiltered shape are named in KI-92; only `scenario-panel.spec.ts` was measured. Fixed as its own change with the `ura-panel` guard copied to each, or left to whoever owns the accessibility sweep. | Nothing to add: the case is red now, so the fix is proved by the same command going green. What has to be proved is that the guard survives — the `measured > 8` assertion is what keeps the skip honest. |

### Baseline Test Report

- **Migrations:** All 58 migrations Ran, no Pending (`php artisan migrate:status` clean).
- **Focused tests:** Not yet run. Will execute per-slice tests during implementation.
- **Browser tests:** Not yet run. Harness requires session-private port and scratch database.
- **Static analysis:** `vendor/bin/phpstan` and `npm run typecheck` not yet executed.

### Proposed Slice Order

#### Phase 1: Snapshot Integrity (Priority 0)
1. **R2-02:** Investigate legacy save disappearance (verify if regression or user error in audit)
2. **R2-03:** Clarify Preflight warning/blocking semantics (document or implement blocking)
3. **R2-01/R2-04:** Clarify snapshot vs new-career flow (update docs or add snapshot import path)

#### Phase 2: Cross-Screen Semantic Consistency (Priority 1)
4. **R2-05:** Fix duplicate Ruleset rows in scenario cards (landed 2026-10-10, see Slice log)
5. **R2-06:** Update obsolete deck ownership copy (landed 2026-10-10, `dd179ba`)
6. **R2-07:** Remove remaining stale "run record screen" references (landed 2026-10-10, `380350e`)
7. **R2-09:** Reconcile stat denominator logic across screens

#### Phase 3: Decision Quality (Priority 1)
8. **R2-10:** Fix skills planner empty-set calculation (landed 2026-10-10, `d95da07`)
9. **R2-12:** Compact race planner presentation for placeholder races (landed 2026-10-10, `473bb19`)

#### Phase 4: Remaining Context (Priority 2)
10. **R2-13:** Verify rarity/potential UI fields exist
11. **R2-14:** Verify Unity Cup state rendering in Cockpit
12. **R2-15:** Document multi-run Dashboard behavior (optional)

### Concurrent Changes Constraint

The working tree has one modified file: `docs/audits/rice-shower-unity-cup-ux-walk.md` (uncommitted). This is the first audit document, which should not be edited. The second audit (`rice-shower-unity-cup-ux-walk-2.md`) is untracked and will be committed separately.

No other sessions' work is present in the tree, so edits to controllers and Vue components are safe.

### Next Action

Begin with **R2-02** (legacy save investigation) to determine if this is a regression or an audit methodology issue. This is the highest-risk finding because it affects data persistence.

### Slice log, 2026-10-10

Erratum on the two state claims above, both written before this slice and both now stale. Line 44 said `phpstan` and `npm run typecheck` had not been executed; they have been, on this slice, and are recorded below. Line 70 said the working tree held one modified file and "no other sessions' work"; the tree now also carries `tests/Feature/CareerLegacyDeckStepsTest.php` modified (the prior session's R2-02 regression test, uncommitted) plus this slice's four files, so a commit here is made with an explicit pathspec and nothing else.

#### R2-02, carried forward, not reopened

The persistence path was already read end to end and the regression test exists and passes. The only new fact this session added is the disproof of hypothesis (a) recorded in the table row: the `sessions` table exists on the dev database with 3 rows and `.env` reads `SESSION_DRIVER=database`. No reproducible failure was demonstrated by any test, so R2-02 stays "code path not at fault, cause unidentified". The prior session's test remains uncommitted, which is a landing question for the owner rather than a defect.

#### R2-05, closed

| Item | Evidence |
| --- | --- |
| Root cause | Two different facts printed under one heading in `ScenarioSelect.vue`: the grid's tool-wide ruleset *version* (`ScenarioSelectController::ruleset()`, null by ruling, `design-2.0` §48) and, since `1098546`, the Unity Cup card's rule *family* (`metadata.ruleset` from `config/scenarios.php`, `'Team'`). `config/scenarios.php:225` already distinguishes them in prose; the template reused the heading anyway. |
| Files changed | `resources/js/pages/Career/ScenarioSelect.vue` (two `<dt>` headings, one `title` wording), `tests/Feature/ScenarioMetadataTest.php` (new heading-set case), `tests/browser/career-scenario-select.spec.ts` (updated one selector, new rendered case), `SCREEN_SPEC.md` SCR-CAR-002 (Layout/Content paragraph, absent-field row, test list). |
| RED first | `vendor/bin/pest --filter=ScenarioMetadataTest` before the fix: `Failed asserting that two arrays are identical`, index 5 `Ruleset` expected `Ruleset version`, index 8 `Ruleset` expected `Rule family`, 1 failed / 1 passed. The pattern matched all ten headings, so the guard is not vacuous. |
| Focused green | `vendor/bin/pest --filter=ScenarioMetadataTest` after the fix: 2 passed (20 assertions). |
| Related regression | `vendor/bin/pest tests/Feature/CareerScenarioSelectTest.php tests/Feature/CareerPreflightTest.php`: 22 passed (336 assertions), which covers the cards' prop derivation and the Preflight contract that reads the same config key. `CareerScenarioSelectTest` alone: 10 passed (161 assertions), the figure `SCREEN_SPEC.md` cites. |
| Browser | `PLAYWRIGHT_PORT=8146 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/cited/browser-8146.sqlite npx playwright test career-scenario-select.spec.ts` after `npm run build`: 7 passed (25.6s). The new case asserts the rendered Unity Cup card shows `Ruleset version · N/A` beside `Rule family · Team`, and that no element on the page is headed bare `Ruleset`. Card inspected in Chromium, so the fix is browser-verified, not only source-verified. |
| Static analysis | `npm run typecheck` exit 0. `vendor/bin/pint --test --format agent tests/Feature/ScenarioMetadataTest.php` passed (explicit path only, never `--dirty`, KI-75). `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`: `[OK] No errors`. `composer lore` exit 0 (303 doc hits, its standing count; the two `SCREEN_SPEC.md` hits are lines 20 and 1000, neither edited here) and `composer lore-code` exit 0 (118 hits; no hit falls on any file this slice changed, so no new ruling is owed). |
| Commit | `9777531`, four paths by explicit pathspec, verified after the fact as `git diff 9777531 -- <those paths>` returning nothing and the commit touching nothing else. The audit documents and the prior session's `CareerLegacyDeckStepsTest.php` change were deliberately left uncommitted: they are not this slice, and `docs/audits/rice-shower-unity-cup-ux-walk.md` (the first audit, modified in the tree by another hand) must not land as part of a code fix. |
| Not covered | `ScenarioPanel.vue:160` still heads the same tool-wide version row plain `Ruleset` on the Cockpit's scenario strip. There it is the only ruleset fact on the surface, so nothing contradicts it and the second audit reported no defect; left alone rather than widened into a second screen. If a future slice puts a rule family beside it, the same collision returns and the heading should follow this one. |

#### Next action

R2-06 (obsolete support-deck ownership copy) is the next slice in the queue and the next cheapest: verify the `deck_slots.ownership` write/read path, then correct the copy in `DeckSelect.vue`.

### Slice 2 log, 2026-10-10

#### Pre-flight, re-recorded

HEAD was `9777531` with exactly the four peer artifacts uncommitted (`docs/audits/rice-shower-unity-cup-ux-walk.md`, `tests/Feature/CareerLegacyDeckStepsTest.php`, the two untracked audit documents). Browser harness available and used: `npx playwright test` with `PLAYWRIGHT_PORT` and `PLAYWRIGHT_SCRATCH_DB` pairs, `npm run build` first whenever a `.vue` file changed. New in the tree during this slice and not mine: `docs/research/ui-ux-application-context-brief.md` (untracked).

#### R2-06, closed

| Item | Evidence |
| --- | --- |
| Root cause | Correction did not propagate. `fdebcc9` (2026-10-09, D3, `ADR-0023`) added the nullable `deck_slots.ownership` column, wired both writers, and rewrote the run screen's sentence in the same commit. The wizard's step 5, the Preflight contract, and five comments in the write path kept the pre-D3 wording. `git log -S"stores no field" -- resources/js/pages/Support/Builder.vue` shows the pair: added by `05e9584` (2026-10-06), removed by `fdebcc9`. |
| Persistence, verified not assumed | Column: `database/migrations/2026_10_09_120000_add_ownership_to_deck_slots_table.php`. Writers: `PreflightController::store()` line 140 and `TrainingRunController::syncDeck()` line 987. Reader: `TrainingRunController` line 269. No persistence code was touched by this slice. |
| Files changed | `resources/js/pages/Career/DeckSelect.vue`, `resources/js/pages/Career/Preflight.vue` (shipped sentences plus their HTML comments), `resources/js/components/support/SupportSlot.vue` (docblock), `app/Http/Requests/StoreDeckRequest.php` (two comments), `app/Http/Requests/Career/StoreDraftDeckRequest.php` (two docblocks), `app/Http/Controllers/Career/DeckSelectController.php`, `app/Http/Controllers/TrainingRunController.php`, `app/Services/Career/SetupDraft.php` (one docblock each), `tests/Feature/SupportDeckBuilderTest.php` (two new cases plus its own stale header citation), `tests/browser/support-deck.spec.ts`, `tests/browser/career-legacy-deck-steps.spec.ts` (the two assertions that quoted retired copy), `SCREEN_SPEC.md` SCR-CAR-007 (Persistence, Implementation References, Gap 1) and SCR-CAR-009/010 status rows. |
| New finding: a red browser test that was not in the baseline | `tests/browser/support-deck.spec.ts:196` asserted the run screen's pre-D3 sentence and therefore failed from `fdebcc9` onward, while the PHP suite stayed green because it asserts props, not copy. Classified pre-existing (introduced 2026-10-09 by the D3 copy change, not by this slice) and corrected here. |
| New finding: a phantom test citation | `SCREEN_SPEC.md:1735` and `SupportDeckBuilderTest`'s header both cite `RunDeckTest`. No such file exists in `tests/` (glob plus `git log` both empty), so the run-scoped deck write had no owner: nothing posted a flag through `runs.deck.sync` in any test. Both citations are corrected and the write is now pinned by `SupportDeckBuilderTest::'it writes the flag this screen posts onto the slot row and reads it back'`. |
| RED first | The sweep case failed on the first surface, `Expecting '<script setup lang="ts">…' not to match '/no column for it|has no owne… only/'`, 1 failed. It then failed on the missing affirmative sentence, so both halves bite. |
| Focused green | `vendor/bin/pest tests/Feature/SupportDeckBuilderTest.php tests/Feature/CareerPreflightTest.php` exit 0, 38 passed (560 assertions). |
| Related regression | `vendor/bin/pest tests/Feature/CareerLegacyDeckStepsTest.php` exit 0, 14 passed (269 assertions), unchanged by the copy edit and still green with the peer's uncommitted R2-02 case in it. |
| Browser | `npm run build` (21.67s), then `PLAYWRIGHT_PORT=8147 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/cited/browser-8147.sqlite npx playwright test career-legacy-deck-steps.spec.ts support-deck.spec.ts -g "six slots, the seven spelled types|keeps the ownership choice"`: 2 passed (44.5s). Both screens' sentences read in Chromium; the run screen's toggle interaction (aria-pressed per slot) still passes. |
| Gates | `npm run typecheck` no diagnostics. `pint --test --format agent` on the six changed PHP paths: `{"tool":"pint","result":"passed"}`. `phpstan analyse --no-progress --memory-limit=1G`: `[OK] No errors`. `composer lore` exit 0 at 303 doc hits and `composer lore-code` exit 0 at 118 code hits, both identical to the Slice 1 counts, so this slice added no hit. Note: an earlier lore-code run reported 166 because this session's own `.scratch-*.log` outputs were untracked in the repo root and the gate scans untracked files; the logs were moved to `/tmp` and the gate re-run. |
| Commit | `dd179ba`, pathspec-limited to the twelve files above. The four peer artifacts stayed uncommitted. |
| Not covered | The wizard's step 5 still refuses a save that equips a card without a flag (`StoreDraftDeckRequest` `required`), while the run screen accepts an absent one and keeps the stored value. That asymmetry is `ADR-0023`'s and is now described accurately on both screens; nothing about it was changed. |

#### Next action

R2-07 (stale run-record references) is next: sweep every user-facing "run record screen" / "run screen" instruction, trace each to the surface that owns the action today, and add the repository-grep guard the resolution record names `NoStaleRunRecordLinksTest`.

### Slice 3 log, 2026-10-10

#### R2-07, closed

| Item | Evidence |
| --- | --- |
| Root cause | A retired surface left its name behind in prose. `F2` deleted `Runs/Show.vue`, made `GET /training-runs/{run}` a redirect, and moved every write to a 2.0 owner; `d251f7f` relabelled the nav rail. Nothing swept the sentences. Seventeen of them survived in nine sources, and two links still addressed the doorway itself. |
| Files changed (19) | `app/Http/Controllers/Career/{CockpitController,ResultController,TimelineController,SaveVeteranController,SkillsPlannerController,RacePlannerController}.php`, `app/Http/Controllers/{TrainingRunController,VeteranController}.php`, `app/Services/RaceFacts.php`, `resources/js/pages/Career/{TrainingDetail,EventDecision,InheritanceEvent}.vue`, `resources/js/pages/Veterans/Show.vue`, `resources/js/components/career/RaceCard.vue`, `tests/Feature/{NoStaleRunRecordLinksTest,CareerRacePlannerTest,VeteranLibraryScreenTest}.php`, `tests/browser/career-race-strip.spec.ts`, `SCREEN_SPEC.md`. |
| Guard | `NoStaleRunRecordLinksTest` existed at `d251f7f` with two cases; both kept verbatim, two added. Its `run screen` and `career record` patterns were widened only after a concept search (not a phrase search) found the same misdirection under a second name. The `runs.show` whitelist counts the doorway's own two redirects, so the guard narrows links without touching the ruling. |
| RED first | The widened sweep named nine files; the `runs.show` link sweep named two. After the edits, 4 of 4 cases pass, and `CareerRacePlannerTest`'s new case pins a destination so a sentence cannot pass by losing one. |
| Tests | Guard 4 passed (5 assertions). `CareerRacePlannerTest` 21 passed (370 assertions) with the new case. Touched-screen batch 121 passed (1968 assertions), then 127 passed (2108 assertions) after the sweep widened. `CareerLegacyDeckStepsTest` 14 passed (269 assertions), so the prior session's held test stays green against these strings. Final combined run across R2-05, R2-06 and R2-07 suites: 61 passed (988 assertions). |
| Browser | `npm run build` exit 0, then `PLAYWRIGHT_PORT=8151 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/cited/browser-8151.sqlite npx playwright test career-race-strip.spec.ts career-cockpit.spec.ts -g "three run-scoped regions|says so when there is no turn being decided|walks the action grid|sizes the cockpit controls"`: 4 passed (2.2m). `career-race-strip.spec.ts` in full: 6 passed (1.5m). Both former stale link assertions read `Go to the Cockpit` in Chromium. |
| Gates | `npm run typecheck` exit 0. PHPStan level 6 `[OK] No errors`. Pint `{"tool":"pint","result":"passed"}` on the slice's PHP paths. `composer lore` 303 doc hits / `composer lore-code` 118 code hits, both exit 0, both unchanged from the standing counts. |
| Pre-existing failures observed, classified, not caused | (1) `career-cockpit.spec.ts:263` (advisor ranked-deficit line) fails in the tree: the concurrent `NextDecision` refactor holds `CockpitController.php`, `Cockpit.vue`, `types.ts`, `AdvisorRail.vue`, `CareerCockpitTest.php` and two untracked new files, all uncommitted, and the advisor region renders only its intro there. Not this slice; not fixed here. (2) Pint reports `method_chaining_indentation` in `tests/Feature/FinaleReportingTest.php` and `FinaleVocabularyTest.php`, files this slice never touched; a whole-directory sweep surfaces them, an explicit-path run does not, which is KI-75's exact trap. Reported, not reformatted. |
| New finding filed | `KI-91` (`e546d5b`): the Skills planner has no link into it anywhere in `app` or `resources`; `git grep -n "runs\\.skills\\.planner" -- app resources routes` returns the route declaration alone and no `skills_planner_url` prop exists. The screen works (19 + 4 cases), so this is an entry point lost in the F2 cutover, and where the door belongs is an `ADR-0020` §1 decision. R2-07's copy is accurate and points at it; the reachability half stays open. |
| Concurrency incident, recorded in full | The commit landed wrong and was corrected in the open. `380350e` was built with a pathspec commit, and a pathspec commit takes **working-tree** content: the spliced `CockpitController.php` I had staged never reached the file (the swap was rewritten by a local hook), so `380350e` captured the concurrent session's uncommitted `NextDecision` refactor and referenced a class that is untracked. A clean checkout of it fatals on every Cockpit route. Detected by diffing the committed blob against HEAD rather than trusting the working tree (`git show HEAD:... | grep -c NextDecision` → 1). `a1379bd` restored the file to HEAD's controller plus only R2-07's four strings, verified by sha256 against the spliced blob (`3514570…`), and `git grep -l "NextDecision" HEAD` now returns nothing. The other session's file was then put back byte-for-byte (`83badb71…`, matched against the pre-swap snapshot), and their work remains uncommitted and intact. **Rule for the next slice in this worktree: after any pathspec commit, diff the new commit against the previous HEAD for every file that was dirty before you started; a clean `git status` afterwards proves nothing about what went in.** |

#### Next action

R2-09 (stat denominator semantics) is next. Read `CockpitController`'s stat strip and `EventDecisionController`'s band rows before choosing semantics: a build target and a scenario cap are different facts, `ADR-0015` makes `ScenarioCaps` the single ceiling owner, and `2026_10_10_170000_drop_stat_ceilings_from_training_runs` already removed the orphaned column. Also decide, inside this slice or the next, whether `ScenarioPanel.vue:160`'s plain `Ruleset` heading follows Slice 1's split.

### Slice 4 to 6 log, 2026-10-10

Pre-flight HEAD was `e546d5b` with the dirty set the round-2 brief listed. That set grew during the session: `config/scenarios.php`, `SCREEN_SPEC.md` and seven `docs/research-scratch/` masters are now also held by the concurrent session, so `SCREEN_SPEC.md` took **no** edit in slices 4, 5 and 6; each slice's spec row is named below as held rather than left stale silently.

| Slice | Finding | Commit | What it changed | Evidence |
| --- | --- | --- | --- | --- |
| 4 | R2-10 | `d95da07` | `coverage.state` (`no_priorities` / `covered` / `unpriced` / `cost`) on the Skills planner; the empty priority list no longer prints a figure, an all-learned list prints its real zero with the reason, `warn` is confined to `cost`. `SkillSpendCoverage`, the priority schema, `runs.build-target.update` and `ADR-0001`'s withdrawn ladder untouched. | `CareerSkillsPlannerTest` 21 passed (306 assertions) with two new cases; `SkillSpendCoverageTest` 5 passed (56); `career-skills-planner.spec.ts` 5 passed (1.1m) on `:8152`, its new case asserting the absence renders and `still to learn cost 0 SP` does not exist in the DOM; typecheck 0, Pint passed, PHPStan `[OK] No errors`, lore 303 / lore-code 118 at exit 0. |
| 5 | R2-18 (new row) | `e2889ff` | The Training decision screen's empty-deck door opened `/career/setup/deck`, a fresh wizard step 5, instead of the run's own builder. `run.deck_url` now arrives from `TrainingDecisionController` and the label reads "Record this run's deck". The wizard's own internal links are unchanged. | `CareerTrainingDetailTest` 10 passed (266 assertions) pinning the prop at `runs.deck` for the same run and the retired literal absent from the component (it was present exactly once); five suites writing through `runs.training` 46 passed (601 assertions). Not browser-verified: that spec's fixture equips a card, so the empty-deck branch never renders there. |
| 6 | R2-12 | `473bb19` | `RaceFacts::describesRace()` (distance, band, surface, fan gate, payout; a tier is excluded because `Debut` names an occasion rather than a course), `detail_recorded` on both rows that print `RaceCard`, and an opt-in `fold-undescribed` that only `RacePlanList.vue` passes. Placeholder cards now read one line where the planner prints a career of them; the drawer keeps every fact, and no race detail was invented. | `CareerRacePlannerTest` 26 passed (403 assertions) pinning both sides of the fold's input; both race specs together 11 passed (5.2m) on `:8156`. **A caught regression worth the record:** the first version folded on both screens and broke `career-race-decision.spec.ts:92`, which holds that each refused field is on the page and reachable; the opt-in prop is what fixes it, and the planner-only scope is now the tested boundary. Typecheck 0, Pint passed, PHPStan `[OK] No errors`, lore counts unchanged. |

`SCREEN_SPEC.md` rows left held, one per slice, because the file is the concurrent session's working property: SCR-CAR-016's coverage sentence (now four states, not a computed total), SCR-CAR-012's door list (the deck door is run-scoped now), and SCR-CAR-023's per-race row list (a placeholder row folds to one line, its drawer keeps the ten). Each is a one-line edit against copy the spec already carries; whoever lands the advisor refactor can take them with it, or the next session can once the file is clean.

#### Blocked, and the reason is not this register

Slice 11 (the R2-05 residual on `ScenarioPanel.vue`) was opened and is **held uncommitted**: the heading now reads `Ruleset version:`, the comment says why, and two assertions were written (`ScenarioMetadataTest`'s vocabulary case, `scenario-panel.spec.ts`'s rendered case). None of it is verified, because the feature suite stopped being runnable mid-session: the concurrent session's **untracked** migration `database/migrations/2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table.php` fatals during `RefreshDatabase`'s migrate. Observed twice inside ten minutes, with the error changing between runs, which is what a half-saved file looks like: first `Method Illuminate\Database\Schema\SQLiteBuilder::dropIndex does not exist`, then at that file's line 74 `Call to undefined method Illuminate\Database\Migrations\Migration@anonymous::quote()`.

The proof that it is not this session's: `CareerRacePlannerTest` passed 26 of 26 with 403 assertions at `473bb19` and minutes later reported **20 failed (0 assertions)**, every case dying at `Migrator.php:517` before an assertion ran. `ScenarioPanelTest` and `ScenarioMetadataTest` failed the same way, 26 failed / 0 assertions. That is every feature test in the tree, for every session, and it is why slices 7 to 10 (all of them already blocked on the advisor refactor) and slice 11 cannot be finished here.

Nothing in the migration, in `config/scenarios.php`, in `SCREEN_SPEC.md` or in any file of the concurrent session was edited, staged, moved or reverted by this session. Held, uncommitted, from slice 11: `resources/js/components/scenario/ScenarioPanel.vue`, `tests/Feature/ScenarioMetadataTest.php`, `tests/browser/scenario-panel.spec.ts`.

**Superseded later the same day, and the paragraph above stays as it was written.** The pre-flight gate
`vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` now passes, 3 passed / 22 assertions, so the
untracked turn-entries migration no longer fatals `RefreshDatabase` and slice 11 was verified and landed
in `c7a9c87`. The file is still on disk and still `Pending` on the dev database (`php artisan migrate:status`
→ `[14] Ran`, then `2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table .... Pending`), which
is its author's to run; nothing here migrated the shared file. What still blocks slices 7 to 10 is not the
suite any more, it is the concurrent set itself, and it grew while slice 11 was being verified:
`app/Http/Controllers/Career/TimelineController.php`, `app/Http/Controllers/TrainingRunController.php`,
`app/Http/Requests/StoreTurnEntryRequest.php` and `app/Models/TurnEntry.php` are now dirty too, with
`app/Enums/TurnChoice.php` and `resources/js/components/career/RecordedActionCard.vue` untracked beside them.
That set is FD-4's turn-choice and correction work, and it lands **on top of** R2-16's target: the energy band
this slice would add to the Cockpit correction form is a field of the same correction path those files write.
Opening `StoreTurnEntryRequest` or `TurnEntry` now would be a second writer on one in-flight contract, so R2-16
is blocked by two sessions' work rather than one, and the brief's own instruction applies verbatim: wait for
the commit, or pick a different slice.

**Checked the claim above against HEAD, and it is half wrong.** The band fields are already committed, so R2-16
needs **no** write-path change: `StoreTurnEntryRequest.php:128-132` at HEAD validates `energy_state` as the enum
and `energy_band` as required-if-band, `TurnEntry` carries both in `#[Fillable]` and casts the state, and the
guided screen already renders the control (`TrainingDetail.vue:425-455`, clean at this writing). What is missing
is only the Cockpit's own correction form, which posts the numeric reading and nothing else, so the slice is
`Cockpit.vue` plus its controller's read-back and those tests, four files, all four in the peer's dirty set.
The peer's current work on the same path is the second reason to stay out: the columns they are adding now
(`choice`, `decision_snapshot`, `corrected_from`, `superseded_by`) reshape what a correction *is*, and a band
control bolted onto a form that is about to record a choice would be rebuilt by them within the hour. R2-16
therefore stays blocked, but it is a smaller slice than this register said, and its remaining risk is the form's
shape, not the schema.

#### Next action

Restore the suite before any further slice: the turn-entries migration needs its index drop and its quoting done the way the three migrations beside it already do it (`grep -l dropIndex database/migrations` names them), and that is its author's edit, not a sweep to be done from outside. Then, in order: verify and land slice 11; take slices 7 to 10 once `Cockpit.vue`, `CockpitController.php`, `CockpitObjectivesTest.php` and `NextDecision*` are committed and the tree is quiet; and settle R2-16's product question (edit the band on the Cockpit, or show it read-only) before code, since both answers are defensible and only one of them is a feature.


#### Not covered by R2-07, deliberately

Four docblock sentences in `CockpitController.php` still describe the retired
screen as a present surface ("The full calendar stays on the run record screen", "built for the run record
screen where the whole calendar is the question"). They are internal comments, the guard's own rule treats
them as the allowed historical class, and that file is the concurrent `NextDecision` slice's working
property: touching it again to edit prose would splice across someone else's in-flight refactor for no
user-visible gain. Same for the `CareerSurfaceCutoverTest` and `career-surface-cutover.spec.ts` headers,
which still state that the record screen "is still the only place a Trainer can reach" four writes; `F2`
withdrew that and the comments did not follow. Recorded here as the next author's cleanup, listed by file
so it can be done in one pass with their landing.

### Slice 11 and 12 log, 2026-10-10 (second sitting)

Pre-flight, run as the brief ordered and as the only command before any slice opened:
`vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` -> **3 passed (22 assertions), exit 0**. FD-4 is off the
migration path for `RefreshDatabase`, so slices opened. HEAD at pre-flight was `473bb19`; the dirty set was the
one the brief listed, and it grew (the FD-4 files named above).

| Slice | Finding | Commit | What it changed | Evidence |
| --- | --- | --- | --- | --- |
| 11 | R2-05 residual | `c7a9c87` | The Cockpit scenario strip headed its one ruleset fact `Ruleset:`. Now `Ruleset version:`, the word the wizard card took in `9777531`, with the comment saying why the strip carries the version and not the rule family. No value, no absence, no `N/A`-with-`title` rule moved. | `ScenarioMetadataTest` 3 passed (22 assertions) including the new heading comparison; `scenario-panel.spec.ts` 7 passed / 1 failed on `:8255` with its own scratch database, the failure being KI-92's sweep and not this change. RED proved without touching the tree: `git show HEAD:resources/js/components/scenario/ScenarioPanel.vue` line 160 reads `Ruleset:`, which is what both the feature matcher (`/^\s*Ruleset:$/m`) and the rendered case (`toHaveCount(0)`) were written against. Typecheck 0, Pint passed on the changed test file, `composer lore` exit 0 and `composer lore-code` exit 0 at the standing 118 hits, with **zero** hits in any changed file on both gates. |
| 12 | R2-01 / R2-04 | `65d7068` | The Dashboard's snapshot door existed, but only inside `<section v-else>`, the no-career state. The sentence moved outside both career branches; its words, its `href="/career/snapshot"` and its destination are untouched. R2-01 needed no code: the wizard's Turn 1 is what a run with no logged turn knows. | `dashboard.spec.ts` **6 passed (2.8m)** on `:8257`, now asserting the door in the empty state *and* the active-career state, the second also pinning the href so the case cannot pass on a link that goes elsewhere. Typecheck 0, `npm run build` 0, `lore-code` 118 at exit 0 with zero hits in either file. Not inspected by screenshot: the evidence is Playwright's rendered role and href assertions against the built bundle, which is what "browser-verified" claims here and nothing more. |

Two things the brief did not ask for and this session did not do: it did not touch `StoreTurnEntryRequest`,
`TurnEntry`, the FD-4 migration or the `runs.show` redirect, and it did not run the full feature suite or
`php artisan test --compact`. Both slices are copy and layout, which the change-class table places at the
affected-feature-test row, and the tree carries two sessions' in-flight work that a broad run would attribute
to this one. **`php artisan migrate:status` was run and is not clean**: `2026_10_10_200000_add_choice_and_corrections_to_turn_entries_table`
is `Pending` on `database/database.sqlite`, the concurrent author's file, not run here because migrating the shared
dev database is theirs to decide.

**PHPStan is red for a reason outside this work.** `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`
-> exit 1, one error: `app/Services/Advisor/NextDecision.php:65`, `missingType.iterableValue` on
`snapshotFor()`'s array return type. That file is untracked (`?? app/Services/Advisor/NextDecision.php`), it is
FD-2's, and `phpstan.neon` analyses `app` only, so neither of this session's two PHP-side edits (both under
`tests/`) was even in scope. Not edited, not silenced, not counted as passing.

**Re-read later in the same sitting, PHPStan is exit 0, and the reason is the peer's.** The offending file left
the tree: `app/Services/Advisor/NextDecision.php` is neither tracked (`git ls-files app/Services/Advisor/` answers
`Advice`, `AdvisorOption`, `TrainerAdvisor` only) nor listed as untracked, so FD-2's untracked class was withdrawn
mid-session and its `missingType.iterableValue` went with it. `Cockpit.vue`, `CockpitController.php`, `types.ts`,
`AdvisorRail.vue`, `CareerCockpitTest.php` are all still dirty, so the withdrawal did not unblock slices 7 to 10.
Both readings are true of the moment each was taken, which is why they are recorded as two rather than the later
number replacing the earlier one.

**New findings, filed and not fixed here.** R2-19: one absent fact, four headings across six surfaces, with the
two test assertions that pin the old wording named in the row. R2-20 / **KI-92**: three Cockpit 44px sweeps
measure DOM presence rather than rendered targets, red since `91494a1` put a `<details>`-held confirm button on
the page `6a74f85`'s sweep reads, with the repo's own remedy already sitting in `ura-panel.spec.ts:206-223`.
`docs/audits/rice-shower-unity-cup-ux-walk-resolutions.md` and `SCREEN_SPEC.md` took no edit from these two
slices, and neither did `rice-shower-unity-cup-ux-walk.md`, which was already dirty in the tree before this
sitting opened; SCR-001's door row and the strip's heading row are left as the next author's one-line changes,
stated here instead of silently stale.

#### Slice 13 log, same sitting: R2-19 closed in `24f7e48`

Slice 11 renamed one surface and stopped, which is the wrong shape for a vocabulary fix: a correction that
leaves four siblings printing the retired word is a half-correction, and the retired literal survives its own
fix. So the sweep continued and found four more headings for the same absent value.

Seven files, one line each on the four components, and three test edits:

| File | Change |
| --- | --- |
| `resources/js/pages/Dashboard.vue` | `Ruleset:` -> `Ruleset version:` on the data-status panel's row |
| `resources/js/pages/Career/Result.vue` | `Ruleset:` -> `Ruleset version:` on the result footer's row |
| `resources/js/pages/Career/Preflight.vue` | `Ruleset snapshot:` -> `Ruleset version:` on the contract read-back |
| `resources/js/pages/Preferences/Edit.vue` | `Global Ruleset` -> `Global Ruleset Version`, keeping the tab's own qualifier |
| `tests/browser/dashboard.spec.ts` | the anchored new heading, plus the retired `^\s*Ruleset:` as `toHaveCount(0)` |
| `tests/browser/preferences.spec.ts` | `exact` on the new label, with the retired label asserted absent: the old wording is a prefix of the new one, so a substring match would pass on either |
| `tests/Feature/RulesetVersionVocabularyTest.php` | new. Six named surfaces with the heading each must print, then the retired-wording sweep over every `.vue` under `resources/js` |

Gates for this slice: `RulesetVersionVocabularyTest` 2 passed (428 assertions, 105 components swept);
`dashboard.spec.ts` + `preferences.spec.ts` 18 passed (7.7m) on `:8261` with its own scratch database;
typecheck 0; `npm run build` 0 (3m 55s, against 1m 29s earlier in the same session, which is the host and not
the change); Pint passed on the new file; `composer lore` 313 and `composer lore-code` 118, both exit 0 and both
at their standing counts.

**RED is asserted from pre-edit file contents, not from a red run.** The four old strings were read this session
before each edit (`Dashboard.vue:316`, `Result.vue:366`, `Preflight.vue:392`, `Preferences/Edit.vue:445`), and the
first run of the new guard did fail — on its own count guard, `Failed asserting that 0 is greater than 50`,
because `File::files()` is not recursive and the sweep had read nothing. That is the instrument proving it can
see, which is the property that matters most in a guard of this shape; it is not proof the four headings were
wrong, and this register does not claim it is. Reverting a file to prove red was not attempted: the session's
task guard holds the working tree, and the last time a revert was reached for, it was blocked as undoing
validated work.

**Two harness runs failed before this one passed, and the cause is boot, not the app.** `:8258` and `:8259` each
ended in `Error: Timed out waiting 60000ms from config.webServer` with a three-line log: no global-setup output
at all, which places the failure before the scratch database was built. `php -l config/scenarios.php` clean,
`php artisan about` exit 0, and a manual `php artisan serve --port=8260` answered `/up` in 0.15-0.5ms while
Playwright's own server never bound. Classified as infrastructure under host load, reproduced as green on the
third port rather than explained away; `migrate:status` was unaffected.

#### Next action, in order

1. Slices 7 to 10 wait on the peer's commit of `Cockpit.vue`, `CockpitController.php`, `types.ts`, `AdvisorRail.vue`
   and their four test files. R2-16 is smaller than this register said (band fields committed; only the form and
   its read-back are missing) and R2-17 is one anchor in the same file.
2. R2-19 was openable by anyone and is now closed as slice 13, `24f7e48`. What it leaves is one
   `SCREEN_SPEC.md:1287` row that still calls the Settings row `Global Ruleset`; the file is a concurrent
   session's working property, so the row waits with the two named in item 4.
3. KI-92 wants a decision before a patch, because the two remedies are not equal: open each disclosure and keep
   the floor on its confirm button, or skip unplaced controls the way `ura-panel` does. Either way the guard
   travels with it.
4. Whoever lands FD-2 and FD-4 should run `migrate:status` on the dev file and the two held `SCREEN_SPEC.md` rows
   named above; both are one line each and both are already written down here.

---

## Gemini Deep Research reconciliation, 2026-10-12

**Why this section exists.** On 2026-10-12 the owner supplied a Gemini Deep Research report on `[Global]`
Umamusume mechanics together with a peer check of that report, and asked for a reconciliation against this
repository. The report is a hypothesis corpus, not an authority: it was decomposed into atomic claims and each
was checked against the repository's own evidence (client captures, the GameTora export, `UMAMUSUME_REFERENCE.md`,
the four scenario guides, `config/scenarios.php`, the support-card and skill datasets, `app/Enums/MoodTier.php`,
and the app's tests) and, where external evidence added value, against independent web sources. This section is
the ledger. It changes no application code, test, schema, seeder or component.

**Headline verdict.** The repository already carries, correctly labelled and sourced, essentially every material
claim in the report; most of the report restates what is already on file, at lower precision. Four claims are
wrong or over-stated, one is version-dependent and stays open, and the reconciliation surfaced one internal
contradiction in `UMAMUSUME_REFERENCE.md`, corrected forward in place (finding F-1). Nothing in the report is a
new game fact this repository lacked.

### Method and instruments

* **Repository evidence (Tier A/B).** `docs/UMAMUSUME_REFERENCE.md` and its provenance master
  `REFERENCE-GUIDE-BUILD-CHAIN.md`; the scenario guides `docs/scenarios/01`, `02`, `03`, `07`; `config/scenarios.php`;
  the committed GameTora datasets (`scenarios.json`, `support_effects.json`, `support-cards.json`, `skills.json`);
  `lang/en/uma.php`; `app/Enums/MoodTier.php`; the screenshot registry and its packet notes; `docs/adr/0014`;
  `docs/research-scratch/SUPPORT-CARDS.md`.
* **Independent web (Tier C), run 2026-10-12, by search only (snippets, not page reads).** TheGamer's mood guide,
  2025-07-22: <https://www.thegamer.com/umamusume-pretty-derby-all-moods-mood-effects-increase-mood-recreation-guide-how-to/>.
  GameTora's Race Mechanics Handbook: <https://gametora.com/umamusume/race-mechanics>. umamusu.wiki `Game:Mechanics`:
  <https://umamusu.wiki/Game:Mechanics>. Recorded per claim below. The peer check's own web findings were treated
  as leads, not as evidence.
* **Not used, and stated so.** No Global client was opened in this pass, no controlled experiment was run, and no
  official page was re-fetched. Every claim whose strongest support is a client capture cites the capture already
  on file here, not a new observation. Two `WebSearch` calls were made and their snippets are quoted as such, not
  as page reads.

### A. Race simulation and core formulas

| ID | Claim as the report states it | Status | Repository evidence | Open question |
| -- | ----------------------------- | ------ | ------------------- | ------------- |
| A-1 | Max HP = 0.8 × style coefficient × Stamina + distance (m); Front Runner 0.95, Pace Chaser 0.89, Late Surger 1.0, End Closer 0.995, Runaway 0.86 | Verified | §1.3.3 (2026-10-05 community read) | Coefficient set is one community page; not client-decoded |
| A-2 | "longer races exponentially increase the demand for raw Stamina" | **Rejected** | §1.3.3: the distance term is linear (1 HP per metre) and the formula's own shape contradicts the word | — |
| A-3 | Uphill target-speed loss = SlopePer × 200 / Power | Verified as published | §1.2.4 quotes the same relation | Unit of `SlopePer` stays ❌ UNVERIFIED (§1.2.4) |
| A-4 | Four phases over 24 sections, boundaries 16.67 / 66.67 / 83.33 % | Verified | §1.2.3, export-derived (138 courses) | — |
| A-5 | Position keep runs section 1 to 10 (41.7 %) | Verified | §1.2.3: `positionKeepEnd` 41.64–41.70 % in all 138 courses | — |
| A-6 | Bands Sprint ≤1200, Mile 1300–1800, Medium 1900–2400, Long 2500–3600 m | Verified, with the 1400 m caveat | §1.2.1, §1.3.3, conflict row 7 | 1400 m is Sprint to one guide and Mile to the export |
| A-7 | Standard-distance skills need a course that is a multiple of 400 m | Verified | §1.2.1, client flag `is_basis_distance` | — |
| A-8 | Footing: Heavy −50 Speed; stamina drain 1.00/1.00/1.02/1.02 turf, 1.01/1.02 dirt | Verified at community grade | §1.2.5, with a recorded dissent the same day | No official page states the drain multiplier |
| A-9 | Rain forces Soft or Heavy footing; snow only in Winter | Verified | §1.2.5 (JP guide + event data) | — |
| A-10 | Full Spurt above 2000 Speed; Endurance Duel above 1200 Stamina; Saving Her Legs above 1200 Power; rare skills strengthened above 1200 Wit — all presented as current Global | **Version-dependent, one half unresolved** | Power-1200 is Global-live: "Fully Charged", a race mechanic added in the 2026-07-01 rework (§2.4 conflict 4, Game8 2026-07-02). The Speed-2000 and Stamina-1200 gates are sourced `[JP]` (§1.3.1, Game8 JP) and to umamusu.wiki, which the registry tags `[Both]` | No primary Global source here confirms the Speed-2000 and Stamina-1200 gates on Global as of 2026-10-12; do not present them as Global |
| A-11 | Runaway is not a fifth style; coefficient 0.86 | Verified | §1.2.2 (gated behind `running_style==1`), §1.3.3 | Two wikis list "Runaway" beside the four; conflict row 9 |

### B. Career training and resource economy

| ID | Claim as the report states it | Status | Repository evidence | Open question |
| -- | ----------------------------- | ------ | ------------------- | ------------- |
| B-1 | Secondary-stat routing (Speed→Power, Stamina→Guts, Power→Stamina, Guts→Speed+Power, Wit→Speed); +2 SP per session, Wit +4 to +5 | Verified | §1.1.1 (two sources agree on the routing) | — |
| B-2 | Facility level +1 per four uses; Summer Camp sets all five to Lv5 | Verified | §1.1.2 | — |
| B-3 | Compound session gain formula, presented as executable | **Provisional** | §1.1.2 reports it as Game8's published shape | Stacking order and rounding are asserted by neither the report nor any source here; do not implement as an equation |
| B-4 | Growth Rate is a per-trainee percentage row | Verified (client) | §1.3.5, `stat_bonus` matched position-for-position to the client's Growth Rate row | — |
| B-5 | Mood: GREAT/GOOD/NORMAL/BAD/AWFUL = +20/+10/0/−10/−20 training, +4/+2/0/−2/−4 race | Verified (client) | §1.1.6, from the Mood Effect panel capture `2026-07-18 001957`; `app/Enums/MoodTier.php` | Report's AWFUL −25 % is rejected (see P-1) |
| B-6 | Energy cap 100, expandable +12; Wit costs no Energy | Verified | §1.1.6 | — |
| B-7 | Training failure scales inversely with Energy; three penalty types | Verified directionally; formula **Unresolved** | §1.1.5 | No tier-A page publishes a failure table by Energy; the 50 % line is advice, not a threshold (§2.6) |
| B-8 | Hint discount ladder 10 / 20 / 30 / 35 / 40 % | Verified (client) | §1.1.4, from the Learn screen capture 2026-10-03 | — |
| B-9 | Friendship bonuses multiply (1.25 × 1.30 = 1.625) | Verified | §1.1.3, §1.4.3 | — |
| B-10 | Bond gauge turns orange at 80 and gates friendship training | Verified | §1.1.3 | — |
| B-11 | Independent Training: 50 min, runs with the app closed, ~1.3 M fans, 20 runs/day, ignores growth modifiers, no trophies or gallery | Verified | §1.1.7, §1.6.8, Global notice 100087 | — |

### C. Support cards and skills

| ID | Claim as the report states it | Status | Repository evidence | Open question |
| -- | ----------------------------- | ------ | ------------------- | ------------- |
| C-1 | Seven types incl. Pal and Group; six slots, one rental, no duplicate card | Verified | §1.4.1, §1.4.5 | — |
| C-2 | Pal carries no Friendship Bonus; Group is SSR-only | Verified | §1.4.1 | — |
| C-3 | Level caps R20 / SR25 / SSR30, +5 per break → 40 / 45 / 50 | Verified (client + export) | §1.4.2 | — |
| C-4 | Effect values interpolate linearly between anchors 1,5,10,…,50, floored | Verified (client) | §1.4.7, three selector positions checked against the export row | — |
| C-5 | Support-effect ids (1, 2, 3–7, 8, 9–13, 14, 15, 18, 19, 27, 28) with their `calc` modes | Verified | §1.4.8 | — |
| C-6 | "Max Stat" effects (ids 20–24) are carried by zero shipped cards | Verified — and it contradicts the guide's own §1.3.4/§1.3.5 | §1.4.8 ("carried by zero of the 559 cards"); `ADR-0014`; `SUPPORT-CARDS.md` "Carried by zero" | See finding F-1 |
| C-7 | Scenario Link is a join of the card's character id to the scenario's link list, not a stored card flag | Verified | §1.4.7 | — |
| C-8 | White sparks add +1 to +5 hint levels | Verified | §1.4.4 | — |

### D. Inheritance and Legacy Lab

| ID | Claim as the report states it | Status | Repository evidence | Open question |
| -- | ----------------------------- | ------ | ------------------- | ------------- |
| D-1 | Blue Spark ★1 +5, ★2 +12, ★3 +21 | Verified | §1.5.2 | — |
| D-2 | Blue-Spark odds: <600 ≈90/10/0 %; 600–1100 ≈50/45/6 %; >1100 ≈20/70/10 % | Verified as odds, not an award schedule | §1.5.3 | — |
| D-3 | Aptitude Sparks: 1★ +1 grade, 4★ +2, 7★ +3, 10–18★ +4; pre-run ceiling A, ≤+4 grades, F cannot reach A; A→S only mid-run | Verified | §1.5.2, §1.5.3 | — |
| D-4 | Three inheritance checkpoints: career start, Classic April, Senior April | Verified | §1.5.1 | — |
| D-5 | Six ancestor nodes (2 parents + 4 grandparents); a repeated ancestor zeroes its link's compatibility | Verified | §1.5.4 | — |
| D-6 | Affinity △/○/◎ raises trigger chance and payout; shared top-grade wins contribute | Verified | §1.5.4 | — |
| D-7 | Green unique Spark from a ★3+ ancestor, 1–3 hint levels | Verified | §1.5.2 | — |
| D-8 | Scenario Sparks ≈10–30 per stat | Verified | §1.5.2 | — |
| D-9 | "Finish the stat above 1100 to optimize the ★3 roll" | Verified as heuristic only | §1.5.3 states a planner mapping stat→★ deterministically is wrong | — |

### E. Scenario-specific mechanics

| ID | Claim as the report states it | Status | Repository evidence | Open question |
| -- | ----------------------------- | ------ | ------------------- | ------------- |
| E-1 | URA: repetition facility; Happy Meek duels (win = stats + a cap tier + a spirit hint + 30 SP, loss = minor stats + 15 SP); six wins escalate; beating the powered-up state pays `Past My Limits`, ≥200 SP, an enhanced-spark chance | Verified | §2.2.1, `config/scenarios.php` `duel` | Duel win chance and spark rate stay null in config |
| E-2 | URA fan gates 60k / 70k (plus a 3-bar Director bond) / 120k, dirt 40k / 60k / 80k | Verified | §2.2.1, `config/scenarios.php` `fan_gates` | — |
| E-3 | Unity Cup: facility level from team rank; Extreme Spirit Burst = multi-stat + `Ignited Spirit` hint + 0 % failure on that facility; Alarm Clock retries a lost team race | Verified | §2.2.2, `config/scenarios.php` | — |
| E-4 | Unity Cup Wit cap 1800 against 1300 elsewhere | Verified | §2.2.2, `config/scenarios.php` | — |
| E-5 | Unity Cup exclusive-skill bands by burst count | Report gives no bands; repository carries them | `config/scenarios.php` `spirit_burst_bands` (4–6 / 7–9 / 10–12 / 13+), from `02-unity-cup.md` | Whether Extreme Bursts count toward the threshold is a live publisher disagreement the config records and does not settle |
| E-6 | Unity Cup facility level from the team-rank ladder | Verified | `config/scenarios.php` `team_rank_ladder` (G/F→1 … S→5) | — |
| E-7 | Trackblazer: Debut in Late June Junior; 60 / 300 / 300 Grade Points, dirt 30 / 200 / 300; surplus does not carry | Verified | §2.2.3, `config/scenarios.php` `grade_objectives` | — |
| E-8 | Trackblazer: 100 Shop Coins for a first place | Verified | `config/scenarios.php` `shop_coins_by_placement` | — |
| E-9 | Trackblazer Pro Shop consumables | Verified | `config/scenarios.php` `shop_items` (from the publisher master) | One guide's "Worth" ratings travel as `estimated` |
| E-10 | Trackblazer rivals grant a distance- or style-tied hint | Verified | §2.2.3 | — |
| E-11 | Trackblazer: a missed deadline is a career fail retried with an Alarm Clock | Verified | §2.2.3 | Which turn a retry resumes on stays ❌ UNVERIFIED |
| E-12 | Grand Concert: five Performance types (Dance, Passion, Vocals, Visuals, Composure), cap 200 +50 per successful live | Verified | `docs/scenarios/07`, Global notice 905 | Per-type start values are not published |
| E-13 | Grand Concert: friendship training pays two types | Verified | `docs/scenarios/07` | — |
| E-14 | Grand Concert: Lesson menu opens on turn 5; Live Techniques and Songs; two bonus layers | Verified | `docs/scenarios/07` | Screen text unmeasured (0 frames) |
| E-15 | Grand Concert: four Promo Lives every six months from late Dec Junior, then the Grand Concert | Verified | `docs/scenarios/07`, notice 905 | — |
| E-16 | Grand Concert: a Great Success raises caps; each live pays 5 SP per Technique Lesson and 25 per Song Lesson since the previous live | Verified | `docs/scenarios/07` | — |
| E-17 | Grand Concert: 16 Songs by early Nov Senior fires the scenario-link event; 18 by early Dec Senior pays `I Wanna Win with You` | Verified | `docs/scenarios/07` | — |
| E-18 | Grand Concert: five scenario-linked characters | Verified | `docs/scenarios/07`, `config/scenarios.php` | — |

### F. Other Global systems

| ID | Claim as the report states it | Status | Repository evidence | Open question |
| -- | ----------------------------- | ------ | ------------------- | ------------- |
| F-1 | Grade codes 100/200/300/400/700 = G1/G2/G3/OP/Pre-OP, with fan gates and yields | Verified | §1.2.1, §1.2.6; the report's figures match the export row for row | Code 700's label map stays single-publisher |
| F-2 | Open League caps at Career Rank A+ and blocks S+; Graded League has no cap | Verified (official) | §1.2.6, §1.6.2, Global notice 1050 | — |
| F-3 | Team Trials: 1 RP per entry, 5 RP cap, 1 RP per 2 h; three units per category across five categories | Verified | §1.6.4 | — |
| F-4 | Champions Meeting: three rounds with Group A/B and a final; per-edition course, weather and footing; disabled skill categories | Verified | §1.6.2 | — |
| F-5 | TP: 100 cap, 1 per 10 min, 30 per Career | Verified | §1.6.10 (Game8 TP/RP guide) | The expansion of "TP" is not a client string |
| F-6 | Carats 150 single / 1500 ten-pull | **Provisional** | §5 (marked expired, not re-fetched) | Not re-verified this pass |
| F-7 | One duplicate SSR card converts to 10 Rainbow Cleats, spent in Cleat Exchange | Verified | §1.6.10, §6 | — |
| F-8 | Goddess Statue is currency only, ×1→×5, 650 Star Pieces to max a trainee | Verified | §1.6.10, §6 | — |
| F-9 | Scout rates 3.00 / 18.00 / 79.00 %, featured 0.75 %, 200-point pity | **Unresolved** | §5 (expired section, not re-fetched) | No Global notice read here; do not treat as current |
| F-10 | Exchange Points convert to Clovers 1:1 at banner expiry | **Unresolved** | §5 (expired) | Same |
| F-11 | Terminology map (Scouts, Carats, Sparks, Pal, Veteran/Trainee, Team Trials, Independent Training, Trackblazer, Grand Concert, the four strategies, Wit) | Verified | §6 agrees with the report row for row | — |
| F-12 | "Team Stadium" is not a client term | Verified | §1.6.4 | — |

### Peer-check discrepancies, reopened independently

Each item below was re-investigated from the repository and, where useful, the web, not accepted on the peer
check's word.

| # | Peer-check item | What was found | Resolution |
| - | --------------- | -------------- | ---------- |
| P-1 | AWFUL motivation −25 % (report) vs −20 % (project) | The project value is a **Tier-A client capture**: the Mood Effect panel frame `2026-07-18 001957` prints "Lowers training results by 20 %" for AWFUL and −4 % while running (`docs/research-scratch/screenshot-notes/06-other-screenshots.md:91`; §1.1.6; DESIGN-CORPUS §4.3). Independent web agrees: TheGamer (2025-07-22) prints "Awful. Reduces Stat Gain from Training by 20 percent and reduces Stats during a Race by 4 percent." | Report **Rejected**. The Global value is −20 % / −4 %. Not version-specific: both the client and the independent guide print 20. |
| P-2 | High-stat gates: Full Spurt >2000 Speed, Endurance Duel >1200 Stamina, Saving Her Legs >1200 Power, >1200 Wit | The repository already separates these: the Power-1200 gate is **Global-live** as "Fully Charged", a race mechanic added in the 2026-07-01 rework (§2.4 conflict 4, Game8 page dated 2026-07-02). The Speed-2000 and Stamina-1200 gates are sourced `[JP]` (§1.3.1). umamusu.wiki `Game:Mechanics` documents the Stamina-1200 limit break and the registry tags that wiki `[Both]`. | **Version-dependent / partly unresolved.** The report's presentation of all four as current Global is wrong for the two it does not caveat; the repository's split (Power live, Speed/Stamina `[JP]`) is the accurate shape. Not closed: no primary Global source in the repo confirms the Speed-2000 and Stamina-1200 gates on Global today. |
| P-3 | Scenario caps and rebalance dates | `config/scenarios.php` holds `cap_bonus` over a 1200 base, sourced from GameTora `scenarios.json` verified 2026-09-27: URA 1400 all; Unity Cup 1300×4 / 1800 Wit; Trackblazer 1200/1900/1200/1200/1500; Grand Concert 1600/1300/1300/1500/1300. §2.5 dates the URA/Unity Cup raise to the 2026-07-01 Global rework. | **Verified**, and the report's table matches it exactly. Each scenario was checked on its own row; the rework date is documented for URA/Unity Cup, and Trackblazer's raised caps stay single-source-per-figure (§2.4 conflict 1). |
| P-4 | Compound training formula | §1.1.2 reports the formula as Game8's published shape; neither the guide nor the report states a stacking order or a rounding rule. | **Provisional.** Do not implement as executable logic. |
| P-5 | Distance term "exponential" | §1.3.3 gives the term as linear (1 HP per metre) and says no metres-per-Stamina conversion is published. | Report **Rejected** on the word; the underlying formula is unchanged. |
| P-6 | Friendship stacking is multiplicative | §1.1.3 and §1.4.3 both print `(1 + a/100) × (1 + b/100)` with the 1.625 worked example, from a JP guide tagged `[Both]`. | **Verified.** The condition is two cards both past the 80 bond on the shared tile. |
| P-7 | Training failure / Energy relationship | §1.1.5 gives direction only (risk rises as Energy falls) and §2.6 records that no tier-A page publishes a probability table; the 50 % line is advice, not a threshold. | **Unresolved** for any formula. The report's "non-linear below 50 %" is not supported and is not adopted. |
| P-8 | Inheritance probabilities vs heuristics | §1.5.3 prints the odds grid **and** states a planner mapping a stat value to a ★ count deterministically is wrong; §1.5.5 separates the grandparent-first loop as `[JP]` strategy. | **Verified as odds, heuristic kept separate.** The report's probability grid matches §1.5.3. |
| P-9 | Unity Cup rewards | Bands, payout timing and the facility ladder are in `config/scenarios.php` from `02-unity-cup.md`; the Extreme-Burst-counting question is a recorded publisher disagreement. | **Verified** where the repository carries a figure; the one open half stays open. The report gives no band figures to compare. |
| P-10 | Grand Concert thresholds | Concert timing, cap rise, SP awards, song counts (16/18) and the rare-skill unlock are all in `docs/scenarios/07`, sourced to Game8 + GameTora + Global notice 899/905. | **Verified**; the report agrees with the file on every figure. |
| P-11 | Independent Training | §1.1.7 and §1.6.8 give the launch (2026-07-22, notice 100087), the ~50-minute runtime, the 20/day cap, the ignored growth modifiers and the achievement limits. | **Verified**; the report's claims match. |
| P-12 | Terminology and provenance | §6 maps `[JP]` against `[Global]` client wording and flags the non-equivalences (Champions Meeting edition names; Open League ceilings). | **Verified.** The report's table agrees; where it says "identical", the repository says the same. |

**One peer-check statement corrected openly.** The peer check describes the uphill relation as "slope percentage ×
200 / Power". The repository's source gives the relation as `SlopePer ∗ 200 / PowerStat` and marks the **unit of
`SlopePer` as ❌ UNVERIFIED** (§1.2.4): no page exposes the field or its scale, and the export's ±10000/±15000/±20000
band values have no published divisor. "Slope percentage" is therefore an assumption the peer check supplies, not a
documented unit; the relation is safe to use because it divides by a stat, but no slope figure may be presented as a
percentage or as metres.

### Findings this pass adds

* **F-1 (documentation defect, fixed forward).** `UMAMUSUME_REFERENCE.md` contradicts itself about the per-stat
  cap-setting support effects (`限界値アップ`, ids 20 to 24). §1.3.4 lists "Support-card cap effects" as a live
  deck layer and §1.3.5 tells a planner to model "cap-setting effects" among the five layers a run starts with,
  while §1.4.8 measures those five effects as carried by **zero of the 559 cards** and says a UI offering a
  "cap raised by support cards" line would display a feature the catalogue does not contain. `ADR-0014` and
  `SUPPORT-CARDS.md` "Carried by zero" side with §1.4.8. Corrected by a dated erratum at §1.3.5, which the
  guide's dated-record rule permits; the original sentences are preserved. The report's own statement ("carried by
  zero shipped support cards") agrees with the corrected reading.
* **F-2 (corroboration of an existing split).** The peer check's distinction — a Power-related mechanic already
  introduced, stamina and speed systems later — is already drawn in this repository at §2.4 conflict 4, which
  refuted the "Fully Charged = Grand Concert resource" reading on 2026-10-05 and reclassified it as a Power-1200
  race mechanic. No change was needed; the finding is that the report is the weaker of the two accounts.
* **F-3 (no new fact).** Across categories A to F, the report contributes no mechanics fact this repository does
  not already hold with a source. Its value is as a cross-check, and its errors cluster exactly where the peer
  check said they would: version boundaries, precision beyond the evidence, and one transcription error (AWFUL).

### Open questions this pass leaves open

1. **Speed-2000 and Stamina-1200 gates on Global.** The next useful evidence is one Global client capture of a
   race report or stat panel that shows the gate firing, or a Global patch note naming it. Until then the gates
   stay `[JP]`-sourced and must not be presented as Global.
2. **Compound session-gain stacking order and rounding.** One controlled comparison — two training turns whose
   previews and outcomes are both recorded against a known deck — would pin the order. No source publishes it.
3. **Training-failure probability by Energy.** No tier-A table exists; the next useful evidence is a
   reproducible in-client sample or an official table.
4. **Slope unit.** Unchanged from §1.2.4; no page exposes the field.
5. **Scout rates and Exchange-Point conversion (F-9, F-10).** §5 is marked expired and was not re-fetched here;
   a Global notice read would close them.
6. **Unity Cup Extreme-Burst counting.** A publisher disagreement the config records and does not settle.
7. **Trackblazer retry resume point.** Unchanged from §2.2.3.

### Validation and scope

* Searched the repository for every corrected value: `−25%`/`25%` on mood (no occurrence); "exponential" against
  the distance term (only the unrelated `ARCHITECTURE.md` backoff claim, already logged as E-1); the Max-Stat ids
  (the §1.3.4/§1.3.5 occurrence now carries the erratum).
* No application code, test, schema, seeder or component was changed. The diff for this pass is this section and
  the §1.3.5 erratum in `UMAMUSUME_REFERENCE.md`.
* Gates run for this pass are recorded in the hand-off that cites this section: `composer lore`, `composer
  lore-code`, and `composer docs` (census). Application tests were not run: no code changed, so no behavior moved.

---

## cockpit-ui-audit-2026-10-12.md

> **Consolidated 2026-10-15 (Round 16, owner-ordered).** Source: `docs/proposals/cockpit-ui-audit-2026-10-12.md` (421 lines), reproduced verbatim below with headings demoted one level. The original was deleted from `docs/proposals/` after line-by-line verification (0 missing). Cite this anchor, not the deleted path.

## Career Cockpit — UI audit (SCR-CAR-011)

> **Reference only. Not authorization.** Nothing in this file is a decision, and no component,
> token, copy string or spec line was changed to produce it. Each section names the files an
> approval would touch and changes none of them.
>
> Recorded 2026-10-12 from the owner's screenshots of `GET /training-runs/{id}/cockpit` (Unity Cup,
> turn 1, no turns logged) and a full read of the screen's own components. It is the proposal-side
> counterpart to `SCREEN_SPEC.md` §SCR-CAR-011, which stays the behavioural authority. The
> improvement list the owner supplied is reconciled in §1 rather than adopted: most of its twelve
> items are already shipped or already true, and four suggestions (in five parts) would reverse a
> landed owner ruling or an accepted ADR.
>
> **The run in the screenshots is empty.** `header.turn === 0` drives the empty branch
> (`Cockpit.vue:155`), the advisor declines, every stat is unrecorded, and the scenario region
> prints absence prose for a team state nobody entered. Roughly a third of the visual density in
> those screenshots is the empty state doing its job, not the layout. Findings below say which
> they are; anything that reads as a defect only on an empty run is marked **(empty-run)**.

### 0. What governs this screen

Per `AGENTS.md` §2, the chain that settles every dispute below is: quality bar → gate registry →
accepted ADRs → `PRD.md` → `ARCHITECTURE.md` → `SCREEN_SPEC.md` (screen behaviour) → `DESIGN.md`
(visual system) → `docs/proposals/design-2.0.md` (reference target). Five points from reading it
in full, because four of the owner's suggestions land on them:

1. **`SCREEN_SPEC.md:1841` fixes the layout and forbids the obvious implementation of one
   suggestion.** "The order is CSS only: no element is hidden at a breakpoint, so nothing leaves
   the accessibility tree and no primary decision needs a horizontal scroll." A tab strip that
   shows one section at a time would violate this directly; an anchor nav would not.
2. **`SCREEN_SPEC.md:1858-1865` is an owner ruling that closes the "prominent stat cards at the
   top" suggestion.** Energy, Mood, Fans and Skill Points print *once*, by
   `CareerStatePanel.vue`, beside the five stats the advisor ranks. They used to print twice, on
   the header as well, and were moved off it because "a reading shown twice is a reading that can
   disagree with itself". `EnergyReadingConsistencyTest` pins the header row, the state strip and
   the event screen to one reading. Moving Energy back to the top of the page reopens the defect
   that ruling closed.
3. **`ADR-0020` §2 and `ADR-0001`'s licence bar two of the action-card suggestions.** §2 carries
   `ADR-0001`'s guardrails forward — a three-band indicator by default, any numeric estimate
   opt-in and unbuilt — so there is no score, no percentage and no confidence anywhere in the
   payload (`SCREEN_SPEC.md:1869`), and `risk` was retired from the wire with
   `alternatives[].tradeoff` named as its honest home (`SCREEN_SPEC.md:1880-1881`).
4. **`ActionGrid.vue:9-16` records a landed decision against "unavailable" action cards.** Every
   entry is a link and every link is live: "seven unfocusable items would fail this slice's own
   keyboard-path acceptance and leave the Cockpit with no way to act." There is no absent arm to
   style.
5. **The G-18 contrast contract does not cover the surface this screen uses most.**
   `tests/Feature/DesignTokensTest.php:540-546` pins 19 pairs, and exactly one of them carries
   `ink-muted`: `ink-muted on sunken`. This screen's dominant muted usage is
   `text-xs text-ink-muted` on `bg-raised` and `bg-panel`. That is a coverage gap in the contract,
   not a measured failure — see §2.9.

### 1. The owner's improvement list, reconciled

The owner's twelve suggestions, each part placed in one of three buckets. A suggestion with more
than one part appears in more than one bucket; the number is the owner's, not this document's.

#### 1.1 Already shipped — no work, or a one-line correction to the suggestion

| # | Suggestion | Where it already is |
| - | ---------- | ------------------- |
| 11 | "Collapse the sidebar" | `AppLayout.vue:131-163` (the rail), with its `aria-expanded` / `aria-controls` / `aria-label` at `:215-231`. The rail collapses to marks and persists to `localStorage` under `trainer-desk.nav-rail` (`:147`). |
| 8 | "Add visible focus states" | `app.css:335-338`: a global `:focus-visible { outline: 2px solid var(--color-ring); outline-offset: 2px }`. `--color-ring` is measured at 5.17 / 4.88 / 4.61 on light raised / panel / page and 7.61 / 9.51 / 9.79 in dark (`DESIGN.md:129`). `app.css:358` also sets `scroll-padding-bottom: 4rem` inside its `max-width: 767px` block, so a focused control is not covered by the pinned mobile nav bar. |
| 8 | "Avoid relying on lime/yellow alone for meaning" | `ActionGrid.vue:51-53` marks the recommendation with a star glyph plus the word `RECOMMENDED`, never colour alone. Every badge on this screen goes through `ProvenanceBadge.vue` with a glyph and a word. The accent (`--color-pick-line`) is a 2px *boundary*, measured 6.24 light / 8.46 dark against `raised` (`DESIGN.md:124`). |
| 11 | "Stack the advisor panel below the main content" on mobile | `Cockpit.vue:702-703` sets the grid and `order-1` at `:703`; the advisor's `order-2` is at `:850` and the actions' `order-3` at `:854`, so mobile reads state panel, then advisor, then actions. `SCREEN_SPEC.md:1843` requires exactly this: "The recommendation stays above the actions on mobile." |
| 12 | "Add lightweight feedback … confirmation toasts" for status/scenario/identity | Inline save states already exist on two of the four forms (`Cockpit.vue:503`, `:651`) and on the correction (`:820-822`, per ruling R3-04). What is missing is *consistency*, not the mechanism — see §2.4. A toast system does not exist anywhere in the tree and would be a new pattern. |
| 7 | "Use progressive disclosure" for technical evidence | Three `<details>` disclosures already ship on this screen: the manual correction (`Cockpit.vue:731`), delete-turn (`:828`) and delete-career (`:943`). The pattern the repo uses is `AbsenceValue.vue`, a native `<details>` whose summary is the accessible name (`DESIGN.md:576-585`). |

#### 1.2 Supported by the governing docs — worth doing, cite the section that asks for it

| # | Suggestion | The governing line |
| - | ---------- | ------------------ |
| 1 | Stronger hierarchy: a real page title, status badge, primary action at the top | `DESIGN.md:188-190` specifies page title `text-2xl font-600`, one `h1` per page. What shipped is `AppLayout.vue:281` at `text-base`, the same size as every `h2` panel heading on this screen, and the trainee name prints in both (`Cockpit.vue:461` and `CareerHeader.vue:82-83`). See §2.1. |
| 2 | Reduce density: slightly larger text, more line height | `DESIGN.md:188-190` specifies body `text-sm` and section headers `text-lg`. Section headers shipped at `text-base` and a large share of this screen is `text-xs`. See §2.2. |
| 3 | Better top summary | Supported in the narrow form: the *scenario resources* strip (`CareerHeader.vue:141-154`) is two bare `N/A` boxes with no hierarchy against the five-field definition list above it. The four readings the suggestion wants (energy, rank, spirit, turn) already exist, and three of them must stay where the owner ruled. See §2.3. |
| 4 | Sticky advisor | `SCREEN_SPEC.md:1841` fixes the columns, not their scroll behaviour, and `design-2.0.md` §23 asks that "the player should never have to search for: *What should I do now?*" That is the argument for sticky. See §3.2 for the two constraints a sticky rail must respect. |
| 5 | Clarify edit mode | The always-open edit strip (`Cockpit.vue:482-689`) mixes eleven identity inputs, two write buttons per select, two export links and a deck link, under an `sr-only` heading, directly beneath the read-only header. See §3.3. |
| 6 | Larger action cards with descriptions and a recommended state | `design-2.0.md` §24 is the section that asks for comparable decision cards, and the grid's entries are already links with a note line. The gain is size and a mark, not new information. See §3.4. |
| 9 | Consistent scenario card headers and badges | The panel shell already composes `ScenarioStatusBadge.vue` (`ScenarioPanel.vue:158`) and `ProvenanceBadge` for `Estimated` (`UnityCupPanel.vue:204`, `:266`). What is inconsistent is header weight and copy length across the Unity Cup sub-panels, not the badge vocabulary. See §3.5. |
| 9 | Two-column scenario layout on desktop | Already true: `ScenarioPanel.vue:171` is `sm:grid-cols-2 lg:grid-cols-4`. The screenshot reads as one long column because Unity Cup composes seven panels and the region is full width under the three columns. |
| 10 | Section nav | Compatible with `SCREEN_SPEC.md:1841` as an anchor list; incompatible as tabs. See §3.6. |
| 11 | Responsive: convert dense tables to stacked cards | `SCREEN_SPEC.md:1841` and `DESIGN.md:209-217` already settle this class of problem: a wide region scrolls inside a focusable container (`overflow-x-auto` plus a `role="region"`, `tabindex="0"` and an `aria-label`), it is not reflowed. The Cockpit's own tables are the scenario caps table (`ScenarioPanel.vue:230`) and the burst-band table (`UnityCupPanel.vue:275`); both are candidates for the existing pattern before any card layout is invented. |

#### 1.3 Conflicts with a binding decision — do not implement as written

| # | Suggestion | Why it stops |
| - | ---------- | ------------ |
| 3 | "Replace the small scattered fields with 4–6 prominent stat cards: scenario, energy, rank, spirit, current turn, priority" | Two parts of this conflict. Energy is owner-ruled into the state panel (`SCREEN_SPEC.md:1858-1865`, guarded by `EnergyReadingConsistencyTest`). Priority is the advisor's first element and already sits at the top of the rail (`NextDecision.vue:168`). Rank and spirit are scenario widgets whose home is the header strip *and* the scenario panel; see §2.6 before proposing a third home. |
| 6 | "Turn the action list into larger selectable cards with a **selected** state" | These are navigation links, not a selection control. A selected state would imply the choice has been committed; the Trainer navigates away to record it. `ActionGrid.vue:9-16` is explicit that each entry links to the screen that records the action. |
| 6 | "Visually distinguish **risky** and **unavailable** actions" | Risk is a held computation: `ADR-0001`'s licence section keeps any numeric estimate opt-in and unbuilt, and `SCREEN_SPEC.md:1880-1881` retires `risk` from the wire. Unavailable arms do not exist by a landed keyboard-path decision (`ActionGrid.vue:9-16`). |
| 7 | "Collapse long explanatory content behind 'Show details'" — applied to the advisor's `why` list | Ruling 5a (`SCREEN_SPEC.md:1886`) requires that where a figure rests on a constant, "the source and the read date print in the **visible text**, not only in the tooltip". A `<details>` is keyboard-reachable and so is not a tooltip, but `design-2.0.md` §2 (line 179) puts Explanation as the fourth of five first-class elements in the rail. The `why` list is the answer to "why should I believe this"; the defensible collapse targets are the scenario panels' methodology prose, not the rail. |
| 9 | "Add badges for 'Recorded' and 'Missing'" | The label set is four — `Confirmed` / `Calculated` / `Estimated` / `Unknown` — and its axis is set by surface class by owner ruling (`DESIGN.md:607-614`, recorded in `docs/proposals/provenance-and-absence-contracts.md` §9). `Recorded` and `Missing` are not in the vocabulary. An absence renders through `AbsenceValue.vue` with a reason, not a chip (`DESIGN.md:576-585`). |
> One suggestion is neither shipped nor a conflict. **#8, "Increase muted text contrast"**, is
> *unverified*: the pair this screen leans on is absent from the G-18 contract, so nothing currently
> proves it either way. The fix is to extend the contract and let the gate answer, not to move the
> token (§2.9, §3.7).

### 2. Findings

#### 2.1 The page title is the same size as every panel heading, and the trainee name prints twice

`AppLayout.vue:281-282` renders the `#title` slot inside `<h1 class="text-base font-semibold">`.
`Cockpit.vue:461` fills it with `props.run.trainee`. `CareerHeader.vue:82-83` then renders the
same name again as an `<h2 class="text-base font-semibold">`, with the Japanese name and the card
form title beside it.

Consequences, all visible in the screenshot:

- The only `<h1>` on the page is `text-base`, which is the *same* size as the `<h2>` of every
  panel: `CareerHeader.vue:82`, `CareerStatePanel.vue:120`, `ActionGrid.vue:33`,
  `AdvisorRail.vue:30`, `Cockpit.vue:896` (Scenario), `Cockpit.vue:910` (Races in this run).
  Nothing establishes rank by size, so a screen reader's heading list and the eye's scan both
  flatten.
- "Rice Shower" appears twice within 40px of vertical space.
- `DESIGN.md:188-190` specifies the page title at `text-2xl font-600`. The shipped `text-base`
  is two steps below that.

This holds on any run; it is not an empty-run artifact.

#### 2.2 The section header scale is one step under the specified one, and `text-xs` carries too much

`DESIGN.md:188-190`: body `text-sm`, table cells `text-sm`, section headers `text-lg`. Shipped:
section headers at `text-base` throughout (citations in §2.1), and a large share of secondary
copy at `text-xs` — `CareerHeader.vue:142,151`, `CareerStatePanel.vue:129,138,149,155`,
`NextDecision.vue:132,188,200,240`, `ActionGrid.vue:55`, `AdvisorRail.vue:39`,
`Cockpit.vue:820,916`. The sub-headings inside the rail (`NextDecision.vue:132,162,188,200,240`)
are `text-xs uppercase tracking-wide`, so five consecutive `text-xs` blocks stack in the right
column.

The cost is not aesthetic. `text-xs` is below `DESIGN.md`'s own body step, and the muted ink it
usually pairs with is the same tier that carries the *reasons* — the sentences that tell a Trainer
whether to go enter a reading or stop looking (`DESIGN.md:576-585`). Small plus muted is the worst
combination available for the text that matters most on this screen.

#### 2.3 The header strip gives the scenario resources no hierarchy

`CareerHeader.vue:141-150` renders "Scenario resources" as a `text-xs` label over a
`ResourceStrip`, which draws one bordered box per widget. On Unity Cup that is two boxes: Team
Rank and Spirit Bursts (`ResourceStrip.vue:67-76`). Both render `N/A` / "not yet recorded" on the
screenshot, at the same weight as the five-field definition list above them
(`CareerHeader.vue:100-139`), which carries Scenario, Year, Month, Turn and Energy.

So five position facts and two scenario facts read as one undifferentiated band. The suggestion to
build a top summary out of scenario, energy, rank, spirit and turn is right about the *problem*
and wrong about the *fix*: four of those five already have an owner-ruled home. The available move
is to give the strip its own visual step — a heading size above `text-xs`, and the two boxes
subordinate to the definition list — rather than to relocate the readings.

#### 2.4 Save feedback is present on two of the four header forms

| Form | Submit handler | Success line |
| ---- | -------------- | ------------ |
| Status | `saveStatus` (`Cockpit.vue:339-341`) | "Saved." (`:503`) |
| Scenario | `saveScenario` (`:343-345`) | none |
| Grade Point period | `savePeriod` (`:347-349`) | none |
| Identity | `saveIdentity` (`:383-415`) | "Saved." (`:651`) |
| Correction | `saveCorrection` (`:277-288`) | "Correction saved." (`:820-822`, ruling R3-04) |

All five post with `preserveScroll: true`, so without a success line a Trainer who presses
"Change scenario" gets no answer at all unless they notice the select re-render. R3-04 already
fixed this for the correction on exactly this reasoning — "a Trainer could not tell a saved
correction from a dead button" — and the same argument covers the two forms that were left out.

There is no dirty indicator anywhere on the screen. That is defensible for the three single-field
selects (each is an explicit submit of one value) but weaker for the identity form: eleven inputs
— rarity, potential level, five growth percentages, four ceilings — behind one "Save identity"
button, with no indication of which fields differ from what the run holds. An empty ceiling field
is a *clear*, not a no-op (`Cockpit.vue:397-403`), which raises the cost of an unexamined submit.

#### 2.5 The accent is spent twice, on two different things (a proposal, not a defect)

`NextDecision.vue:130` wraps the whole decision card in `border-2 border-pick-line`.
`ActionGrid.vue:47` gives the single `RECOMMENDED` entry the same `border-2 border-pick-line`.

`ActionGrid.vue:5-7` states the intent: "the count is Von Restorff's one accent, not an accent per
card". One accent, spent twice — on a rail card that is *entirely* the recommendation, and on a
grid entry that *is* the recommendation — means the border no longer discriminates. The rail
already distinguishes itself by position, heading and the `bg-chrome` CTA
(`NextDecision.vue:218-223`); the grid entry needs the border more.

This is not a defect against a written rule — no section pins the accent to exactly one element —
but it is the cheapest hierarchy win on the screen and it is one class string.

#### 2.6 The same two scenario facts print twice, far apart on the page

`CareerHeader.vue:141-149` prints Team Rank and Spirit Bursts through `ResourceStrip`.
`UnityCupPanel.vue` prints a Team Rank section ("Rank not recorded…") and a Spirit section
("Current Spirit / N/A"). The screenshot shows both pairs.

The data cannot disagree: `CockpitController.php:143` builds the team section once — "Built once:
the header's rank and the scenario section's team panel read the same pass." So this is not the
defect the 2026-10-08 ruling closed. It is a density cost: two absences stated twice, in different
words, at opposite ends of the page, and the header's wording ("N/A / not yet recorded") differs
from the panel's ("Rank not recorded: the league letter is read off the client, and this run has
none entered yet").

Worth an owner call, not an agent fix: the header strip exists so a Trainer mid-career can read
position without scrolling, and the panel exists because the scenario owns the mechanic. §5
carries the question.

#### 2.7 The identity props interface declares `caps` twice

`Cockpit.vue:120-128` declares `caps: Record<string, { entered: number | null; scenario: number }>;`
twice in the same interface, with the docblock intent split across the two. TypeScript merges
identical members, so nothing breaks; it is a copy-paste artifact from the `ADR-0025` pass. Two
locations, one fix, no behaviour.

#### 2.8 A page-level comment contradicts the page's own query-string state

`Cockpit.vue:12-14`: "the only query-string state here is none at all." `Cockpit.vue:245-252`
then implements `?edit_turn=<id>` for the arbitrary-turn correction, and the selector navigates
it. The comment predates ruling 2 of plan §9.6. Stale comments are the cheapest source of a wrong
future decision, so this is worth one line when the file is next touched.

#### 2.9 The contrast gap is in the *contract*, not in a measurement

`tests/Feature/DesignTokensTest.php:532-602` is the G-18 browser gate, and it is real now: the
`beforeEach` at `:472-487` skips only when the Playwright driver is absent, and otherwise restores
Vite and reads resolved custom properties off `documentElement` in both themes. The pair list at
`:540-546` is:

```
ink on panel, ink on raised, ink-muted on sunken, ink on tint-speed, ink on tint-stamina,
ink on tint-power, ink on tint-guts, ink on tint-wit, ink on tint-sp, ink on green-tint,
ink-strong on raised, on-chrome on chrome, on-pick on pick, on-pick on green, up on panel,
down on raised, goal on raised, goal-line on raised, green-deep on sunken
```

Exactly one pair carries `ink-muted`, against `sunken`. This screen's muted text sits on
`bg-raised` (`CareerStatePanel.vue:155` labels inside raised cells; `CareerStatePanel.vue:149`
absence prose under a raised row) and on `bg-panel` (every panel's own sub-copy). Neither pairing
is in the contract, so a regression to `ink-muted`'s value would not fail the gate even though
this screen is its heaviest consumer.

Hand arithmetic on the current hexes (`DESIGN.md:113-117`) clears 4.5:1 on all four combinations:
light `#6E6459` on `raised` `#FFFFFF` about 5.8:1, light on `panel` `#F8F8FB` about 5.5:1, dark
`#AAABB5` on `raised` `#24262A` about 6.8:1, dark on `panel` `#121013` about 8.5:1. **Those figures
are hand-computed and are not gate evidence.** The right change is two rows in
`g18ContractPairs()` — `ink-muted on raised`, `ink-muted on panel` — which moves the count from
19 and moves `DesignTokensTest.php:538`'s `toHaveCount(19)` to 21, together with the key order at
`:540-546`. It does **not** touch `DESIGN.md:145`'s "20 of the 60 declared roles", which counts
colour *roles*, not contrast *pairs* — and that line already disagrees with the test, which pins
**59** tokens (`:253`, `:493`) against DESIGN.md's 60. Then the gate answers §2.2's question about
`text-xs` with a number.

### 3. Proposed changes

Ordered by value against risk. Each names what must not break.

#### 3.1 Restore the type scale — page title `text-2xl`, section headers `text-lg`

`DESIGN.md:188-190` already specifies this; nothing needs a ruling.

- `AppLayout.vue:281` — `text-base` to `text-2xl`, and check `font-600` against the shipped
  `font-semibold`.
- The six `<h2>` headings listed in §2.1 — `text-base` to `text-lg`.
- Stop printing the trainee name twice. The header needs the Japanese name and card title, so the
  cheaper direction is to keep `#title` as the trainee and reduce the header's `h2` to the
  identity line it actually adds.

**Must not break:** `CareerLayout.vue:32` forwards `#title`, so the change is in `AppLayout` and
is felt by every 2.0 screen, not just this one. `tests/browser/career-cockpit.spec.ts` scans this
screen with axe (`:180`, `:260`); `tests/browser/accessibility.spec.ts` covers the other 2.0
screens but does not visit the Cockpit, because it needs a run. Level 2 verification:
`career-cockpit.spec.ts` plus `SCREEN_SPEC.md` §SCR-CAR-011's state table.

#### 3.2 Make the advisor column sticky at `lg`, with a scrollable region when it overflows

`Cockpit.vue:850` is the advisor's column div. `lg:items-start` is already set at `:702`, so the
column will not stretch. The change is `lg:sticky lg:top-4` plus a max-height and an inner
`overflow-y-auto`.

Two constraints, both from this repository's own measured rules rather than from taste:

1. **A scrollable region needs the focusable-container treatment.** `DESIGN.md:209-217` requires
   an `overflow-x-auto` region to carry `role="region"`, `tabindex="0"` and an `aria-label` so it
   is reachable by keyboard, not trackpad alone. A vertical overflow box a keyboard user cannot
   scroll is the same failure one axis over.
2. **The rail is already taller than the viewport on a populated run.** Objectives, priority,
   recommendation, benefit lines, the G4 dual readout, the CTA, alternatives, `why`, `switch_if`
   and the recorded-action card stack well past a screen (`NextDecision.vue:129-272`). Sticky
   without an inner scroll would clip the recorded-action card permanently.

The `bg-chrome` "Go to Rest" CTA at `NextDecision.vue:218-223` is already a filled primary
pill; the suggestion to give Rest a clear primary treatment is met, and making the rail sticky is
what stops that pill scrolling away.

**Must not break:** mobile order (`SCREEN_SPEC.md:1843`), the `aria-live="polite"` on the priority
line (`NextDecision.vue:168`), and the `#action-recommended` fragment target
(`ActionGrid.vue:41`), which the rail's CTA depends on for scroll-and-focus. A sticky ancestor
changes where `scrollIntoView` lands, so the existing `scroll-margin-top` needs re-checking.

#### 3.3 Give the header edit strip a visible heading and collapse it behind an explicit mode

`Cockpit.vue:482-689`. Today the `<h3>` is `sr-only` ("Edit the run's identity") and the `<ul>`
renders six always-open groups (`:485`, `:507`, `:529`, `:560`, `:655`, `:676`). The proposal:

- A visible heading, "Run identity", at the section-header step.
- A single "Edit identity" disclosure (native `<details>`, the pattern already used at `:731`)
  that opens status, scenario, period and the identity form together.
- Export (CSV, JSON) and the deck link move out of the disclosure: they are reads, not writes, and
  they are the two things a Trainer wants without entering an edit mode.
- A "Saved." line on the scenario and period forms, matching `:503` and `:651` — the R3-04 fix
  extended, not a new pattern.
- An unsaved-changes marker on the identity form only, driven by `identityForm.isDirty`, since
  that is the one form where an unexamined submit has a non-no-op consequence (an empty ceiling
  clears it, `:397-403`).

**Must not break:** the four forms' hidden sibling fields (`:319-337`, `:360-381`) — a partial edit
must keep blanking nothing. The `name` attributes and ids (`status`, `scenario`,
`current_objective_index`, `trainee_rarity`, `potential_level`) are referenced by
`CareerCockpitTest`; renaming them is a test change and the test is named in the same commit. No
new endpoint: all four already write through `runs.update`.

#### 3.4 Action grid: spend the accent once, and give the entries the section's size step

`ActionGrid.vue`. The seven entries already carry a label, a note and a live href; the proposal
adds no information, only weight:

- Entry label `text-sm` to `text-base`, note `text-xs` to `text-sm` (§2.2's body step).
- Keep the star glyph plus the word `RECOMMENDED` (`:51-53`).
- Move the accent: the grid entry keeps `border-2 border-pick-line`, and `NextDecision.vue:130`
  drops to `border border-rule` plus its existing position and heading. That restores the one
  accent the grid's own comment asks for (§2.5).
- Do **not** add icons without going through `DESIGN.md` §6. `NavGlyph.vue:3-21` documents the
  gate: marks are inline paths on one stroke and one grid, filtered by the lore gate's allowed
  motif list. Seven new action marks are a `NavGlyph` extension plus a lore pass, not a style
  change. Feasible, but it is its own slice.

**Must not break:** `#action-recommended` and `tabindex="-1"` (`:41-43`) — the rail's CTA targets
them. Exactly one entry marked, or none when the advisor declines (`SCREEN_SPEC.md:1843`,
`ActionGrid.vue:5-7`). Every entry stays a focusable link (`:9-16`).

#### 3.5 Scenario region: consistent panel headers, shorter lead copy, nothing hidden that §5 requires

`ScenarioPanel.vue` composes the panels; `UnityCupPanel.vue` is Unity Cup's own.

- Sub-panel headings to one step so seven panels read as siblings under the region's `h2`.
- The publisher-conflict `AlertRow` at `UnityCupPanel.vue:300` and the methodology paragraphs at
  `:304`, `:333` are the longest always-open copy on the page. These are candidates for a
  `<details>` whose summary states the claim — "Three parts of this table are publisher conflicts"
  is already the summary sentence. **The `AlertRow` itself stays open**: it is a warning, and a
  collapsed warning is not a warning.
- `ProvenanceBadge` `estimated` at `:204`, `:266` and `ScenarioStatusBadge` at
  `ScenarioPanel.vue:158` stay as they are; they are the vocabulary (§1.3).
- The two tables (`ScenarioPanel.vue:230`, `UnityCupPanel.vue:275`) get the
  `overflow-x-auto` plus focusable-container treatment from `DESIGN.md:209-217` if they overflow
  at any supported width, rather than a card reflow.

**Must not break:** gate G-33 — no scenario name may appear in a component; the shell switches on
matrix flags only (`Cockpit.vue:898-900`). A fifth scenario must reach every one of these changes
through config with no edit here.

#### 3.6 An anchor section nav, not tabs

A `<nav aria-label="On this page">` with links to the regions, sticky under the career bar.
Every target heading already has a stable id: `career-header-heading`, `cockpit-header-edit`,
`career-state-heading`, `career-actions-heading`, `career-scenario-heading`,
`career-timeline-heading`, `career-advisor-heading`.

- Needs `scroll-margin-top` on the targets; `app.css` already sets `scroll-padding-bottom` for the
  mobile bottom bar, so this extends an existing rule rather than adding one.
- A skip link to it would also satisfy SC 2.4.1 for the page as a whole.
- **Not tabs.** `SCREEN_SPEC.md:1841` forbids hiding elements; tabs hide panels by definition.

#### 3.7 Extend the G-18 pair list by two rows

`tests/Feature/DesignTokensTest.php:317-349` (`g18ContractPairs`) plus `:538-546` (the pinned key
order). Add `ink-muted on raised` and `ink-muted on panel`. Then `DESIGN.md:99-108` needs a dated
note: it still reads as though the browser gate were `markTestIncomplete`, which is stale — the
gate is implemented and runs wherever Playwright is installed.

This is the smallest change in §3 and the only one that converts a hand-computed claim into
evidence. It also settles §2.2: once the pairs are pinned, whether `text-xs` muted copy clears
4.5:1 is a gate result, not an opinion.

### 4. What a later pass must not do

1. Move Energy, Mood, Fans or Skill Points out of `CareerStatePanel` — owner ruling 2026-10-08,
   `SCREEN_SPEC.md:1858-1865`, guarded by `EnergyReadingConsistencyTest`.
2. Add a score, a percentage, a confidence or a risk to the rail — `ADR-0001`'s "What this does
   not license", `ADR-0020` §2, `SCREEN_SPEC.md:1869` and `:1880-1881`.
3. Mark an action grid entry unavailable, or make the grid a selection control —
   `ActionGrid.vue:9-16`.
4. Collapse the rail's `why` list, or the `switch_if` list, out of visible text —
   `SCREEN_SPEC.md:1886` (ruling 5a).
5. Introduce a fifth provenance label, or chips named `Recorded` / `Missing` —
   `DESIGN.md:607-614`, `docs/proposals/provenance-and-absence-contracts.md` §9.
6. Hide any region at any breakpoint — `SCREEN_SPEC.md:1841`.
7. Add a skeleton or a cold-load spinner, or give a cold load a custom loading state — ADR-0007
   reserves custom loading for user-initiated async. `Cockpit.vue:463-466`'s `role="status"` and
   `role="alert"` lines are the whole loading story the page needs, and §3.3's save feedback stays
   inside the same line: user-initiated writes get feedback, page loads do not get skeletons.
8. Put a scenario name into a component — gate G-33, `config/scenarios.php` is the only source.

### 5. Questions for the owner

1. **§2.6 — the header strip vs the scenario panel.** Team Rank and Spirit Bursts are printed
   twice on one screen from one payload, in different words. Which copy is the run's statement of
   record, and should the header strip keep its own wording or adopt the panel's?
2. **§2.1 — the duplicate trainee name.** Keep the trainee in the `#title` slot and reduce the
   header `h2`, or the reverse? The second carries the Japanese name and card title, so the two
   options are not symmetric.
3. **§3.2 — the sticky rail's inner scroll.** A sticky rail taller than the viewport needs a
   scrollable region with `role="region"` and `tabindex="0"`, which adds a tab stop to the page.
   Accept that tab stop, or accept the rail clipping its lower third?
4. **§3.4 — action marks.** Seven new marks through `NavGlyph` would need a `DESIGN.md` §6 lore
   pass and are gated on motif, not taste. Worth its own slice, or leave the grid word-only?

### 6. Verification plan

| Change | Minimum |
| ------ | ------- |
| §3.1 type scale, §3.3 edit mode, §3.4 grid, §3.5 scenario region | the affected feature tests (`CareerCockpitTest`, `CockpitObjectivesTest`), then `tests/browser/career-cockpit.spec.ts` and `tests/browser/accessibility.spec.ts` |
| §3.2 sticky advisor | the same two browser specs, plus a keyboard pass: tab into the rail, scroll it with arrow keys, and confirm the `#action-recommended` fragment still lands focus |
| §3.7 G-18 pairs | `php artisan test --compact --filter=DesignTokensTest`, which is the evidence; hand arithmetic in §2.9 is not |
| anything touching `AppLayout` | the full suite, then the hand-off sequence in `AGENTS.md` §9 |

Nothing here touches a migration, a controller, a Form Request or the fetch pipeline, so no
schema, route or network gate is engaged. The lore gate runs as usual: this document is prose in
`docs/`, where a `lore-ignore-line` marker is valid but is not itself a ruling.

---

## setup-wizard-ui-audit-2026-10-12.md

> **Consolidated 2026-10-15 (Round 16, owner-ordered).** Source: `docs/proposals/setup-wizard-ui-audit-2026-10-12.md` (390 lines), reproduced verbatim below with headings demoted one level. The original was deleted from `docs/proposals/` after line-by-line verification (0 missing). Cite this anchor, not the deleted path.

## Career setup wizard — UI audit (SCR-CAR-002/003/005/008/009/010)

> **Reference only. Not authorization.** Nothing in this file is a decision, and no component,
> token, copy string or spec line was changed to produce it. Each section names the files an
> approval would touch and changes none of them.
>
> Recorded 2026-10-12 from the owner's screenshots of the six wizard steps and a read of each
> page's source. It is the proposal-side counterpart to `SCREEN_SPEC.md` §SCR-CAR-002, 003, 005,
> 008, 009 and 010, which stay the behavioural authority. The companion audit of the Cockpit is
> `docs/proposals/cockpit-ui-audit-2026-10-12.md`; the two share §4's vocabulary rules.
>
> The owner's improvement list is reconciled in §1 rather than adopted. Most of it is already
> shipped, and six items would reverse a landed decision — the sharpest being the drawn ancestry
> tree, which `LegacySelect.vue:14-16` refuses on WCAG grounds in its own docblock.

### 0. What governs this flow

Six screens, one shell, one draft. The parts that settle disputes below:

1. **`SetupLayout.vue` is the step bar and nothing else.** `AppLayout` plus
   `<nav aria-label="Setup steps">` (`SetupLayout.vue:54-80`). Its docblock records why the
   product sidebar stays: `SetupDraft` writes every step to the session as it is saved, so leaving
   mid-flow loses nothing, and "which step am I on" is answered by the step count and
   `aria-current="step"` rather than by hiding the way out (`:6-12`).
2. **The draft is session state, not a run.** `SetupDraft` holds every step; `training_runs` is
   written once, at Preflight (`routes/web.php:40-46`). A mid-flow screen never touches a run row.
3. **Gate G-33: no scenario name may appear in a component.** `ScenarioSelect.vue:5-8` states it
   plainly — the page "prints props and holds no `config()` lookup and no scenario-name branch",
   and `CareerScenarioSelectTest` proves a fifth scenario arrives with no edit here. Any change to
   card content has to reach it through `ScenarioSelectController`, not through a component.
4. **`ADR-0020` §3 applies harder here than on the Cockpit.** Four of the six screens are
   *record-only* surfaces: Legacy computes no inheritance, no expected stat, no Affinity payout,
   no Spark chance, no score and no ranking (`LegacySelect.vue:6-10`). BuildTarget refuses a risk
   tolerance and per-skill marks as stored-shape changes (`BuildTarget.vue:615-620`).
5. **`DESIGN.md:197-208` ratifies three widths**: `max-w-5xl` shell, `max-w-3xl` in-page form and
   long copy blocks, `max-w-lg` narrow form. §2.1 measures all six screens against that.

### 1. The owner's improvement list, reconciled

#### 1.1 Already shipped — no work, or a correction to the suggestion

| Suggestion | Where it already is |
| ---------- | ------------------- |
| **2.1** searchable, filterable roster | `TraineeSelect.vue:252-326`. Search box, three aptitude facet selects (`:264-275`), a unique-skill select (`:277-289`), sort and order selects, Filter and Clear filters buttons. |
| **2.5** sort options | `TraineeSelect.vue:291-296`, driven by a `sortKeys` prop, so the key list is the controller's and not a component branch. |
| **2.7** pagination | `TraineeSelect.vue:447-464`: real pagination with `aria-current="page"` on the active link and a live count ("{{ total }} trainees match."). Virtualization would be a new dependency, barred by `AGENTS.md` §5 without owner approval. |
| **3.4** reachability only where computable | `BuildTarget.vue:438-442` states in prose "Whether a target is reachable in the turns left is not computed here", and each field prints `N/A` with its reason (`:475-478`). The bar that ships measures the entered number against the cap and nothing else (`:465-466`). |
| **3.5** reorderable skill priorities with explicit numbers | `BuildTarget.vue:569` renders an `<ol>`; `:541-544` states the contract: "Reorder with the buttons; no drag is required." |
| **3.2** compact target summary | `BuildTarget.vue:624-633` ships a live Summary with a `calculated` `ProvenanceBadge` whose title reads "assembled by this tool from the values entered on this form, not a stored fact". It sits at the bottom rather than the top; §3.3. |
| **5.6** disable irrelevant ownership controls | `DeckSelect.vue` writes to one slot at a time through the `filling` ref (`:115`), and `SupportSlot.vue:187` words the two options "Owned" and "Rented". |
| **5.7** Slot 6 distinction | `SupportSlot.vue:102` carries the `Friend slot` badge. |
| **5.8** analysis empty state | `DeckAnalysis.vue:58-60`: "Nothing to analyse yet. The analysis reads the effects the six cards state, so it is blank until the deck has at least one card in it. Fill a slot on the left and this fills in with it." |
| **6.2** empty / incomplete / saved / selected / warning states | Empty: `ScenarioSelect.vue:133-139`, `TraineeSelect.vue:345-351`, `DeckAnalysis.vue:58-60`. Incomplete: `Preflight.vue:175-193`. Saved: every write announces `role="status"` — `ScenarioSelect.vue:128`, `TraineeSelect.vue:343`, `BuildTarget.vue:643`, `LegacySelect.vue:651`, `DeckSelect.vue:290`, `Preflight.vue:433`. Selected: `aria-pressed` plus the word "Selected" / "Chosen" (`ScenarioSelect.vue:253,264-267`, `TraineeSelect.vue:428,442`). |
| **6.6** separate blocking errors from warnings | Already split, deliberately. `Preflight.vue:159-172` is a `role="alert"` refusal with `tabindex="-1"` that is focused on return, headed "The career was not created." The warnings beneath are a separate `<section>` whose own comment says "Warnings below never block; this does" (`:159-160`), and the deck warning's detail reads "A career can start either way." |

#### 1.2 Supported by the governing docs — worth doing, cite the section that asks for it

| Suggestion | The governing line |
| ---------- | ------------------ |
| **Cross-page** consistent max-width container | `DESIGN.md:197-208` already ratified it. Measured: `BuildTarget.vue:336` uses `max-w-3xl` and complies; the other five constrain nothing. See §2.1. |
| **Cross-page** sticky step bar | `SetupLayout.vue:54-80` puts the step bar in document flow. The wizard is the longest flow in the app, and `design-2.0.md` §40 puts the same bar at the top of the mobile order. |
| **Cross-page** completed steps visibly distinct | `SetupLayout.vue:69` distinguishes only the current step. Every other step renders identically whether or not it was saved. See §2.2. |
| **Cross-page** persistent bottom action bar with Back | Each step already writes its own draft. Back exists on two steps of six, and Preflight's link row has a duplicate and two missing doors — see §2.3. |
| **Cross-page** helper text plus expandable "Learn more" | The house pattern is a native `<details>` whose summary carries the reason (`DESIGN.md:576-585`, `AbsenceValue.vue`), already used here at `LegacySelect.vue:402` and `:459`. |
| **Cross-page** muted-text contrast and minimum size | Same finding as the Cockpit §2.9: `ink-muted on raised` and `ink-muted on panel` are absent from the G-18 pair list (`tests/Feature/DesignTokensTest.php:540-546`), and this flow leans on both. |
| **Cross-page** semantic badges over colour | Already true where a badge exists: `PARTIALLY DOCUMENTED` is glyph plus word (`ScenarioSelect.vue:152-159`), warnings carry `!` plus title plus detail (`Preflight.vue:185-187`), `ProvenanceBadge` always pairs glyph and word. |
| **1.2** equal-height scenario cards | `ScenarioSelect.vue:141-147` is a CSS grid, so cards already share a row height. The screenshot reads unequal because the metadata block is optional (`:217` vs `:240`). |
| **1.3** stronger selected state | The border and the word are there (`ScenarioSelect.vue:146`, `:264-267`). `:260-263` explains why the word sits beside the action and not in the heading: inside the `<h2>` it would join the accessible name and "Trackblazer" would stop matching itself. Only a check icon is genuinely absent. |
| **1.4** collapsible "Scenario details" | The mechanics are the card's sourced content (`ScenarioSelect.vue:233-238`) and `design-2.0.md` §25 names key mechanics as what the card exists to show. Defensible to collapse the `key_mechanics` list; not the fact grid. |
| **3.1** grouped two-column target sections | `BuildTarget.vue:337-340` already groups "What the career is for" into a `sm:grid-cols-2` fieldset. The ceilings fieldset (`:507-535`) is the one that would gain most. |
| **3.6** per-field validation | Already done: every target and ceiling field renders its own `role="alert"` (`BuildTarget.vue:481-486`, `:528-533`) with a per-fieldset fallback (`:490-495`) and `describedBy` wiring (`:459`, `:521`). |
| **4.2** collapse parent branches | `LegacySelect.vue:402` and `:459` already use `<details>` for the per-grandparent spark editors. Extending that to a whole branch is the same pattern. |
| **5.2** slot state badges | `SupportSlot.vue` carries the friend badge and words the ownership actions. "Empty" is a named absence with a reason rather than a badge (`:107`), which is `DESIGN.md:576-585`'s rule for an absence. |
| **5.5** card effects inside each slot | `DeckSelect.vue:89-102` and `DeckAnalysis.vue` already read `SupportCardEffects` anchors; the per-slot surface is `SupportSlot.vue`'s card block. |
| **6.1** readiness summary | `Preflight.vue:195-201` already prints the inverse — "No warnings. Every section is filled…" — when the list is empty. A count line is an addition to the non-empty branch, not a new pattern. |

#### 1.3 Conflicts with a binding decision — do not implement as written

| Suggestion | Why it stops |
| ---------- | ------------ |
| **4.1** visual two-branch ancestry tree | Refused in the file itself. `LegacySelect.vue:14-16`: "the nodes are a nested list rather than a drawn tree, because a tree drawn as a picture is unreadable to a screen reader and unreachable by keyboard, and the ancestry assignment is a labelled `<select>` with no dragging path at all (WCAG 2.2 SC 2.5.7)." The nested `<ol>` at `:296-330` is the accessibility mechanism, not a styling shortfall. |
| **6.7** disable "Start Career" when warnings exist | `Preflight.vue:159-160` and the deck warning's own detail record that warnings never block. Disabling on a warning would refuse a career the server accepts. The blocking path already exists: a refused write returns a focused `role="alert"` (`Preflight.vue:127-137`, `:161-172`). |
| **1.5** "Recommended" label for a fitting scenario | No source for a recommendation, and the card already names that absence. `ScenarioSelect.vue:198-200`: "Neither the corpus nor `config/scenarios.php` holds either, so they are named absences: a sentence written here would be an invented fact (`AGENTS.md` §5)." The same argument covers a fit label: the wizard asks for the scenario *first*, before the target a fit would be measured against. |
| **1.6** make the whole card clickable | `ScenarioSelect.vue:10-14` records the inverse: the card-button pattern is a native `<button>` with `aria-pressed` precisely because it "is in the tab order, needs no arrow-key handler to be reachable, and states which card holds the stored choice". A second click target gives one action two paths and two accessible names. |
| **4.4** "Add grandparent" instead of blank fields | The four grandparents are the record's fixed shape — six nodes, `AncestryGraph::build()`'s own node set (`LegacySelect.vue:12-13`). A blank grandparent is a stored *absence*, not an empty row; hiding it would remove the surface where a Trainer records a name she reads later. |
| **6.8** final confirmation dialog | The house pattern for a confirm step is a native `<details>` with an explicit submit inside it — `Cockpit.vue:943-966` (delete-career), `:828-845` (delete-turn). A modal adds a focus-trap surface the flow does not otherwise need, and Preflight is already the summary such a dialog would restate. |
| **2.3 / 2.4** scenario fit, "+N more" on trait chips | "Scenario fit" is the same unsourced recommendation as 1.5. "+N more" is worth doing but changes `AptitudeBadge`'s contract: every letter currently prints its word (`TraineeSelect.vue:373-387`), and `design-2.0.md` §17 plus `DESIGN.md` §3.1 require letter *and* word so nothing is colour-only. A "+N" collapse must not hide letters. |

### 2. Findings

#### 2.1 Five of six screens ignore the ratified form width

`DESIGN.md:208`: "Ratified widths, in one place: 5xl shell, 3xl form, lg narrow form." Measured
across the flow:

| Screen | Content width |
| ------ | ------------- |
| `BuildTarget.vue:336` | `max-w-3xl` — compliant |
| `LegacySelect.vue:296` | none; the `<ol>` runs to the shell edge |
| `DeckSelect.vue:246` | none; `grid-cols-1 lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]` full bleed |
| `ScenarioSelect.vue:141` | none; `grid-cols-1 xl:grid-cols-2` full bleed |
| `TraineeSelect.vue:353` | none; `space-y-4` full bleed |
| `Preflight.vue:206,318,360` | none; three full-bleed `dl` grids |

`AppLayout.vue:295` is `flex-1 px-6 py-6` with no max-width, so the shell does not supply one.
This is the owner's "unused horizontal space" observation, and it is a variance from a ratified
number rather than a taste call. The scenario cards and the trainee rows read worst: their `dl`
and chip rows stretch to the full shell width, which is why "Optimizes / Systems / Tracked
resources" in the screenshot carry a very wide gutter between term and value.

#### 2.2 The step bar cannot tell a saved step from an unsaved one

`SetupLayout.vue:69` applies `stepCurrent` only when `index + 1 === props.step` and nothing
otherwise. On step 4, steps 1–3 render identically to steps 5–6: the same `stepLink` class, the
same weight, no mark. The wizard *does* know which steps are saved — `SetupDraft` writes each one
to the session as it lands (`SetupLayout.vue:10-11`) — but no screen passes that back into the
bar, so the bar shows position and nothing else.

The cost is that "have I done this one yet?" is answered by clicking, not by looking, and the
Forward-only navigation means the only way back is a step bar that gives no feedback about where
you have been.

#### 2.3 The step bar is not sticky, and Back is present on two steps of six

`SetupLayout.vue:54-80` puts the bar at the top of document flow. `AppLayout.vue:295` pins
nothing. So on Legacy — the longest screen in the app, with two parent branches, four grandparent
cards and eight spark editors — reaching the step bar or the "Leave setup" link requires
scrolling to the very top.

Back, measured across the six steps:

| Step | Back to the previous step |
| ---- | ------------------------- |
| Scenario (`ScenarioSelect.vue`) | none — it is step 1 |
| Trainee (`TraineeSelect.vue:238-243`) | "Change scenario", which is step 1 and happens to be the predecessor |
| Target (`BuildTarget.vue:317`) | "Change scenario" only; no Back to Trainee |
| Legacy (`LegacySelect.vue:654-657`) | none; "Next: Support deck" is the only link out |
| Deck (`DeckSelect.vue:422-431`) | "Back: Legacy" — present |
| Preflight (`Preflight.vue:402-425`) | "Back" — present, but see below |

Every step writes to the draft, so Back is pure navigation and risks nothing. Steps 3 and 4 are
the two longest forms on the flow and neither offers a way back.

Two related defects sit in Preflight's own link row (`:401-425`):

- **"Back" and "Edit Deck" are the same link.** `:403` and `:415` both bind `props.edit.deck`, so
  two of the four links go to the same place with different words.
- **Two steps have no door at all.** The row offers Edit Legacy, Edit Deck and Edit Target —
  there is no Edit Scenario and no Edit Trainee, so a Trainer who reaches Preflight and sees the
  wrong trainee has to use the step bar (which only scrolls, it does not say what is stored) or
  leave and restart.

#### 2.4 The trainee row carries ten aptitude badges at full weight, on every row

`TraineeSelect.vue:150-161` declares `aptitudeCells` as a static ten-entry list — Turf, Dirt,
Sprint, Mile, Medium, Long, Front runner, Pace chaser, Late surger, End closer — and
`:380-387` renders one `AptitudeBadge` per entry. Ten letter-plus-word chips wrap onto two lines
per row, repeated on every row of the page, in the screenshot reading as a wall of chips that
outweighs each trainee's name.

`design-2.0.md` §17 and `DESIGN.md` §3.1 require letter **and** word so nothing is colour-only,
so the chips cannot be thinned by dropping words. What is not required is that all ten carry
equal weight on a *selection* screen, whose question is "is this the trainee I want?" and not
"what are her aptitudes?" — the latter is `TraineeProfile.vue`, one click away at
`TraineeSelect.vue:434-439`. This is the strongest density finding on the flow.

One code note travelling with it: `aptitudeCells` is a component-local constant, so it is a
second declaration of an axis list the catalog already owns. `ADR-0004`'s element order and
`CatalogController::aptitudes()` are the server's reading of the same axes
(`TraineeSelect.vue:373-376` says as much). A tenth cell added to the schema would not arrive
here without an edit, which is the same class of drift G-33 was written to prevent on the
scenario side.

#### 2.5 `BuildTarget`'s fieldset legends and Summary share one size, so the Summary reads as a field group

`BuildTarget.vue:338,436,499,539` set every `<legend>` at `text-sm font-semibold`. The Summary
heading at `:625` is also `text-sm font-semibold`, so the readout of the whole form — the one
element that should rank above its own fieldsets — is visually identical to four field-group
labels. It also sits last, after the skill-priority fieldset, where a summary is least useful.

#### 2.6 Several absences still use a `title`-only reason

`DESIGN.md:576-585` retires `N/A` carrying only a `title` as the shipped idiom, because "a
`title` is reachable with a mouse and by nothing else". The migration is partial and named as
partial in `SCREEN_SPEC.md`. On this flow the remaining `title`-only absences are:

- `ScenarioSelect.vue:115-116,166,171,178,185,190,195,203,209,243` — the whole fact grid.
- `TraineeSelect.vue:235,249,377-379`.
- `BuildTarget.vue:477` (the entered-target readout) and `:524` (the scenario-publishes note).
- `LegacySelect.vue:8-9` — the Spark chance, which is the screen's headline absence.
- `Preflight.vue:218` and the other `N/A` cells in the three review grids.

Each is a mouse-only reason on a screen whose whole job is to state what has not been recorded.
`AbsenceValue.vue` is the replacement and the pattern is already in use elsewhere.

#### 2.7 Legacy's form is full-bleed while its copy is `max-w-2xl`

`LegacySelect.vue:235` constrains the intro paragraph to `max-w-2xl`. The form that follows
(`:296` onward) has no width at all, so the explanatory copy reads as a column and the fields as
a wall. `BuildTarget.vue` gets this right by constraining the form itself (`:336`).

#### 2.8 Preflight's three review grids use three different column counts for the same kind of data

`Preflight.vue:206` is `sm:grid-cols-2` (Build), `:318` is `sm:grid-cols-2` (Support deck),
`:360` is `sm:grid-cols-3` (Target). Same `dl`-of-cards shape, same `dt`/`dd` treatment, three
column counts with no stated reason, so the Target block reflows differently from the two above
it at the same viewport.

### 3. Proposed changes

Ordered by value against risk.

#### 3.1 Constrain the five unconstrained screens to the ratified width

`DESIGN.md:208`. `BuildTarget.vue:336` is the template: a `max-w-3xl` on the form element
itself, so the copy and the controls share a measure.

- `ScenarioSelect.vue:141` — the `<ul>` gets `max-w-3xl` (or `max-w-5xl` if two columns are
  wanted; §2.1's complaint is the gutter, so a narrower two-column grid beats a wider one).
- `TraineeSelect.vue:353`, `LegacySelect.vue:296`, `Preflight.vue`'s three grids — `max-w-3xl`.
- `DeckSelect.vue:246` keeps its two-column grid, which is a genuine workspace split, but the
  container gets `max-w-5xl`.

**Must not break:** the deck grid's `minmax(0,2fr)` tracks, which prevent a long card name from
blowing the layout; keep them inside whatever container is chosen.

#### 3.2 Make the step bar sticky, add Back, and mark completed steps

`SetupLayout.vue`. One component, all three changes:

- `sticky top-0 z-10 bg-page` on the `<nav>`, with a bottom border already present (`:54`).
  Check against `app.css`'s `scroll-padding-top` for the mobile bottom bar so a focused control
  is not covered by either bar.
- Completed-step state: the step bar needs a `completed: number[]` (or similar) prop fed from
  the draft, so `index + 1 < props.step && completed.includes(index + 1)` gets a distinct class.
  The data is already in `SetupDraft`; the change is a controller prop, not a new query. The
  current step keeps `aria-current="step"`; a completed step should not get `aria-current`,
  which is reserved for the current position in a set.
- Back on the two steps that lack one, Target and Legacy, matching the "Back: Legacy" pattern
  DeckSelect already uses (`DeckSelect.vue:422-425`): a bordered link to the previous step's
  route, before the submit. Navigation only, nothing writing. (Trainee's "Change scenario" and
  Target's "Change scenario" both point at step 1 rather than the previous step; leave them as
  they are and add the Back link beside them, or repoint — an owner call, §5.)

**Must not break:** `aria-current="step"` on exactly one item (`SetupLayout.vue:70`); the
`sr-only` duplicate count line (`:62`); and the `to: null` branch (`:74-77`), which the docblock
records as the honest answer for a step whose slice has not landed.

#### 3.3 Move BuildTarget's Summary above the fieldsets

`BuildTarget.vue:624-633` relocated to sit immediately under the intro paragraph (`:303`), and
raised one step to `text-base` so it ranks above the four `text-sm` legends. It is a live
readout with a `calculated` badge; it belongs where a Trainer checks it, which is before and
during entry, not after four fieldsets.

**Must not break:** the `calculated` badge and its title (`:628-631`) — the summary is tool
arithmetic over entered values and must keep saying so.

#### 3.4 Subordinate the aptitude chips on the selection screen

`TraineeSelect.vue:380-387`. Options, in ascending order of invasiveness:

1. Wrap the chip block so the track and distance axes read at full weight and the style axis sits
   behind a "Show all aptitudes" `<details>`, with the summary carrying the count. The letters
   and words all stay in the DOM; nothing becomes colour-only.
2. Keep all ten visible but at `text-xs` inside the chip, which is a density fix and not a
   contract change.
3. Leave the row alone and put the decision weight on the name and the actions, which are already
   the row's landmarks.

Option 1 is the one that answers the owner's "+N more" intent without touching
`AptitudeBadge`'s letter-and-word contract. It also gives `aptitudeCells` (§2.4) a reason to be
derived from the controller rather than declared locally, since the split by axis is server
knowledge.

**Must not break:** letter **and** word on every badge that renders (`design-2.0.md` §17,
`DESIGN.md` §3.1). No badge may collapse to a colour or to a bare letter.

#### 3.5 Finish the `AbsenceValue` migration on this flow

Six screens, all listed in §2.6. The pattern is `DESIGN.md:576-585`: the marker is a native
`<details>` whose summary carries the reason, so it is one statement in two media.

Two measured constraints travel with it, from the same section:

- The summary keeps its disclosure triangle only while its display stays `list-item`;
  `inline-flex`, `block` and `inline-block` each paint no marker. So the component's own classes
  are not optional.
- `<details>` is flow content and belongs in a field or cell container (`dd`, `div`, `td`, `li`),
  not inline in a `<p>` or a `<span`. Several of the `N/A`s above sit inside `<dd>` already
  (`ScenarioSelect.vue:166` onward), which is the compliant shape; the ones inside prose
  (`TraineeSelect.vue:378`) need the sentence restructured into a row, which is a small design
  change per site.

**Must not break:** `Preflight.vue`'s review grids are a *summary* surface, not an entry surface;
check whether `SCREEN_SPEC.md` §SCR-CAR-010 pins their wording before changing any of them.

#### 3.6 Collapse the scenario card's `key_mechanics` list

`ScenarioSelect.vue:233-238`, behind a `<details>` whose summary states the count ("Key
mechanics (3)"). The fact grid (`:162-213`) stays open — it is the comparison the card exists
for, and `design-2.0.md` §25's "should not become" list is about scenario-specific sprawl, not
about the facts.

**Must not break:** G-33. The count comes from `card.metadata.key_mechanics.length`, a prop, not
from any lookup in the component.

#### 3.7 One column count for Preflight's three review grids

`Preflight.vue:206,318,360` to a single value. `sm:grid-cols-2` is the majority and the Target
block's five stat cells divide by two more awkwardly than by three only if the cells are narrow;
at the ratified `max-w-3xl` (§3.1) two columns is the safer choice.

**Must not break:** the deck grid's `<ol>` semantics (`:318`) — it is an ordered list of slots and
must stay one.

#### 3.8 Fix Preflight's link row

`Preflight.vue:401-425`, the two defects from §2.3:

- The "Back" link (`:402-407`) and the "Edit Deck" link (`:414-419`) both bind
  `props.edit.deck`. Drop one; "Edit Deck" is the more honest name for a link that goes to step 5
  to change a value, so "Back" should either go or point somewhere "Edit Deck" does not.
- Add "Edit Scenario" and "Edit Trainee" so all five earlier steps have a door, or state in the
  row why two do not. `props.edit` already carries the routes it would need
  (`:403,409,415,421`), so this is a prop-list addition in `PreflightController`, not a new
  endpoint.

**Must not break:** `Preflight.vue:5-8`'s contract — "Every fact on this page is read from the
draft the five earlier steps filled… so Back and Edit round trips are lossless by construction".
The Edit links navigate to a step that opens on the draft; adding two more keeps that true.

### 4. What a later pass must not do

1. Draw the ancestry as a picture, or add a drag path — `LegacySelect.vue:14-16`, WCAG 2.2
   SC 2.5.7.
2. Disable or relabel "Start Career" because warnings exist — `Preflight.vue:159-160`; warnings
   never block and a career can start with them.
3. Add a scenario recommendation, a "fit" score, or a fifth purpose — `ScenarioSelect.vue:198-200`,
   `BuildTarget.vue:430` ("a fifth purpose, Competitive Build, which this tool does not record
   yet").
4. Add a risk tolerance or per-skill marks — `BuildTarget.vue:615-620`, a stored-shape change on
   `BuildTargetPayload::KEYS`.
5. Hide a grandparent node, or turn the six-node graph into a free-form tree —
   `LegacySelect.vue:12-16`.
6. Drop the word from an aptitude badge, or colour-code one — `design-2.0.md` §17,
   `DESIGN.md` §3.1.
7. Put a scenario name into a component — gate G-33.
8. Add a dependency (virtualization, an icon set, a modal library) — `AGENTS.md` §5 requires
   human approval.
9. Hide a step or a screen at a breakpoint — `SCREEN_SPEC.md:1841` states the same rule the
   Cockpit answers to.

### 5. Questions for the owner

1. **§3.2 — what marks a completed step?** The draft knows which steps were written, but a
   Trainer can also visit a step and leave without saving. Should "completed" mean *written to the
   draft* (precise, invisible to the user's intent) or *current or previously visited* (approximate
   but honest about the visit)? The two disagree on a Trainer who opens step 2 and leaves.
2. **§3.4 — how much aptitude belongs on a selection screen?** All ten letters are the
   record; the screen's question is narrower. Subordinate, or leave at full weight and let the
   profile screen carry the detail?
3. **§3.1 — `max-w-3xl` or `max-w-5xl` for the scenario cards?** Two columns at `3xl` are
   cramped for a card with an eight-row fact grid; two columns at `5xl` leave the gutter the
   owner objected to. One column at `3xl` is the third option and the least like the screenshot.
4. **§2.3 — should the step bar stay visible on mobile?** `AppLayout.vue:183-187` already carries
   a bottom navigation bar below `md`. A sticky step bar plus that bar is two pinned strips; the
   alternative is sticky at `md` and up only.
5. **§2.3 / §3.8 — what does "Back" mean when the previous step is not the one you want?**
   Preflight's Edit row offers Legacy, Deck and Target but not Scenario or Trainee, so the two
   choices a Trainer most often wants to change are the two with no door. Is the right fix to add
   the two missing Edit links, or to make the step bar carry each step's stored value so the bar
   answers "is this the right trainee?" without a round trip?
6. **§3.2 — where does Back go on Trainee and Target?** Both already carry a "Change scenario"
   link that points at step 1 rather than the previous step. Add a Back beside it, or repoint it?

### 6. Verification plan

| Change | Minimum |
| ------ | ------- |
| §3.1 width, §3.5 absence migration, §3.6, §3.7 | the affected feature tests (`CareerScenarioSelectTest`, the trainee, target, legacy, deck and preflight suites), then `tests/browser/accessibility.spec.ts` |
| §3.2 sticky bar and Back | the same accessibility spec, plus a keyboard pass: tab through the sticky bar, confirm it does not cover a focused control at the bottom of a long Legacy form, and confirm `aria-current="step"` is on exactly one item |
| §3.3 Summary relocation | `BuildTarget`'s own feature tests; the Summary is asserted by its heading id `target-summary-heading` (`BuildTarget.vue:625`) |
| §3.8 Preflight link row | the Preflight feature tests plus a link-count assertion — two of the four links currently resolve to the same href |
| §3.4 aptitude subordination | `TraineeSelect`'s feature tests plus an assertion that every rendered badge still carries both its letter and its word |
| anything touching `SetupLayout` | the full suite — every one of the six screens mounts it |

Nothing here touches a migration, a controller write path, the fetch pipeline or
`config/scenarios.php`. The lore gate runs as usual: this document is prose in `docs/`, where a
`lore-ignore-line` marker is valid but is not itself a ruling.

---

## remaining-screens-ui-audit-2026-10-13.md

> **Consolidated 2026-10-15 (Round 16, owner-ordered).** Source: `docs/proposals/remaining-screens-ui-audit-2026-10-13.md` (646 lines), reproduced verbatim below with headings demoted one level. The original was deleted from `docs/proposals/` after line-by-line verification (0 missing). Cite this anchor, not the deleted path.

## Trainer Desk screens: UI audit (40 page components, 43 routes, plus the three error documents)

> **Reference only. Not authorization.** Nothing in this file is a decision. No component, token, copy
> string, gate or spec line was changed to produce it. Every section names the files an approval would
> touch and touches none of them.
>
> Recorded 2026-10-13 from a full read of every in-scope page and its composed components, plus the
> controllers that build their props. It is the third audit in this directory:
> `docs/proposals/cockpit-ui-audit-2026-10-12.md` owns `SCR-CAR-011` (the Cockpit) and
> `docs/proposals/setup-wizard-ui-audit-2026-10-12.md` owns the six `/career/setup/*` steps. Those two
> surfaces are excluded here, and where a finding is really about the shell they share with everything
> else, it is reported once in §2 and cross-referenced rather than restated.
>
> **Method limit, stated up front.** This is a source audit. No browser, dev server, Playwright run, PHP
> test, build, or lore gate was executed for it, with the two exceptions named in §1.3. Every claim about
> rendered appearance (contrast, computed height, whether a repaint happens, whether a bar moves) is marked
> `needs measurement` and names the measurement that would settle it. `AGENTS.md` §9's evidence rule
> applies: a gate that was not run is reported as not run.

### 0. Scope, standards, and how the findings were produced

#### 0.1 In scope

| Cluster | Routes | Page files |
| ------- | ------ | ---------- |
| A: shell, home, errors | `/`, the shared shell, `404/419/500` | `Dashboard.vue`, `layouts/AppLayout.vue`, `resources/views/app.blade.php`, `resources/views/errors/*` |
| B: reference catalogs | `/umamusume`, `/umamusume/{slug}`, `/skills`, `/skills/{skill}`, `/support-cards`, `/support-cards/{card}` | `Catalog/{Index,Show}`, `Skills/{Index,Show}`, `SupportCards/{Index,Show}` |
| C: the Database hub | `/database` plus `trainees, supports, skills, races, scenarios, events, shop-items, sparks` | `Database/{Index,Trainees,Supports,Skills,Races,Scenarios,Events,ShopItems,Sparks}` |
| D1: run lifecycle | `/training-runs`, `/training-runs/create`, `/training-runs/import` (both states) | `Runs/{Index,Create,Import}` |
| D2: queue and settings | `/review`, `/preferences` (+ `export`, `backup`) | `Review/Index`, `Preferences/Edit` |
| E: Legacy Lab, Veteran library, deck builder | `/legacy`, `/legacy/{run}`, `/legacy/compare`, `/veterans`, `/veterans/{veteran}`, `/veterans/compare`, `/training-runs/{run}/deck` | `Legacy/{Index,Builder,Compare}`, `Veterans/{Index,Show,Compare}`, `Support/Builder` |
| F: career decision screens | `/training-runs/{run}/{training,races,races/planner,skills}` | `Career/{TrainingDetail,RaceDecision,RacePlanner,SkillsPlanner}` |
| G: record, outcome, snapshot | `/training-runs/{run}/{events,inheritance,timeline,result,veteran}`, `/career/snapshot{,/setup,/review}` | `Career/{EventDecision,InheritanceEvent,Timeline,Result,SaveVeteran}`, `Career/Snapshot/{Entry,Setup,Review}` |

Excluded by the brief: `Career/Cockpit.vue` and the seven wizard pages (`ScenarioSelect`, `TraineeSelect`,
`TraineeProfile`, `BuildTarget`, `LegacySelect`, `DeckSelect`, `Preflight`). `Career/Snapshot/*` sits under
`/career/snapshot`, not `/career/setup/`, so it is in scope; it is a second door beside the wizard
(`routes/web.php:99-112`), not a step of it.

#### 0.2 The three standards applied

1. **`DESIGN.md`**, the shipped visual contract. The clauses this audit measured against, with their lines:
   the Design Read and dial `ENERGY 1 / RHYTHM 1 / MOTION 1` (`:24-27`), colour-is-semantic and the
   no-pastel-no-shadow no-marketing principles (`:49-64`), the token roles and their measured pairs
   (`:110-129`, `:145-162`), the type scale (`:188-190`), the ratified widths (`:197-208`), the wide-table
   focusable-region rule (`:209-224`), spacing plus one `h1` plus one primary action plus `rounded-md`
   (`:225-230`), the three motifs and their single homes (`:231-248`), the absence and provenance rules
   (`:567-624`), the lore boundary (`:626-660`), motion (`:679-726`), and the WCAG 2.2 AA floor with the
   44px house bar (`:851-872`).
2. **`antislop` core (`R-01`..`R-38`) plus `antislop-ui`.** For an Operate surface the live rules are the
   Hard Gates `R-02` (no em dash in copy), `R-03` (no overflow, 44px), `R-17`/`R-18`/`R-36`/`R-38` (no
   invented numbers, claims, or disguised placeholders), `R-24`/`R-26` (no dead nav, no dead controls),
   `R-25`/`R-32`/`R-34` (contrast, keyboard, every shipped theme), `R-27` (empty, loading, error), and the
   Purpose-Gate and Quality-Lock pair `R-01`, `R-04`, `R-06`, `R-09`, `R-11`, `R-12`, `R-15`, `R-16`, `R-19`,
   `R-29`, `R-31`. From `antislop-ui` the App and Dashboard tells were the checklist: default dashboard
   shell, stat cards with invented deltas, filler activity feed, chart without a question, generic
   Name/Status/Date columns, filler data in fields, and an empty state that names neither cause nor action.
3. **impeccable `audit` and `critique` rubrics**: the five dimensions (Accessibility, Performance, Theming,
   Responsive, Implementation Integrity) scored 0-4 per cluster, severity `P0`..`P3`, the cognitive-load
   checklist (chunking to four, one focal point, no memory bridge, progressive disclosure that holds
   something back), and the two personas that matter for a single-Trainer analyst desk: Alex (keyboard-first,
   high-volume) and Sam (screen reader, contrast, keyboard-only).

#### 0.3 What was run

Two commands, both read-only, per `AGENTS.md` §9's background rules:

- `npm run typecheck` on 2026-10-13: exit 0, output `> tsc --noEmit`. Its meaning is in §4.1, not here.
- `impeccable detect --json resources/js/pages`: `[]`, zero findings. Also an instrument result (§4.6).

Nothing else ran. The browser suite, `php artisan test`, Pint, PHPStan, and both lore gates are **not run**
for this audit, so no claim in it is gate evidence.

### 1. Score card

#### 1.1 Cluster scores (impeccable's five dimensions, 0-4)

| Cluster | A11y | Perf | Theme | Resp | Integrity | Sum | One-line reason |
| ------- | ---- | ---- | ----- | ---- | --------- | --- | --------------- |
| A shell, home, errors | 3 | 3 | 4 | 2 | 2 | **14** | nav, skip link and both themes are exemplary; the shell applies no ratified measure and the front door's headings invert the scale |
| B reference catalogs | 2 | 2 | 3 | 3 | 2 | **12** | artwork, provenance and filter idioms are correct; absence reasons are mouse-only and the two most frequent actions are full reloads |
| C Database hub | 3 | 3 | 4 | 3 | 2 | **15** | the best-themed cluster; a dead prop path, four header sizes, and an unvalidated config-to-badge index |
| D1 run lifecycle | 2 | 3 | 3 | 3 | 2 | **13** | the index and combobox are the app's model surfaces; the import step bypasses its own form machinery and errors are visual only |
| D2 queue, settings | 3 | 3 | 2 | 2 | 2 | **12** | correct ARIA tablist and live regions; sub-44px primary commit, an irreversible default, and a theme save that does not repaint |
| E Legacy, Veterans, deck | 2 | 3 | 2 | 3 | 2 | **12** | one row shape and one notice constant are strong; the builder is a narrower copy of the wizard's editor and drops recorded ranks |
| F career decisions | 3 | 3 | 3 | 2 | 3 | **14** | caps, glyphs and the guided write are single-owned; motif dose and the type scale are not held |
| G record, outcome, snapshot | 2 | 3 | 2 | 3 | 2 | **12** | the rail disclosures and refusals are right; five surfaces style themselves with tokens that do not exist, and the confirm screen omits identity |
| **Tree (this pass)** | 20 | 23 | 23 | 21 | 17 | **104 / 160** | 65%: impeccable's band for this is *Acceptable, significant work needed*, and the work is concentrated in three dimensions, not spread thin |

Reading the columns matters more than the total. **Theming reaches 23/32 (72%) while Implementation
Integrity sits at 17/32 (53%) and Accessibility and Responsive at 20/32 each.** The palette, tokens, motifs
and both themes are close to the contract; the drift is in the layer where a decision was written down once
and then not carried into the components, plus a set of props that no longer match the server or the child
that consumes them.

#### 1.2 Severity counts

| Cluster | P0 | P1 | P2 | P3 | Total |
| ------- | -- | -- | -- | -- | ----- |
| A | 0 | 2 | 4 | 6 | 12 |
| B | 0 | 2 | 5 | 5 | 12 |
| C | 0 | 2 | 2 | 7 | 11 |
| D1 | 0 | 4 | 2 | 2 | 8 |
| D2 | 0 | 4 | 3 | 1 | 8 |
| E | 1 | 4 | 6 | 1 | 12 |
| F | 0 | 1 | 5 | 6 | 12 |
| G | 0 | 7 | 4 | 1 | 12 |
| **Total** | **1** | **26** | **31** | **29** | **87** |

One P0 (`F-E1`, data loss on the Legacy builder's confirm) is not a design finding at all and is called out
in §3.6 as a defect that belongs in `KNOWN-ISSUES.md`, not in a UI slice queue.

#### 1.3 What the audit found already correct

This is the antislop purpose test run over the whole surface, and the app passes it broadly:

- **None of the classic slop vocabulary exists here.** Sweeps across the eight clusters found zero
  gradients-as-identity, zero glassmorphism, zero glow, zero decorative skeletons, zero `dark:` utilities,
  zero raw hexes in components, zero shadows (the contract bans them at `DESIGN.md:231`), no bento mosaic,
  no stat-card row with invented deltas, no activity feed, no chart placed without a question, and no
  generic `Name/Status/Date/Actions` table with a decorative row menu. The one near-miss is a
  `status_label` column that can only ever hold one value (`F-E12`), which is a column-content defect, not
  the template tell. The marks the app does use (`⚠`, `✦`, `◆`, `★`, and one typographic `i`) are
  glyph-plus-word state markers, not emoji decoration; the `i` is filed at `F-E9`. `DESIGN.md:24-27`'s dial
  `ENERGY 1 / RHYTHM 1 / MOTION 1` is honoured almost to the letter: the two places it is not are filed,
  `F-G10`'s single `animate-pulse` and §4.4's record that the shell does carry a `transition-[width]` the
  contract says the tree contains none of.
- **Swap-the-logo test:** `PRODUCT.md` and the client-vocabulary rules produce a surface that could not
  belong to another product. Torn-page turn chip, argyle lattice bleed on capsule headers, the enamel sheen,
  the orange-up blue-down rule, and the four trust labels are all specific to this tool.
- **Single-owner discipline is real and holds:** `ProvenanceBadge.vue` owns the four marks,
  `AbsenceValue.vue` owns the keyboard-reachable absence, `VeteranRow` is the one shape six surfaces print
  (asserted, not hoped for: `tests/Feature/VeteranLibraryScreenTest.php:78-101`), `ScenarioCaps::forRun` is
  read by both the form's `max` and the request that validates it (`TrainingDetail.vue:410`,
  `StoreTurnEntryRequest.php:70,81-85`, closing `ADR-0015`), and `TraineeCombobox.vue` is a genuine combobox
  with `aria-activedescendant` resolved only to a visible option.
- **Honesty under missing data is the strongest trait in the product.** Held computations render as refusals
  with the ruling as the reason (`RaceDecisionController.php:250-256`, `SaveVeteran.vue:273-283`,
  `AncestryGraph.php:203-209`), no screen prints a score, band, chance or prediction, and empty states name
  the cause and the command that fills them (`SkillCatalog.vue:130-149`, `SupportCardCatalog.vue:158-178`,
  `Support/Builder.vue:396-410`).
- **Accessibility plumbing exists and is tested:** one base-layer `:focus-visible` ring (`app.css:334-337`),
  skip link to a real `#main`, `scroll-padding-bottom` under the pinned mobile bar, a reduced-motion block,
  axe A+AA scans scoped to `#app` including one mid-visit, 44px sweeps with a shared helper
  (`tests/utils/tap-targets.ts`), `lang="ja"` on Japanese runs, and focus return on every disclosure.

The findings below are therefore not "the app looks AI-generated". They are the narrower, more fixable set:
rules that shipped in one place and not in another, channels a keyboard user cannot reach, and controls
whose stated behaviour and actual behaviour disagree.

### 2. Cross-cutting findings

Eight themes account for most of the weight. Each merges the cluster rows named in its own list; the cluster
tables in §3 keep the per-site citations.

#### 2.1 X-1. An absence's reason is still mouse-only on 40 of 43 routes (P1)

`DESIGN.md:576-585` settled this: the marker is a native `<details>` whose summary carries the reason as its
accessible name, because "a `title` is reachable with a mouse and by nothing else". The component shipped
(`AbsenceValue.vue`), and the migration stalled.

- **Measured on this tree:** `AbsenceValue` is imported by **10 files**. The clusters enumerated
  **roughly 100 unmigrated `title`-only sites** between them: `F-E3` lists 37 lines across 7 files, `F-G7`
  lists 26 across 4, `F-F1` 11 across 6, `F-B2` 9 across 5, `F-C2` 8, `F-D23` 5, `F-A1` 4 plus the shell's
  version absence that repeats on all 43 routes, `F-D1` (whole columns render blank rather than `N/A`).
- **The register understates it.** `SCREEN_SPEC.md:3341-3359` item 17 records "**17** single-line sites
  across 9 files" and names only `TrainingCard`, `RunRaceStrip`, `SkillsPlanner`, `RaceCard`, `SaveVeteran`,
  `AncestryNode`, `UraPanel`, `Result`, `ScenarioPanel`. Its own text already concedes that multi-line sites
  are out of a single-line sweep's reach; this pass found that the un-swept remainder includes every catalog,
  Database, Legacy, Veteran, Dashboard, Review and Preferences screen. Not one of those files appears in the
  register's count.
- **Impact.** The reason is the half that tells the Trainer whether to go enter a reading or stop looking.
  `Legacy/Compare.vue:262` ("This Legacy's own star count was not recorded") and
  `Veterans/Compare.vue:265` ("no source publishes this trainee's aptitude letter") are different
  instructions, and a screen-reader user hears only "N/A" for both.
- **Fix, once:** migrate the call sites to the existing component, `compact` inside cells and rails, passing
  each site's current sentence verbatim. The three placements and display constraints in `DESIGN.md:587-600`
  are the reason this cannot be a blind sed pass, and the specs that pin the current idiom by
  `getByTitle(...)` must move with it, which needs owner approval per `AGENTS.md` §9.
- Merged rows: `F-A1`, `F-B1`, `F-B2`, `F-C2`, `F-D1`, `F-D23`, `F-E3`, `F-F1`, `F-G7`.

#### 2.2 X-2. The type scale never landed, and half the pages print their title twice (P2)

`DESIGN.md:188-190` specifies section headers `text-lg font-600` and a page title `text-2xl font-600`, one
`h1` per page. The shell renders `#title` at `text-base` (`AppLayout.vue:277-281`; cockpit audit §2.1), so
every page starts one to two steps low.

- **Measured:** **75** `<h2>`/`<h3>` elements carry `text-base font-semibold` across 29 files in
  `resources/js`, and **30** sites already use the specified `text-lg font-semibold`. Both treatments ship at
  once, which is worse than uniform drift: `Database/ShopItems.vue:155` is compliant and its sibling
  `Database/Events.vue:69` is not.
- **Duplicated titles:** `Skills/Index.vue:37` prints "Skill search" as `#title` and `SkillCatalog.vue:74`
  prints the identical string as a `text-2xl` `h2`; same for `SupportCards/Index.vue:49` with
  `SupportCardCatalog.vue:91`, `Support/Builder.vue:257,259` (same string twice on one page), `Review/Index.vue:33,35`
  and `Preferences/Edit.vue:163,165` (two different names for one page), the eight Database areas
  (`F-C4`), and `Catalog/Index.vue:50`, whose `h1` reads "Database" while the sidebar it came from reads
  "Database", the visible heading reads "Umamusume catalog", and `SCREEN_SPEC.md:695` calls the entry point
  "Nav 'Catalog'".
- **Inverted case:** four clusters report section headers rendered as `text-xs font-bold uppercase
  tracking-wide text-ink-muted` (`Dashboard.vue:112,201,246,297,313`, `Veterans/Show.vue:47,142,186`,
  `Legacy/Index.vue` region labels, `Snapshot/Setup.vue`). That is `antislop-ui`'s "generic AI typography"
  tell (uppercase with wide tracking, `R-06`) applied to a heading, and it puts the section label below the
  body size while the data beside it is `text-lg` or `text-2xl`. On the Dashboard the largest words on the
  page are three numbers set in `<p>`.
- **Fix:** one shell decision (cockpit audit §3.1), then per page either delete the duplicate heading or
  demote it to the section step, and promote the eyebrow labels to `text-lg font-semibold text-ink-strong`,
  which is already the shipped class pair elsewhere. Do not restyle pages before the shell decision, or the
  spread widens.
- Merged rows: `F-A3`, `F-B6`, `F-C4`, `F-C5`, `F-D27`, `F-E10`, `F-F5`, `F-G12`.

#### 2.3 X-3. The enamel motif lost its single home (P2)

`DESIGN.md:231-248` rules the gloss **material** and names exactly one home: the primary action button.
`:228` rules one primary action per screen.

- **Measured:** **52** `enamel` occurrences in `resources/js`. Screens carrying more than one: all four of
  cluster F (`TrainingDetail.vue:634-640` plus five per-card `Train` buttons; `SkillsPlanner.vue` has three
  co-equal writes), cluster E's `Remove Spark` button, which wears the gloss on a **destructive** control
  while `Add Spark` beside it is plain (`Legacy/Builder.vue:384` versus `:395`), `Veterans/Index.vue:154,200`,
  `Support/Builder.vue:307,379`, `Result.vue:341,355` (a read and a write side by side, equally glossed),
  `SaveVeteran.vue:304,333,398` (tag chips, an add button, and the save), and `Timeline.vue:110` filter chips.
  The error documents' secondary anchors use `rounded-full border-2` (`F-A9`), so a pill shape appears on
  non-primary controls in exactly the places the product already failed.
- **Impact.** The one affordance that means "press this to finish" now marks selection steps, toggles,
  deletes and secondary reads. Hierarchy is carried by borders alone, and the motif's scarcity is what spent
  its signal.
- **Fix per screen:** keep enamel on the completion write, restyle the rest with the bordered secondary pair
  the same files already use. No new token, no new class. Where a screen genuinely owns several writes
  (the skills planner), that is an owner call about whether `one primary action per screen` binds a
  multi-write screen, which §6 asks.
- Related colour findings in the same family: the goal pennant's `goal-line` hue borrowed for obligations,
  warnings, and a learned list (`F-F2`), a deck error band painted in the action green (`F-E8`), the success
  flash painted action-green (`F-A7`), and `animate-pulse` on one milestone (`F-G10`; my own grep confirms
  `InheritanceEvent.vue:156` is the only `animate-*` in the tree, against `DESIGN.md:679-726`'s MOTION 1).
- Merged rows: `F-A9`, `F-E7`, `F-E8`, `F-F2`, `F-F3`, `F-G9`, `F-G10`.

#### 2.4 X-4. Writes that are refused, or that fire twice, are silent (P1)

`AGENTS.md` §13 requires every data view to render its error state, and `ADR-0007` gives user-initiated
writes their own feedback. Four distinct mechanisms fail:

1. **Errors the page never prints.** `F-D2`: eight of the eleven import columns have rules in
   `ImportHistoricalRunRequest.php:134-138` and no render path in `Import.vue`, which reads only five stat
   names plus `mood` and `energy`. `F-E2`: the deck page reads only `errors.deck`, while the common refusal
   (a card not in the catalogue) is attached to `deck.2.support_card_id`, a key the page never touches;
   `tests/Feature/SupportDeckBuilderTest.php:265` pins that key as the shape that arrives. `F-G5`: a rejected
   snapshot lands back on a Review page that renders no error text at all.
2. **The control does not say it is working.** `F-D3`: the import commit posts through `router.post`, outside
   the page's own `useForm`, so `form.processing` never becomes true, the button never disables, and a second
   press writes a second run from the same 200-row payload (no idempotency key at
   `TrainingRunController.php:1088-1099`).
3. **The warning hides when the risk arrives.** `F-E4`: "Confirming replaces the whole deck" is gated on
   `analysis.covered > 0`, so the moment a Trainer empties the slots, the only sentence about the pending
   destructive write disappears.
4. **Focus is not managed, and defaults commit writes.** `F-F9` (one of four screens manages focus on a
   refused write, three do not), `F-A8` (the shell's `role="status"` node is created in the same tick as its
   message, so announcement depends on engine behaviour), `F-D21` (the review card preselects `Confirmed`,
   the one verdict that writes a catalog row, with no confirm step and no view that still lists the row).

- **Fix pattern, once:** iterate the error object the page already receives rather than a hardcoded key list,
  route every write through the page's own `useForm` so `processing` exists, gate a destructive warning on
  the pending write rather than on current data, and adopt the shipped refusal idiom
  (`role="alert" tabindex="-1"` plus focus move, `EventDecision.vue:325-336`).
- Merged rows: `F-D2`, `F-D3`, `F-F9`, `F-A8`, `F-A7`, `F-E2`, `F-E4`, `F-G3`, `F-G5`, `F-D21`, `F-D24`.

#### 2.5 X-5. Form and control semantics that only a mouse sees (P1)

`F-D4`: seven error paragraphs on `Runs/Create.vue` and four on `Import.vue` are unattached, `aria-invalid`
and `aria-describedby` are absent while `CandidateForm.vue:40-41` ships the correct association elsewhere in
the same tree. `F-G4`: `Snapshot/Setup.vue:183-215` wraps a number input and an "I don't know this value"
checkbox in one `<label>` with no `for` or `id`, so the input's accessible name is the whole block and the
checkbox is unnamed. `F-D22`: the review queue's primary commit is an `enamel ... px-3 py-1.5` pill with no
height floor, in a screen whose sibling (`Preferences/Edit.vue`) carries `min-h-11` on all ten controls;
`tests/browser/review.spec.ts` has no target sweep and no axe scan while `preferences.spec.ts:237-248` has
both. `F-E11`: `/legacy` and `/veterans` pagers are `px-3 py-1` anchors while the deck and support-catalog
pagers on the same product carry `min-h-11`. `F-G11`: the star-roll table scrolls in a bare
`overflow-x-auto` div with no `role="region"`, `tabindex="0"` or label, one call site away from the correct
wrapper at `Result.vue:251-253`. `F-F8`: a disabled Compare toggle's class list is identical to the
unselected one, so the fifth pick fails silently.

- **Fix:** reuse the four idioms already in the tree (`describedBy()` at `Preferences/Edit.vue:217`,
  `tests/utils/tap-targets.ts` for the sweep, `Result.vue:251` for the region, `SkillPlanRow.vue:113`'s
  `disabled:opacity-40`). No new component.
- Merged rows: `F-D4`, `F-D22`, `F-E11`, `F-F8`, `F-G4`, `F-G11`.

#### 2.6 X-6. Copy that is addressed to a repository contributor (P2)

- `F-G2` (P1): two **em dashes** in shipped, server-composed copy at `TimelineController.php:240` and `:270`,
  rendered verbatim by `CareerTimeline.vue:208,213`. `AGENTS.md` §5 and `R-02` both ban them, and the
  sibling surface spells the same state with a hyphen (`EventCard.vue:38`). Why it survived is an instrument
  finding, not a judgement call: see §4.2.
- `F-B3`, `F-D28`: literal backticks around `php artisan uma:fetch ...` render as punctuation in Trainer
  sentences on four surfaces, while `Catalog/Show.vue:328` uses a real `<code>` for the same class of key.
- `F-D28`: the Settings screen prints `(PRD US-11, PRD §6 non-goal 12)`, "`ADR-0001` §3 records...",
  "which `AGENTS.md` §5 scopes to the owner", and "(design-2.0 §48)" in body copy. Those citations are true
  and they are not user-facing language (`R-16`).
- `F-B7`: one release fact in five spellings, two of them internal notation: `available on [Global]`, a `dt`
  reading "Released on [Global]", raw column values `JP-only` beside the terms-map `Japan only`, and
  `(Global)`. The bracketed tag is this repository's documentation notation (`AGENTS.md` §1), not a client
  word. `F-B10`: `AptitudeGrid.vue:9-23` re-types "Front runner" where `lang/en/uma.php:109-112` and
  `Umamusume.php:141` both say "Front Runner".
- `F-E5` (P1): `Veterans/Show.vue:104` labels the same integer "Skills learned" that
  `Veterans/Compare.vue:249-252` labels "Skills recorded" *and explains is not "learned"* (the pivot holds
  Suggested and Skipped rows). One label overstates the data.
- `F-A10`: the 419 document's title and `h1` say "Sign-in session expired" in a product with no sign-in
  (`AGENTS.md` §12), while the body copy two lines down explains exactly that.
- `F-A6`: the Dashboard's DATA STATUS panel hard-codes `'Current'` (`DashboardController.php:252`) while the
  adjacent source string appends "source marked stale" when `config('scenarios.php:51')` flips. The panel
  that answers "can I trust this data" contradicts itself the first time the flag is set.
- Merged rows: `F-A6`, `F-A10`, `F-B3`, `F-B7`, `F-B10`, `F-D28`, `F-E5`, `F-G2`.

#### 2.7 X-7. Props that no longer match the child, the server, or the token set (P1, P2)

The cluster files list these individually; the pattern is one worth naming because nothing catches it.

- `F-G1` (P1): `bg-warning` / `text-on-warning` / `text-warning` are used at **eight sites in six files**
  (`EventDecision.vue:286,421`, `Result.vue:296,302`, `CareerTimeline.vue:104`, `EventCard.vue:36,61`,
  `ScenarioStatusBadge.vue:36`). There is no `--color-warning` role anywhere in `app.css`'s theme block, so
  Tailwind v4 emits no utility and each renders as unstyled text. Verified by grep of `resources/**` on
  2026-10-13. The product's only "this record is incomplete" signal is invisible; the ⚠ glyph and the words
  survive, so it is a lost affordance rather than lost content. The declared pair for this job is `risk`
  (`DESIGN.md:127`), and the same pages already use it (`EventDecision.vue:74,330`).
- `F-C1` (P1): `Database/ShopItems.vue:79` passes `:recheck="props.recheck"`; the page's own props interface
  at `:40-45` does not declare it, and the controller does send it (`DatabaseController.php:255-258`). The
  Shop Items area silently loses the re-check pointer that Events and Sparks both render, breaking the
  "every reference view carries one" rule its own component docblock states.
- `F-G6` (P1): `EventCard.vue:67` renders `JSON.stringify(props.deltas ?? {})` for a prop that is neither
  declared (`:8-20`) nor sent (`EventDecisionController.php:177-191`), so a delta-only recorded outcome
  prints the literal `Deltas: {}`.
- `F-F11`, `F-E12a`, `F-G8`, `F-D1` (runs.total unused), `F-D8`: page-local interfaces missing fields their
  children require (`RacePlanner.vue:54-60` versus `RacePlanList.vue:43`), `veteran_url` sent and ignored,
  an `after` value built per row and rendered nowhere, a prop pinned by a test and never printed, and a
  provenance string written by the commit that the confirm step never shows.
- Merged rows: `F-C1`, `F-C10`, `F-D8`, `F-E12`, `F-F11`, `F-F12`, `F-G1`, `F-G6`, `F-G8`.

#### 2.8 X-8. Measure and width: the contract's three ratified numbers are used by almost nobody (P2)

`AppLayout.vue:295` is `<main id="main" class="flex-1 px-6 py-6 pb-24 md:pb-6">` (verified by read): no
`max-w` of any kind, so on the 1280px+ primary target every page's prose runs to the shell column's full
width. `F-A2` notes the three standalone error documents **do** constrain themselves to
`mx-auto max-w-5xl`, so the copies are the compliant ones. `F-F6`: none of the four career decision screens
constrains prose or form width; the wizard audit §1.2 found the same for five of six wizard steps.
`F-D6` and `F-G12` find `max-w-2xl`, a value `DESIGN.md:197-208` does not ratify, used in three places
(`Runs/Import.vue:147,235`, `Snapshot/Entry.vue:18`, `Snapshot/Review.vue:57`) while `SCREEN_SPEC.md:576`
records `max-w-2xl` as the shipped import form card, which makes the doc list the stale side per
`AGENTS.md` §2. Wide tables are the one class of surface that is right: they scroll inside a focusable
labelled region and are never reflowed (`F-C` "Already correct", `Result.vue:251-259`,
`RacePlanner.vue:219-224`).

### 3. Findings by cluster

Each row is the cluster's own finding, with its citations. Fix text is compressed; the full version of each,
including the alternative that was rejected and why, is in the pass notes for that cluster. Severity is the
impeccable scale. "Guarded" names the tests or specs an approval would have to move with the change
(`AGENTS.md` §9: no test is deleted or weakened to make a change pass).

#### 3.1 Cluster A: shell, home, errors

| ID | Sev | Finding | Files | Fix | Guarded |
| -- | --- | ------- | ----- | --- | ------- |
| F-A1 | P1 | Home-screen absences are mouse-only | `Dashboard.vue:133-137,154-161,170-177`, `AppLayout.vue:291-293` | `AbsenceValue :reason compact` | `dashboard.spec.ts:142-148` |
| F-A2 | P1 | The shell applies no ratified measure to `main` | `AppLayout.vue:295`, errors constrain themselves (`404:38`) | `mx-auto w-full max-w-5xl` on `main` | `dashboard.spec.ts:195-224`, `veterans.spec.ts:40-43` |
| F-A3 | P2 | Five `h2`s at `text-xs uppercase`, bodies at `text-lg`/`text-2xl` | `Dashboard.vue:112,201,246,297,313` | promote to `text-lg font-semibold`, demote the count `dd`s | `dashboard.spec.ts:87-92,130` (accessible names) |
| F-A4 | P2 | RECENT VETERANS' only two CTAs point at `/legacy` while `/veterans` is one click away, and the empty state contradicts the library's own filing sentence | `Dashboard.vue:246-291`, `DashboardController.php:201`, `VeteranController.php:78` | point `library_url` at `veterans.index`, reuse `filingNotice` | `dashboard.spec.ts:79,115`, `veterans.spec.ts:100-130` |
| F-A5 | P2 | The mobile bar's More disclosure never signals that the current page lives inside it | `AppLayout.vue:128-129,186-187,344` | one computed over `moreItems` using the existing `isCurrent()`, plus an `sr-only` word | `veterans.spec.ts:35,51`, `KeyboardPathTest.php:69` |
| F-A6 | P2 | DATA STATUS prints a literal `Current` beside a stale flag | `DashboardController.php:248-272`, `config/scenarios.php:41,50-51` | compose `state` from the same flag | `DashboardTest.php:57-60`, `dashboard.spec.ts:91-92` |
| F-A7 | P3 | The success flash paints confirmation in the action green | `AppLayout.vue:296-302`, `app.css:57-61` | `border-rule bg-raised`, the pair `Cockpit.vue:464` uses | `TokenPairHygieneTest.php:93` |
| F-A8 | P3 | The `role="status"` region is created in the same tick as its message | `AppLayout.vue:296-302` | keep one region mounted, bind only its text | four specs resolve `getByRole('status')` as a single node |
| F-A9 | P3 | Error-page secondary anchors are `rounded-full border-2` | `404:52-59`, `419:49-52`, `500:46-49` | copy `Dashboard.vue:236`'s secondary pair | `ErrorPageViewsTest.php:47,110-113` |
| F-A10 | P3 | 419 title says "Sign-in session expired" in a product with no sign-in | `419.blade.php:17,35` (its own comment says so) | retitle to the cause the body states | `ErrorPageViewsTest.php:41-52` asserts that string |
| F-A11 | P3 | The visit indicator may paint a static bar | `VisitProgressBar.vue:27-31` `appearance-none` with no `::-webkit-progress-value` | measure first, then at most a `bg-idle` track | `accessibility.spec.ts:42-59` |
| F-A12 | P3 | The third motif has no `@utility`; the turn chip is six class lists in one component | `app.css:291-323` defines `enamel` and `lattice-bleed` only; `ResourceStrip.vue:106-119` | `@utility turn-chip` beside `lattice-bleed` | `DesignTokensTest.php:184-205` |

#### 3.2 Cluster B: reference catalogs

| ID | Sev | Finding | Files | Fix | Guarded |
| -- | --- | ------- | ----- | --- | ------- |
| F-B1 | P1 | Skill mechanics: `{{ group.condition ?? 'N/A' }}` prints a bare marker and `v-if` drops the precondition and base-time rows entirely | `Skills/Show.vue:115,118,123`, `SkillController.php:129` | keep the rows, render through `AbsenceValue` | `skills.spec.ts:75-79` |
| F-B2 | P1 | Nine absence reasons are `title`-only across the three details and two lists | `Catalog/Show.vue:165,235,242`; `Skills/Show.vue:76,84`; `SupportCards/Show.vue:126,134,145-150`; `SkillRow.vue:22` | swap to `AbsenceValue` in the `dd`; the two in-sentence sites are blocked until the sentence becomes a row (`DESIGN.md:597-600`) | `skills.spec.ts:69-72` pins `[title=...]` and `toHaveText('N/A')` |
| F-B3 | P2 | Markdown backticks render as characters in remedy sentences on all six screens | `Catalog/Show.vue:146,275,281,330`, `TraineeCatalog.vue:123`, `SkillCatalog.vue:135-136`, `SupportCardCatalog.vue:167-168,260` | delete the backticks, use the existing `<code>` idiom | `EmptyStateCommandNamesTest.php:60,81-82` (pattern survives) |
| F-B4 | P2 | Catalog index prints no result count and one message for "empty catalog" and "no match" | `TraineeCatalog.vue:119-124,39-43`, `CatalogController.php:171-182` | port the two-state split and the `X of Y` line the siblings ship | `catalog.spec.ts:22-26`, `SCREEN_SPEC.md:703,719` |
| F-B5 | P2 | Row drill-in and pager are full document reloads on screens that already use Inertia `Link` | `TraineeCatalog.vue:145,196`, `SkillCatalog.vue:160,187`, `SupportCardCatalog.vue:218,244`, `SkillRow.vue:12` | `Link` with `preserve-scroll` for internal destinations; external source URLs stay anchors | `skills.spec.ts:57`, `navigation-determinism.spec.ts:75-90` |
| F-B6 | P2 | Three index screens print their page title twice, in two sizes, and one pair of names disagrees | `Skills/Index.vue:37` vs `SkillCatalog.vue:74`; `Catalog/Index.vue:50` vs `TraineeCatalog.vue:88` | one string per screen, owned by the page | `catalog.spec.ts:10`, `support-cards.spec.ts:11-17` |
| F-B7 | P2 | One release fact, five spellings, two of them internal notation | `SkillCatalog.vue:152`, `SupportCards/Show.vue:131`, `Catalog/Show.vue:331,180`, `SupportCard.php:69` | route through `ReleaseStatus::label()` and extend `lang/en/uma.php`; add server-side availability words like `typeWords()` already does | `support-cards.spec.ts:50,87`, `catalog-detail.spec.ts:95-104` |
| F-B8 | P3 | The unique-skill fact carries `◆` on the index and `✦` on the detail | `SkillCatalog.vue:168-172` vs `SkillRow.vue:15-19`, `Skills/Show.vue:91-95` | one glyph; `✦` is the pinned one | `skills.spec.ts:66-67` |
| F-B9 | P3 | Two date formats in one Provenance section (`M j, Y` at `CatalogController.php:353`, `Y-m-d H:i T` at `:388`) | `Catalog/Show.vue:397-403`, `Skills/Show.vue:184` | one formatter for the read-instant, after the owner ruling `AGENTS.md` §16 asks for | `CatalogRosterTreeTest.php:541-565`, `CatalogDetailPageTest.php:228` |
| F-B10 | P3 | Running-style words re-typed in the component, in the wrong case | `AptitudeGrid.vue:9-23` vs `lang/en/uma.php:109-112`, `Umamusume.php:141` | pass `aptitudeLabels()` through and delete the local list | five tests compare `Front Runner` |
| F-B11 | P3 | Three filter surfaces, two focus implementations | `SkillCatalog.vue:87,96,110`, `SupportCardCatalog.vue:101,115,127,139` vs `TraineeCatalog.vue:98,103,109` | delete the redundant per-control utilities; the base ring owns it | 44px sweeps, axe |
| F-B12 | P3 | The cluster's densest label colour is outside the contrast contract | `g18ContractPairs()` at `DesignTokensTest.php:298` carries one `ink-muted` pair | add `ink-muted on raised` and `ink-muted on panel`, let the gate answer | `DesignTokensTest.php:532` (list must match the contract) |

#### 3.3 Cluster C: Database hub and eight areas

| ID | Sev | Finding | Files | Fix | Guarded |
| -- | --- | ------- | ----- | --- | ------- |
| F-C1 | P1 | Shop Items passes a prop its interface never declares, so the re-check pointer it must have never renders | `ShopItems.vue:40-45,79`, `DatabaseController.php:255-258`, `SourceNote.vue:16,24-34` | add the missing prop line; the data already arrives | `DatabaseTest.php:136-156` |
| F-C2 | P1 | Races and Skills state absence reasons by mouse only | `Races.vue:118-140`, `SkillCatalog.vue:171-180` | `AbsenceValue` with `compact` | `database.spec.ts:179-185` pins `getByTitle` |
| F-C3 | P2 | Config state strings index the badge with no validation: one typo renders a whole reference view | `DatabaseController.php:188,230,292`, `ProvenanceBadge.vue:54-61`, `config/reference.php:28-34` | one whitelist in the three loops, falling back to `unknown` | `DatabaseTest.php:117-135` |
| F-C4 | P2 | Every area pairs a small `h1` with a larger duplicate `h2` | five areas `text-2xl` under `AppLayout.vue:281`; shared lists print the standalone heading | demote or delete the duplicates | `database.spec.ts:100,117,150,158,195` pin `level: 2` |
| F-C5 | P3 | Four section-header sizes coexist inside one hub (`text-xl`, `text-lg`, `text-base`, `text-sm`) | `Scenarios.vue:68,90,119,133`, `ShopItems.vue:155`, `Sparks.vue:177`, `Events.vue:69,85` | set the drifted ones to the size the cluster already ships | `database.spec.ts:200-201` |
| F-C6 | P3 | Hub area links hover to the colour they are already on | `Index.vue:37,42` (`bg-raised` ground, `hover:bg-raised`) | `hover:bg-panel`, the pair Events and Sparks use | `database.spec.ts:29-53` |
| F-C7 | P3 | Pagination is the same block copied into four files, as raw anchors that reload the document | `Races.vue:159-171`, `TraineeCatalog.vue:194-206`, `SkillCatalog.vue:185-197`, `SupportCardCatalog.vue:242-254` | one pager, or `Link`; keep `withQueryString` | 44px sweep at `database.spec.ts:268-285` |
| F-C8 | P3 | `TraineeCatalog`'s empty state diverges from its two siblings (no count line, no source argument, one message for two states) | `TraineeCatalog.vue:119-124` vs `SkillCatalog.vue:130-152` | port the two-state split, name the source from `config('uma.sources')` | `database.spec.ts:142-151` |
| F-C9 | P3 | Events and Sparks drawers disclose nothing the row does not already print | `Events.vue:124-141`, `Sparks.vue:141-161` vs `ShopItems.vue:131-143` | delete the drawer, or move one extra sentence into it | `database.spec.ts:77-94` pins the drawer path |
| F-C10 | P3 | The four-state union is hand-re-declared far past the file count that documents it | `ProvenanceBadge.vue:12` plus 18 more files, 49 literal sites measured (§4.4) | alias once per file; report the doc figure as stale | the `defineProps` compiler limit |
| F-C11 | P3 | Redundant per-control focus outlines beside the base ring | as `F-B11`, in the Database-rendering copies | delete | needs measurement (which ring paints) |

#### 3.4 Cluster D1: runs index, create, import

| ID | Sev | Finding | Files | Fix | Guarded |
| -- | --- | ------- | ----- | --- | ------- |
| F-D1 | P1 | The import preview prints **blank cells** where the file has no value, indistinguishable from a dropped column | `Import.vue:65-67,200-211`, `TrainingRunController.php:1185` | `AbsenceValue` with `compact` on the null branch only; present cells stay raw | `run-import.spec.ts:80-92`; `SCREEN_SPEC.md:632` needs a §7 entry |
| F-D2 | P1 | Eight of eleven validated import columns produce errors the page never renders | `Import.vue:59,80-101,321-322`, `ImportHistoricalRunRequest.php:134-138` | drive the existing `rowErrors` loop from the `headers` prop, so a column cannot exist without a render path | `run-import.spec.ts:97-107` pins the composed sentence |
| F-D3 | P1 | The confirm step posts outside `useForm`: no pending state, no disable, and two presses write two runs | `Import.vue:127-139,217-222`, `TrainingRunController.php:1088-1099` | post through the page's own form (or one `submitting` ref) with the `:disabled`/`aria-busy` pair the step-one button already has | `run-import.spec.ts:81,131` |
| F-D4 | P1 | Field errors and required state on both forms are visual only | `Create.vue:107-212`, `TraineeCombobox.vue:456,460`, `Import.vue:243-283` | `aria-invalid` plus `aria-describedby` using the shipped idiom | `runs.spec.ts:76-107` (the combobox's ARIA surface) |
| F-D5 | P2 | Two scenario-less runs of one trainee give the links list the same name twice; status and date sit outside the anchor | `Index.vue:67-75`, `TrainingRunController.php:62,66-68` | `aria-label` from props already on the row, or move the meta span inside the link | `RunsIndexTest.php:69-76`, `NoStaleRunRecordLinksTest.php` |
| F-D6 | P2 | The confirm half carries no width; the page uses `max-w-2xl`, which the ratified list does not name | `Import.vue:147,157-165,235`, `DESIGN.md:197-208`, `SCREEN_SPEC.md:576` | apply `max-w-3xl` to the prose halves; leave the table region as a scroll contract | `run-import.spec.ts:57-73,87-92` |
| F-D7 | P3 | Index row and preview table run at the smallest type in the app, with `font-mono` on prose | `Index.vue:73-75`, `Import.vue:198,207,307` | `text-sm` with mono on the numerals only, the split `Import.vue:207` already makes | `runs.spec.ts:41-56`, `DesignTokensTest.php:126-153` |
| F-D8 | P3 | The commit stores a provenance string (`pasted CSV (a3f9c1d2)`) the preview never shows | `TrainingRunController.php:1073-1096`, `ImportHistoricalRunRequest.php:205-222` | pass `sourceLabel()` into the preview payload and append it to the identity line | `run-import.spec.ts:80-95,124-141` |

#### 3.5 Cluster D2: review queue, preferences

| ID | Sev | Finding | Files | Fix | Guarded |
| -- | --- | ------- | ----- | --- | ------- |
| D21 | P1 | The verdict defaults to `Confirmed`, the one option that writes a catalog row, with no confirm step and no view that still lists the row | `CandidateForm.vue:16-20,77-83`, `ResolveMatchCandidate.php:31-47`, `ReviewController.php:30` | start the field empty with a `Not set` option; the request's `required` rule already exists | `ReviewQueueTest.php:23-62`, `review.spec.ts:40-43` |
| D22 | P1 | The queue's primary commit is the only enamel action in the tree with no height floor, on the screen with no target sweep and no axe scan | `CandidateForm.vue:42,58,68,80`, `Review/Index.vue:83,87` | add the shipped `inline-flex min-h-11 items-center`, sweep with `tests/utils/tap-targets.ts` | `review.spec.ts:21-23` |
| D23 | P1 | Five Settings absences, including the Restore and Reset refusals, are `title`-only | `Preferences/Edit.vue:293-298,358-363,416-430,452-458` | `AbsenceValue` without `compact` (settings rows are not dense cells) | `preferences.spec.ts:114-169` asserts those exact `title`s |
| D24 | P1 | Saving a theme change confirms success and changes nothing on screen | `Edit.vue:137-153,166-170,211-225`, `app.blade.php:7`, `PreferenceController.php:122`; no `data-theme` write exists in `resources/js` | the server already owns first paint, so widen the flash to name the effect rather than adding a client write | `PreferenceControlsTest.php:53-71`, `preferences.spec.ts:41-67` |
| D25 | P2 | The merge target is a bare numeric id while the app ships a trainee combobox; a wrong-but-existing id is unreversible | `CandidateForm.vue:49-59`, `ResolveMatchCandidate.php:32-34`, `Review/Index.vue:65-67` | reuse `TraineeCombobox.vue`, or at minimum print the suggested row's id | `ReviewFormAccessibilityTest.php:19-29` |
| D26 | P2 | One empty state covers "never fetched" and "you just cleared thirty", and tells the Trainer to run a fetch in both | `Review/Index.vue:41-46`, `ReviewController.php:27-33,64` | branch on the pending count; the cleared branch names where the promoted rows now live | `review.spec.ts:12-14,34-37` detect on `Nothing pending.` |
| D27 | P2 | Both pages print a `text-2xl` `h2` under the shell `h1`, with different names; the tier badge is the smallest item on the row | `Review/Index.vue:33,35,57-60`, `Edit.vue:163,165` | delete the duplicate `h2`s; one visible badge step, mono for the date tail | `preferences.spec.ts:11,121-122` |
| D28 | P3 | Governance citations, literal backticks, `space-y-6`/`space-y-4`/`-mt-2` rhythm, and two identically-styled primary and backup buttons | `Review/Index.vue:45`, `Edit.vue:166-170,173,241-247,286,360,407,418,427,455,482` | strip the citations, keep both sentences the specs assert, use `space-y-3`, gloss only the submit | `preferences.spec.ts:112-113,114-115,123,137,140,160,169,181` |

#### 3.6 Cluster E: Legacy Lab, Veteran library, deck builder

| ID | Sev | Finding | Files | Fix | Guarded |
| -- | --- | ------- | ----- | --- | ------- |
| **F-E1** | **P0** | The Legacy builder is a narrower second copy of the wizard's ancestry editor: it never passes `rank-label`, so a recorded rank prints "Rank not recorded", and its confirm re-posts without the four keys the request then nulls, so rank letter, ancestor ranks and ancestor Sparks are **lost on a routine save** with no message | `Builder.vue:37-48,235-248`, `AncestryNode.vue:54,77`, `AncestryGraph.php:176-185,194-198`, `StoreLegacySelectionRequest.php:309-320`, `LegacyController.php:236-251` | the payload already carries the data: declare and pass the props, and seed plus post the four keys the wizard step already posts | `LegacyLabPageTest.php:243-312`, `legacy.spec.ts:153-283`, `AncestorRankTest.php:52-62`, `GrandparentRanksTest.php:66-112` |
| F-E2 | P1 | A refused deck write reaches the page but never the screen (the common refusal is keyed to a slot the page does not read) | `Builder.vue:107-109,273-275`, `StoreDeckRequest.php:54-91`, `SupportDeckBuilderTest.php:248-266` | iterate the errors object as `Veterans/Compare.vue:164-173` already does | `support-deck.spec.ts:91-146` |
| F-E3 | P1 | 37 absence reasons across seven files are mouse-only, in the tree's largest un-migrated pocket; `AncestryNode` uses `AbsenceValue` for the Spark chance and `title` for its own rank three lines above | `Legacy/{Index,Compare,Builder}`, `Veterans/{Index,Show,Compare}`, `AncestryNode.vue:93,101,117`, `SupportSlot.vue:138,171`, `SparkChip.vue:58` | `AbsenceValue compact`; where the reason already prints as prose, drop the `title` | `legacy.spec.ts:177-185,275-277`, `support-deck.spec.ts:83` |
| F-E4 | P1 | The clear-all warning disappears exactly when the deck is empty, i.e. when the destructive write is largest | `Builder.vue:304-316` gated on `analysis.covered`, `DeckAnalysis.php:122-124` | delete the `v-if`; the `slots` array is the truth it needs | `support-deck.spec.ts:148-185` |
| F-E5 | P1 | One integer, two labels, and one label ("Skills learned") claims more than the pivot holds | `Veterans/Show.vue:104-111` vs `Compare.vue:248-259`, `VeteranRow.php:11-26` | one label per fact, owned server-side, with the compare surface's qualifier sentence on both | `VeteranLibraryScreenTest.php:103-138` pins values, not labels |
| F-E6 | P2 | `DeckAnalysis` prints its own `Calculated` chip (a second owner of the badge vocabulary), leaves its `Confirmed` anchors unlabelled, heads pure counts with "Strengths"/"Weaknesses", and ships the grammar defect "1 of the 1 cards carry" | `DeckAnalysis.vue:11-15,92-97,136-155`, `DeckAnalysis.php:277,299` | import `ProvenanceBadge`, retitle the two panels, fix the plural | `support-deck.spec.ts:171-185`, `DeckAnalysisTest.php:98-188` |
| F-E7 | P2 | Enamel on a destructive `Remove Spark`, two or three chrome pills per screen | `Builder.vue:382-400,444-450`, `Veterans/Index.vue:152-201`, `Support/Builder.vue:305-382` | enamel only on the screen's commit | `support-deck.spec.ts:91-111`, `legacy.spec.ts:193-194` |
| F-E8 | P2 | The deck's error band is `border-goal-line bg-green-tint` while its two sibling screens write the same message in `risk` | `Builder.vue:273-275` vs `Career/DeckSelect.vue:241`, `Legacy/Builder.vue:438` | copy the sibling's class list | `DesignTokensTest.php:334,344` |
| F-E9 | P2 | One record-only contract, three renderings: `bg-sunken` note on Legacy, bare line on Veterans, **bare line with no role** on `/veterans/compare`, plus a typographic `i` used as a glyph and the refusal stated three times on one screen | `Legacy/{Index,Builder,Compare}`, `Veterans/{Index,Show,Compare}:112/44/120`, `NavGlyph.vue:56` | one rendering, keep `role="note"`, drop the duplicate footer | `legacy.spec.ts:97-101,159,242`, `veterans.spec.ts:91` |
| F-E10 | P2 | Four pages print a second `text-2xl` heading under the shell title (`Support/Builder` prints `Support deck` twice); `Legacy/Builder.vue:176` puts a trainee *name* at page-title size; three Veteran section headers are `text-xs uppercase text-ink-muted` | as listed | delete duplicates, promote the eyebrows | `veterans.spec.ts:90`, `support-deck.spec.ts:165` |
| F-E11 | P2 | Two library pagers miss the 44px bar while two other pagers meet it, and row buttons hover to the colour they sit on | `Legacy/Index.vue:237,251`, `Veterans/Index.vue:255,279` vs `Support/Builder.vue:476`, `SupportCardCatalog.vue:248` | the existing class list; `hover:bg-sunken` | `support-deck.spec.ts:91-111` |
| F-E12 | P3 | `/legacy` rows have no link to the Veteran detail although `veteran_url` is in the payload; a `status_label` column that can only ever hold one value sits in the deciding slot; the compare caption says "nothing here is ... totalled" while the cell adds two parents | `Legacy/Index.vue:20-31,184-241`, `VeteranRow.php:24-26`, `Legacy/Compare.vue:202-204,367-384` | link the name, drop the invariant column, align caption and arithmetic | `VeteranLibraryScreenTest.php:78-101`, `legacy.spec.ts:317-325` |

F-E1 is filed as a UI finding because it was found from the read path, but its data-loss half is a defect
and `AGENTS.md` §11 says a new `KI-nn` entry is the register's own four-part form, in the owner's hand. It
should not sit in a design queue.

#### 3.7 Cluster F: career decision screens

| ID | Sev | Finding | Files | Fix | Guarded |
| -- | --- | ------- | ----- | --- | ------- |
| F-F1 | P1 | Absence reasons still `title`-only on all four screens, including two held figures where the ADR-0016 reason lives only in a tooltip; `RaceCard` renders a bare `N/A` with no reason in any channel | `RaceDecision.vue:140`, `RacePlanner.vue:133-135,145-152,254-256,303-305`, `TrainingCard.vue:410-420`, `SkillPlanRow.vue:84`, `RaceCard.vue:164,212` | `AbsenceValue compact`, server's sentence verbatim; supply the null-title arm | `career-race-decision.spec.ts:71` pins the hover channel |
| F-F2 | P2 | The goal pennant's hue is borrowed for obligation, warning, and a learned list, contradicting the screen's own ruling that mandatory is never a Goal | `RaceDecision.vue:149,152,182`, `SkillsPlanner.vue:257-259,284` vs `AlertRow.vue:9-14`, `RunRaceStrip.vue:157-159` | weight plus `text-ink-strong`, `border-rule` | `DesignTokensTest.php:344,545` |
| F-F3 | P2 | Every screen ships multiple enamel primaries (five `Train` buttons plus "Record this training"; three co-equal writes on the skills planner) | as §2.3 | one gloss per screen; the cards already have a secondary treatment | four 44px sweeps count height only |
| F-F4 | P2 | Two permanent refusal panels sit above the only content that can demand action | `RacePlanner.vue:127-156` above `:161-182`; `RaceDecision.vue:187-193` | reorder inside the existing components; nothing deleted | `career-race-planner.spec.ts:61` reads the region by name |
| F-F5 | P2 | 18 section headers at `text-base`, zero at the specified `text-lg`; `SkillsPlanner` skips a level (`h2` to `h4`) | line list in the cluster notes | the scale step plus `h4` to `h3` | `career-skills-planner.spec.ts:149` |
| F-F6 | P2 | None of the four constrains prose or form width; the only measure in the cluster is one `max-w-prose` helper line | `TrainingDetail.vue:386`, `RaceDecision.vue:252`, `SkillsPlanner.vue:314,349`, `RacePlanner.vue:185-215` | the shipped `max-w-3xl` idiom | `career-race-planner.spec.ts:204-216` |
| F-F7 | P3 | "Senior Year turn ." renders for the seven finale-block obligations | `RaceDecision.vue:181-185` vs the guard at `:170` | reuse the file's own guarded arm | `CareerRaceDecisionTest.php:183` |
| F-F8 | P3 | The fifth Compare pick is refused invisibly: the disabled class list equals the unselected one | `RacePlanList.vue:88-105` | the cluster's own `disabled:opacity-40` | `career-race-planner.spec.ts:132,177` |
| F-F9 | P3 | Write feedback is uneven: one screen moves focus and associates every field, three print one floating message | `TrainingDetail.vue:200-214,249-253` against `RaceDecision.vue:309-311`, `RaceCard.vue:233-235`, `SkillsPlanner.vue:329-331` | extend the existing idiom | `career-race-decision.spec.ts:111` |
| F-F10 | P3 | One fact read out up to five times (the turn number on TrainingDetail; the next obligation twice; the readiness refusal in a panel and every drawer; the hint-lack sentence twice) | line list in the notes | one prose reading per screen | `career-skills-planner.spec.ts:111` |
| F-F11 | P3 | Page-local interfaces drifted from child and server: `Group` lacks `total`/`truncated` the child requires and the controller sends; `status_labels` is shipped and unused; the win-probability refusal is passed into the *readiness* prop | `RacePlanner.vue:54-60,196` vs `RacePlanList.vue:43`, `SkillsPlanner.vue:53`, `AcquisitionStatusEditor.vue:127` | align the three | `CareerRacePlannerTest.php:362` |
| F-F12 | P3 | Stale docblocks and retired-component citations, including `AGENTS.md` §7 naming four components that are not in the tree | `TrainingDecisionController.php:47-52`, `RunRaceStrip.vue:7-9`, `AGENTS.md` §7, `DESIGN.md:124,569` | report-only; the corrections belong to a docs pass | `GuidedTurnStagesTest.php:40-88` |

#### 3.8 Cluster G: record, outcome, snapshot

| ID | Sev | Finding | Files | Fix | Guarded |
| -- | --- | ------- | ----- | --- | ------- |
| F-G1 | P1 | Five surfaces' incomplete-record chips use `bg-warning`/`text-on-warning`, tokens that do not exist, so the product's only "needs attention" signal renders unstyled | `EventDecision.vue:286,421`, `EventCard.vue:36,61`, `CareerTimeline.vue:104`, `Result.vue:296,302`, `ScenarioStatusBadge.vue:36`; `app.css` has no `warning` role | repoint at the declared `risk` pair the same pages already use | `career-event-decision.spec.ts:115-136` pins the words and `title`, not the fill |
| F-G2 | P1 | Em dashes in server-owned UI copy on the two most scan-relevant rail lines | `TimelineController.php:240,270`, rendered at `CareerTimeline.vue:208,213` | use the hyphen form `EventCard.vue:38` already ships | `career-timeline.spec.ts:68-95` |
| F-G3 | P1 | The snapshot confirm screen reads back eight state numbers and never names the trainee, scenario, position or notes it is about to write | `SnapshotController.php:116-148`, `Review.vue:33,63-76`, `routes/web.php:109-112` | one identity row from the `payload` prop, using `SaveVeteran.vue:203-237`'s `dt/dd` shape | `SnapshotReviewTest.php:46-98` |
| F-G4 | P1 | Each stat field wraps its number input and its "I don't know" checkbox in one `<label>` with no `for` or `id`: the input's name becomes the block's text and the checkbox is unnamed | `Snapshot/Setup.vue:183-215` | three labels, real ids; the toggle handler is already correct | `SnapshotReviewTest.php:46-58`; the passing axe scan is not counter-evidence |
| F-G5 | P1 | Both snapshot refusal points are silent: Setup prints errors for three fields only while the request rules eight, and the confirm POST lands on a page with no error surface at all | `Setup.vue:123,137,170`, `StoreSnapshotRequest.php:48-56`, `Review.vue:33-37` | print per-field errors as Setup already does for three, and reuse the `role="alert" tabindex="-1"` block `EventDecision.vue:325-336` owns | `SnapshotReviewTest.php:93-98` |
| F-G6 | P1 | A recorded outcome with only deltas prints the literal `Deltas: {}`, from a prop that is not declared and never sent | `EventCard.vue:65-68,8-20`, `EventDecisionController.php:177-191,239-243` | delete the line; print the outcome as prose, or compose one `outcome_text` server-side | `career-event-decision.spec.ts:115-136` |
| F-G7 | P1 | 26 absence reasons across four record screens are mouse-only, and `EventDecision.vue:218-247` hand-rolls a second copy of `CareerStatePanel`'s already-migrated grid | as cited | migrate, and adopt `CareerStatePanel` rather than duplicating it | `career-inheritance-event.spec.ts:176-190` pins the pre-migration `title`s (owner approval) |
| F-G8 | P2 | The rail's audit pane renders two identical five-stat tables from one server call, prints the result string twice or three times, and ships an `after` field no template reads | `CareerTimeline.vue:174-214`, `TimelineController.php:207-209` | one block per row, drop the dead field | `career-timeline.spec.ts:86-91` pins the duplication, so the spec moves too |
| F-G9 | P2 | Enamel on filter chips, tag chips, and two side-by-side primaries on Result | `Timeline.vue:110`, `SaveVeteran.vue:304,333,396-402`, `Result.vue:341,355` | keep the write control, reuse the secondary pair in the same files | `career-timeline.spec.ts:107`, `career-save-veteran.spec.ts:141` |
| F-G10 | P2 | The one `animate-pulse` in the app marks a static "current milestone" | `InheritanceEvent.vue:156` | delete the class; the fill and the word already carry it | `career-inheritance-event.spec.ts:195-270` pins glyph and labels, not motion |
| F-G11 | P2 | The star-roll table scrolls in a div the keyboard cannot reach, one call site from the correct wrapper | `InheritanceEvent.vue:310-333` vs `Result.vue:251-253` | add the region's three attributes | `career-inheritance-event.spec.ts:163,337` |
| F-G12 | P3 | 30 section headers across six screens at `text-base`, zero at `text-lg`; two snapshot pages use `max-w-2xl` while the same flow uses `max-w-3xl` | line list in the notes | fold into the shell decision; normalise the two widths | `career-timeline.spec.ts:107`, `career-result.spec.ts:128` |

### 4. Instrument findings: what the quality bar currently cannot see

These five are the audit's most actionable output, because each one is a gate that reports a pass while the
class of defect it names is present in the tree. They are reported, not repaired: `AGENTS.md` §5 forbids new
suppressions and §2 forbids silently rewriting a rule to match code.

#### 4.1 `npm run typecheck` reads no Vue file

`package.json:8` is `"typecheck": "tsc --noEmit"` and `tsconfig.json:15` includes
`resources/js/**/*.ts`, `tests/browser/**/*.ts`, `tests/utils/**/*.ts`. There is no `vue-tsc` in
`devDependencies` (`package.json:12-23`). A plain `tsc` cannot parse an SFC, so **none of the 48 page
components or the 64 components under `resources/js/components/` (counted on the working tree, 2026-10-13) is
type-checked**, while `AGENTS.md` §9 and §15 list `npm run typecheck` as the gate "if TypeScript, Vue or Blade
changed" and this pass observed it exit **0**.

Two escapes found the same way prove the ceiling rather than argue it: `Database/ShopItems.vue:79` reads a
prop its own interface does not declare (`F-C1`), and `career/EventCard.vue:67` reads `props.deltas`, which
is neither declared nor sent by the controller (`F-G6`). Both are `TS2339` candidates in a checked file and
both are invisible today. Any fix to those two lines should be accompanied by a decision on whether the gate
gains an SFC-aware pass; that is a dependency question (`AGENTS.md` §5, no new package without owner
approval), so it is a question here and not a proposal.

#### 4.2 The copy-hygiene guard cannot see server-composed copy

`tests/Feature/RenderedCopyHygieneTest.php:35-49` builds its file list by walking for `.blade.php` and
`.vue` only. It exists precisely because `tools/gate.py` checked the D-79 em dash only over prototype HTML
and "a whole slice of Blade work landed with em dashes in copy that renders" (its own header, `:12-16`). The
same class has now moved one layer up: `TimelineController.php:240` and `:270` ship two em dashes in strings
the rail prints verbatim (`F-G2`), and the guard exits green. The Inertia prop payload is today's Blade
template.

#### 4.3 The G-18 contrast contract covers one `ink-muted` pairing

Carried from the cockpit audit §2.9 and re-confirmed here: `g18ContractPairs()` (`:298` in the same test)
pins 19 pairs and exactly one carries `ink-muted`, against `sunken`. This pass found the app's dominant
muted usage is `ink-muted` on `raised` and `panel` (`F-B12` enumerates 21 label sites on three detail screens
alone; `F-E10` finds three *section headings* in the muted token). Two pair rows would convert a family of
judgement calls into a gate result. No ratio is claimed in this document, and none should be.

#### 4.4 Registers that lag the code

| Register | What it says | What this pass measured |
| -------- | ------------ | ----------------------- |
| `SCREEN_SPEC.md:3341-3359` item 17 | 17 `title`-on-`N/A` sites across 9 files | roughly 100 enumerated unmigrated sites across five clusters; no catalog, Database, Legacy, Veteran, Dashboard, Review or Preferences file appears in the list |
| `DESIGN.md:615-618` | a fifth trust label "is a four-file change" | the four-member union is written out at **49 sites in 19 files** (measured 2026-10-13 with `git grep -E "'confirmed' \| 'calculated' \| 'estimated' \| 'unknown'"`) |
| `DESIGN.md:693-696` | "The tree contains zero `transition-*`, `duration-*` and `animate-*` utilities" | `AppLayout.vue:204` carries `transition-[width]` plus `motion-reduce:transition-none`, pinned by `KeyboardPathTest.php:101-102`, and `InheritanceEvent.vue:156` carries `animate-pulse` |
| `AGENTS.md:209-211` and `DESIGN.md:124` | `ResourceStrip.vue`, `StatBand.vue`, `RaceCalendar.vue`, `GuidedStep.vue`, `GradePointMeter.vue` are the live scenario-driven components | only `ResourceStrip.vue` is in the tree (verified: `find resources/js -name StatBand.vue` etc. return nothing). The nearest shipped relatives are scenario-specific (`scenario/TrackblazerRaceCalendar.vue`, `scenario/RaceCalendarNote.vue`) or differently shaped (`career/RaceCard.vue`, `career/ObjectiveRow.vue`); `DESIGN.md:410-411` and `:569` carry two further stale figures, and §3's page inventory names `Runs/Show.vue` and `Database/Database.vue`, neither of which exists |
| `DESIGN.md:569` | shipped validation bound "0..1200" | per-run entered ceilings are enforced through `ScenarioCaps::forRun` (`StoreTurnEntryRequest.php:53-85`, `ScenarioCaps.php:103-125`, `EnteredStatCapsTest.php:77`) |

Per `AGENTS.md` §2 these are code-wins-rule-stale statements: reported here, not edited there. They matter
because a future brief sized by §7's component list will ask for work on files that do not exist; this pass's
own brief did exactly that, and the correction is recorded at the top of the cluster F notes.

#### 4.5 An unvalidated index between config and a component

`ProvenanceBadge.vue:61` indexes `BADGES[props.state]` with no fallback, and three Database controllers pass
`(string) $row['state']` straight out of `config/reference.php` (`DatabaseController.php:188,230,292`) with no
whitelist. `DESIGN.md:619-621` already names this failure mode. One capitalisation slip in the config takes a
whole reference view down at render time, and no build step or test catches it first (`F-C3`).

#### 4.6 The deterministic detector saw nothing

`impeccable detect --json resources/js/pages` returned `[]`. The detector's rule set is CSS and markup-slop
shaped (gradients, glass, radius, shadow, generic content) and this app has genuinely none of those, so the
empty result is partly a real pass and partly an instrument limit: it does not read SFC prop contracts, token
existence, or a `title`-only channel, and those three are exactly the channels the P1s in §2.1, §2.5 and §2.7
travel on. Useful signal: the slop-taste failure mode antislop is built to catch is not present here. The
failure mode that is, is drift between a written rule and the 40 screens that shipped before it.

### 5. What a later pass must not do

1. Do not move a colour token to answer a contrast question. Propose pair rows in `g18ContractPairs()` and
   let the browser gate decide (cockpit audit §2.9, `F-B12`).
2. Do not add a fifth provenance label, a `Recorded`/`Missing` chip, or a badge that qualifies a section
   rather than a displayed value (`DESIGN.md:607-624`, `F-E6`).
3. Do not invent a `--color-warning` role to justify the eight dead classes. Use the declared `risk` pair
   (`F-G1`, `F-E8`); `DESIGN.md:127` reserves it for failure and rejection, and the incomplete-record state
   is that state.
4. Do not reflow the race calendar, the turn log, or the compare tables into stacked cards. The contract is
   `overflow-x-auto` plus `role="region"` plus `tabindex="0"` plus an `aria-label`
   (`DESIGN.md:209-224`), and it is already correctly implemented at `Result.vue:251-259`,
   `RacePlanner.vue:219-224`, and both compare surfaces.
5. Do not add a cold-load spinner, a skeleton, or an `aria-busy` on a page load. `ADR-0007` reserves custom
   loading for user-initiated async, and `AGENTS.md` §13 plus `DESIGN.md` §3 treat the absence as deliberate.
6. Do not add a score, percentage, confidence, risk grade, win chance or recommendation to any surface,
   including the deck analysis and the compare tables (`ADR-0001` §3, `ADR-0020` §2-§3, `PRD.md` §6;
   `F-E6` is about *labelling*, not about earning the right to compute).
7. Do not name a scenario in a component (gate G-33) and do not give a scenario-driven component a default
   `scenarioLabel` (`AGENTS.md` §7).
8. Do not restyle per-page headings before the shell `#title` decision in cockpit audit §3.1 is settled;
   independent page restyling is how §2.2's 75-versus-30 spread got worse.
9. Do not migrate an absence without moving the specs that pin the current channel. `getByTitle(...)`
   assertions exist in `dashboard`, `preferences`, `database`, `skills`, `legacy`,
   `career-race-decision`, `career-inheritance-event` and `career-event-decision` specs. That is a test
   change, which needs owner approval and a stated reason (`AGENTS.md` §9, §14).
10. Do not add a client-side theme write to fix `F-D24`. Server-composed first paint is pinned twice
    (`DesignTokensTest.php:184-205`, `ErrorPageViewsTest.php:48-51`).
11. Do not treat a passing axe scan as proof of an accessible name. `F-G4`'s nested-label block scans clean:
    a label that wraps two controls is valid ARIA-free markup, and axe only reports what it can see.
12. Do not widen `AGENTS.md` §7's component list as a side effect, and do not hand-edit `PRODUCT.md`,
    `SKILL.md`, or the derived ADR index.

### 6. Questions for the owner

1. **Absence migration (§2.1).** Roughly 100 sites, eight clusters, and at least seven specs that pin the
   current `title` channel. Take it as one slice with the spec moves included, screen by screen behind
   `SCR-CAR-011`'s pattern, or leave the register at 17 and accept that the keyboard Trainer gets "N/A" on
   40 of 43 routes?
2. **Type scale (§2.2).** `DESIGN.md:188-190` marks the scale **Proposed**, not established. Either the shell
   `h1` and the 75 `text-base` headers move to the written scale in one pass, or the written scale is
   restated to what shipped and the drift ends by ruling rather than by CSS. Which?
3. **Enamel and multi-write screens (§2.3, `F-F3`).** Does "one primary action per screen" bind a screen that
   genuinely owns three writes? If it does not, `DESIGN.md:228` needs a clause; if it does, six screens need
   the treatment reduced.
4. **`F-E1` (the P0).** Fix as a defect slice outside this audit's scope, and file the register entry. Its
   data-loss half is not a design question and its "must not break" list already names the four tests that
   pin the shape.
5. **`F-D1` versus `SCREEN_SPEC.md:632`.** The spec says the preview renders values "verbatim as raw strings
   straight from the parse"; `AGENTS.md` §5 says an unrecorded value renders `N/A` with a reason. Which
   governs the import preview's empty cells? (The rest of the row's argument for raw values, KI-46's
   row-pointing messages, is unaffected either way.)
6. **Gate reach (§4.1, §4.2).** Do you want the typecheck to see SFCs (a dependency question, since `vue-tsc`
   is not installed) and the copy-hygiene sweep extended to the PHP that composes UI strings, or do you want
   both left as source-audit findings that a human re-checks?

### 7. Verification plan for any approved subset

Per `AGENTS.md` §9's cost-aware escalation. Nothing below was run for this audit.

| Approved change | Level 1 while working | Level 2 at slice end | Level 3 before hand-off |
| --------------- | --------------------- | -------------------- | ---------------------- |
| §2.1 absence migration (any subset) | the feature test for the touched screen plus the spec that pins its `title` | those plus `tests/browser/accessibility.spec.ts` | the hand-off sequence, plus a browser pass on the touched screens with a session-private pair (`PLAYWRIGHT_PORT=<port> PLAYWRIGHT_SCRATCH_DB=.scratch-uma/browser-<port>.sqlite npm run test:browser`) |
| §2.2 type scale, shell and pages | `tests/browser/<screen>.spec.ts` for the touched pages | the same plus `CareerCockpitTest` (shared shell) | the full suite, since `AppLayout` is felt by every screen |
| §2.3 motif dose, `F-E8`, `F-A7` | the touched screen's spec (height sweeps count height, not gloss) | the cluster's specs | `php artisan test --compact`, then `vendor/bin/pint --test --format agent <explicit paths>` (never `--dirty`, KI-75) and PHPStan |
| §2.4 write-feedback and double-submit (`F-D2`, `F-D3`, `F-E2`, `F-E4`, `F-G5`) | the flow's feature test plus its browser spec | add `SupportDeckBuilderTest`, `ImportHistoricalRunTest`, `SnapshotReviewTest` | the full suite; a double-submit fix needs a counted assertion against a scratch database, not a screenshot |
| §2.5 form semantics (`F-D4`, `F-G4`, `F-D22`, `F-E11`, `F-G11`) | the screen's spec plus `tests/utils/tap-targets.ts` where height is involved | `tests/browser/accessibility.spec.ts` | the full suite; announce behaviour needs a real AT pass, which this tree has never recorded |
| §2.6 copy (`F-G2`, `F-B3`, `F-B7`, `F-B10`, `F-D28`) | `RenderedCopyHygieneTest`, `EmptyStateCommandNamesTest`, the named browser assertions | the touched screens' feature tests | `composer lore` and `composer lore-code`, with a one-line ruling per hit |
| §3.6 `F-E1` (P0) | `LegacyLabPageTest`, `AncestorRankTest`, `GrandparentRanksTest`, `legacy.spec.ts` | those plus `CareerLegacyDeckStepsTest` | the full suite and the hand-off sequence; this one changes a write path, so it is not a UI-only change |
| §4.3 G-18 pair rows | `php artisan test --compact --filter=DesignTokensTest` | the browser half of the same file | the pair list and the `DESIGN.md:145-148` count move together, then the full suite |

Pre-flight before any Level 3 or browser run: time one trivial SQLite read against the dev file
(`KI-74`). If it takes more than a few seconds the box is contended and the sweep is void before it starts.

### 8. Hand-off notes

- **Deliverables produced:** this file. Eight cluster notes with the full per-finding reasoning, the
  rejected alternatives, and the complete citation lists live in the session scratch directory
  `.scratch-uma/ui-audit-2026-10-13/` (`cluster-A.md`..`cluster-G.md`, `cluster-D1.md`, `cluster-D2.md`,
  plus `typecheck.log`). They are gitignored, so anything in them worth keeping is quoted into this
  document; if a later pass needs the unabridged reasoning behind a specific row, the scratch directory is
  the source and may already be gone.
- **Not delivered by this pass, stated plainly:** no browser measurement (so §2.2's size claims are class
  reads, not pixels, and `F-A11`, `F-C6`, `F-D24`, `F-E7` remain open questions), no contrast arithmetic, no
  suite run, no pint or PHPStan, no lore gate, and no fix of any kind. `AGENTS.md` §7's rule that this audit
  was told to enforce also says the doc is written only when the task asks; it asked, and this is it.
- **Facts this pass corrected in its own brief:** the four components §7 of `AGENTS.md` names as the career
  surface's scenario-driven set are not in the tree; `transition-*` does ship in the shell; the badge-union
  re-declaration count is 49 sites in 19 files, not four files; and `npm run typecheck` cannot see any of
  them. All four are recorded in §4.4 rather than repaired in place.

---

## ux-laws-report-cockpit-wizard-2026-10-13.md

> **Consolidated 2026-10-15 (Round 16, owner-ordered).** Source: `docs/proposals/ux-laws-report-cockpit-wizard-2026-10-13.md` (228 lines), reproduced verbatim below with headings demoted one level. The original was deleted from `docs/proposals/` after line-by-line verification (0 missing). Cite this anchor, not the deleted path.

## Laws of UX report — Career Cockpit and the setup wizard

> **Scope.** The two surfaces Agent 3 owns: `SCR-CAR-011` (`/training-runs/{run}/cockpit`) and the six
> `/career/setup/*` steps. Method, instruments and the evidence contract are
> `ux-laws-audit-plan-2026-10-13.md` (§3 matrix, §4 instruments, §5 method, §6 output contract, §9 rules).
> Findings from the three prior audits are cited by their own IDs in the **Existing ID** column and are
> not re-filed. Everything here was re-derived on the tree at the SHAs named in §Verdict, not inherited.
>
> **State of the tree this report was produced on.** Master `0a58c88` (2026-10-13, late). A concurrent
> session landed the ratified type scale (`4d31de4`, `209e05e`) and the scenario systems work is in
> flight in its working tree; the shared `public/build` bundle postdates no Vue change of the day. Every
> verdict below cites a `file:line`, a measured number from the harness runs named, or a committed
> test. **Measured 2026-10-14:** a peer session rebuilt `public/build` twice (22:54, then 00:10 with
> the goals ladder in it) and committed both builds, so the bundle post-dates every landed slice; the
> five surface specs and the ladder's rendered read ran against it and the marks below carry their
> numbers. The sweep's one red (the scenario card's Systems absence, whose flag landed at
> `8668d9d`) had its pins moved forward at `0bc9fa2`; the five surface specs then read **43 passed /
> 0 failed**.
>
> **Provenance check (continuation pass, 2026-10-13 late).** The peer's later landings `35a1c68`
> (catalog rows and pagers onto Inertia `Link`, with a shared `Pagination.vue`) and `28a2718` (the
> review merge target's id beside its suggestion) touch none of the files this report cites and
> neither surface; the report's citations stand as written.

## UX Audit Summary

- **Overall assessment.** Both surfaces are **Strong with two recorded tensions**. The cockpit honours
  the app's own spine (`design-2.0.md` §2: State → Options → Recommendation → Explanation → Decision)
  structurally, not decoratively: `NextDecision.vue`'s five-element order is its declared contract and
  every branch prints one element. The wizard's one real law failure of the day — the stepper marking
  visited-not-saved steps "done" — was fixed at `4a5ff88` under owner ruling C-1 ("keep visited", answered
  by the owner 2026-10-13 late, through this pass's blocking questions; the plan's §10.3 row and
  `4a5ff88`'s own body, written before the answer, call it open), and
  the scenario screen now opens on an option-card grammar rather than forty facts (`84f9353`).
- **Strongest patterns.** 44px floors hold everywhere they were measured (7/7 anchor targets at 768 and
  1280; the per-screen sweeps); every absence is a named absence (`AbsenceValue`) rather than a blank
  or an invented figure; every recommendation carries its provenance badge and its "why" list in
  visible text; two-step disclosures guard the destructive paths.
- **Highest-risk issues.** (1) The cockpit's visual reading order at 768px puts the timeline strip
  before the decision content while the DOM reads decision-first — a Continuity tension between the
  brief's CSS-only column order and a screen reader's sequence (P2, below). (2) The client's alert
  vocabulary (red exclamation, pink failure badge, obligation tag) has no counterpart in the app's
  semantic set; recorded as a convention per Laws-7, not a defect (P3, below).
- **Laws most frequently violated.** After today's slices: none outright FAIL on either surface. The
  recurring PARTIAL was **Goal-Gradient (18)** on the cockpit; it is now measured: the peer landed the
  goals ladder (`8668d9d`, composer at `careerGoalSections()` :1793, `GoalMark.vue`, the grouped
  renderer) and its rendered read passes all six grammar checks (2026-10-14). **Von Restorff (15)** /
  **Closure (12)** stay as recorded by the Laws-7 ruling.
- **Accessibility and responsive risk summary.** axe A+AA scans are green wherever they ran
  (`accessibility.spec.ts`; the cockpit's 9-case spec; the wizard specs). Focus is not obscured by the
  two pinned bars: the cockpit's `scroll-margin-top` block and the wizard's `scroll-padding-top` were
  both click-through verified (0 of 7 anchors obscured; the 9.1 keyboard pass). Reduced motion is
  inherited from the platform; the tree carries one `animate-pulse` (F-G10). The 2026-10-14 sweep put
  numbers on every mark this report had left open: the five surface specs read **36 passed / 1
  failed** at the sweep's first pass, the failure the career-goals flag `8668d9d` landed with its
  pins unmoved (moved forward at `0bc9fa2`; classified at the P2
  row below); the one risk that remains open is that config landing itself, not a measurement.

## Route-by-route findings

### `/training-runs/{run}/cockpit` — SCR-CAR-011

- **User goal:** see where the career stands this turn and what to do about it, without losing the
  thread of what was already decided.
- **Primary task:** read the current state, take the recommended action or an alternative, record what
  the turn produced.
- **Overall rating:** **Strong**.

| Law or principle | Status | Evidence | User impact | Recommendation | Severity | Issue type | Existing ID |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 3 Fitts's Law | PASS | `s01-measure`: 7/7 anchor targets ≥ 44px at 1280 and 768; the spec's own sweep (`career-cockpit.spec.ts`) | primary actions are where the hand is | none | — | — | cockpit audit §3.2 |
| 11 Continuity | PARTIAL | `s01-measure`: the nav renders first (y=159) and 7/7 anchor targets land below the pinned nav; at 1280 three column headings share y=801 (timeline 20% / decision 50% / advisor 30%, `SCREEN_SPEC.md:1841`'s CSS-only order), and at 768 the timeline strip is visually first (y=849) while DOM reads it after the scenario region (`Cockpit.vue:930` → `:944`) | a sighted scan and a screen-reader sequence disagree about what comes first on tablet | none this pass — the spec's own ruling orders the columns and forbids hiding; the DOM sequence is meaningful on its own. Re-test if the brief's column order changes | P3 | responsive | cockpit audit §3.2 |
| 26 Flow | PASS | `NextDecision.vue:5-8` (the five-element contract in its docblock); the recommended action carries its own door (`:218-223`); `#action-recommended` is the fragment the selector navigates | the next action is the page's answer, not a hunt | none | — | — | cockpit audit §3.2 |
| 20 Tesler's Law | PASS | `NextDecision.vue:10-14` ("the server owns the ranking; this component formats what it is handed and acts for nobody"); refusals name the missing inputs in words (`:172-185`) | the Trainer answers the game's question, not the tool's data model | none | — | — | — |
| 17 Peak–End | PASS | `RecordedActionCard` expected/actual with reconciliation (`NextDecision.vue:261-271`); correction and delete are two-step disclosures with focused alerts (`career-cockpit-forms.spec.ts`) | the turn's ending is stated and correctable | none | — | — | — |
| 16 Serial Position | PASS | nav first (`s01-measure` y=159), advisor rail last and sticky (`AdvisorRail.vue`, pass-2 4.1) | the two ends of the page carry the orientation and the decision | none | — | — | — |
| 19 Zeigarnik | PASS | the identity disclosure's "Unsaved changes." dirty marker and `role="status"` saved lines (pass-2 3.2/3.3) | unfinished edits are visible and resumable | none | — | — | cockpit audit §3.3 |
| 15 Von Restorff | PASS (PARTIAL per Laws-7) | one enamel primary per region (pass-2 4.2); the client's alert channels recorded as a convention, not adopted | the one important action is the one accented | none | — | — | cockpit audit §3.2 |
| 4 Hick's Law | PASS | the advisor's alternatives are the only inline option set (small, with trade-offs, `NextDecision.vue:225-237`); the scenario region reads its panels per scenario | decision points stay small | none | — | — | — |
| 18 Goal-Gradient | PARTIAL | deficits and caps on the training cards (`TrainingCard.vue:15`); the goals ladder is Agent 1's module, Slice 0.3 pending | distance-to-goal is visible per training, not per career | land Slice 0.3 when the module lands | P2 | interaction | — |
| 24 Cognitive Load | PASS | the why list is provenance-badged visible text (`NextDecision.vue:240-250`, `SCREEN_SPEC.md:1886` ruling 5a); no contributor copy (X-6 fixed) | recognition over recall | none | — | — | X-6 |
| 27 Consistency / 1 Aesthetic-Usability | PASS | the ratified scale landed (`209e05e`: cockpit, advisor, scenario panels); tokens only, no `dark:` (G-19 sweep) | one visual voice | none | — | — | X-2 |
| 5 Miller / 8 Common Region / 7 Similarity / 10 Prägnanz / 2 Jakob's / 23 Doherty | PASS | regions chunked to the seven the nav names; `role="status"` save feedback; native controls throughout | — | none | — | — | — |
| 13 Common Fate | N/A | MOTION 1 (`DESIGN.md:694-696`); no coordinated motion exists to judge | — | — | — | — | — |

### `/career/setup/scenario` — SCREEN-002

- **User goal:** choose the scenario this career runs under.
- **Primary task:** one Select press per card.
- **Overall rating:** **Strong** (Mixed before `84f9353`).

| Law or principle | Status | Evidence | User impact | Recommendation | Severity | Issue type | Existing ID |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 4 Hick's Law | PASS | `84f9353`: the eight-row fact grid and the metadata block sit behind one "Facts and mechanics" disclosure per card; the card opens on name, badge, subtitle, status, action | the choice is readable before the facts are | none | — | interaction | wizard audit §3.2 |
| 21 Occam's Razor | PASS | one Select per card (`:271-281`); the page-level ruleset fact stayed per-card but named (R2-05's two-`Ruleset` fix preserved, `:100-116` re-aimed) | no duplicate doors | none | — | — | wizard audit §2.2 |
| 3 Fitts / 19 Zeigarnik / 23 Doherty / 15 Von Restorff | PASS | 44px sweeps (`career-scenario-select.spec.ts:168-182`); `aria-pressed` carries the stored state; `role="status"` "Saving your choice…"; one enamel per card | — | none | — | — | — |
| 12 Closure | PASS | the subtitle's absence is named (`AbsenceValue`, `84f9353`), never left to guesswork | — | none | — | — | X-1 |
| G-33 (gate, not a law) | PASS | no scenario key or name literal in the component (`ScenarioSelect.vue:5-8` docblock; the gate) | a fifth scenario needs no edit here | none | — | — | — |

### `/career/setup/trainee` — SCREEN-003

- **User goal:** pick the trainee this career trains.
- **Primary task:** search, inspect, select.
- **Overall rating:** **Strong**.

| Law or principle | Status | Evidence | User impact | Recommendation | Severity | Issue type | Existing ID |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 4 Hick's / 24 Cognitive Load | PASS | the aptitude block sits behind "Show all aptitudes" (pass-2 Phase 11, option 1); the controller owns the axis order (`TraineeSelectController` `APTITUDE_AXES`) | the selection question stays "who?", not "what are her aptitudes?" | none | — | interaction | wizard audit §2.5 |
| 2 Jakob's Law | PASS | `TraineeCombobox.vue` is a real combobox (listbox + options + `aria-activedescendant` behaviour the runs spec asserts) | the pattern is the platform's | none | — | — | — |
| 3 Fitts / 12 Closure / 27 Consistency | PASS | the screen's sweeps and disclosures in `career-trainee-select.spec.ts` | — | none | — | — | X-1 |

### `/career/setup/target` — SCREEN-004

- **User goal:** state what this career is for.
- **Primary task:** pick a purpose, distances and surfaces, enter per-stat targets.
- **Overall rating:** **Strong**.

| Law or principle | Status | Evidence | User impact | Recommendation | Severity | Issue type | Existing ID |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 18 Goal-Gradient | PASS | each stat field shows the ceiling it aims at; the summary was promoted above the fieldsets (pass-2 Phase 10) | the target reads as a direction, not five numbers | none | — | — | — |
| 5 Miller / 6 Proximity / 8 Common Region | PASS | five stat fields in one fieldset group; ceilings beside their fields | chunked at a glance | none | — | — | — |
| 24 Cognitive Load | PASS | the summary sentence is computed from entered values and says so (pass-2 Phase 10; `BuildTarget.vue`) | the tool's arithmetic is labelled as its own | none | — | — | — |
| 3 Fitts / 19 Zeigarnik / 27 Consistency | PASS | the step bar (post-`4a5ff88`); the screen's spec sweeps | — | none | — | — | — |

### `/career/setup/legacy` — SCREEN-005

- **User goal:** record the ancestry this career inherits from.
- **Primary task:** per grandparent: name, rank, sparks.
- **Overall rating:** **Strong**.

| Law or principle | Status | Evidence | User impact | Recommendation | Severity | Issue type | Existing ID |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 20 Tesler's Law | PASS | `ebcbbac`: each grandparent's spark editor is a disclosure whose visible summary now names her ("Sparks for Grandparent A1"); the wizard writes ancestors as names, not slot records (ADR-0010 §2) | the payload's shape never reaches the Trainer | none | — | interaction | wizard audit §2.5 |
| 7 Similarity / 9 Uniform Connectedness | PASS | the wizard's and the builder's editors are the same shape (`Builder.vue:435-439` names the apartness rule) | one spark grammar everywhere | none | — | — | — |
| 3 Fitts / 12 Closure | PASS | the deck-steps spec's disclosure keyboard path (`career-legacy-deck-steps.spec.ts:195-196`) | — | none | — | — | F-G11 (same pattern) |

### `/career/setup/deck` — SCREEN-006

- **User goal:** equip the six support slots.
- **Primary task:** pick a card per slot.
- **Overall rating:** **Strong**.

| Law or principle | Status | Evidence | User impact | Recommendation | Severity | Issue type | Existing ID |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 4 Hick's / 25 Choice Overload | PASS | the picker narrows per slot and names an ask that matched nothing (`support-deck.spec.ts:267`); one slot is aimed at a time (`?deck_slot=N`) | 500+ cards never meet the Trainer at once | none | — | interaction | — |
| 6 Proximity / 7 Similarity | PASS | the picker rows print type as word + glyph, never colour alone (`support-deck.spec.ts:211`) | — | none | — | — | — |
| 3 Fitts / 19 Zeigarnik / 27 Consistency | PASS | the screen's sweeps; the owned/equipped persistence states (`support-deck.spec.ts:188`) | — | none | — | — | — |

### `/career/setup/preflight` — SCREEN-008

- **User goal:** see the whole entered setup, fix what reads as a gap, start the career.
- **Primary task:** read five composed steps, press Start Career once.
- **Overall rating:** **Strong** (Mixed before pass-2's Phase 14).

| Law or principle | Status | Evidence | User impact | Recommendation | Severity | Issue type | Existing ID |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 21 Occam's Razor / 26 Flow | PASS | one Edit door per previous step, all five distinct (`Preflight.vue:410-442`; the feature test's five-door distinctness case); the duplicate Back/Edit Deck pair is gone | no door is a dead end or a duplicate | none | — | interaction | wizard audit §2.4 |
| 17 Peak–End | PASS | the run is created exactly once on Start Career (the feature test composes the contract); warnings never block | the commitment is deliberate | none | — | — | wizard audit §2.4 |
| 24 Cognitive Load | PASS | each warning carries title + detail + its own Edit link (`:178-202`) — the HLF's single-row shape the slice asked for | a gap is a sentence, not a form | none | — | — | wizard audit §2.4 |
| 19 Zeigarnik | PASS | the step bar's visited marks and the one-column review grids (pass-2 Phase 14.1) | entered vs unentered reads at a glance | none | — | — | wizard audit §2.2 |
| 3 Fitts / 27 Consistency | PASS | `career-preflight.spec.ts` 4 passed this pass | — | none | — | — | — |

## Cross-page consistency findings

| Pattern | Verdict | Evidence |
| --- | --- | --- |
| Step bar | One component, one grammar, all six steps: `aria-current="step"`, count line in two media, sticky md+ with `scroll-padding-top`; the visited/not-started words shipped at `4a5ff88` and ruled final (C-1) | `SetupLayout.vue:61-88`; the 9.1 keyboard pass |
| Disclosure grammar | One pattern everywhere a control is hidden: native `<details>`, `min-h-11` summary, the word carries the state ("visited", "not built", "Facts and mechanics", "Sparks for Grandparent A1") | the four surfaces above |
| Absence grammar | One component (`AbsenceValue`), the reason as the accessible name; every screen in scope migrated (pass-2 Phase 12; wizard slices) | `AbsenceValue.vue` docblock |
| Save feedback | `role="status"` lines and `aria-live` priority, the same shape on both surfaces | pass-2 3.2; `NextDecision.vue:168` |
| Primary action | One enamel per region; secondary actions are bordered links (pass-2 4.2) | the cockpit and wizard screens |
| Type scale | The shell `#title` is `text-2xl`, section headers `text-lg` (`209e05e`, `4d31de4`; `AppLayout.vue:290`) — X-2's spread closed on these surfaces | the shell at HEAD |
| Write protection | Both surfaces' writers refuse double writes (the import guard's measured one-row proof is the pattern; the wizard's Start Career creates exactly once) | A.1's count; `CareerPreflightTest` |

## Prioritized remediation plan

- **P0.** None. The wizard's stepper "done" P0 (the live capture's misleading completion indicator) is
  fixed at `4a5ff88` and ruled final (C-1); its acceptance criterion — a step the session only opened
  never reads as done — is **measured**: the sweep's wizard-side specs ran against the bundle that
  carries `4a5ff88` (`career-preflight.spec.ts` 4 passed; `career-scenario-select.spec.ts` 6 passed /
  1 peer-attributed failure).
- **P1.** None on these two surfaces. The preflight duplication (the audits' P1) landed at `3f9618c`
  and verified this pass.
- **P2.**
  1. **Goal ladder on the cockpit (Law 18) — LANDED AND MEASURED.** The peer landed the whole ladder
     at `8668d9d` (23:28): `careerGoalSections()` now composes the three real groups, `GoalMark.vue`
     spends the reserved `--color-goal` token as the word-paired pennant, and
     `TraineeGoalsPanelTest` (+225) pins the composer. The rendered read (2026-10-14, a URA run on
     the 00:10 bundle): the Trainee goals group renders with **one** footer line in the client's own
     shape ("Goals Achieved {cleared} of {total}"), **zero** percentage forms, **zero** bar/progress
     elements, **zero** Due-now marks on a fresh run — the honest state, because a run that has
     recorded no turn has no `nextTurnToPlay()` to be current against (`TrainingRun.php:640-644`),
     so every unmet goal is `upcoming` and the Due-now state only attaches once a turn is recorded
     (`CockpitController.php:1958-1960`, the `$state` match inside `traineeGoalRows()`; repointed
     2026-10-14 from `:1948-1950`, which the peers' career commits shifted by ten lines); the
     panel-wide count read 21 rows (all three groups plus
     the milestone list items, not the goals group alone), axe A+AA clean. Alternates-counted-once
     is carried by the composer's own tests, not re-asserted in a browser.
  2. **The config reconciliation — LANDED, then its pins moved.** The career-goals flag is committed
     at `8668d9d`, not uncommitted as an earlier revision of this note said (the working tree's `MM`
     was a staged revert and re-add netting zero). The sweep's one browser failure (`No scenario
     system` no longer rendering for Our Grand Concert) and the two `CareerScenarioSelectTest`
     systems-array pins were that landing's missed companions; they moved forward at `0bc9fa2`
     (the two arrays, the capability map in `LessonEvidenceBoundaryTest`, the README's source count
     now reading Nine for the `gametora-ura-objectives` source), and the scenario spec reads **7
     passed**. The original "bundle-lagged renders" item is closed by the same sweep: the peer's
     rebuilds (22:54, 00:10) put every landed slice's render behind a committed bundle, and the
     specs ran against it — with the last count **43 passed / 0 failed** across the five surface
     specs after the reconciliation, with two transient webServer start-up timeouts retried green
     (infrastructure, not product).
- **P3.**
  1. **Continuity's column-order tension (cockpit, tablet).** The brief's CSS-only order is a ruling;
     if it ever changes, re-test the DOM sequence against the new columns. No change now.
  2. **The client's alert vocabulary (Laws-7, ruled "record as convention").** The report records it:
     the client's red exclamation badge (`01:113`), pink failure badge (`01:318`) and pinned
     obligation tag (`05:85`) are alert semantics the app deliberately cut (`app.css:57`,
     `DESIGN.md:53`, §9 item 3). Von Restorff and Closure are scored PARTIAL-with-citation on the
     surfaces where a warning would have carried them; no component may adopt the vocabulary without
     a Track 2 D ruling (`frontend-fidelity-2026-10-13.md` §6 D).

## Verdict on this report's own rules

Every PASS/PARTIAL above carries a `file:line`, a committed test, or a measured number from
`.scratch-uma/agent3-uxlaws/` (`s01-measure.mjs` output, the cockpit and preflight spec runs). No
verdict rests on "it looks clean"; the two tensions the laws lens could not settle inside the ruling
chain (Continuity's column order, the alert channels) are recorded with their governing citations, and
no token, colour, warning role, score or scenario name was introduced anywhere (ux-laws §9, all
fourteen, held).
