# Implementation Plan — Hermes session, 2026-11-16 (pairs with `docs/roadmap/implementation-roadmap-hermes-2026-11-16.md`)
<!-- PLAN-CONTRACT
plan: implementation-plan-hermes-2026-11-16
status: landed
slices: —
superseded-by: —
amended-by: —
open: —
-->

**Goal.** Execute every remaining slice from HEAD `d417a9a5` to a shipped `v3.0.0`, with each slice's
completion proven by a command rather than described in prose.

**Architecture of the work.** Bank the uncommitted fixes first so every later reading is attributable to a sha;
then turn the two registers into measured verdicts; then make the suite honest; then test the three unwalked
scenarios; then release.

**Spec.** `docs/roadmap/implementation-roadmap-hermes-2026-11-16.md` is the spec this plan implements. Read both. Where they
disagree, the roadmap governs and this file is corrected.

**This pair stands alone.** It references no other session's roadmap or plan. Cross-session facts live in the project mnemon
memory store, not here.

**Measured basis.** `master` @ `d417a9a5`, 88 porcelain rows, 108 routes, `laravel/framework v13.32.0`,
`lint:ui` baseline 209, `VERSION.md` `3.0.0` pre-release untagged. Re-measure all of it in R0.

## Global constraints

- Shell is Git Bash. `make` does not exist (KI-4); use `composer` scripts or `vendor/bin/*`.
- Never `git add -A`, `git add .`, `git commit -a`, or a real-index commit. Private index seeded with
  `git read-tree HEAD`; explicit pathspecs; commit bodies via `-F <file>` (no `< > | & ;` in `-m`).
- Never `pint --dirty`. Always `pint --test --format agent <paths>`.
- Browser suite always with a session-private port and scratch DB.
- Artifacts under `.scratch-uma/<session>/…`; every log quoted in the hand-off, never merely produced.
- Focused runs during implementation; the full suite at R3.8 and R7 only.
- Nothing is committed without the owner naming the paths in that turn.
- Every figure the roadmap states as **[M]** is re-derived before it is used.

## Review focus — five inputs/conditions the spec implies but no task's tests pin

1. **A slug-shaped trainee URL that collides with an id.** `getRouteKeyName() = 'slug'` re-binds every
   `{umamusume}` route; a slug that looks numeric is the case that breaks. R1.1's test owns it.
2. **A run at turn 0 with a scenario that composes a team panel.** The BO-002 class. Every cockpit computed
   that reads `props.scenario` must survive a null team block. R3's cockpit cases own it.
3. **A draft whose form is dirty when the tab is closed and reopened.** The `sessionStorage` draft's
   fingerprint must reject a stale record rather than reseed a form over newer server data. R3.6 owns it.
4. **A worktree that moves mid-slice.** Any commit built from a stale read. R0's re-measure plus R1.0's
   `git diff --cached --name-only` precondition own it.
5. **A test red for an environmental reason read as a product regression.** The classification step. Every
   R3 slice's acceptance includes one classification line per failure.

---

# Tasks

## Task 1 (R0) — Baseline re-verification

**Files:** none. The output is a quoted block in the session notes.

- [ ] **Step 1: Run the five readings**

```
git rev-parse HEAD
git status --porcelain | wc -l
git status --porcelain | cut -c1-2 | sort | uniq -c
git diff --cached --name-only
git stash list
```

- [ ] **Step 2: Compare against the roadmap's §0 table**

Expected: `d417a9a5`, 88 rows, histogram `47 " M" / 1 "M " / 3 " D" / 37 "??"`, one staged row
(`.tooling/REVIEW-LOG.md`), six stashes. Any delta: record it and re-read the affected roadmap rows.

- [ ] **Step 3: Abort condition**

If HEAD is not `d417a9a5`, do not proceed to Task 2 with the roadmap's shas as authority. Re-measure each
sha in the roadmap's §1 exclusion table with `git merge-base --is-ancestor <sha> HEAD`; anything false becomes
a new task.

---

## Task 2 (R1.0) — Commit-attribution decision, then the private-index commit

**Files:** `.scratch-uma/<session>/` for the index and message only.

- [ ] **Step 1: Present the classification**

For each candidate path: `git diff HEAD --numstat -- <path>`, and for any path whose count moved, the hunks
(`git diff HEAD -U1 -- <path>`). Present the table; the owner picks A / B / C / D per item.

- [ ] **Step 2: Private index, never the real one**

```python
import subprocess, pathlib
repo = r"D:/Projects/umamusume-laravel13"
idx  = str(pathlib.Path(repo) / ".scratch-uma" / "<session>" / "index-<slice>")
def git(*a, **kw):
    return subprocess.run(["git", *a], cwd=repo, capture_output=True, text=True,
                          env={**__import__("os").environ, "GIT_INDEX_FILE": idx}, **kw)
git("read-tree", "HEAD")          # the ONLY correct seed
git("add", "--", *AUTHORIZED_PATHS)
git("commit", "-F", MSG_FILE)
git("show", "--stat", "HEAD")     # verification, not courtesy
```

