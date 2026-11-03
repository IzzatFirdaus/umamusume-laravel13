# Git reconciliation — 2026-11-03

**Purpose.** This pass did one thing: force the prior reports' commit claims and the repository's
actual git state to agree. Every SHA is pasted with its `git cat-file` result. Every "committed"
claim below is followed by the `git log` line that proves it. The fresh-clone proof is pasted in §4.

**Path taken: A (the SHAs exist).** All 37 cited SHAs are `EXISTS`, and every one is contained in
`master` **and** `origin/master`; `git rev-list --count origin/master..HEAD` is `0` at pass start.
The `git status` that showed 60+ modified files and "up to date with origin/master" was the state
**before** the commits landed. The commits then landed, and the tree is now clean.

## §1 — Reconciliation table

All rows: `git cat-file -e <sha>^{commit}` → **EXISTS**; `git branch -a --contains` →

`* master` and `remotes/origin/master`. No row is `MISSING`.

| SHA | Claimed in | cat-file | Subject (git log -1) | Date |
|---|---|---|---|---|
| 88043b3b | this pass's final report; `2026-11-02-followup-closeout.md` | EXISTS | fix(catalog): render base stats and growth rates on trainee detail | 2026-11-03 |
| 45ebbc37 | same | EXISTS | fix(database): harden race detail values, rewards and absence notes | 2026-11-03 |
| 64446e10 | same | EXISTS | test(database): race-detail spec for populated, partial, empty states | 2026-11-03 |
| 6cbc52e4 | same | EXISTS | chore(pipeline): cite why 8 races lack enrichment | 2026-11-03 |
| 1c15a29a | same | EXISTS | fix(pipeline): give race enrichment parsers their own contract | 2026-11-03 |
| d6c5cd3d | same | EXISTS | test(database): update source ledger counts for the enrichment sources | 2026-11-03 |
| cfbdad26 | same | EXISTS | docs(proposals): follow-up closeout for catalog fix and addendum phases | 2026-11-03 |
| cf088ccb | `2026-11-02-followup-closeout.md` (plan closure) | EXISTS | fix(career-trainee-profile): restore aptitudeGroups and stats computeds | 2026-11-02 |
| 335881fd | `2026-11-02-ui-ux-audit-implementation-pass-5.md` | EXISTS | fix(pipeline): read a record's name leniently so one source's shape cannot crash another | 2026-11-02 |
| 627a7248 | pass-5 | EXISTS | fix(raceplanner): render every group, name the cap, drop the dead computed and the Critical prefix | 2026-11-02 |
| b0f5a1ef | pass-5; `audit-corrections.md` | EXISTS | fix(review): remove the dead sticky declaration on the pager | 2026-11-02 |
| d7869fff | pass-5 | EXISTS | docs(known-issues): KI-150 page-level sticky unavailable under the shell container context | 2026-11-02 |
| a76a95a1 | pass-5 | EXISTS | fix(veterans): neutral verdicts use the neutral pill | 2026-11-02 |
| 2187e057 | pass-5 | EXISTS | docs(audit-corrections): retract LegacyBuilder-F-01 and VeteransCompare-F-01 | 2026-11-02 |
| b4458c09 | pass-5 | EXISTS | fix(raceplanner): remove the duplicate closing template tag the group loop left | 2026-11-02 |
| 1af9eb53 | pass-4 | EXISTS | fix(racedecision): absent values use words not dashes | 2026-11-02 |
| a7d5cefb | pass-4 | EXISTS | fix(turnedit): page-load status, refusal focus | 2026-11-02 |
| 354779c9 | pass-4 | EXISTS | fix(saveveteran): confirm overwrite of existing veteran | 2026-11-02 |
| 3a9d4c76 | pass-4 | EXISTS | fix(career): race doors from route() not string replacement | 2026-11-02 |
| 4657be9e | pass-4; pass-5 | EXISTS | fix(legacy,runs): single-run compare states its own absence, one page name | 2026-11-02 |
| 5414af81 | pass-4 | EXISTS | fix(runsimport): drop the duplicated header list | 2026-11-02 |
| baf0146f | pass-4 | EXISTS | test(runs-create): pin the breadcrumb leaf to the page's own name | 2026-11-02 |
| 696d12de | pass-1; pass-2; pass-3 | EXISTS | fix(review): progress bar uses actual resolved count; add reopen path | 2026-11-02 |
| b9055ce0 | pass-2; pass-3 | EXISTS | fix(cockpit): skills URL from route() not string replacement | 2026-11-02 |
| 43f0b8b0 | pass-3 | EXISTS | fix(saveveteran,runs): precondition role, pager aria-disabled | 2026-11-02 |
| c60af3a1 | pass-3; pass-5; implementation index | EXISTS | feat(career-setup): resolve 7 findings | 2026-11-02 |
| c2967b36 | pass-3; pass-4 | EXISTS | fix(preferences): live regions, describedby ids, refusal focus, copy | 2026-11-02 |
| 5d3bda00 | pass-3; pass-4 | EXISTS | fix(dashboard): type scale to four tiers, decoupled Needs-you column | 2026-11-02 |
| 9d753b7a | pass-3; pass-4 | EXISTS | test(dashboard): pin the new quick-actions copy | 2026-11-02 |
| b95dc5cc | pass-3; pass-4 | EXISTS | fix(runs): status pill carries data-status so state is not colour-only | 2026-11-02 |
| c6e55619 | pass-3; pass-4 | EXISTS | fix(career-setup): remaining setup URLs from route() props | 2026-11-02 |
| 00e3cf15 | pass-3; pass-4 | EXISTS | test(utils): tick the finality acknowledgment before starting a run | 2026-11-02 |
| 21e13313 | named in the verification dispatch; own subject is the report commit | EXISTS | docs(proposals): pass-3 report for UI/UX audit implementation | 2026-11-02 |
| 15861ea3 | named in the verification dispatch; own subject is the report commit | EXISTS | docs(proposals): pass-2 report for UI/UX audit implementation | 2026-11-02 |
| a85ff5fa | pass-2 | EXISTS | docs(proposals): pass-1 report for UI/UX audit implementation | 2026-11-02 |
| a1bfac85 | `AUD-20261102-029`; legacy-builder pass; completion report | EXISTS | fix(legacy): parents side by side in standard and wide layouts | 2026-11-02 |
| 5827a7be | legacy-builder pass; completion report | EXISTS | docs(audits): M2 screen audit of /legacy/{id} | 2026-11-02 |

