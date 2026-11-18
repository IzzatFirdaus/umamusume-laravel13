# Plan 2 of 4 - Test-suite and tooling truth (phase 2)

**Pair:** `docs/roadmap/remaining-work-roadmap-2026-11-17.md` phase 2.
**Depends on:** plan 1 (phases 0.x must be landed first; a sweep on an uninstallable tree measures nothing).
**Measured at:** `HEAD be8893a2`, 2026-11-17.

---

## Why this plan exists

Three independent defects make a browser sweep's result uninterpretable today, and two make the gate
sequence expensive to run at all. None is a product defect, which is exactly why they survive:

1. One case fails by construction in the default config.
2. One spec drives controls that no longer exist.
3. Seven probe files carry 9 investigation cases into a release gate.
4. The fence checker crashes when invoked the way `AGENTS.md` §10 documents it.
5. `composer test` is named like a release gate and is not one.

---

# Slice 2.1 - `nativephp-packaged.spec.ts` must not run in the default sweep

## Objective

`npx playwright test --list` counts this spec (`Total: 876 tests in 125 files`). Its first statements throw
when two env vars are absent:

```ts
if (!exePath) { throw new Error('NATIVEPHP_EXE_PATH is not set'); }
if (!secret)  { throw new Error('NATIVEPHP_SECRET is not set'); }
```

So a full sweep starts **one failure down**, before any product behaviour is exercised. The release plan
excluded it **in prose only**.

## Steps

1. Confirm: `npx playwright test --list | grep -c nativephp-packaged` -> 1.
2. Edit `playwright.config.ts` to exclude it from the default project:
   ```ts
   // The packaged-exe smoke runs only from playwright.nativephp.config.ts, which sets the two required
   // env vars (NATIVEPHP_EXE_PATH, NATIVEPHP_SECRET). Without them this spec throws on its first
   // statement, so leaving it in the default sweep starts every gate run one failure down.
   testIgnore: '**/nativephp-packaged.spec.ts',
   ```
3. Confirm the dedicated config still owns it: `playwright.nativephp.config.ts` has
   `testMatch: 'nativephp-packaged.spec.ts'` and its own 300 s timeout.
4. Re-list: `npx playwright test --list` and read its final count line -> the total drops by exactly 1 case / 0 files.
5. Prove the dedicated path still resolves: `npx playwright test --config=playwright.nativephp.config.ts --list`
   -> 1 test listed.

## Files modified

`playwright.config.ts`.

## Acceptance

Default listing no longer contains the spec; the dedicated config still lists it; both listings quoted.

## Risk

A silent `testIgnore` also hides a genuinely broken packaged build. Mitigate by leaving
`.github/workflows/nativephp-windows-acceptance.yml:88` as the place that runs it, and note in the commit body that the
coverage moved rather than disappeared.

---

# Slice 2.2 - `career-preflight-indep.spec.ts` drives removed controls (owner ruling required)

## Objective

This spec's only case asserts a `Career mode` group and a `Training policy` group. Both have **zero** hits in
`resources/js`: Phase E2 removed those controls, and `tests/browser/career-preflight.spec.ts:303` is the counter-spec that
pins their absence.

## Measured

```
grep -rn 'Career mode' resources/js            -> 0 hits
grep -rn 'Training policy' resources/js        -> 0 hits
grep -rn 'Start Independent Training' resources/js -> 0 hits
grep -c 'test(' tests/browser/career-preflight-indep.spec.ts -> 1
```

## The two permitted outcomes (a test is never deleted without owner approval and a stated reason)

**Option A - retire it (recommended).** It duplicates a cut record: the removal is pinned better by
`tests/browser/career-preflight.spec.ts:303-306`, which asserts `toHaveCount(0)` for the same controls, and the idle-mode
entry it described no longer exists as a flow.

Steps for A:
1. Record the reason in the register: append to KI-114's neighbourhood, or file a new row, stating that the
   spec drove controls removed in Phase E2 and that `career-preflight.spec.ts` is the counter-spec.
2. `git rm tests/browser/career-preflight-indep.spec.ts` with the reason in the commit body.
3. `npx playwright test --list` -> its final count line shows the total down by 1.

**Option B - rewrite it to the shipped preflight.** Only if the owner wants idle-mode coverage.

Steps for B:
1. Read `resources/js/pages/Career/Preflight.vue` for what the page does draw (the E2 note at `:226-231`
   records what was removed).
2. Rewrite the case to assert the shipped controls and the absence the page discloses.
3. Run it alone: `PLAYWRIGHT_PORT=8311 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<s>/b.spec.sqlite npx playwright test tests/browser/career-preflight-indep.spec.ts --workers=1 --reporter=line` -> green.

