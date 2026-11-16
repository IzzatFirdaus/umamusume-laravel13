# Implementation Plan — 2026-11-16 (pairs `docs/roadmap/implementation-roadmap-2026-11-16.md`)

**Read with the roadmap.** That file owns the phase/slice map, the dependency order, the acceptance bar and the
completed-work exclusions; this file owns the code-level steps. Where they disagree the roadmap governs and this file
is corrected. Slice ids are shared and must stay in step: P0.1 · P1.1–P1.3 · P2.1–P2.3 · P3.1–P3.8 · P4.1–P4.5 ·
P5.1–P5.5 · P6.1–P6.2 · P7.1–P7.3.

**Execution contract, every slice.** (1) Re-measure the named paths and claims before touching them —
`git status --porcelain`, `git ls-files`, read the file — and correct the slice if a claim is stale, recording the
correction. (2) Focused tests before broad sweeps; one Pest process at a time (overlapping runs hang rather than
fail). (3) Every failure is classified before any re-run. (4) Nothing is staged, committed, pushed or installed
without the owner naming the paths in that turn; run the picked branch and nothing adjacent.

**Environment facts that change how commands are written [M].**
- `Makefile` file **exists** (4,418 B) but GNU `make` is **not found** in this shell (`command not found`); KI-4 stands as measured here. Run targets via composer scripts (`composer test`, `composer lint`, `composer stan`, `composer lore`, `composer lore-code`, `composer audit`, `composer dev`, `composer fix`) or `vendor/bin/*` directly.
- Shell is Git Bash. A shell prefix (`GIT_INDEX_FILE=x git ...`) **does not work here** — pass an `env` mapping (recipe
  below).
- `git diff --output=<file>` exits 1 in this environment; write diffs through Python `subprocess` instead.
- Browser token tests read `public/build`: `npm run build` before believing any CSS-token reading.
- Session-private ports and scratch DB copies only; artifacts under `.scratch-uma/<session>/`.

---

## Private-index commit recipe (used by P1.1, P1.2 and every later commit)

Never touch the real index. The seed is `read-tree HEAD`; pathspecs are explicit; the message comes from a file.

```python
import os, pathlib, subprocess
repo = r"D:/Projects/umamusume-laravel13"
idx  = str(pathlib.Path(repo) / ".scratch-uma" / "<session>" / "index-<slice>")
msg  = str(pathlib.Path(repo) / ".scratch-uma" / "<session>" / "msg-<slice>.txt")
def git(*a, **kw):
    return subprocess.run(["git", *a], cwd=repo, capture_output=True, text=True,
                          env={**os.environ, "GIT_INDEX_FILE": idx}, **kw)
git("read-tree", "HEAD")                      # the ONLY correct seed
git("add", "--", *AUTHORIZED_PATHS)           # explicit pathspecs, never -A
git("commit", "-F", msg)                      # no < > | & ; in a -m string
print(git("show", "--stat", "HEAD").stdout)   # verification, not courtesy
```

Verification after any commit: `git show --stat <sha>` lists **only** the authorized paths. A peer file in the stat
means the commit is wrong — reset the private index and rebuild; never amend a protected sha. If `.git/index.lock`
appears, `rm -f .git/index.lock` before retrying.

---

## P0.1 — Baseline re-verification (blocking, ~2 min)

**Files:** none. Output is a quoted block in the hand-off.

```bash
# MEASURED 2026-11-16 (second re-measure; supersedes the expect- block below)
git rev-parse HEAD                                         # 178a907904a340c0e767119881c7d6189d2ecabc
git rev-list --left-right --count origin/master...HEAD      # 0  2  (0 behind, 2 ahead)
git status --porcelain | wc -l                             # 206
git status --porcelain | cut -c1-2 | sort | uniq -c         # 112 " D" / 33 " M" / 37 " ??" / 1 "D " / 23 "MM"
git diff --cached --name-only                               # 24 files staged (peer work; NOT this session's)
git stash list                                              # 6
git status --porcelain -- <the 17 MWG paths>                # MWG landed in HEAD; 2 still dirty (SearchableSkillPicker, Career/TraineeSelect)
command -v make                                             # not found (KI-4 stands); use composer scripts instead
```