Machine output backing the table (full run in the session log):

```
EXISTS | 88043b3b | fix(catalog): render base stats and growth rates on trainee detail | contained-in: * master,  remotes/origin/HEAD -> origin/master,  remotes/origin/master
...
EXISTS | a85ff5fa | docs(proposals): pass-1 report for UI/UX audit implementation | contained-in: * master,  remotes/origin/HEAD -> origin/master,  remotes/origin/master
```

## §2 — Tree state at pass start

```
$ git status --short | wc -l
0

$ git rev-list --count origin/master..HEAD
0

$ git rev-parse HEAD
787554862e6478658a05f7e81a80a4cf9d8c3c67
$ git rev-parse origin/master
787554862e6478658a05f7e81a80a4cf9d8c3c67

$ git branch -a -v
  almondine-plywood                       f6018f87 docs(contributing): record the fact-check adjacency lesson
  chore/cloud-workspace-fixes             8ac014ea fix(devcontainer): boot cloud workspace end-to-end
* master                                  78755486 docs(proposals): audit implementation pass reports and closeout notes
  release/0.1.0                           d90de476 [ahead 2] docs(research-scratch): consolidate the Trainer Advisor spec ...
  release/2.1.0                           0ef1506c style(skills): format the planner controller after the docblock removal
  worktree/cherry-pick                    7da98984 [behind 4] feat(docs): add implementation instructions ...
  remotes/origin/HEAD                     -> origin/master
  remotes/origin/master                   78755486 docs(proposals): audit implementation pass reports and closeout notes
  ...

$ git stash list
stash@{0}: WIP on master: b4458c09 fix(raceplanner): remove the duplicate closing template tag ...
stash@{1}: On master: peer-traineeprofile
stash@{2}: On master: Phase 2.1 work in progress (foreign, pre-state)
```

`git status --short` was **empty** at pass start: no staged changes, no modified files, no
untracked files. The 60+ modified files and three untracked migrations described in the
critique were the pre-commit state seen earlier in the session; they were committed before this
pass began, by the series in `git log` (the three migrations are in `b63bee59
feat(db): schema migrations for trainee-profile and race-enrichment columns`, and the audit
reports in `2c9445e5 docs(audits): 2026-10-30 through 2026-11-02 UI/UX audit reports`).