A shell prefix (`GIT_INDEX_FILE=x git …`) does not work in this environment; pass the env mapping.

- [ ] **Step 3: Verify the commit**

`git show --stat <sha>` lists exactly the authorized paths. Any peer file in the stat → the commit is wrong;
reset the private index and rebuild. Do not amend a protected sha.

---

## Task 3 (R1.1) — BO-001 slug binding

**Interfaces.** Produces: `Umamusume::getRouteKeyName(): string` returning `'slug'`.

- [ ] **Step 1: Write the failing test** in `tests/Feature/CareerTraineeSelectTest.php`

```php
it('resolves a trainee profile by slug as well as by id', function () {
    $trainee = Umamusume::factory()->create(['slug' => 'rice-shower']);
    $bySlug = $this->get(route('career.trainee.profile', ['umamusume' => 'rice-shower']));
    $byId   = $this->get(route('career.trainee.profile', ['umamusume' => $trainee->id]));
    $bySlug->assertOk();
    expect($bySlug->viewData('page')['props']['trainee']['id'])
        ->toBe($byId->viewData('page')['props']['trainee']['id']);
});

it('404s an unknown trainee slug', function () {
    $this->get(route('career.trainee.profile', ['umamusume' => 'nobody']))->assertNotFound();
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `vendor/bin/pest --filter=CareerTraineeSelectTest --compact`
Expected: FAIL — the slug form does not resolve.

- [ ] **Step 3: Enumerate every `{umamusume}` binding before editing**

Run: `grep -rn "umamusume" routes/web.php` and `grep -rn "'umamusume' =>" app/Http/Controllers`.
Write the list into the slice notes; each route is a call site this change reaches.

- [ ] **Step 4: Apply the binding** in `app/Models/Umamusume.php` (the worktree already carries it as a ` M` diff)

```php
public function getRouteKeyName(): string
{
    return 'slug';
}
```

- [ ] **Step 5: Run to verify it passes, then re-run the enumerated call sites**

Run: `vendor/bin/pest --filter=CareerTraineeSelectTest --compact` → PASS, then
`vendor/bin/pest --filter='CatalogDetailPageTest|TraineeCardFormTest|CatalogProfileRomanisationTest' --compact`
→ PASS. A call site that built the route with an id and now resolves a slug is the failure this step exists
to catch.

- [ ] **Step 6: Commit** (private index, Task 2's recipe)

`fix(career): resolve the trainee profile by slug as well as by id (BO-001)`

---

## Task 4 (R1.2) — BO-004 / BO-011 dark risk token

**Files:** `resources/css/app.css` (§ dark block, `--color-risk`), test `tests/Feature/DesignTokensTest.php`.

- [ ] **Step 1: Re-measure the three pairs on the worktree value**

For `#FF6F3B` against the dark `raised`, `panel` and `page` fills, compute the WCAG contrast ratio for each.
Quote the three figures. If any is below 3:1, the token is wrong — stop and report rather than committing.

- [ ] **Step 2: Confirm the guard enumerates the token**

Run: `grep -n "color-risk" tests/Feature/DesignTokensTest.php` → the named-token list at `:836-841`.

- [ ] **Step 3: Commit** with the `BO-011` comment intact (private index)

`fix(a11y): seed the focus-ring colour at rest and lift the dark risk token (BO-004, BO-011)`

- [ ] **Step 4: Rebuild so the browser gate reads the new value**

Run: `npm run build` — then grep the emitted CSS for the token to confirm it landed.

---

## Task 5 (R1.4) — Dispositions for the deletions and the untracked rows

- [ ] **Step 1: Enumerate**

```
git status --porcelain | grep '^ D'
git status --porcelain | grep '^??'
```

- [ ] **Step 2: For each ` D`** — restore (`git restore -- <path>`) or commit the deletion with a stated reason.
  A tracked file deleted in the worktree is one `git checkout` away from silent loss.

- [ ] **Step 3: For each `??`** — one of: named as session scratch (already gitignored), committed by the owner's
  instruction, or recorded in the hand-off with its owner. Nothing unnamed.

---

## Task 6 (R2.x) — Register truth, one cluster per slice

**Files:** `KNOWN-ISSUES.md` (append per entry, four-part form, never renumber; note the file is
  uncommitted in the worktree — quote HEAD's or the worktree's count with its ref, never bare); `docs/audits/RECONCILIATION-REGISTER.md`
for walk-derived rows.

- [ ] **Step 1: Reproduce the entry's own claim** with the surface's route and a reading (a browser observation,
  a focused test, or a `grep` that is dispositive).

- [ ] **Step 2: Classify** — `CONFIRMED` (with the reading) / `STALE` (with what closed it) / `DUPLICATE` (with
  the id it duplicates) / `BLOCKED-ON-<named thing>`.