## Acceptance

Either the file is gone with its reason recorded and the listing total reduced by exactly 1, or it is green
in isolation with its assertions quoted.

---

# Slice 2.3 - The nine probe cases (owner ruling P-5)

## Objective

Seven files carry 9 cases that are investigation apparatus, not product coverage. They run in every sweep.

| File | Cases | What it is |
| --- | --- | --- |
| `flash-name-probe.spec.ts` | 1 | diagnostic dump of accessible-name behaviour |
| `flash-name-probe-settled.spec.ts` | 1 | second probe of the same question |
| `flash-name-fix.spec.ts` | 1 | fix-verification probe comparing three locators, logging PASS/FAIL strings |
| `flash-name-first.spec.ts` | 1 | timing experiment, no product assertion |
| `flash-name-mechanism.spec.ts` | 1 | static `page.setContent` fixture, no app under test |
| `containment-mechanism.spec.ts` | 1 | controlled fixture that spins its own Vite server on port 5201 |
| `annotation-sweep.spec.ts` | 1 | sweep artefact |
| `containment.spec.ts` | 3 | **pins a deliberate limitation** - defensible, keep |

## Steps (recommended ruling: retire 6 files, keep `containment.spec.ts`)

1. Before removing anything, prove the behaviour each probe investigated is otherwise pinned:
   ```bash
   grep -rn 'getByRole(.status' tests/browser/preferences.spec.ts | head
   grep -rn 'container-type\|sticky' tests/browser/*.spec.ts | head
   ```
   If a probe's subject is pinned nowhere else, keep that file and say so.
2. For each retired file, record the retirement and its owning spec in the register or in the R8/S18 handoff's
   pending list.
3. `git rm` the six files in one commit with the reason and the owning specs named in the body.
4. `npx playwright test --list` -> the final count line drops by the ruled number of cases, and the file count by 6.

## Acceptance

Listing total equals 876 minus (2.1's 1 + 2.2's 1 + the ruled probe count); `containment.spec.ts` still lists
3 cases.

## Pitfall

`containment-mechanism.spec.ts` starts its **own** Vite server on port 5201. If it is kept, it must run with
its own port and must not share a sweep with a browser run; that is the reason to retire it rather than keep it.

---

# Slice 2.4 - `tools/md_fence_check.py` must survive a deleted tracked file

## Objective

`AGENTS.md` §10 documents `python tools/md_fence_check.py [paths]`. Bare, it crashes:

```
FileNotFoundError: docs/audits/RECONCILIATION-REGISTER.md
```

because it walks `git ls-files` and 25 tracked markdown paths are deleted in this worktree.

## Steps

1. Reproduce and capture the traceback.
2. Read the file's path-gathering function. Add a missing-file guard that **reports** rather than raises:
   ```python
   if not path.exists():
       missing.append(path)
       continue
   ```
   and print `N missing tracked path(s): <list>` at the end.
3. Keep the exit code meaningful: 0 when all present files are clean, non-zero when a fence is invalid. A
   missing file is a warning, not a fence failure - otherwise the tool is unusable between a deletion and its
   replacement landing (slice 0.5).
4. Verify three ways:
   ```bash
   python tools/md_fence_check.py                       # exit 0 on this tree, names the missing paths
   python tools/md_fence_check.py KNOWN-ISSUES.md       # exit 0, "0 invalid closing fence(s) in 1 file(s)"
   git stash list && python tools/md_fence_check.py docs/  # still 0 on a populated subtree
   ```

## Files modified

`tools/md_fence_check.py`.

## Acceptance

Bare invocation exits 0 on this tree and names every missing path; a deliberately broken fence in a scratch
copy still exits non-zero (prove the guard did not swallow the signal).

---

# Slice 2.5 - One command that runs the gate sequence

## Objective

`composer test` runs `config:clear`, `npm run typecheck`, `artisan test` - no pint, no phpstan, no lint:ui, no
lore, no fences, no browser. The project therefore has no single command whose exit code means "the gates
passed".

## SUPERSEDED — EXECUTED, AND THE COMPOSER FORM WAS REFUTED

The `gates` script this slice planned **does not work on this host**, and the reason is in the harness rather than
in the JSON. Measured, in order:

| Round | Command | Result |
| --- | --- | --- |
| 1 | `composer run gates` | `stdin is not a tty`, exit 1 |
| 1 | `composer run lore` (single `@php` step) | exit 0, 610 hits |
| 1 | each of the 9 steps as its own one-line Composer script | **all exit 0**, including `@php artisan test --filter=FanLadderTest` |
| 2 | `bash tools/gates.sh --fast` (per-step `< /dev/null`) | 7 of 8 green; `typecheck` red on a peer's in-flight edit |