Tracked-state checks:

```
$ git ls-files database/migrations/2026_11_02_112258_make_global_release_date_nullable_on_character_cards.php
database/migrations/2026_11_02_112258_make_global_release_date_nullable_on_character_cards.php
$ git ls-files database/migrations/2026_11_02_123126_add_race_enrichment_columns_to_race_catalog_slots.php
database/migrations/2026_11_02_123126_add_race_enrichment_columns_to_race_catalog_slots.php
$ git ls-files database/migrations/2026_11_02_141639_add_base_stats_and_growth_rates_to_character_cards.php
database/migrations/2026_11_02_141639_add_base_stats_and_growth_rates_to_character_cards.php

$ git ls-files docs/audits/ | wc -l      -> 43
$ ls docs/audits/ | wc -l                -> 43

$ git ls-files docs/framework-ui-ux/_audit-prompt.md
docs/framework-ui-ux/_audit-prompt.md
```

## §3 — Commits made this pass

| SHA | Subject | Files touched | Feature group |
|---|---|---|---|
| `032b0c02` | fix(env): quote APP_NAME so a fresh clone's .env parses | `.env.example` (1 line) | reproducibility |
| this commit (self) | docs(proposals): git reconciliation — prove the SHAs and the fresh clone | `docs/proposals/2026-11-03-git-reconciliation.md` | record |

No feature work was committed this pass: the feature commits in §1 already existed on `master`
and `origin/master`. `032b0c02` is a genuine fix found by the fresh-clone proof (see §4).

## §4 — Fresh-clone verification

Clone of `032b0c02`, a local `--no-hardlinks` clone into the OS temp directory, then
`composer install`, `.env` from `.env.example` with `DB_CONNECTION=sqlite` and an absolute
Windows `DB_DATABASE`, `key:generate`, `migrate --seed`, `npm ci`, `npm run build`.

```
$ git clone --no-hardlinks D:/Projects/umamusume-laravel13 <tmp>/verify
=== cloned HEAD 032b0c02 ===
$ composer install --no-interaction --prefer-dist --no-progress
...
Generating optimized autoload files
> @php artisan package:discover --ansi
...
110 packages you are using are looking for funding.

$ php artisan migrate --seed
   INFO  Preparing database.
  Creating migration table ................................................................ DONE
   INFO  Running migrations.
   2026_11_02_112258_make_global_release_date_nullable_on_character_cards ................ DONE
   2026_11_02_123126_add_race_enrichment_columns_to_race_catalog_slots ................... DONE
   2026_11_02_141639_add_base_stats_and_growth_rates_to_character_cards .................. DONE
    INFO  Seeding database.
=== migrate --seed OK ===

$ npm ci --no-audit --no-fund
=== build OK ===
✓ built in 14.14s

$ php artisan test tests/Feature/GametoraRaceFansParserTest.php tests/Feature/GametoraRaceParserTest.php
  Tests:    4 passed (19 assertions)
=== done ===
```

**Result: PASS** — migrate+seed, npm install and Vite build all succeed from a clean checkout,
and the schema includes the three 2026-11-02 migrations.

Two failures happened on the way and are part of the record:

1. **First clone attempt** failed at `php artisan key:generate`: *"The environment file is
   invalid! ... unexpected whitespace at [Trainer Desk]"*. Root cause: `.env.example` line 1 was
   `APP_NAME=Trainer Desk`, unquoted, which dotenv rejects. The shared working copy never hit it
   because its own `.env` differs. Fixed in `032b0c02`.
2. **Second attempt** failed at migrate because this verification script wrote the Git-Bash
   `/tmp/...` path into `DB_DATABASE`; PHP on Windows cannot open it. Re-running against the same
   clone with the `cygpath -m` Windows path succeeded (output above). This is a harness
   bug in the script, not a repository defect.
