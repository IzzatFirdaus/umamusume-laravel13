# Project Implementation Plan — 2026-11-16

Pairs `docs/roadmap/project-roadmap-2026-11-16.md`. The roadmap states what remains and why; this file
states how, with the exact command whose output is the evidence.

**Baseline SHA at authoring:** `3cbbd8b9`. Re-derive before starting any slice:
`git rev-parse --short HEAD`. If it has moved, re-verify rather than re-run.

---

## Commit recipe (every slice that commits)

Peer files are staged intermittently on this worktree. A plain `git add -A` would sweep them into your
commit. Use a private index:

```python
import os, subprocess
repo = "D:/Projects/umamusume-laravel13"
idx  = os.path.join(repo, ".scratch-uma/<session>/index-<slice>")
env  = {**os.environ, "GIT_INDEX_FILE": idx}

def git(*a):
    r = subprocess.run(["git", *a], cwd=repo, env=env, capture_output=True, text=True)
    return (r.stdout + r.stderr).strip()

git("read-tree", "HEAD")
git("add", "--", "<paths>")
subprocess.run(["git", "-c", "user.name=<n>", "-c", "user.email=<e>",
                "commit", "-m", "<msg>"], cwd=repo, env=env)
```

Then recover the real index for the committed paths:

```bash
git reset -q -- <paths>
```

Verify the commit touched only what you intended:

```bash
git show --stat --oneline HEAD
```

The shell-prefix form `GIT_INDEX_FILE=x git ...` does **not** work in this Git Bash. Use the Python
`env=` form.

---

## Phase A — Register truth

### A.1 — Verification pass (blocking, ~30 min)

One probe per entry. The probe is a `grep` of the entry's named symbol against the tree.

```bash
cd /d/Projects/umamusume-laravel13

# Already known-fixed (confirm, then close)
grep -n "base_stats\|growth_rates" resources/js/pages/Catalog/Show.vue      # KI-152
grep -c "Career tools" resources/js/pages/Career/Cockpit.vue                # KI-144 (comments only)
ls tests/Feature/FilterContractTest.php 2>&1                                # KI-140 (expect: absent)
grep -rn -- "--text-kpi" resources/css                                       # KI-151 (expect: 0)

# Still to verify
grep -n "title=" resources/js/pages/Career/BuildTarget.vue                   # KI-141 / KI-159
grep -rn "data-region=\"team-state\"" tests/browser/full-career-journey.spec.ts  # KI-136 / KI-137
grep -n "CandidateForm" resources/js/pages/Review/Detail.vue                 # KI-143
grep -rn "race-strip" resources/js/pages/Career/Cockpit.vue                  # KI-147
grep -rn "640" tests/browser/career-cockpit.spec.ts 2>/dev/null              # KI-160
npm run lint:ui 2>&1 | tail -20                                              # KI-145
```

Record each result next to its entry. A probe that returns 0 hits where the entry claims presence means
**ALREADY-FIXED**, not "cannot reproduce".

### A.2 — FIX bucket

Work one entry per commit. Each commit message names the defect and the evidence line.

**KI-141 + KI-159 — `title`-only reasons (do together, same pattern).**

Current shape in `resources/js/pages/Career/BuildTarget.vue`: one `title=` attribute carrying an
explanation that is only reachable by hover or a screen reader that reads titles. The shipped pattern is
a visible sentence beside the control, or `AbsenceValue`. Move the text out of `title` into a visible
element. Then:

```bash
npm run typecheck && npm run build
php vendor/bin/pest --filter=CareerBuildTargetTest --compact
```

**KI-160 — Cockpit overflow at 640px.**

```bash
PLAYWRIGHT_PORT=8191 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/b-8191.sqlite \
  npx playwright test tests/browser/career-cockpit.spec.ts --workers=1 --reporter=line
```

Fix in `resources/js/components/Career/NextDecision.vue` — the Obligation strip is what pushes past the
viewport. Acceptance: `document.documentElement.scrollWidth <= 640` at that width, asserted in the spec.

**KI-137 + KI-136 — `full-career-journey.spec.ts`.**

