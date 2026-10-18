# Documentation-vs-codebase drift audit — 2026-10-16

**Read-only.** No tracked file modified, nothing fixed, no `.md` edited. HEAD `e845887`, live main repo
`D:/Projects/umamusume-laravel13`.

**Path deviation, stated up front.** The dispatch names `docs/audits/doc-drift-audit-2026-10-16.md`.
That directory is, by the owner's ruling of 2026-10-16, for knowledge gathered from and about *Umamusume Pretty
Derby* only. This is a repository audit, so it lives here in `docs/audits/`, beside `full-audit-2026-10-16.md` and
the `rice-shower-unity-cup-*` documents. Recorded rather than silently overridden.

---

## 1. Scope

| Measure | Count | Note |
|---|---|---|
| `.md` under the repo, excluding `node_modules/`, `vendor/`, `.git/` | **5,534** | the dispatch's precondition command |
| `.md` under `node_modules/`, `vendor/`, `storage/` | **568** | out of scope per the constraints |
| `.md` under `.kilo/` worktrees | **3,508** | out of scope — duplicate trees |
| `.md` under `.scratch-uma/` | ~600 | **not read as evidence** (constraint), and not read as corpus |
| `.md` under agent-tool skill mirrors (`.cursor`, `.gemini`, `.github`, `.windsurf`, `.warp`, `.trae`, `.roo`, `.kiro`, `.kilocode`, `.grok`, `.factory`, `.continue`, `.codewhale`, `.codebuddy`, `.augment`, `.hermes`, `.opencode`, `.claude`, `.agents`, `.superpowers`, `.junie`, `.codex`, `.cline`, `.copilot`, `.impeccable`, `.qoder`, `.ai`, `.venv`) | ~1,200 | out of scope — gitignored scaffolding per `AGENTS.md` §11 |
| **Documentation corpus actually swept** | **129** | the scripted walk |
| Same corpus by a looser `find` | 154 | the 25-file delta is `jev-inspect/*`, `storage/app`, and the four small tool dirs the script prunes. **Not reconciled exactly** — reported as a discrepancy, not smoothed over |

`find . -name "*.md"` returns **5,534**, far over the dispatch's 100-file batching threshold, so this pass ran
batched. Coverage is stated in §9; the split is not silent.

## 2. Method

Three scripts, all read-only, written to `.scratch-uma/doc-drift-2026-10-16/` (my own tooling; no `.scratch-uma`
file is cited as evidence anywhere in this report):

| Script | Does |
|---|---|
| `extract.py` | walks the 129-file corpus, pulls every backticked token, classifies it as a path / class / route claim, tests it against the tree |
| `classify.py` | buckets every absent-path claim into INTENTIONAL / PREFIX-RELATIVE / BASENAME-MISSING / GENUINE |
| `phase5.py` | enumerates 672 real code surface files across 16 directories and reports those named by no `.md` anywhere |

**What counted as a claim.** Only tokens whose presence in the tree is decidable by one check: a path with a known
extension, an `App\…` class, or an explicit `route('name')` call. Prose, intent, design language and opinion were
not extracted.

**What was NOT checked, and why.** The dispatch's claim taxonomy is 15 kinds; this pass swept **3** mechanically
(path, class, route name). Behaviour claims, commit SHAs, test counts, model columns, config keys, env keys, enum
cases, source citations and route URLs were **not swept**. Those need either `php artisan route:list`,
`migrate:status`, or a test run — all barred by the constraints (no build, no browser suite, no gate runs) — or
human reading per file, which at 129 files including a 535 KB `SCREEN_SPEC.md` and a 410 KB
`frontend-development-plan.md` is a multi-pass job. They are named in §9.

**Two instrument bugs found and fixed mid-pass, and both changed the numbers:**

