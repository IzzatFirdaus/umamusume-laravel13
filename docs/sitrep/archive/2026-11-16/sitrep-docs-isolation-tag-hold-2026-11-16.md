# SITREP — Docs Isolation, KI Corrections, and Tag Hold

**Session date:** 2026-11-16 (work continued past midnight into 2026-11-17 by host clock)
**Author:** Hermes Agent
**Report written:** after all work in this session stopped
**Scope:** the brief *"Safe Isolation, Register Corrections, and Release Prep"*, its follow-up authorizing PR #25's merge, and the follow-up authorizing the tag attempt.

Every claim below is tagged:

- **[V]** Verified — measured this session via command output or file read; the command is cited.
- **[C]** Computed — calculated locally, not gate-confirmed.
- **[A]** Assumption / unverified — stated as such, not acted on.

---

## 1. Original objectives

The session ran three briefs in sequence. Stated goals, verbatim intent:

**Brief 1 — isolate, correct, prepare.** Do not push local `master` (it carries five peer commits plus
uncommitted peer work). Cut a clean branch from `origin/master`, cherry-pick this session's two verified
documentation commits (`7717111c`, `255b0c30`), apply three authorized `KNOWN-ISSUES.md` corrections
(KI-116, KI-114, KI-127), push, open a PR, do **not** merge; do **not** tag; do **not** touch the peer-owned
`docs/audits/RECONCILIATION-REGISTER.md`.

**Brief 2 — merge PR #25.** Authorized, on the grounds that it is docs-only, gates pass, no peer commits
included, and no CI is expected by its trigger design. Tag still refused.

**Brief 3 — resolve the tag blocker or document the hold.** Check the quiet-box precondition; if the host
fails, do not run the browser suite and record a HOLD; if it passes, run `npx playwright test --workers=1`,
and tag `v3.0.0` only on green.

---

## 2. Verified project state

### 2.1 Shared working tree (not this session's to modify)

| Fact | Value | Command |
|---|---|---|
| Branch | `master` | `git rev-parse --abbrev-ref HEAD` |
| HEAD | `be8893a2` | `git rev-parse HEAD` |
| Dirty paths | **198** | `git status --short \| wc -l` |
| Composition | 39 `M`, 108 `D`, 47 `??` | `git status --short` grouped |
| Stashes | **6** | `git stash list \| wc -l` |
| vs `origin/master` | **15 ahead, 5 behind** | `git rev-list --count` both ways |
| `v3*` tags | **none** | `git tag -l 'v3*'` |
| Peer file | ` D docs/audits/RECONCILIATION-REGISTER.md` still deleted-on-disk | `git status --short` |

The shared tree moved during the session. It was `3cbbd8b9` at PR #25's open, `e1253e94` at the tag-premise
check, `47c148be` after, and `be8893a2` at report time. Peer commits landed between every measurement. **[V]**

The 15-ahead set is peer work, not this session's: `be8893a2`, `248e1cbb`, `9638ee4b`, `47c148be`,
`20dfeb38`, `e1253e94`, `3cbbd8b9`, `7f99e3d6`, `255b0c30`, `2be83336`, `aefd6549`, `50e31d14`, `1a1aeca3`,
`7717111c`, `3158a9b8`. **[V]** Note that `255b0c30` and `7717111c` appear here — they are the *originals*
this session cherry-picked; the local `master` copies were never pushed and never will be, because the
cherry-picks `d24b21cf` and `bc9a7550` carry byte-identical content on the remote.

### 2.2 This session's worktree

| Fact | Value | Command |
|---|---|---|
| Path | `.scratch-uma/worktrees/docs-corrections` | `git worktree list` |
| Branch | `feature/docs-and-corrections` | `git rev-parse --abbrev-ref HEAD` |
| HEAD | `2142f553` | `git rev-parse HEAD` |
| Working-tree status | **clean** (empty `git status --short`) | `git status --short` |
| Roadmap file | 391 lines | `git show 2142f553:docs/roadmap/...` |

### 2.3 Remote state

| Ref | SHA | Command |
|---|---|---|
| `origin/master` | `46c61e1c` | `git ls-remote origin refs/heads/master` |
| `origin/feature/docs-and-corrections` | `2142f553` | `git ls-remote origin` |
| PR #25 | **MERGED**, merge commit `46c61e1c`, `mergedAt 2026-11-16T17:31:40Z`, `mergedBy IzzatFirdaus` | `gh pr view 25 --json state,mergedAt,mergeCommit` |
| PR #26 | **OPEN**, `MERGEABLE`, 1 file | `gh pr view 26 --json state,mergeable,files` |