17 MWG files (all under `resources/js/`):
`components/Form/BaseInput.vue`, `components/Form/SearchableSkillPicker.vue`, `components/catalog/TraineeCatalog.vue`,
`components/legacy/AncestorEditor.vue`, `components/skills/SkillCatalog.vue`, `components/support/CardPicker.vue`,
`pages/Career/{EventDecision,LegacySelect,SaveVeteran,SkillsPlanner,TraineeSelect}.vue`, `pages/Database/Races.vue`,
`pages/Legacy/Builder.vue`, `pages/Runs/Index.vue`, `pages/Support/Builder.vue`, `pages/SupportCards/Index.vue`,
`pages/Veterans/Index.vue`.

**Abort conditions.** HEAD moved → do not execute P1 with the roadmap's shas as authority; re-run
`git merge-base --is-ancestor <sha> HEAD` for each sha the roadmap cites and turn any false result into a slice. A
missing or staged (`MM`) MWG file → re-classify before P1.1. Any delta from the roadmap's §0 → record it and correct
the affected roadmap rows; never restore a peer's deletion to make a number match.

**Acceptance.** The block above is quoted with each delta named against §0.

---

## P1.1 — The 17 MWG files (owner A/B/C/D)

**Step 1 — Re-derive and present.** `git diff HEAD --numstat -- <the 17>` (expect `17 files +128/−40` [M]); for any
file whose count moved, show `git diff HEAD -U1 -- <file>`. Present one row per file: path, `+n/−m`, one line on what
the hunk does. State the set's character: `BaseInput.vue` gains `autocorrect` / `autocapitalize` / `spellcheck` /
`enterkeyhint` / `inputmode` props defaulted to `undefined` (so an absent hint stays absent from the DOM instead of
rendering `spellcheck="false"` on an enumerated attribute); the other 16 opt call sites in.

**Step 2 — Execute the ruling.**
- **A (commit the set):** private index with exactly the authorized paths —
  `feat(forms): adopt MWG form-capture hints across the 17 audited files`.
- **B (commit a named subset, park the rest):** same recipe, subset pathspecs; the parked remainder is recorded with
  its `--numstat` reading.
- **C (park):** record the reading and the decision; change nothing.
- **D (discard):** `git restore -- <paths>` **only** on owner instruction — never on this plan's initiative.

**Step 3 — Verify.** Commit: `git show --stat <sha>` lists only authorized paths, then `npm run typecheck` (exit 0),
`npm run build` (exit 0). Focused browser check on a form-heavy spec before and after, so the hint attributes are
shown not to change behaviour:
`npx playwright test tests/browser/career-cockpit-forms.spec.ts --workers=1 --reporter=line` (private port/DB).
Park: the `--numstat` reading re-printed unchanged into the hand-off.

**Acceptance.** One or two conventional commits containing only authorized paths, or a park record naming all 17 with
the reading that parked them. `components/support/CardPicker.vue` is peer-modified — it never rides in a commit it does
not own, and a subset commit that omits it must say so.

---

## P1.2 — BO-011 dark risk token + focus ring

**Step 1 — Re-measure before committing.** `git show HEAD:resources/css/app.css | grep -n 'color-risk'` (HEAD `:531`
`#FF4400`) against `grep -n 'color-risk' resources/css/app.css` (worktree `:531` `#FF6F3B`). For `#FF6F3B` against the
dark `raised`, `panel` and `page` fills, compute the WCAG ratio for each and quote all three. Any pair below 3:1 →
stop and report; the token is wrong and committing it is worse than leaving it uncommitted.

**Step 2 — Confirm the guard enumerates the token.**
`grep -n 'color-risk' tests/Feature/DesignTokensTest.php` → the named-token list around `:836-841`. If `--color-risk`
is absent from that list, the guard cannot fail on it — add it in the same commit.