1. **`ROUTE_RE` was not a route detector.** A dotted lowercase token is overwhelmingly a `table.column`, a config
   path, or a JS API. The first run flagged **716** "absent routes"; almost all were noise. Replaced with an
   explicit `route('x')` call scan → **1** absent route claim. Separately, `route_names()` scanned only `routes/`,
   and `routes/web.php:276` says the artwork route is registered in `bootstrap/app.php` outside the `web` group —
   so `artwork.show` was falsely counted absent. Adding `bootstrap/app.php` took the parsed set 84 → **85**.
2. **`.lstrip(".\\/")` ate the leading dot of dot-directories.** `lstrip` strips *every* leading character in the
   set, so `.ai/rules/index.md` became `ai/rules/index.md` and every dot-directory cite read ABSENT. Direct check:
   `.ai/rules/index.md` is **PRESENT**. Fixed → absent-path rows 3,206 → **3,032**.

The lesson is the same one `AGENTS.md` §9 keeps teaching: an instrument that cannot fail, or fails silently, is
not evidence. Neither bug was visible until a known-present file was reported absent.

## 3. Verified claims summary

| | Claims | Verified | Absent |
|---|---|---|---|
| Path | 11,057 | 8,025 | **3,032** |
| Route name | 33 | 32 | **1** |
| Class | 101 | 99 | **2** |
| **Total** | **11,191** | **8,156** | **3,035** |

72.8% of path claims resolve. The absent 3,032 are not 3,032 findings — they are rows, and they collapse:

| Bucket | Rows | Is it a finding? |
|---|---|---|
| INTENTIONAL | 85 | **No.** `storage/` backups and snapshots, `.scratch-uma/` cited evidence, `test-results/`, `public/build/`, plus `SECURITY.md` and `config/inertia.php` — both of which `AGENTS.md` §12 and §18 state do **not** exist |
| PREFIX-RELATIVE | 210 | **No.** Instrument artifact: `Support/Builder.vue` resolves under `resources/js/pages/`. The cite is short, not wrong |
| BASENAME-MISSING | 1,030 | **Candidate.** A bare filename found nowhere — `CONSTRAINTS.md` (201×), `PLAN.md` (58×), `StatBand.vue` (17×), `welcome.blade.php` (16×), `UraPanel.vue` (10×) |
| GENUINE | 1,707 | **Candidate.** A full path from a known root that is absent — `docs/research-scratch/DESIGN-CORPUS.md` (73×), `docs/research-scratch/GOVERNANCE.md` (56×), `resources/views/runs/show.blade.php` (27×) |

**2,737 candidate rows across the two real buckets.** Distinct-token counts were not tallied; the rows are
dominated by a few dozen names cited repeatedly, which is why the verified findings in §4 are stated per token,
not per row. Anyone reading "3,032 absent" as 3,032 defects would be wrong by roughly two orders of magnitude.

## 4. Absence findings

### 4.1 Claimed code artifacts that do not exist

Every row below was **verified by direct existence check this pass**, not inferred from the sweep.