A Composer script passes its caller's stdin straight through. The agent `terminal` tool supplies a **closed**
stdin, and `artisan test` is the only step that opens it, so the whole script reports failure. Every individual
step passes when run any other way. A tweak to the JSON cannot fix that, so the step shipped as a shell script that
redirects stdin per step, and `composer.json` now just delegates:

```json
"gates": ["bash tools/gates.sh"]
```

**Two lines in the JSON above are actively dangerous and must not be reintroduced:**

1. **`"@php tools/md_fence_check.py"` is a false green.** `@php` runs the PHP binary on a Python file: it prints
   the source and **exits 0** while checking nothing. Measured `php tools/md_fence_check.py` -> exit 0, docstring
   printed. The correct step is `python tools/md_fence_check.py`.
2. **`"@php artisan test --compact"` inside a Composer script fails unattended** for the stdin reason above.

**What actually shipped, and passes:**

```
bash tools/gates.sh        # all checks including the PHP suite
bash tools/gates.sh --fast # everything except the PHP suite
```

The script also drops `config:clear` (it mutates `bootstrap/cache` for no gate value) and keeps the order cheap
and deterministic first. Measured `--fast` summary: **`GATES: 1 failed: typecheck:1`**, with lore docs 610/75,
lore code 365, pint passed, phpstan clean, ui-lint reporting findings, fences 0 invalid in 292 files. The single
failure is a peer's uncommitted widening of a shared type, attributed in the execution plan.

**Do not re-plan this slice. Read `tools/gates.sh`.**

## Files modified

`composer.json`. (Do **not** add a `Makefile` target: `make` is not installed on this host, and a target that
cannot execute is worse than no target.)

## Acceptance

`composer gates` exit 0 quoted with its duration; a deliberate violation makes it exit non-zero; both runs
quoted.

---

# Slice 2.6 - The full browser sweep, once, on a quiet box

## Objective

No full sweep has run at this tree. Until one does, every "the browser suite is green" claim in the docs is
inherited or absent.

## Pre-flight (all four; a contended box voids the run)

```bash
# 1. Build freshness - the harness refuses a stale bundle
npm run build
ls -l public/build/manifest.json
# the manifest's mtime must be newer than every file under resources/js

# 2. No competing test process
tasklist | grep -iE 'php|node' | head        # or: ps -W | grep -iE 'php|node'

# 3. Scratch DB path is inside the repo and git-ignored
git check-ignore -v .scratch-uma/sqlite

# 4. Session-private lock so two runs cannot share the harness
mkdir .scratch-uma/<session>/playwright.lock
```

## Command

```bash
PLAYWRIGHT_PORT=8321 \
PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-8321.sqlite \
npx playwright test --workers=1 --reporter=line
echo "EXIT=$?"
```

## Rules that decide whether the number is real

- **Quote the exit code**, not the last three lines. Playwright's closing block prints pass and skip counts and
  omits the failure count.
- Cross-check with `grep -cE '✘|^\\s+[0-9]+\\) ' <log>` when the summary looks clean.
- `afterEach` must leave zero rows in `training_runs`, `turn_entries`, `veterans`, `deck_slots`,
  `independent_training_runs`. Query the scratch file directly after the run:
  ```bash
  python -c "import sqlite3,sys; c=sqlite3.connect(r'<scratch>'); print({t: c.execute(f'select count(*) from {t}').fetchone()[0] for t in ['training_runs','turn_entries','veterans','deck_slots']})"
  ```
- Release the lock directory afterwards (`rmdir`), even on failure.

## Acceptance

876-minus-ruled-out cases with **0 failures**, or <=3 named failures each carrying a register row and an
owner-facing reason. Exit code and summary line quoted in the hand-off.

## If the box will not quiet down

Record the probe readings (CPU, free memory, competing processes) and state that the sweep **did not run**.
A degraded run is only valid with an explicit owner ruling recorded beside it. Do not shard around
contention: `fullyParallel: false` means `--shard` splits **files**, so four shards need four scratch
databases and four quiet windows, which is more work than waiting.

---

## Gate sequence for this plan

```bash
php vendor/bin/pint --test --format agent playwright.config.ts tools/md_fence_check.py composer.json
php vendor/bin/phpstan analyse --no-progress --memory-limit=1G
npm run typecheck
node tools/ui-framework-check.mjs
python tools/md_fence_check.py
php tools/lore.php; php tools/lore.php code
composer gates; echo "EXIT=$?"
```