### 2.4 Worktrees present on the box

Four, two of which are not mine: **[V]** `git worktree list --porcelain`

- `D:/Projects/umamusume-laravel13` — `master` @ `be8893a2` (shared, peer-dirty)
- `D:/Projects/umamusume-laravel13/.scratch-uma/worktrees/docs-corrections` — mine @ `2142f553`
- `D:/Projects/umamusume-laravel13/.scratch-uma/worktrees/s2-head` — detached @ `7f99e3d6` (peer)
- `D:/tmp/td-test-f737` — detached @ `e1f4d66a` (peer, and **outside the repository**, which AGENTS.md §9 forbids; not this session's artifact)

---

## 3. Work completed

| # | Work | Evidence | Status |
|---|---|---|---|
| 1 | Cut isolated worktree from `origin/master` `ae26b799` instead of resetting shared `master` | `git worktree list` shows the branch; shared `master` HEAD unchanged by me | **Done [V]** |
| 2 | Cherry-pick `7717111c` → `bc9a7550` | Content compare of the roadmap file at both revs: `identical=True`, 23,505 chars each | **Done [V]** |
| 3 | Cherry-pick `255b0c30` → `d24b21cf` | Content compare: `identical=True`, 33,943 chars each | **Done [V]** |
| 4 | Three `KNOWN-ISSUES.md` corrections, each from measurement | `git show --stat c8cd2835` → `KNOWN-ISSUES.md \| 46 ++-\| 39 insertions, 7 deletions` | **Done, merged [V]** |
| 5 | Roadmap R4 rewritten with branch, gates, tag command | `git show --stat c6ac080d` → `63 insertions, 3 deletions` | **Done, merged [V]** |
| 6 | Push branch, open PR #25 | `gh pr view 25` → `MERGED`, `46c61e1c` | **Done [V]** |
| 7 | Merge PR #25 as authorized | `gh pr view 25 --json mergedAt` → `2026-11-16T17:31:40Z` | **Done [V]** |
| 8 | Test the tag-blocker premise (spec red?) | Two green runs, §7.1 | **Done, premise refuted [V]** |
| 9 | Check quiet-box precondition | Three failing axes, §7.2 | **Done, host fails [V]** |
| 10 | Record measured HOLD in roadmap | `git show --stat 2142f553` → `62 insertions, 20 deletions` | **Done, PR #26 open [V]** |
| 11 | Tag `v3.0.0` | `git tag -l 'v3*'` → empty | **NOT DONE — blocked, deliberately [V]** |
| 12 | Full 124-spec browser suite | — | **NOT RUN — host contention, deliberately [V]** |

### 3.1 The three KI corrections, as landed

Verified present in `origin/master`'s `KNOWN-ISSUES.md`: **[V]**

**KI-116** — index row now reads: `KI-116 OPEN — openable-unopened: forLevel() exists at SupportCardEffects.php:209, prior "does not exist" claim refuted 2026-11-16`. Body gains a dated `2026-11-16` note at line 3680 recording the signature (`public static function forLevel(int $cardId, int $level, ?array $dictionary = null): array`), the docblock at `:199`, the `atLevel()` cross-reference at `:156`, and that no ADR is owed because `card_level` storage already landed at `df624cca`.

**KI-114** — item 2 of the cause list withdrawn, not the whole entry. The shipped string is `Not equipped.` at `SupportSlot.vue:143`, and both named specs already assert it. Items 1, 3, 4 (the deck-picker migration) stand.

**KI-127** — recorded as measured rather than as briefed. `CapsuleHeader.vue` was never deleted tree-wide; it is a tracked orphan with one comment reference. `c1f09053` replaced the facility panel's capsule with the page-wide plain `<h2>`. The `.card` wrapper from `1119e437` survives. The panel remains a dead registration.

---

## 4. Files created / modified

| Path | Change | Where it landed | Mine? |
|---|---|---|---|
| `KNOWN-ISSUES.md` | +39 / −7 | `c8cd2835`, merged into `46c61e1c` | **Yes** |
| `docs/roadmap/v3.0.0-implementation-roadmap-2026-11-16.md` | created (349 lines added across the PR), then +63/−3 | `bc9a7550` + `c6ac080d`, merged into `46c61e1c` | **Yes**, from a file that was untracked in the shared tree |
| `docs/roadmap/v3.0.0-implementation-roadmap-2026-11-16.md` | +62 / −20 | `2142f553`, **on the open PR #26 only** | **Yes, unmerged** |
| Everything else in the PR #25 diffstat | — | — | No: `ae26b799..46c61e1c` is exactly two files |

The PR #25 diffstat, as proof the scope claim held: **[V]**

```
KNOWN-ISSUES.md                                    |  46 ++-
 .../v3.0.0-implementation-roadmap-2026-11-16.md    | 349 +++++++++++++++++++++
 2 files changed, 388 insertions(+), 7 deletions(-)
```

Zero peer files, zero peer commits. **[V]**

### 4.1 Scaffolding created in the worktree, all git-ignored

Needed to make the worktree runnable for the browser spec. None of it is tracked. **[V]** `git status --short --ignored` shows exactly 11 ignored entries:

`node_modules/` (Windows junction to the shared tree), `vendor/` (pre-existing junction), `public/build/` (copied, then rebuilt), `.env` (copied), `bootstrap/cache/services.php` + `packages.php` (copied), `storage/framework/views/*.php`, `storage/logs/laravel.log`, `test-results/`, `tools/__pycache__/`.

---

## 5. Decisions and rationale

**D1 — Worktree isolation instead of `git reset --hard origin/master`.** The brief ordered a reset. At execution the shared tree held **186** dirty paths and `master` was 7 commits ahead of `origin/master`. The reset would have destroyed uncommitted peer work *and* peer commits while the brief's next step expected those peer files present. A worktree cut from `origin/master` achieves the brief's stated intent (separate this session's docs commits from the peer chain) with zero destruction. **This is the single most consequential deviation from the brief.**

**D2 — Cherry-pick rather than push local `master`.** Straight from the brief, and correct: local `master` carries peer commits, some unverified.

**D3 — Refute, don't obey, on all three KI premises.** Each of the brief's three claims was tested against the tree; two were partly or wholly wrong (KI-127 wholly, KI-114 one-quarter). Recorded what was measured. The owner subsequently endorsed exactly this behaviour.

**D4 — Test the tag blocker instead of reporting it.** The brief asserted `full-career-journey.spec.ts` was red. Three ancestors of `origin/master` claimed that was fixed (`9b594a27`, `83105a45`, `f080bb99`), so the claim was checked by running the spec. It is green. This dissolved the stated blocker and exposed the real one.

**D5 — Do not run the full browser suite.** The host failed all three axes of the repo's own precondition. AGENTS.md §9 and KI-74 say a contended box voids a sweep. Running it would have produced failures attributable to the box, and reporting them as findings would have been worse than reporting nothing.

**D6 — Do not tag.** `git tag` needs a green full suite and an owner-chosen SHA. Neither existed.

**D7 — PR #25 merged on explicit authorization; PR #26 opened but not merged.** Merging a PR is a remote write; the owner authorized #25 specifically after seeing its diffstat. #26's authorization has not arrived.

---

## 6. Tests and outcomes

### 6.1 `full-career-journey.spec.ts` — the premise under test

Run against worktree content byte-identical to `origin/master` (`git diff --stat origin/master HEAD` empty). Each run got its own port and scratch database. **[V]**

| Run | Command | Port | DB | Result | Test duration |
|---|---|---|---|---|---|
| 1 | `PLAYWRIGHT_PORT=8262 PLAYWRIGHT_SCRATCH_DB=... npx playwright test tests/browser/full-career-journey.spec.ts --workers=1 --reporter=json` | 8262 | `probe-8262.sqlite` | `expected: 1, skipped: 0, unexpected: 0, flaky: 0`, `errors: []`, exit 0 | 13,659 ms |
| 2 | same, port 8263 | 8263 | `probe-8263.sqlite` | `ok 1 [chromium] … walks the whole career from the wizard to a filed Veteran (9.1s)`, `1 passed (26.4s)`, exit 0 | 9.1 s |

**Not a vacuous pass.** After both runs the scratch database held **0 rows** in all five tables checked — `independent_training_runs`, `training_runs`, `turn_entries`, `veterans`, `deck_slots` — which is the spec's own `afterEach` cleanup, and can only happen if the walk created and filed what it claims to. **[V]**

**Why it is green:** `9b594a27` re-baselined `:114`/`:128`/`:133` against the shipped cockpit, and `83105a45` re-baselined the KI-139 deck door. Both are ancestors of `origin/master`. `f080bb99`'s own commit message records `1 passed (23.8s)`. The spec source at `origin/master` carries `details[data-region="team-state"]`-class scoping and the identity read-back that replaced the removed `CockpitIdentityCard` assertion. **[V]**

### 6.2 Quiet-box precondition — three samples

`PROCESS-PLANS.md` sets the bar: `<30% CPU, >6GB free, zero peer runs`. **[V]**

| Sample | Free memory | CPU | `php` procs | `node` procs | Verdict |
|---|---|---|---|---|---|
| 1 (early, before spec runs) | — | — | 0 | 0 | quiet enough, spec runs taken |
| 2 (pre-merge check) | 3,067,212 KB ≈ **2.93 GB** | **62%** | 7 | 25 | **fails all three** |
| 3 (20 s later) | 3.40 GB → 3.83 GB | 40% → 55% | 7 | 25 | **fails all three** |

The 7 `php` processes include **three live peer `pest` runs**: pid 26188 `--filter=CareerS`, pid 23408 `--compact`, pid 7860 `--compact --disp`; plus `artisan serve` pid 10792 and `php -S 127.0.0.1:8000` pid 12840. **[V]**

`tools/gate-preflight.php` exits **0**, `training_runs LIMIT 1 in 4.00 ms` (threshold 2000 ms) — the SQLite path itself is not contended. That one green axis is why the precondition needs three axes, not one. **[V]**

### 6.3 Gates run, with output

| Gate | Command | Result |
|---|---|---|
| Markdown fences | `python tools/md_fence_check.py` | `0 invalid closing fence(s) in 240 file(s)`, exit 0 |
| Docs census | `python tools/doc_census.py` | `invalid closing fences (working tree): 0 across 0 file(s)`; `dead markdown links: 701 (701 GONE, 0 UNTRACKED)` — the 701 is the standing baseline, not a regression |
| Lore, docs mode | `php tools/lore.php` | `lore-docs: 632 hit(s), 75 exempt line(s)`; **delta 0** attributable to this session — the same 632 measured with the edits stashed in the same worktree |
| Lore, code mode | `php tools/lore.php code` | `360 hit(s)`; zero hits on `KNOWN-ISSUES.md` or the roadmap file |
| Tree matches HEAD | `git diff HEAD --stat` | empty at every commit point |

### 6.4 Gates NOT run, and why

- **PHP suite (`pest`)**, PHPStan, Pint, `npm run typecheck` — the change under review touches two markdown files and no PHP, TypeScript, Vue or Blade. The change-class table's minimum for a docs-only change is the doc gates above. The suite was run at R1.2 by the preceding session against the same code: `2436 passed / 37419 assertions`.
- **Full browser suite (124 specs)** — host failed the precondition, §6.2.
- **`npm run build`** was run in the worktree only to make the spec runnable: `✓ built in 9.89s`, exit 0. That bundle is what both green runs executed against. It is not a repository gate result.

---

## 7. Commits and branches

### 7.1 Landed on `origin/master` via PR #25

| SHA | Subject | Diffstat |
|---|---|---|
| `bc9a7550` | `docs(roadmap): record Track A green and Track B R1/R2/R3 measurements` | cherry-pick of `7717111c` |
| `d24b21cf` | `docs(roadmap): complete R2 classification of all 21 open issues` | cherry-pick of `255b0c30` |
| `c8cd2835` | `docs(issues): correct stale KI claims based on live tree measurement` | `KNOWN-ISSUES.md` +39/−7 |
| `c6ac080d` | `docs(roadmap): record branch isolation, gate output, and the exact tag command` | roadmap +63/−3 |
| `46c61e1c` | `Merge pull request #25 from IzzatFirdaus/feature/docs-and-corrections` | merge commit |

All four content commits confirmed ancestors of `origin/master` by `git merge-base --is-ancestor`. **[V]**

### 7.2 On the open PR #26, not merged

| SHA | Subject | Diffstat |
|---|---|---|
| `2142f553` | `docs(roadmap): tag on HOLD, measured host contention is the blocker, not the spec` | roadmap +62/−20 |

`2142f553` confirmed **NOT** an ancestor of `origin/master`. **[V]**

### 7.3 Branch

`feature/docs-and-corrections`, cut from `origin/master` `ae26b799`, local head `2142f553`, remote head `2142f553` — equal. **[V]**

---

## 8. Outstanding tasks, in execution order

1. **Merge PR #26** — https://github.com/IzzatFirdaus/umamusume-laravel13/pull/26. One markdown file, `MERGEABLE`, gates clean. Requires owner authorization; not self-authorized.
2. **Wait for a quiet box.** Re-run the three-axis check: free memory `>6 GB`, CPU `<30%`, zero peer `pest`/`serve` processes. The box was moving (3.40 → 3.83 GB, CPU 40 → 55%) throughout this session, so it may clear on its own.
3. **Run the full browser suite** on the worktree at `origin/master` content:
   `PLAYWRIGHT_PORT=<port> PLAYWRIGHT_SCRATCH_DB=.scratch-uma/sqlite/<name>.sqlite npx playwright test --workers=1`
   Session-private port and DB are mandatory (KI-73, KI-80).
4. **Tag, only if step 3 is green:** `git tag -a v3.0.0 -m "Trainer Desk v3.0.0" <sha>` then `git push origin v3.0.0`. The `<sha>` must be an `origin/master` commit whose full suite passed; `46c61e1c` is the candidate today, not a proven target.
5. **Verify the release workflow fired:** `release.yml` triggers on `v*` tags and on manual dispatch only.
6. **Resolve the shared `master` divergence** — 15 ahead / 5 behind `origin/master`. That is peer work; it is not this session's to push or reset.

---

## 9. Blockers

**B1 — Full browser suite unrun (host contention).** The binding tag blocker. Measured in §6.2. Environmental, not code. No code fix this session can make will clear it.

**B2 — Peer deletion ` D docs/audits/RECONCILIATION-REGISTER.md`** in the shared tree. At `origin/master` the file is present and both doc gates are clean over it, so the breakage is confined to the shared dirty tree. The peer must either `git restore` it or `git add` the deletion. Not this session's file.

**B3 — PR #26 awaits owner authorization.** Opened; merging is a remote write needing the same explicit authorization #25 got.

**B4 — `v3.0.0` does not exist.** `git tag -l 'v3*'` is empty. `VERSION.md:3` still says pre-release; `VERSION.md:9` names two conditions, one now measured met and one still unmet.

---

## 10. Known risks

**R1 — Register index rows are stale for KI-136/137/138/139.** `KNOWN-ISSUES.md:87-88` and `:196-197` print `KI-136 OPEN — not dispatched` / `KI-137 OPEN — not dispatched`, while the entry bodies were amended by `a13fc230` and `83105a45` to RESOLVED. **Not corrected here** — those rows were outside the brief's authorized three. A future session reading the index will conclude the whole-career walk is red when it is green. Same defect class as the three corrected entries.

**R2 — Local `master` is 15 ahead of `origin/master` with peer work.** If anyone pushes it, unverified peer commits publish. The two commits this session authored there (`7717111c`, `255b0c30`) are already superseded on the remote by content-identical cherry-picks, so nothing of value is lost by leaving them unpushed.

**R3 — Worktree scaffolding is fragile.** `node_modules` and `vendor` are Windows junctions into the shared tree; `.env`, `public/build`, and `bootstrap/cache/{services,packages}.php` are copies. If the shared tree is cleaned, dependency-installed, or rebuilt, this worktree breaks. The `bootstrap/cache` copies were required at all: without `services.php` Laravel re-read `packages.php`, which lists `Native\Desktop\NativeServiceProvider`, while `autoload_psr4.php` has no such mapping — producing `Class "Native\Desktop\NativeServiceProvider" not found` on every artisan call and a silent web-server start failure inside Playwright.

**R4 — `docs/sitrep/` holds 5 untracked sitreps.** The directory is not in `origin/master`, and AGENTS.md at `origin/master` does not name it. A local commit `2be83336 docs(config): promote docs/sitrep to standing directory` exists but is **not** an ancestor of `origin/master`, so the promotion is local-only. This report sits in that directory and is likewise untracked.

**R5 — `D:/tmp/td-test-f737` worktree is outside the repository.** AGENTS.md §9 forbids artifacts outside the tree. Not this session's artifact; flagged so it is not mistaken for mine.

**R6 — The 701 dead markdown links figure is a standing baseline, not a regression.** `doc_census.py` reports it every run. Do not treat it as damage from this session.

---

## 11. Uncommitted changes

**In this session's worktree: none.** `git status --short` is empty at `2142f553`. **[V]**

**In the shared tree: 198 paths, none of them this session's.** Composition 39 `M`, 108 `D`, 47 `??`. The one this session watched all along is ` D docs/audits/RECONCILIATION-REGISTER.md`. **[V]**

**Untracked artifact this report becomes:** `docs/sitrep/sitrep-docs-isolation-tag-hold-2026-11-16.md` in the shared tree. Nothing else was left behind: all probe databases (`probe-8261/8262/8263.sqlite`) were deleted, `.pr-body.md` was moved out of the worktree to the Hermes scratch cache, and the PR #26 body lives at `C:\Users\exatf\AppData\Local\hermes\cache\scratch\pr-hold-body.md`.

---

## 12. Deviations from the original plan

| Brief said | What happened | Why |
|---|---|---|
| `git reset --hard origin/master` on shared `master` | Not run; worktree cut instead | Would have destroyed 186 dirty peer paths and 7 peer commits |
| Cherry-pick `7717111c`, `255b0c30` | Done as `bc9a7550`, `d24b21cf` | As briefed; content verified byte-identical |
| KI-116: change status from `blocked` to `unopened` | Done, with the measured signature and no-ADR reasoning recorded | As briefed; premise independently verified true |
| KI-114: "the whole `Empty.` claim is false" | Only item 2 withdrawn; items 1/3/4 stand | Measurement showed 1 of 4 items false, not 4 of 4 |
| KI-127: note `CapsuleHeader` retired tree-wide at `c1f09053` | Recorded the measured truth instead: still a tracked orphan | The file was never deleted; the briefed sentence was false |
| "Do not merge yet. Let CI run" | PR #25 later merged on explicit authorization; **no CI ran** | None of the three workflows trigger on `pull_request` |
| "`full-career-journey.spec.ts` is still RED" | Refuted: green twice | Measured; fixes are ancestors of `origin/master` |
| "Run `npx playwright test --workers=1` if the box has quieted" | Not run | Box failed all three axes |
| "Run `git checkout master; git pull origin master; git tag`" | Not run | Shared `master` is peer-dirty and diverged; tagging awaits the suite |
| Tag "authorized, do not execute" (brief 1), then "execute if green" (brief 3) | Not executed | Condition precedent never met |

---

## 13. Verified vs assumption legend

**Verified [V]** — shared tree at `be8893a2` with 198 dirty paths and 6 stashes; worktree clean at `2142f553`; PR #25 merged at `46c61e1c`; PR #26 open and mergeable; no `v3*` tags; the two `full-career-journey.spec.ts` green runs with their durations and empty scratch DBs; the three quiet-box measurements; all four doc-gate outputs; the three KI corrections present in `origin/master`; the PR #25 diffstat covering exactly two files; cherry-pick content identity; `git worktree list`.

**Computed [C]** — none material. Currency conversions of `FreePhysicalMemory` (KB → GB) were done by PowerShell division and re-read as JSON.

**Assumption [A]** — that the two green spec runs are representative of the spec's health on this host rather than a lucky interleaving. Two consecutive greens after a documented multi-month red history is strong but not exhaustive; the authoritative confirmation is the full-suite run in step 3 of §8. Nothing in this report depends on the assumption being true — the tag is held either way.

---

## 14. Final handoff

Two things landed, one thing is held, and one premise was wrong.

**Landed:** PR #25 merged into `master` at `46c61e1c` — four commits, two files, zero peer content. It carries this session's two documentation commits plus three `KNOWN-ISSUES.md` corrections (KI-116, KI-114, KI-127), each written from measurement after the briefed wording was tested and partly refuted.

**Held:** `v3.0.0` is **not tagged** and the full 124-spec browser suite has **not run**. The stated blocker was wrong — `full-career-journey.spec.ts` is green, proven twice with empty scratch databases — but a real one stands behind it: `VERSION.md` also requires the full suite, and this host fails the quiet-box precondition on all three axes (≈3 GB free vs 6 GB, 40–62% CPU vs <30%, three live peer `pest` runs vs zero).

**Next, in order:** merge PR #26 (authorization needed) → wait for a quiet box and re-check the three axes → run the full suite with a session-private port and DB → tag on green → verify `release.yml`. Do not push the shared `master`: it is 15 ahead of `origin/master` and five of those are peer commits.

**Report path:** `D:\Projects\umamusume-laravel13\docs\sitrep\sitrep-docs-isolation-tag-hold-2026-11-16.md` (untracked, like the five sitreps beside it).