| Artifact | Cited by | Rows | Reality |
|---|---|---|---|
| `StatBand.vue`, `RaceCalendar.vue`, `GuidedStep.vue`, `GradePointMeter.vue` | `AGENTS.md:209-210` §7 | 30 | **Four of the five scenario-driven components `AGENTS.md` §7 names do not exist.** Only `ResourceStrip.vue` is present. §7 also pins `ResourceStrip.vue:14` as the `scenarioLabel` prop line — the surviving half of a five-item list |
| `CONSTRAINTS.md` | `AGENTS.md:88`, `CLAUDE.md:10`, `DESIGN.md:882` | 201 | **Tracked but deleted, uncommitted.** `git status` = ` D CONSTRAINTS.md`. The root pointer stub `AGENTS.md` §3 still lists in its documentation map is gone from the working tree |
| `PLAN.md` | `AGENTS.md:91` | 58 | Same: ` D PLAN.md` |
| `tailwind.config.js` | `ARCHITECTURE.md:20`, `DESIGN.md:878` | 9 | Absent. Tailwind v4 is CSS-first, so the file was correctly removed — the citation was not updated |
| `resources/views/runs/show.blade.php` | `SCREEN_SPEC.md:455` | 27 | Absent. Also `catalog/show.blade.php` (15×), `welcome.blade.php` (10×), `components/layout.blade.php` (8×), `runs/create.blade.php` (10×) — **`AGENTS.md` §1 says "4 Blade files only"** and §13 calls `resources/views/components/` empty since B1, yet the spec corpus still names five retired Blade views as live surfaces |
| `docs/research-scratch/GOVERNANCE.md` | `KNOWN-ISSUES.md:2653` | 56 | Absent. `AGENTS.md` §2 and §3 name the gate registry as a **section of** `docs/research-scratch/GOVERNANCE.md` (which **is** present), not a root file. Cited by its retired path |
| `docs/research-scratch/GOVERNANCE.md` | `KNOWN-ISSUES.md:2653` | 43 | Same folding |
| `docs/research-scratch/GOVERNANCE.md` | `docs/UMAMUSUME_REFERENCE.md:145` | 31 | Absent; cited as a live master |
| `DESIGN.md` | `DESIGN.md:30`, `:884` | 57 | Absent. `DESIGN.md` cites a predecessor of itself at a path that no longer exists |
| `docs/research-scratch/DESIGN-CORPUS.md` | `DESIGN.md:887` | 73 | Same — the single most-cited absent path in the corpus |
| `resources/js/pages/Runs/Show.vue` | `KNOWN-ISSUES.md:2172` | 47 (38 + 9) | Absent. `KNOWN-ISSUES.md` itself uses the past tense ("which owned the equivalent forms") — the cite is historical, the path is dead |
| `resources/js/trainee-combobox.ts` | `AUDIT-AND-VERIFICATION.md:918` | 19 | Absent |
| `resources/js/app.ts` / `app.js` | `frontend-development-plan.md:291`, `AUDIT-AND-VERIFICATION.md:629` | 20 | Both absent. `AGENTS.md` §6 names `resources/js/spa.ts` as the entry; the plan cites the pre-2.0 entry |
| `tools/screenshots.py` + `screenshots_selftest.py` | `PROCESS-PLANS.md:7333` | 22 | Both absent |
| `docs/proposals/provenance-and-absence-contracts.md` | `docs/audits/rice-shower-unity-cup-round2-reconciliation.md:1511` | 15 | `D` in the working tree — tracked, deleted uncommitted |
| `App\Actions\UpsertCharacterCard` | `CATALOG-ROSTER-WORKSTREAM.md:2016` | 1 class | Absent. The only genuine absent class in the corpus |
| `route('run')` | `frontend-development-plan.md:1249` | 1 route | Absent against the 85 parsed route names. Either a prose abbreviation or a stale name |

**Four scenario guides are in the same state as `CONSTRAINTS.md`** — `docs/proposals/Our-Grand-Concert-plan.md` (22×),
`docs/proposals/Trackblazer-plan.md` (12×), `docs/proposals/URA-Finale-plan.md` (10×), `docs/proposals/Unity-Cup-plan.md` (10×) are all
**tracked and deleted uncommitted** (`git status` ` D`), and `config/scenarios.php` prose plus the metadata
reports cite them by path. A peer is mid-move; until it lands, 54 citations read as drift that may in fact be a
relocation.

### 4.2 Claimed completions not in the tree

The archetype, cross-referenced from `full-audit-2026-10-16.md` §4 B6 and re-confirmed here: a Report-class
document claims **`WriteTeamStateRequest` (Unity Cup)** landed. `grep -rn WriteTeamStateRequest app/ tests/
routes/` → **zero hits**; no such file. The capability exists under a different name —
`StoreTrainingRunRequest.php:85` validating `team_name`, reached by `routes/web.php:195 runs.update`.