3. **The PASS is not out-of-the-box, and does not close KI-102.** `.env.example` still defaults to
   MySQL (`DB_CONNECTION=mysql`, `DB_HOST=mysql`, `DB_DATABASE=laravel`, lines 25–28). The literal
   next-clone move — `cp .env.example .env && php artisan migrate` — still dies at
   `getaddrinfo`. The run above supplied `DB_CONNECTION=sqlite` and an absolute `DB_DATABASE` by
   hand; `032b0c02` fixed only the unquoted `APP_NAME`. The revised plan's **Slice E** is
   therefore still required, and KI-102 remains open.

The full PHP suite was not run inside the clone; a representative parser pair was, to prove the
test harness boots against the seeded scratch-of-clone database.

## §5 — What remains uncommitted

```
$ git status --short
 D docs/audit-corrections.md
 D docs/audit-fix-pass-addendum.md
 D docs/pass2-closeout-notes.md
?? docs/proposals/audit-corrections.md
?? docs/proposals/audit-fix-pass-addendum.md
?? docs/proposals/pass2-closeout-notes.md
```

**Six lines, one peer action, nothing of this pass's.** These are a concurrent rename of three
documents from the repository root into `docs/proposals/`. They appeared at 12:52, after this
pass's 12:14 start (the tree was empty then, §2). The content did not change — blob hashes are
identical on both sides:

```
audit-corrections      old=2e14ecaa187d452d600ffb81b896cb0ebfb91d90 new=2e14ecaa187d452d600ffb81b896cb0ebfb91d90
audit-fix-pass-addendum old=22ff9331830339ed28a202bc0b3871a038fde8d7 new=22ff9331830339ed28a202bc0b3871a038fde8d7
pass2-closeout-notes    old=63b49d5f0d50d3bdbfe27f1f8dece452e56c353b new=63b49d5f0d50d3bdbfe27f1f8dece452e56c353b
```

They are **not committed here**: they belong to a peer session in this shared worktree, and
committing them would sweep that session's in-flight move into this pass's history. This pass's
own files are all committed; the tree holds no uncommitted work of mine.

## §6 — Owner actions

- **Push:** three commits are local and unpushed once this correction lands — `032b0c02`
  (env quoting), `c1e97c2c` (this report), and the correction commit that adds this section.
  `git rev-list --count origin/master..HEAD` will read `3`. They need a push before
  `origin/master` moves.
- **Peer coordination:** none required — no file was withheld.
- **Fresh-clone test failures:** none in the executed set. The full PHP/browser suites were not
  run inside the clone.

## §7 — The claim about the sequence

The 37 SHAs are accurate: all exist on `master` and `origin/master`, the tree was clean at the
start of this pass, and the three migrations are tracked. **The prior reports' commit
attributions were accurate; the `git status` they were judged against was the pre-commit
snapshot.**

That is the whole claim. It does **not** validate the reports' readings of those commits:

- pass-5 §2.3 mis-stated `a76a95a1`'s scope — the commit touched one file,
  `Veterans/Compare.vue`; `Legacy/Compare.vue:278-279` still routes `single column` to
  `pill-caution`;
- `AGENTS.md` §9's "219 as measured 2026-10-27" is contradicted by
  `ui-lint-baseline.json`'s 209.

Both remain flagged for the owner under the revised plan's §Deviations item 6.

**The sequence's work is durable in git as of `78755486` (origin/master). A clone of `032b0c02`
migrates, seeds and builds — after the harness supplies SQLite settings (§4, item 3). It is not a
zero-edit first-clone yet.**

## §8 — What this pass does not close

1. **Slice E / KI-102** — `.env.example` still defaults to MySQL; the literal
   `cp .env.example .env && php artisan migrate` still fails. See §4, item 3.
2. **Slice D** — the `Legacy/Compare.vue:278-279` pill mapping is unchanged; `a76a95a1` touched
   `Veterans/Compare.vue` only.
3. **Push** — the commits above are local; `origin/master` does not have them yet.


## Peer revert coordination note (2026-11-03)

9a61197f landed tests requiring fixes from 6f3d5a81 and e4786df4. The peer commit is not
self-contained: running it on 9a61197f alone, without those fixes, fails its two new focus tests.
The push sequence must include the whole series, moving together.

Preserved peer state: .scratch-uma/remaining-code-2026-11-03/*.staged-peer-revert.vue (2 files:
Edit.staged-peer-revert.vue, RaceDecision.staged-peer-revert.vue), untracked and not staged.
The peer should be told their pre-revert state was saved there.