- [ ] **Step 3: Fix only what is cheap and inside this change's boundary;** everything else keeps its class and
  its evidence.

- [ ] **Step 4: Write the register entry** with status line, proving command, and — for a closure — the commit
  and what closure does not cover.

- [ ] **Step 5: Verify** the entry parses: `grep -A6 "^### KI-<n>" KNOWN-ISSUES.md`.

Cluster membership is in the roadmap's R2 table. Work a whole cluster before opening the next; the deliverable
is the register's new state, which is the artifact each change obliges.

---

## Task 7 (R3.x) — Suite truth

- [ ] **Step 1: Reproduce each named red in isolation**, with its own filter, in a session-private log.
  Note `f080bb99` already records a green `full-career-journey.spec.ts` — verify before spending a budget on it.
- [ ] **Step 2: Classify** before anything re-runs — regression / pre-existing / unrelated / infrastructure /
  flaky / inconclusive — and for a regression, run the focused reproduction first.
- [ ] **Step 3: Fix at root or record.** A pre-existing red goes to `KNOWN-ISSUES.md`; a test is never deleted
  or skipped without owner approval and a stated reason.

- [ ] **Step 4: Report the skip census with the run.** Exactly two true skips exist tree-wide
  (`DesignTokensTest.php:651`, `SkillAutomationTest.php:12`); the 11 `test.skip(true, …)` calls in
  `tests/browser` are runtime data-conditional guards. If `DesignTokensTest` skipped its browser arm, the
  contrast gate did not run — say so, never report it as green.

- [ ] **Step 5: Cover the one unreached page.** Write a browser spec for `Veterans/TransferRequests.vue`
  (route `/veterans/transfers`, `routes/web.php:298`): drive the tabs, the pager (`:134`) and the
  `data-testid="no-transfers"` empty state; delete anything it creates in `afterEach`.
- [ ] **Step 4: For R3.3, move the `env()` call** in `app/Http/Middleware/EnforceNativePHPSecret.php:13` behind
  `config()`. **Measured: no `config/nativephp.php` exists** [M] — create it for this key or put the key in an
  existing config file; the middleware's behaviour is unchanged either way.

  ```php
  $secret = config('nativephp.secret');
  ```

  with the key declared in the chosen config file. Then `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`.

- [ ] **Step 5: R3.7 — re-measure both sides of the `lint:ui` disagreement**

Run: `npm run lint:ui` and read `tools/ui-lint-baseline.json`'s `count`. If the run exceeds the baseline, fix
the new values or re-baseline **with the reason recorded in the file's own `reason` field** — never widen a
gate silently.

- [ ] **Step 6: R3.8 — Level-3 sweep on a settled tree**

```
vendor/bin/pest --compact
PLAYWRIGHT_PORT=81xx PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-81xx.sqlite npm run test:browser
php artisan migrate:status
```

Quote each command's summary line **and** its exit code. A count without one of those is a claim.

---

## Task 8 (R5.x) — Feature residuals

- [ ] **Step 1: Reproduce the reading on the named route** before touching code. R5.1's `Not a Scenario Link`
  reading, R5.3's caret and truncation, R5.2's `of 6` — each is a browser observation first.

- [ ] **Step 2: Fix, or write a verdict** (`falsified` with the reading / `owner-gated` with the decision named).

- [ ] **Step 3: For R5.1, unify the derivation** so one source feeds both the draft deck and the run deck, then
  pin it with a case that fails when the two disagree.

- [ ] **Step 4: R5.5** — write the `DESIGN.md` §4.7 decision request. No placeholder work executes while it is
  unruled.

---

## Task 9 (R6) — Scenario walks

- [ ] **Step 1: Confirm the gate.** R3 has no unclassified failure. If it does, this task waits.

- [ ] **Step 2: Trackblazer** — drive the wizard, the cockpit, the scenario detail and the run deck with a real
  run; capture screenshots into the audit's evidence directory; read every panel against the source.

- [ ] **Step 3: Our Grand Concert**, then **URA Finale**, in that order.

- [ ] **Step 4: Per scenario**, file a scenario-specific defect or attribute a shared defect to the shared
  surface. Cite the reading, never the recollection.

---

## Task 10 (R7) — Release bar

- [ ] **Step 1:** run the seven conditions of the roadmap's R7, each on the settled tree, quoting each command's
  output.
- [ ] **Step 2:** `VERSION.md` / `CHANGELOG.md` for the phase exits that produced them.
- [ ] **Step 3:** present the tag to the owner. The tag is the release act and it is the owner's to cut.

---

## Self-check (run before handing this plan to anyone)

- Every roadmap phase R0–R7 has at least one task here. ✅
- Every task names its files, its command and its acceptance. ✅
- No task's deliverable is prose alone except Task 1 (a reading block) and Task 10 Step 3 (a decision request),
  both of which the owner's standing request requires. ✅
- No task weakens a gate, deletes a test, or adds a dependency. ✅
- Every figure the tasks inherit is marked for re-measurement. ✅