The entry names the fix inline: `:114` becomes `page.locator('details[data-region="team-state"]')`, and
`:128` takes a scoped locator. This file is **peer-owned** (dirty in another session's worktree). Check
before touching:

```bash
git status --porcelain tests/browser/full-career-journey.spec.ts
```

If dirty, file the finding and leave it.

**KI-143 / KI-147 — spec/UI disagreement.**

Read the spec header, then the rendered component, and make them agree. If the UI is authoritative,
rewrite the spec. If the spec is authoritative, the UI is the defect and the fix is larger — say so and
file rather than guess.

**KI-145 — `lint:ui` debt.**

```bash
npm run lint:ui
```

Two branches: pay the debt down (remove arbitrary values), or enumerate each new value in
`tools/ui-lint-baseline.json` with a reason. The baseline must not simply be bumped to 282 silently; that
is raising a bar to pass a check.

### A.3 — TERMINAL bucket (record only)

**KI-150.** Change its `**State:**` line to:

```markdown
**State:** CLOSED — terminal (option 3, no page-level sticky). `AppLayout.vue:615-618` states `@container`
is load-bearing for 21 files that resolve `@2xl:`–`@5xl:` against `main`, and forbids removing it to make
an element stick. `StickyFooter.vue` has zero consumers; the one bitten case (`Review/Index.vue`) was
removed in pass 5. Ruling 8B (switch to `@media`) is withdrawn: it would break the shell to enable a
feature no live component wants.
```

**KI-142.** Delete the duplicate block at `KNOWN-ISSUES.md:4621` (the second copy; the first is at
`:4341`), then set the surviving entry's state.

```bash
grep -n "KI-142" KNOWN-ISSUES.md
```

**KI-148.** Already purged; set `**State:**` to `CLOSED — purged, no code change`.

### A.4 — Acceptance

```bash
# every heading resolves to a state
grep -E "^#{2,3} KI-" KNOWN-ISSUES.md | wc -l          # expect 114 headings
# no OPEN survives unclassified
grep -cE "\*\*State:\*\* OPEN" KNOWN-ISSUES.md          # expect 0 after Phase A
```

Commit each bucket separately so the hand-off can attribute each verdict.

---

## Phase B — Browser truth

### B.1 — Triage gate

Per spec, one grep. This is the primitive; do not re-run the spec to find out if it is stale.

```bash
# already done, recorded
grep -rn "Career mode" resources/js          # 0 hits -> _screens-indep-training.spec.ts:51 stale
grep -rn "Career mode" resources/js          # 0 hits -> career-preflight-indep.spec.ts:57 stale
grep -rn "Facts and mechanics" resources/js  # 0 hits -> accessibility.spec.ts:110 stale
```

**Order of work — highest yield first:**

1. `career-profile.spec.ts` — 8 tests all time out in `beforeEach`. That is one shared fixture, not eight
   defects. One isolated run may clear all eight.
2. `career-deck-summary.spec.ts` (6) and `career-trainee-select.spec.ts` (6).
3. `career-legacy-deck-steps.spec.ts` (4).
4. The six single-test specs.

### B.2 — Isolated re-run

One process at a time. Never the default port, never the shared DB.

```bash
cd /d/Projects/umamusume-laravel13
PLAYWRIGHT_PORT=8191 \
PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-8191.sqlite \
  npx playwright test tests/browser/career-profile.spec.ts --workers=1 --reporter=line
```

Read the result as one of four:

| Result | Classification | Action |
| --- | --- | --- |
| Passes isolated | contention artifact | record; do not change code |
| Fails identically | regression | name the SHA range; file in `KNOWN-ISSUES.md` |
| Passes now, failed at sweep | fixed between SHA_SWEEP and HEAD | record, close |
| Fails on a literal absent from `resources/js` | stale spec | rewrite or delete the case |

### B.3 — The three stale specs

For each, choose rewrite or delete. Delete only when **every** assertion in the case encodes the removed
UI. Prefer rewrite when the case still tests something real.

```bash
# confirm the counter-spec is authoritative and must not be touched
sed -n '299,306p' tests/browser/career-preflight.spec.ts
```

That block asserts `Career mode`, `Independent Training`, `Training policy` all `toHaveCount(0)`. It is
the record of the Phase E2 cut. The three failing specs predate it.

After editing:

```bash
npm run typecheck
PLAYWRIGHT_PORT=8191 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/b-8191.sqlite \
  npx playwright test tests/browser/accessibility.spec.ts tests/browser/_screens-indep-training.spec.ts \
  --workers=1 --reporter=line
```

### B.4 — Acceptance

```bash
PLAYWRIGHT_PORT=8191 \
PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-final.sqlite \
  npx playwright test --workers=1 --reporter=line
```

Zero failures, or a named set small enough to list with a filed reason each.

---

## Phase C — Release bar

Run in this order; each is a gate, not a formality.

```bash
cd /d/Projects/umamusume-laravel13

# 1. PHP suite — one process only, never overlapping
php vendor/bin/pest --compact

# 2. Browser suite (session-private)
PLAYWRIGHT_PORT=8191 \
PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-final.sqlite \
  npx playwright test --workers=1 --reporter=line

# 3. Style, types, build, static analysis — explicit paths, never --dirty
vendor/bin/pint --test --format agent <changed paths>
npm run typecheck
npm run build
vendor/bin/phpstan analyse --no-progress --memory-limit=1G

# 4. WCAG AA tokens
php vendor/bin/pest --filter=DesignTokensTest --compact

# 5. Clean clone + artwork
git clone . /tmp/td-clean && cd /tmp/td-clean    # keep inside the repo tree if policy requires
composer setup

# 6. QWEN.md truth — manual read against the tree
# 7. Register ledger
grep -cE "\*\*State:\*\* OPEN" KNOWN-ISSUES.md
```

Condition 1 must quote the measured skip census: **2 PHP skips**, both `markTestSkipped`
(`tests/Feature/DesignTokensTest.php:651` and `tests/Feature/SkillAutomationTest.php:12`), against
`->skip(` → 0 hits, plus **11 browser data-conditional guards** across 8 files.

**Do not** run `pint --dirty` — the dirty set belongs to peers. **Do not** run two Pest processes at once.

---

## Phase D — Version truth and tag

```bash
grep -n "3.0.0" VERSION.md
grep -nE "^## \[" CHANGELOG.md
git tag --list | sort
```

All three must agree: `3.0.0` declared untagged pre-release, the five shipped tags listed, and the
changelog carrying a `[3.0.0]` section. Fix disagreement in the doc, never by tagging.

Then present the Phase C evidence in the hand-off and **stop**. The owner cuts the tag.

---

## Phase E — Scenario walks

Manual. No automation substitutes for a reading.

**Evidence standard, per observable event:** *what happened / intent / expected vs observed / evidence*.
A screenshot without a value caption is not evidence. Unclear evidence is written **unverified** with
what would settle it. Never write "pass" without a reading.

### E.1 — Trackblazer

Start the app on a session-private port with file session/cache drivers so the shared dev DB is not
mutated by page loads:

```bash
SESSION_DRIVER=file CACHE_STORE=file php artisan serve --port=8192
```

Walk: scenario select → trainee → build target → legacy → deck → preflight → first turns → Pro Shop →
race decision → race result → cockpit close-out. Record each screen's values.

Trackblazer is the largest untested surface and the only scenario with no Unity Cup cross-reference, so
its numbers have nothing to be checked against. That is why it is walked first.

### E.2 — Our Grand Concert, then URA Finale (control)

Same walk shape. URA Finale is the control: it is the best-covered scenario, so a defect found there is
likely a shared-surface regression rather than a scenario gap. Attribute shared defects to the shared
surface, not to the scenario.

### E.3 — Defect handling

A walk finding goes into `KNOWN-ISSUES.md` as a reading with the four-part evidence line. **Code changes
and walks do not share a turn** — this is deliberate, so a walk cannot be made to pass by editing code
mid-observation.

### E.4 — Host-limited QA (record once, do not force)

| Check | Blocker | Record |
| --- | --- | --- |
| ProvenanceMark screen-reader double-read | no NVDA/VoiceOver on this host | unverified, named |
| Physical mobile keyboard | no device | unverified, named |
| Safari find-in-page / paint | property not in the tree | closed, no code change |

---

## Execution order

```
A.1 verify ──> A.2 fix ──> A.3 record ──> A.4 acceptance
B.1 triage ──> B.2 re-run ──> B.3 stale specs ──> B.4 acceptance
E.1 Trackblazer ──> E.2 Concert ──> E.2 URA control
C (needs A.4 + B.4) ──> D.1 ──> D.2 tag request ──> STOP
```

A and B are independent and may interleave. C gates on both. E is independent and may run any time, but
its findings enter the register, not the code, in the same turn.

---

## Risks

| Risk | Effect | Mitigation |
| --- | --- | --- |
| `HEAD` moves mid-session | counts attributed to the wrong SHA | re-derive `git rev-parse HEAD` before every slice; quote it in the hand-off |
| Peer stages files while you work | `git add -A` sweeps foreign work | private index only; `git show --stat` after every commit |
| Two Pest runs overlap | both hang, reading as slowness | one process at a time; never background a writing command |
| Browser suite re-seeds per retry | ~23 min per retry, 7 h for a serial sweep | triage by grep first; isolated re-run only for survivors |
| Register has duplicate blocks | a fix closes one copy and leaves the other open | dedupe before assigning verdicts (KI-142 at `:4341` and `:4621`) |
| Stale spec deleted that encoded a decision | the decision's evidence disappears | `career-preflight.spec.ts` is authoritative and is never touched |
| `title`-only reasons moved to `sr-only` in a refactor | KI-141/159 recur | assert visible text, not attribute presence |

---

## Self-check before handing this plan to an executor

1. Does every slice name a file or a command? If not, it is a report, not a slice.
2. Is every "already done" claim backed by a line reference measured today?
3. Does any slice raise a baseline to pass a gate? If so, it is wrong.
4. Does any slice reverse a recorded owner ruling without a new written ruling? If so, it is wrong.
5. Can the executor tell, from the acceptance line alone, whether the slice is finished?