Six further Report-class completions are **closed in the working copy but not at HEAD** (`metadata.ruleset`,
KI-102/103/104, the four locator fixes, `AGENTS.md`'s clean encoding). Those are a landing gap rather than a
false completion, and the mechanism that makes them look like drift is the same one behind §4.1's deleted files:
the working tree and HEAD disagree.

### 4.3 Claimed behaviors not in the code

**Not swept.** Behaviour claims are not decidable by a path test and were explicitly left out of the instrument.
The one contradiction found by reading, not sweeping, is `TurnChoice.php:72 'Fell' => false` against
`TrainingDecisionController.php:548` "penalty field has to appear for `Poor Rest` and `Fell` exactly as it does
for `Failure`" — recorded as `full-audit-2026-10-16.md` §9 D-14.

### 4.4 Claimed test coverage that does not exist

| Claim | Source | Reality |
|---|---|---|
| "`tests/Feature/CareerTrainingDetailTest.php` (10 cases…)" | `SCREEN_SPEC.md:2180` | **21** cases |
| "`tests/browser/career-training-detail.spec.ts` (6 cases…)" | `SCREEN_SPEC.md:2180` | **14** cases |
| "`tests/Feature/AncestryNodePickerTest.php`" | `KNOWN-ISSUES.md:1743` | **file absent** |
| "`tests/Feature/RunGoalsPanelTest.php`" | `KNOWN-ISSUES.md:1920` | **file absent** |
| "`run-detail.spec.ts`" | `KNOWN-ISSUES.md:2206` (18×) | **file absent** |

## 5. Undocumented surfaces

672 code surface files across 16 directories; **92 are named by no `.md` in the corpus.**

| Directory | Undocumented | 
|---|---|
| `database/migrations/` | 25 |
| `tests/Feature/` | 23 |
| `tests/browser/` | 9 |
| `resources/js/pages/Career/Cockpit/Panels/` | 6 |
| `resources/js/components/Form/` | 6 |
| `tests/browser/pages/` | 4 |
| `resources/js/components/scenario/` | 3 |
| `app/Services/DataPipeline/Contracts/`, `app/Enums/`, `app/Console/Commands/`, `resources/js/components/`, `tests/Unit/`, `config/` | 2 each |
| `app/Actions/`, `app/Http/Controllers/…`, `resources/js/components/review/` | 1 each |

### The headline: a MariaDB subsystem in a SQLite-only project

| Path | Kind | Size | Docs reference | Verdict |
|---|---|---|---|---|
| `app/Console/Commands/MigrateToMariaDb.php` | artisan command | 2,691 B | **none** | **UNDOCUMENTED-LIVE** — a registered `Command` class |
| `app/Actions/MigrateToMariaDbAction.php` | action | 8,217 B | **none** | **UNDOCUMENTED-LIVE** — instantiated at `MigrateToMariaDb.php:25` |
| `config/mysql-backup.php` | config | 414 B | **none** | **UNDOCUMENTED-LIVE** |
| `tests/Feature/MariaDbBackupTest.php` | feature test | 2,927 B | **none** | **UNDOCUMENTED-LIVE** |
| `app/Console/Commands/UmaDbGuard.php` | artisan command | — | **none** | **UNDOCUMENTED-LIVE** (registration not traced) |
| `app/Enums/CareerPhase.php`, `CareerYear.php` | enums | — | **none** | **not caller-verified** |
| `app/Services/DataPipeline/Contracts/SupportCardSourceParser.php`, `SupportEffectSourceParser.php` | contracts | — | **none** | **not caller-verified** — `AGENTS.md` §8 names `Contracts\SourceParser` but not these two |
| `config/filesystems.php` | config | — | **none** | framework default |
| 3 framework migrations (`users`, `cache`, `jobs`) | migrations | — | **none** | **expected** — `AGENTS.md` §1 calls the `users` table an unused framework default |

`grep -in "mariadb\|mysql" AGENTS.md` returns **nothing**. `grep -in mariadb ARCHITECTURE.md` returns **nothing**.
`AGENTS.md` §1 states: **"SQLite only (`database/database.sqlite`, WAL + `busy_timeout`)"**. §11 permits no new
package without owner approval, and §5's Floor bans undocumented surfaces in the same breath it bans suppressions.
So ~14 KB of live, registered, tested MariaDB code sits outside every authority document in the repository.
That is the pass's single strongest finding, and it is a Phase-5 finding — invisible to any forward reading of
the docs.

The other 79 undocumented files are bulk: migrations whose columns are read by models that name them
differently, test files no report cites, and the `components/Form/` family that `full-audit-2026-10-16.md`
Appendix B row T1-4 credits as **landed** while no `.md` names the individual files. Recommendation for the whole
long tail: **a generated surface register**, not prose — `AGENTS.md` §3's file discipline forbids a new root doc,
so `docs/research-scratch/INDEX.md` or a `composer docs` extension is where it belongs.

## 6. Cross-file contradictions

| Fact | Source A | Source B | Authoritative | Verdict |
|---|---|---|---|---|
| Database engine | `AGENTS.md` §1 "**SQLite only**" | `app/Console/Commands/MigrateToMariaDb.php` + `config/mysql-backup.php`, both live | **the code** (§2: "the code wins and the rule is stale") — but §2 also requires recording the conflict, and none is recorded | **UNRESOLVED** |
| Blade surface count | `AGENTS.md` §1 "4 Blade files only", §13 "`resources/views/components/` is empty since B1" | `SCREEN_SPEC.md:455`, `DESIGN.md:387,513,801` name five retired Blade views | the tree | **UNRESOLVED** (docs stale) |
| §7 component roster | `AGENTS.md:209-210` names 5 components | tree holds 1 | the tree | **UNRESOLVED** |
| SCR-CAR-012 test counts | `SCREEN_SPEC.md:2180` says 10 / 6 | tree holds 21 / 14 | the tree | **UNRESOLVED** |
| `StatBand.vue` | `frontend-development-plan.md:2107` "mid-migration… the tree disagrees" | `StatBand.vue` absent at both candidate paths | the tree | **RESOLVED by code, doc stale** |
| 1536px wide step | `DESIGN.md:313` "Dashboard-only" | `DESIGN.md:325` dated erratum "Every screen renders the composition its configured mode names" | `DESIGN.md:325` — it names its own supersession and preserves the original | **RESOLVED** |
| Dashboard grid cap | `DESIGN.md:317` "the Dashboard's grid no longer carries a page-level `max-w-*`" | `Dashboard.vue:139` `'grid w-full max-w-6xl …'` with `:135-138` defending it | unresolved owner question | **UNRESOLVED** (`full-audit` D-9) |
| `Fell` as failure | `TurnChoice.php:72` `false` | `TrainingDecisionController.php:548` treats it as one | the tree, once one moves | **UNRESOLVED** (D-14) |

## 7. Self-drift — docs that name their own drift

| File | Correction | Names | Verdict |
|---|---|---|---|
| `DESIGN.md:325` | "Erratum 2026-10-16 (owner ruling)… supersedes its scope, not its record" | `DESIGN.md:313` | **Accurate and correctly shaped** — the original sentence is preserved verbatim per §11. But the erratum sits directly above `:317`'s grid-cap claim, which §6 shows the code contradicts. **A correction that names a source which is itself stale** |
| `scenario-field-metadata-wiring-report-2026-10-16.md` §erratum | withdraws "the Rule family row prints a snake_case key" | `ScenarioSelectController::ruleFamilyLabel()` | **Accurate** — the method exists at `:208` and humanizes |
| `KNOWN-ISSUES.md:2901` KI-104 | "FIXED IN TREE, NOT LANDED" | `UnityCupEditor.vue` | **Accurate** — HEAD still nests the `<details>`; the working copy does not |
| `.scratch-uma/findings-capture-2026-10-16/` (not cited as evidence — the *record of it* in `KNOWN-ISSUES.md` is) | withdrawal of the snake_case reading | same | **Accurate**, consistent with the register |
| `frontend-fidelity-2026-10-13.md:335-346` | presents Track 2 as awaiting owner rulings | five owner-signed `DESIGN.md` amendments | **Stale, and it carries no drift note** — unlike `DESIGN.md`, it did not record its own supersession (`full-audit` D-11) |
| `frontend-development-plan.md:2107` | asserts a live `StatBand.vue` mid-migration | a file that no longer exists | **Stale, no drift note** (D-8) |

The pattern worth naming: the documents that carry dated errata (`DESIGN.md`, `KNOWN-ISSUES.md`) are accurate;
the ones that do not (`frontend-fidelity`, `frontend-development-plan`) are where the drift concentrates.
`AGENTS.md` §11 already mandates the former discipline. The drift is compliance, not convention.

## 8. What the docs claim that the code refutes

The corpus makes **11,191 checkable repo-fact claims and 8,156 of them hold** — the documentation is broadly
accurate, and any headline of "3,032 defects" would be a misread of my own instrument. What the refuted residue
actually says is narrower and more useful: **the documentation corpus is anchored to a tree that has been
restructured twice since it was written, and the restructurings were recorded in the code and in dated errata but
not in the cites.** Four of the five scenario-driven components `AGENTS.md` §7 instructs an agent to reuse do not
exist; §1 declares the Blade surface down to four files while `SCREEN_SPEC.md` and `DESIGN.md` still name five
retired views as live; §3's documentation map lists `CONSTRAINTS.md` and `PLAN.md`, both tracked and deleted
uncommitted, cited 259 times between them; and the folded masters the corpus still cites by their pre-folding root
paths — `docs/research-scratch/GOVERNANCE.md`, `docs/research-scratch/GOVERNANCE.md`, `docs/research-scratch/GOVERNANCE.md`,
`docs/design-research/{DESIGN,CONSTRAINTS}.md` — account for ~260 rows against `AGENTS.md` §17's own rule to cite
the master and its anchor instead. Add two claimed-but-absent test files, a spec that undercounts its own screen by
11 and 8 cases, and one Report claiming a request class that has never existed. None of this is fabrication; it is
rot, and it is concentrated in exactly the files that do not carry dated correction notes.

## 9. What the code holds that the docs do not name

92 of 672 surface files are named nowhere in the corpus. Three-quarters of that is an acceptable long tail —
framework-default migrations, per-slice tests no report quotes, the `components/Form/` family that a plan credits
as landed without naming its members. But the head of the list is not. **`app/Console/Commands/MigrateToMariaDb.php`,
`app/Actions/MigrateToMariaDbAction.php`, `config/mysql-backup.php` and `tests/Feature/MariaDbBackupTest.php` —
about fourteen kilobytes of live, registered, tested MariaDB code — appear in no document in this repository**,
including the two that would own the question: `AGENTS.md`, which states in its first section that the project is
SQLite-only, and `ARCHITECTURE.md`, which owns the schema. There is no ADR for it, no erratum, no `KI` entry and no
register line. A reader following the documentation would conclude the MySQL driver does not exist in this project
and would be wrong. `app/Console/Commands/UmaDbGuard.php`, two enums (`CareerPhase`, `CareerYear`) and two
`DataPipeline` contracts (`SupportCardSourceParser`, `SupportEffectSourceParser`) are undocumented alongside it.
The reverse-direction finding is the more dangerous half of this pass, because nothing in the docs — and therefore
nothing in any agent's context — points at it.

## 10. Coverage: what was and was not done

| Phase | Status |
|---|---|
| 1 classify every `.md` | **Not done as specified.** Classification needs a per-file read of 129 files. The corpus was instead bucketed by directory and by whether it is repo-fact-bearing; the sweep covered all 129 regardless of class |
| 2 extract claims | **Partial — 3 of 15 kinds** (path, class, route), mechanical, all 129 files |
| 3 cross-reference | Done for those 3 kinds, with two instrument bugs found and corrected |
| 4 absence findings | Done for path/class/route claims; §4.3 behaviour **not swept** |
| 5 undocumented surfaces | **Done** — 672 files, 92 unnamed |
| 6 contradictions | **Sampled**, not exhaustive — 8 found by reading, 3 of them new to this pass |
| 7 self-drift | **Sampled** — 6 entries |
| 8 report | This file |

**Batches not covered**, in the dispatch's order: per-file claim inventories for batches 1–8; SHA verification
(`git cat-file -t` on every cited sha — hundreds of cites, none checked); test-count claims beyond `SCR-CAR-012`;
config-key and env-key claims; enum-case claims; model-column and migration claims; source-citation claims
(`UMAMUSUME_REFERENCE.md:178` style); the worktree `.md` sweeps (batches 1–7 against each of the five worktrees).
A full pass at that granularity is a multi-session job, and this report does not claim to be one.

## 11. Explicit stop

No file was modified. Nothing was fixed, renamed, re-pointed or committed. The two scripts and their outputs live
under `.scratch-uma/doc-drift-2026-10-16/` and are cited as **method**, never as evidence — every finding above
was re-checked against the tree by a second instrument (the Grep tool or a direct `ls`), because a local hook in
this environment strips single quotes from Bash arguments and that bug alone produced two false readings this pass.

No gate ran: `php artisan test`, `route:list`, `migrate:status`, `pint --test`, PHPStan, `npm run typecheck`,
`composer lore`, `composer lore-code`, `npm run build` and the browser suite are all **NOT RUN**. Route URLs, live
behaviour and test outcomes therefore remain AMBIGUOUS by construction, and §4.3 and §10 say so rather than
inferring them. Cross-model review was neither offered nor used.

`DOC-DRIFT AUDIT COMPLETE — 129 .md files read, 11,191 claims extracted, 8,156 verified, 0 drifted (shape-drift
not swept), 3,035 absent, 92 undocumented surfaces; report at docs/audits/doc-drift-audit-2026-10-16.md. No file changed.`

### Errata, 2026-10-16 (filed after the follow-up passes)

One correction. Original text preserved above per `AGENTS.md` §11 — the erratum quotes the claim it supersedes,
states the correction, then names the evidence.

**E-10 — §4.1's "cites a predecessor of itself" row is overstated.** Original, the `DESIGN.md`
row in §4.1: *"Absent. `DESIGN.md` cites a predecessor of itself at a path that no longer exists."* Correction:
**both cites carry the word `formerly`**, so they are labelled historical pointers, not live references to a
missing file. `DESIGN.md:30` reads "the consolidated research package, **formerly**
`DESIGN.md`" and `:884` reads "**formerly** `DESIGN.md`". The path is
indeed gone, but a reader is told so at the point of the cite. The row's *count* (57 rows against that token)
stands; its *characterisation* as drift does not. This is the same class as the D-12 withdrawal filed in
`full-audit-2026-10-16.md` — a citation that names a missing file is not automatically drift when the sentence
itself marks it as superseded.

Verified: `sed -n '28,32p' DESIGN.md` → "consolidated research package, formerly `DESIGN.md`";
`sed -n '882,886p' DESIGN.md` → "`docs/research-scratch/DESIGN-CORPUS.md` section \"DESIGN.md\" (formerly
`DESIGN.md`): measured ramps, contrast tables,".

**Anchor note.** This audit was measured at `e845887`; the tree has since advanced to `23f9d64` (`cbb7df8` merged
`feature/screenshot-registry`, plus four follow-up commits). The erratum is filed against the later tree; the
audit body remains the record of the earlier read.