**Step 3 — Commit** (private index, recipe above), keeping the `BO-011` reference intact:
`fix(a11y): seed the focus-ring colour at rest and lift the dark risk token (BO-011)`.

**Step 4 — Rebuild and prove the value landed.** `npm run build` (exit 0), then grep the emitted CSS under
`public/build/` for `#FF6F3B`. The browser token gate reads the build, not the source.

**Step 5 — Focused gate.** `vendor/bin/pest --filter=DesignTokensTest --compact`.

**Acceptance.** Token + ring seed committed; three ratios quoted per surface; emitted build carries the value; the
guard enumerates the token. **BO-004 stays open** (P5.5).

---

## P1.3 — Dispositions for 112 deletions and 36 untracked rows

**Step 1 — Enumerate with counts.**
```bash
git status --porcelain | grep '^ D' | wc -l        # 112
git status --porcelain | grep '^??' | wc -l        # 36
git status --porcelain | cut -c1-2 | sort | uniq -c
```

**Step 2 — Classify each ` D`.** Three classes only:
- **move** — prove the replacement exists: `docs/audits/<subdir>/<same-basename>` for the 17 audit documents (for
  example `docs/audits/reconciliation-register/RECONCILIATION-REGISTER.md`). Record the path pair.
- **peer removal** — the 90 `jev-inspect/**` rows (a foreign project's tree) and any other row whose owner is another
  session. Not this change's to restore; recorded, not touched.
- **loss** — no replacement and no owner. Restore with `git restore -- <path>` or commit the deletion with a stated
  reason. A silent ` D` of a tracked file is one `git checkout` from silent loss — but restoring 90 files of a foreign
  tree to tidy a count is the other failure mode.

**Step 3 — Classify each `??`.** Session scratch (already gitignored → say so), owner-named commit, or
recorded-with-owner. The two roadmap/plan pairs, the absorbed legacy plans, the reorganised `docs/audits/` subdirs, the
peer stat-visual set, `win-unpacked/`, `.devcontainer/bootstrap-agent-controls.sh` and the root artifact sentinel each
get one of those three with a name attached.

**Step 4 — Verify.** `git status --porcelain | cut -c1-2 | sort | uniq -c` after; `git ls-files --deleted | wc -l`;
a paired existence check printed per move.

**Acceptance.** No row unnamed; every *loss* restored or committed with a reason; the 17 audit documents proven to
exist at their new paths.

---

## P2.1 — Advisories verified, not inherited

```bash
composer audit --format=plain
composer show laravel/framework      # v13.32.0 [M]
node -e "const p=require('./package.json');console.log(p.scripts)"   # audit/typecheck/build entry points
```

For each advisory record: package, advisory id, CVE, affected range, fixed-in version, and **reachability here** —
console-only, dev-only, or unexercised code. The register's figure (9 advisories / 6 packages [M]) is a hypothesis to
re-derive, not a list to paste.

**Acceptance.** Every advisory carries reachable-fixed / not-reachable / owner-gated with the reasoning. An advisory
whose fixed-in cannot be confirmed stops the slice — never update a lock on an unverified number.

---

## P2.2 — Narrow update to a verified fixed-in

```bash
composer update <package> --with-dependencies      # never a blanket `composer update`
composer show <package>                            # >= the verified fixed-in
composer audit --format=plain                      # no reachable critical/high
vendor/bin/pest --compact                          # compare against P3.8's classified baseline
make stan                                          # PHPStan at the recorded level
```

Only `composer.lock` may change; `composer.json` moves only if a constraint must (owner call). A new package is never
added here (`AGENTS.md` §5). If the update breaks the suite in a way that needs an owner decision, park it:
`git checkout -- composer.lock`, then report.

**Acceptance.** Lock at a confirmed fixed-in version (or a written reason it must not move); no new unclassified test
failure; both audits clean of reachable critical/high.

---

## P2.3 — Front-end advisories

```bash
npm audit --omit=dev --json
```
Classify as P2.1. If a reachable advisory requires a move: narrow update, then `npm run typecheck` (exit 0),
`npm run build` (exit 0), and the token-sensitive specs re-run after the rebuild.

**Acceptance.** Every advisory classified or a written reason it is out of reach; typecheck and build exit 0.

---

## P3.1 — `ProvenanceMark` shape

**Step 1 — Measure the current shape.** `ProvenanceMark.vue` renders `<button type="button" class="pill pill-neutral
focus:outline-2 focus:outline-offset-2">` at `:68-76` with `role="tooltip"` on the bubble at `:84` [M]. Measure the
rendered box (a `.mjs` probe against the dev server, or the spec's own `boundingBox()`): ~23px at the last reading.

**Step 2 — Owner picks one shape.**
- **(a) Keep the button.** WCAG 2.2 **SC 2.5.8** (AA) requires 24×24 CSS px, so ~23px fails; 44px is SC 2.5.5 (AAA),
  not the AA bar. Raise the hit area with a sized class while keeping the pill's visual box, then update
  `tests/browser/provenance-mark.spec.ts`, `career-cockpit.spec.ts`, `unity-cup-panel.spec.ts` and
  `fixtures/provenance-mark.{html,ts}` to the button semantics (`getByRole('button')`, not `getByRole('img')`).
- **(b) Restore the static shape.** Return `role="img" tabindex="0"` with the tooltip on focus; the specs stay as they
  are.

**Step 3 — Verify.** The affected specs green · `npm run typecheck` exit 0 · `npm run build` exit 0 · the measured box
quoted before and after.

**Acceptance.** One shape in the component and in every spec; no spec asserts a role the component does not render.

---

## P3.2 — Slug binding fixtures

```bash
vendor/bin/pest --filter=CareerTraineeSelectTest --compact
vendor/bin/pest --filter=TraineeCardFormTest --compact
vendor/bin/pest --filter='CatalogDetailPageTest|CatalogProfileRomanisationTest' --compact
```
Fix only real failures: request paths and fixtures use the model's `slug` (the binding at
`app/Models/Umamusume.php:110` is part of P1.1's set). A dual `whereNumber`-compatible binding is an owner call, not a
default. The case that must be covered: a slug that looks numeric resolves to the row whose slug it is.

**Acceptance.** The three suites green; the numeric-looking-slug case asserted.

---

## P3.3 — Scenario label reconciliation

```bash
vendor/bin/pest --filter=CareerScenarioSelectTest --compact
npx playwright test tests/browser/career-cockpit-forms.spec.ts tests/browser/scenario-panel.spec.ts --workers=1 --reporter=line
```
Measured split: `config/scenarios.php:68` is `Spirit Bursts (combined)`; `career-cockpit-forms.spec.ts:182` expects
`(combined)`, `:248` and `scenario-panel.spec.ts:94` expect bare. Apply **one** owner-confirmed direction to every
expectation. Do not change the config label to dodge a test — the config is product data, the specs are the claim.

**Verify.** All three suites green; `grep -rn 'Spirit Bursts' tests/ config/` shows one direction only;
`composer lore-code` added-lines = 0.

---

## P3.4 — Line endings

```python
import pathlib
for p in ["resources/js/pages/Career/EventDecision.vue",
          "tests/browser/career-cockpit-forms.spec.ts"]:
    f = pathlib.Path(r"D:/Projects/umamusume-laravel13") / p
    b = f.read_bytes()
    f.write_bytes(b.replace(b"\r\n", b"\n"))
```
Whole files only — a half-converted file re-breaks the guard. Measured: 444 lines `EventDecision.vue`, 374 lines
`career-cockpit-forms.spec.ts`, both uniformly CRLF [M]; `.gitattributes` declares `eol=lf`.

```bash
vendor/bin/pest --filter=LineEndingGuard --compact
```
The guard reads the **working tree** and asserts it read >500 files (`LineEndingGuardTest.php:56`) — if that floor
fails, the sweep proved nothing and the red is the harness, not the files.

**Acceptance.** Offender list empty and the file-count floor still satisfied.

---

## P3.5 — `env()` behind config

`config/nativephp.php` **does not exist** [M] — create it (repo convention: `env()` only in `config/`).

```php
<?php
// config/nativephp.php
return [
    // Generated per build by the packaging workflow; read only through config() so the
    // value is cacheable and PHPStan's no-env-outside-config rule stays satisfied.
    'secret' => env('NATIVEPHP_SECRET'),
];
```
`app/Http/Middleware/EnforceNativePHPSecret.php:13`:
```php
$secret = config('nativephp.secret');
```
Behaviour is unchanged: a missing secret keeps the middleware's current fall-through, a present one still gates the app
(403 without, 200 with).

**Verify.** `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` — the failure count drops by exactly one and
no new error appears · the packaged-exe path still resolves the secret (load the `nativephp-packaged-exe-testing`
skill before touching it) · `make test` unaffected.

**Acceptance.** No `env()` in `app/` for this key; PHPStan clean on the file; the 403/200 behaviour demonstrated by a
reading.

---

## P3.6 — `lint:ui` disagreement settled

```bash
npm run lint:ui                    # quote the count line and the exit code
grep -n '"count"' tools/ui-lint-baseline.json
```
Baseline `count: 209`, `allowlist: 24` [M]. KI-145 is duplicated and drifted: `:96` CLOSED ("202 vs 209, 7 under,
exit 0"), `:205` OPEN ("282 vs 219"). Fix new values, or re-baseline **with the reason written into the file's own
`reason` field** — never widen a threshold to make a run pass.

**Acceptance.** 0 findings, or a re-baseline whose reason names what moved and why; the exit code quoted in both cases.

---

## P3.7 — The unreached page gets a spec

Measured: `resources/js/pages/Veterans/TransferRequests.vue` exists [M]; route `/veterans/transfers`
(`routes/web.php:298`); pager at `:134`; empty state `data-testid="no-transfers"`; zero `tests/browser` references.

```ts
// tests/browser/veterans-transfer-requests.spec.ts (shape, not final text)
test('lists requests, pages, and shows the empty state', async ({ page }) => {
    // drive /veterans/transfers on the scratch DB: tabs → pager → empty state,
    // clean up every row created in afterEach
});
```
```bash
PLAYWRIGHT_PORT=81xx PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-81xx.sqlite \
  npx playwright test tests/browser/veterans-transfer-requests.spec.ts --workers=1 --reporter=line
```

**Acceptance.** The spec passes on a scratch DB, asserts the empty state it can actually reach, and leaves no rows
behind (post-spec count quoted).

---

## P3.8 — Level-3 sweep, classification, skip census

```bash
vendor/bin/pest --compact                                     # one process at a time
PLAYWRIGHT_PORT=81xx PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-81xx.sqlite \
  npm run test:browser
vendor/bin/pint --test app database tests
```
Known-red seed at authoring [M]: PHP 6 (`CareerScenarioSelectTest` label, `CareerTraineeSelectTest` ×3 +
`TraineeCardFormTest`, `LineEndingGuardTest` ×2) — all addressed by P3.2/P3.3/P3.4; browser 11 (`ProvenanceMark` pills,
`app.css` drift, scenario label) — addressed by P3.1/P1.2/P3.3, re-run to confirm rather than assume. Subprocess-heavy
feature tests that fail inside a loaded full run and pass in isolation are a contention artifact, not a regression —
classify them as such with both readings.

**Skip census.** Exactly 2 true skips tree-wide (`DesignTokensTest.php:651`, `SkillAutomationTest.php:12`); the 11
`test.skip(true, ...)` calls in `tests/browser` are data-conditional guards inside live cases. A skipped contrast arm is
reported as **skipped**, never as green.

**Acceptance.** Zero unclassified failures; each command's summary line **and** exit code quoted.

---

## P4.1 — Reconcile the two open populations

```bash
grep -oE '^KI-[0-9]+ OPEN' KNOWN-ISSUES.md | awk '{print $1}' | sort -u | wc -l        # 34
git show HEAD:KNOWN-ISSUES.md | grep -oE '^KI-[0-9]+ OPEN' | awk '{print $1}' | sort -u | wc -l   # 34
grep -oE '^#+ KI-[0-9]+' KNOWN-ISSUES.md | grep -oE 'KI-[0-9]+' | sort -V | uniq -d    # 7 duplicated ids
```
The census artifact `.scratch-uma/roadmap-recon/ki-census.{md,json}` files **53** by reading entry bodies. Classify each
of the 19-entry gap as *status line stale*, *duplicate block*, or *body-only openness*, citing the entry and its line
anchor. Both status shapes parse: `Status: OPEN` and `**Status:** OPEN`.

**Acceptance.** One measured live list with the method named per count; neither 34 nor 53 is called "the" count again.

---

## P4.2 — CODE_FIX bucket

Per entry: reproduce the claim (a browser observation, a focused test, or a dispositive grep) → fix at root → focused
spec → `vendor/bin/pint --test <paths>` → `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` on touched files
→ `composer lore-code` added-lines 0 → register write. Bucket at the census's reading: KI-59, 60, 61, 87, 91, 95, 98,
99, 102, 103, 104, 114, 116, 131, 136, 137, 147, 149, 151, 152, 159, 160, 162.

**Acceptance.** Each entry CONFIRMED-and-fixed with its proving command and commit, or retains its class with its
evidence. Register entry parses: `grep -A6 '^### KI-<n>' KNOWN-ISSUES.md`.

---

## P4.3 — OWNER_RULING bucket

For each entry (KI-63, 101, 104, 128, 129, 130, 132, 150 at the census's reading) write: the question, the measured
state with its command, and both branches with the consequence of each. No code executes before the ruling.

**Acceptance.** Answerable requests, each citing a reading or an exact file:line; zero unruled code changes.

---

## P4.4 — Stale and duplicate closures

Close with the falsifying evidence (a sha that is an ancestor, or a reading). Never renumber. Do not delete a
duplicate block: a duplicate records a filing error worth keeping visible — cross-reference it.

```bash
git merge-base --is-ancestor <sha> HEAD && echo ancestor || echo NOT-ancestor
```

**Acceptance.** Every closure names what closed it and what closure does not cover; duplicate ids still resolve.

---

## P4.5 — Dead-panel ruling

Measured: `CareerMilestonesPanel.vue`, `HappyMeekPanel.vue`, `PreFinalReviewPanel.vue`, `UraMandatoryLadder.vue` have
zero importers; `SpiritBurstsPanel.vue` and `TeamRankLadderPanel.vue` appear only in `ScenarioPanel.vue` comments.

```bash
grep -rn "CareerMilestonesPanel\|HappyMeekPanel\|PreFinalReviewPanel\|UraMandatoryLadder" resources/js | grep -v 'Panels/'
```
Present compose-or-delete; execute the picked branch. Deleting another session's component on this plan's authority is
forbidden — KI-70's whole hazard is one session removing what another is editing.

**Acceptance.** Panels composed (with a spec that reaches them) or deleted with the ruling recorded in
`KNOWN-ISSUES.md`; `npm run build` and `npm run typecheck` exit 0 either way.

---

## P5.1 — `/api/v1/skills` ruling

Measured: `routes/api.php:10-17` carries six endpoints, no skills [M]; `ARCHITECTURE.md:313-316` documents umamusume +
training-runs only. Branch (a) — mirror `App\Http\Controllers\Api\V1\SupportCardController` with read-only index+show,
add a feature test and the contract row; branch (b) — record "FR-E stops at six". Execute the picked branch only.

**Acceptance.** Branch (a): feature test green, the route in `php artisan route:list --json`, the contract row added.
Branch (b): the ruling recorded with its reading.

---

## P5.2 — NFR-19 diagnostics ruling

Present `PRD.md:393-395` against `ARCHITECTURE.md:548-549` and the measured absence (no `diagnos*` route, component,
config or test). Build branch: dev-only middleware + Inertia page (request duration, query count, payload size) behind a
local-env route guard, with a feature test. Deferral branch: record it as terminal in the deferred list.

**Acceptance.** Implemented behind the guard and proven by a test, or recorded as a terminal deferral — never silently
absent.

---

## P5.3 — CI premise and `QWEN.md`

Owner decides track-vs-ignore first. Then correct only statements the tree falsifies: artwork-mirror manual-ness
(auto-mirror landed), uploads (still cut per `PRD.md` §6.13 — verify, do not delete), CI (three workflows exist; none
runs the quality gates; build green, acceptance run `43768347753` red with `No installed .exe found under
$programsDir`). Pair every corrected sentence with the reading that falsified the old one.

**Acceptance.** `QWEN.md` contradicts nothing in the tree; regeneration risk noted. This is the one prose deliverable
the project's own exit bar requires.

---

## P5.4 — Externally gated items

Record, with the command that proves each gate closed: trainer image uploads (owner threat-model sign-off), Docker /
devcontainer (`docker --version`), tool-config schemas (path absent). No code, no fabrication, no "in progress".

---

## P5.5 — BO-004 dark `To do` pill

Re-measure `--color-risk` on `/` in dark theme with a `.mjs` probe (`getComputedStyle` → ratio against the pill's
background). Present the figure and both branches: replay a candidate token and re-measure, or record "keep" with the
owner's name. If the token changes, re-run P1.2 Step 4 (`npm run build`) so the browser gate reads the new value.

**Acceptance.** An owner decision recorded; if the token moved, the rebuild and its emitted value quoted.

---

## P6.1 — Trackblazer walk

Drive the wizard → cockpit → scenario detail → run deck with a real run on a session-private server and scratch DB.
Screenshot every screen into the audit's own evidence directory; read every panel against its source text. A screenshot
without a value is a caption, not a reading.

**Acceptance.** Per-screen readings with values; a scenario-specific defect filed or a shared defect attributed to the
shared surface.

## P6.2 — Our Grand Concert, then URA Finale

Same discipline, same evidence standard. URA is the control: it is the scenario earlier walks covered, so a method that
finds nothing there is a method that is not looking.

**Acceptance.** As P6.1, per scenario.

---

## P7.1 — The seven conditions

```bash
vendor/bin/pest
PLAYWRIGHT_PORT=81xx PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-81xx.sqlite npm run test:browser
vendor/bin/pint --test app database tests
npm run typecheck && npm run build
make stan
composer lore
php artisan migrate:status
```
Quote each summary line and exit code. Conditions 4–7 (WCAG pairs, clean-clone artwork render + offline placeholder,
`QWEN.md` truth, the BO ledger's terminal states) are verified by their own named readings, not by these commands.

**Acceptance.** Seven conditions pass, or the exceptions carry the owner ruling that permits them; a skipped arm is
reported as skipped.

## P7.2 — `VERSION.md` / `CHANGELOG.md`

Update both for the phase exits landed, in the project's own formats (Keep a Changelog; SemVer per `VERSION.md` +
`ADR-0019`). Current state: `3.0.0` pre-release, tags ≤ `2.1.1`.

**Acceptance.** Declared version, tag list and changelog agree; an untagged `3.0.0` is stated as pre-release.

## P7.3 — Tag request

Present P7.1's quoted evidence, the version state and the tag. The tag is the owner's act.

**Acceptance.** The owner cuts the tag or records why the release waits.

---

## Self-check (run before handing this plan to an executor)

- Every roadmap phase has at least one task here: P0→P0.1, P1→P1.1–P1.3, P2→P2.1–P2.3, P3→P3.1–P3.8,
  P4→P4.1–P4.5, P5→P5.1–P5.5, P6→P6.1–P6.2, P7→P7.1–P7.3. ✅
- Every task names its files, its commands and its acceptance. ✅
- Prose-only deliverables are exactly the four the roadmap permits (P5.3, P7.2, P7.3, register writes a change
  obliges). ✅
- No task weakens a gate, deletes a test, adds a dependency, or adopts a peer's uncommitted work. ✅
- Every inherited figure is marked for re-measurement, and P0.1 is the first task. ✅
- Slice ids match the roadmap's, one for one. ✅
