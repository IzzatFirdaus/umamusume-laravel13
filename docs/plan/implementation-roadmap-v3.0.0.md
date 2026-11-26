STATUS (2026-11-17): The 2026-11-16 recovery premise was falsified at G0 — no reset occurred; seven fixes landed on master via other routes. `recover/phase1` (79d8d33a) is preserved as orphaned evidence. `SLICE-RECORDS` at HEAD carries no S67–S69; the ledger gap is real and is future work. Rulings 1–4 shipped (MWG vendored, Baseline 2024, BaseInput folded, app.css EOF with peer authorship). Ruling 5 moot (QWEN.md absent).

---

## Basis of this roadmap — read this first

I cannot execute shell commands or read files from `D:\Projects\umamusume-laravel13` with the tools available in this session. Every line below is therefore built from **evidence already measured in the SITREP and prior session transcripts**, not from a fresh inspection. Where the SITREP is explicit (HEAD sha, index deltas, ratio tables, DB row counts) I treat it as verified. Where it is inferred ("wizard likely filters…") or inherited from memory (`e712598c`) I treat it as **unverified and made a Phase 0 gate**, not an assumption the roadmap builds on.

This is deliberate: the last two dispatches each contained 2–5 wrong premises (BO-013 was not a value fix; BO-008 may be a scope difference; slice-9 Playwright specs may duplicate `DesignTokensTest`). A roadmap that inherits those premises would spend the next cycle re-fixing them. Phase 0 exists to falsify them cheaply before any editing budget is spent.

Three contradictions in the SITREP I am **not** silently resolving — they are Phase 0 tasks:

1. SITREP's next-steps say `make test` / `make lint`, but `QWEN.md` lists KI-4 "GNU make unavailable on Windows — use composer scripts." One of those is wrong. Verification commands below use direct binaries (`vendor/bin/pest`, `vendor/bin/pint`) to sidestep the question until Phase 0.6 settles it.
2. `5869d1a0` is described as an "orphan commit" in §4 but "ancestor of HEAD" in §2. Those are different states. Phase 0.3 resolves.
3. `e712598c` / `5b8e1a83` are cited as prior work in §9 but absent from `git log --oneline -15`. Phase 0.4 resolves.

---

# Trainer Desk v3.0.0 — Implementation Roadmap
<!-- PLAN-CONTRACT
plan: implementation-roadmap-v3.0.0
status: landed
slices: —
superseded-by: —
amended-by: —
open: —
-->

**Target:** a fully functional v3.0.0 where every user-facing surface renders correctly, every WCAG pair passes or is an owner-accepted carve-out, artwork is present on first install without manual intervention, and the test suite is green without infrastructure ambiguity.

**Not in scope of "done":** Phase 3 devcontainer (Docker absent), Phase 4 tool configs (no schemas). Those are tracked as permanently-gated, not open slices.

---

## Roadmap laws (apply to every slice)

| # | Law | Why |
|---|---|---|
| L1 | Verify the premise before editing. Every `file:line` below is a hypothesis until read. | Two prior dispatches shipped wrong fixes from unverified premises. |
| L2 | One finding = one commit, explicit pathspecs, `-F` message. | `git add -A` would sweep peer work; `-m` with metachars is denied. |
| L3 | Never commit from the real index. | SITREP §2: real index holds 68 stale deletions that regress BO-011. |
| L4 | Peer file mtime < 5 min → skip slice, log it. | §4.1 doctrine, unchanged. |
| L5 | "Stale" is a valid, successful outcome. | BO-002, BO-015 already proved this. |
| L6 | No finding ID in a commit unless the fixing code path is in the same diff. | `5869d1a0` claimed BO-008 with no code behind it. |
| L7 | Protected SHAs never amended: `9c99aeaa`, `133e61bc`, `8aff0d83`, `86d649d3`, `24b0aba4`, `abfd1e5f`, `5869d1a0`, `8ef841fb`. | Ratified. |
| L8 | A slice needing 3+ components stops and reports scope. | Prevents sprawl. |

---

## Phase 0 — Reconciliation gate (blocking; no product edits until closed)

**Objective:** replace every inherited premise with a measured fact. Zero commits in this phase except 0.7 (ledger).

### 0.1 — Stale index disposition

- **Read:** `git diff --cached --stat`, `git diff --cached -- resources/css/app.css tests/browser/review-detail.spec.ts`.
- **Decision required:** confirm the 68-deletion claim with your own eyes. Then choose: (a) leave the real index stale and rely on private `GIT_INDEX_FILE` for every future commit, or (b) `git restore --staged` — which touches peer-dirty files and needs owner sign-off.
- **Recommendation:** (a). The stale index is annoying, not unsafe, if every commit is pathspec-scoped.
- **Verification:** the chosen state reproduced in a fresh `git diff --cached --stat` output pasted into the ledger.
- **Deliverable:** one-line ruling in ledger S67.

### 0.2 — BO-013 revert reconciliation

- **Read:** `git show 1392e30f -- resources/js/pages/Career/Cockpit.vue`, then the current `denominatorLabel` computed.
- **Objective:** determine what the *correct* state-aware behaviour is — the component was returning `'floor'` conditionally; `5869d1a0` flattened it to `'cap'`; `1392e30f` reverted. The final fix must preserve the condition and only correct the wrong branch.
- **Output:** a written classification: `BO-013 = conditional-not-value` or `BO-013 = correctly reverted, close`. If conditional, produce the exact predicate and the exact line to change.
- **Verification:** `npx playwright test tests/browser/career-cockpit.spec.ts -g "floor|cap" --workers=1` must fail for the *stated* reason before any edit, and pass after.
- **Dependency:** none.

### 0.3 — `5869d1a0` ancestry and content audit

- **Read:** `git merge-base --is-ancestor 5869d1a0 HEAD; echo $?`, `git show --stat 5869d1a0`.
- **Objective:** settle §2 vs §4 contradiction. Then enumerate which of BO-001/008/009/010/013/017 actually have code in that commit — `git show 5869d1a0 -- <each path>`.
- **Output:** a table `BO-ID | code path present (y/n) | still in HEAD (y/n) | classification`.
- **Why blocking:** if BO-008 has no code in `5869d1a0`, the commit message is a lie and BO-008 is fully open. If it does, the escalation may be about something else.
- **Verification:** the table pasted into ledger S67.

### 0.4 — `e712598c` / `5b8e1a83` lineage

- **Read:** `git branch -a --contains e712598c`, `git branch -a --contains 5b8e1a83`, `git log --all --oneline --grep="e712598c\|5b8e1a83"`.
- **Objective:** determine whether the light-ink re-theme and Stat Slice work are in HEAD ancestry.
- **Branch:** if absent → mark the grey-scale parity analysis "computed against a tree that no longer exists; recompute before citing" and remove the design-token assumptions from any active slice. If present → note the branch and move on.
- **Verification:** the `--contains` output (empty or branch names) pasted into ledger.

### 0.5 — `DesignTokensTest` scope inspection

- **Read:** locate the test class (`grep -rn "DesignTokens" tests/`), read its assertions for `--color-risk` and background pairs.
- **Objective:** answer one question — **does `DesignTokensTest` already assert `--color-risk` contrast on `#333333`, `#2A2A2A`, `#09090B`?**
- **Branch:**
  - If **yes** → Phase 1 needs **zero Playwright specs**. It becomes: edit token → run DesignTokensTest → commit. This kills the SITREP's "write 2 specs" deliverable.
  - If **no** → add the assertion *to DesignTokensTest*, not to Playwright. A token contrast is a unit-level invariant, not a browser behaviour.
- **Verification:** the test file read, and the exact assertion lines quoted.

### 0.6 — Build/quality command reality check

- **Read:** `composer.json` scripts, `Makefile` (if present), `package.json` scripts, locate `phpstan.neon` or `phpstan.neon.dist` (`find . -maxdepth 2 -name "phpstan*"`).
- **Objective:** resolve the `make` vs `composer` contradiction (KI-4) and locate phpstan config. Produce a **canonical command table** used by every later slice's verification block.
- **Deliverable:** the table in ledger S67; all later slices cite it rather than guessing.
- **Verification:** each command in the table run once, exit code recorded.

### 0.7 — BO-008 reframe (read-only; no fix)

- **Read:** the trainee-select controller and its query (`grep -rn "GlobalReleased\|external_ref" app/Http/Controllers/`), then `routes/web.php` for the trainee select route.
- **Objective:** determine whether the "133 vs 66" is a **bug** or a **scope difference** (catalogue shows all 133; wizard filters to 66 global-released).
- **Branch:**
  - If scope difference → BO-008 is a **labeling** ticket. Fix is copy: "Released globally (66 of 133)" or similar. One line, one commit, **Phase 2.3**.
  - If genuine bug → proceed to Phase 2.4 (data correction) and only then run `unsourced-rows.py`.
- **Do not** run `unsourced-rows.py` before this gate. If the 6 rows are irrelevant to the wizard query, that script is probing a non-bug.
- **Verification:** the controller `WHERE` clause quoted verbatim.

### 0.8 — Ledger S67

- **Deliverable:** one commit, `docs(ledger): S67 reconciliation gate — index, BO-013, BO-008 reframe, lineage, command table`.
- **Content:** the six rulings above, the command table, and a STATE block.
- **Verification:** `git show --stat <sha>` lists exactly the ledger file.

**Phase 0 exit condition:** every premise in this roadmap that says "verify" has been verified, and the canonical command table exists. Only then does Phase 1 begin.

---

## Phase 1 — Slice 9 / BO-004 accessibility token (real defect, user-visible)

**Objective:** `--color-risk` meets WCAG AA 4.5:1 on all three surface backgrounds, or is documented as an owner-accepted carve-out.

**Verified inputs (SITREP §3):** `#FF4400` = 4.155:1 on `#2A2A2A`, 3.657:1 on `#333333`. Both fail.

**Token choice:** `#FF6F3B` (4.566 on raised, 5.187 on panel, 7.190 on page). `#FF6D38` at 4.510 is a 0.010 margin over AA — fragile across subpixel rendering, font smoothing and display gamma. Choose headroom.

### Slice 1.1 — Token edit

- **Read first:** `resources/css/app.css`, the `--color-risk` declaration and its immediate neighbours. `git diff HEAD -- resources/css/app.css` to see the peer MWG EOF block — **the edit must not touch it**.
- **Action:** change exactly one declaration. Do not reorder, do not reformat, do not touch the MWG block.
- **Deliverable:** one-line diff in `app.css` token section only.
- **Verification:**
  ```
  vendor/bin/pest --filter DesignTokensTest
  ```
  (plus the contrast assertion added in 1.2 if 0.5 found it absent)
- **Dependency:** 0.5 (know whether the test covers this), 0.6 (command table).
- **Done when:** DesignTokensTest green and the diff is exactly one line.

### Slice 1.2 — Contrast assertion (only if 0.5 found the pair uncovered)

- **Read first:** the existing DesignTokensTest for the pattern used for other tokens.
- **Action:** add one assertion per background pair for `--color-risk`. If `#FF6F3B` is the token, assert `>= 4.5` on raised and `>= 5.0` on panel (matching the computed headroom, so the test fails if someone lowers lightness again).
- **Deliverable:** test additions only. **No Playwright spec** unless 0.5 proves DesignTokensTest cannot express the assertion.
- **Verification:** `vendor/bin/pest --filter DesignTokensTest` green; then deliberately revert the token in a scratch copy and confirm the test fails (proves the assertion is live, per doctrine #6 "verify the assertion, not just setup").
- **Dependency:** 1.1.
- **Done when:** test fails on old token, passes on new token, and the test itself was observed doing both.

### Slice 1.3 — DESIGN.md erratum

- **Read first:** `DESIGN.md` structure; confirm no BO-004 mention exists (SITREP says 0 matches).
- **Action:** append an erratum section naming BO-004, old value, new value, ratios on all three backgrounds, and the WCAG threshold. Dated.
- **Deliverable:** `DESIGN.md` addition only.
- **Verification:** `grep -n "BO-004" DESIGN.md` returns the new section.
- **Dependency:** 1.1 (ratios must be the shipped values).
- **Done when:** the erratum quotes the same numbers the test asserts.

### Slice 1.4 — Private-index commit

- **Read first:** `.scratch-uma/commit-msg-slice8.txt` for the body format.
- **Action:**
  ```
  GIT_INDEX_FILE=.scratch-uma/index-slice9 git add resources/css/app.css tests/.../DesignTokensTest.php DESIGN.md
  GIT_INDEX_FILE=.scratch-uma/index-slice9 git commit -F .scratch-uma/commit-msg-slice9.txt
  ```
  Pathspecs only. Never `-A`. Never the real index.
- **Deliverable:** one commit. Body: Finding BO-004, Classification real defect, Evidence ratios, Verification DesignTokensTest output, Peer note (MWG block left unstaged).
- **Verification:** `git show --stat <sha>` lists exactly three files; `git show <sha> -- resources/css/app.css` shows one token line and **no** MWG block.
- **Dependency:** 1.1–1.3.
- **Done when:** the diff is clean of peer content and the test result is quoted verbatim.

**Phase 1 exit:** a human can look at the risk indicator on all three surfaces and pass a contrast checker; DesignTokensTest guards it going forward.

---

## Phase 2 — BO audit closure (remaining findings)

**Objective:** every BO finding from `browser-observation-2026-11-15/03-code-change-plan.md` is either fixed with a passing assertion, or closed as stale with evidence, or escalated with a product question. No finding left in limbo.

**Inputs:** `03-code-change-plan.md`, `04-excluded-findings.md` (nothing here is touched), followup `00-corrections.md` and `07-ex12a-override.md` (these override the main audit where they conflict).

### Slice 2.1 — Slice 8 (unknown BO ID)

- **Read first:** `03-code-change-plan.md` slice 8. **Not yet read in any transcript** — its BO ID and surface are unknown. This is the single largest unknown in the roadmap.
- **Objective:** identify the finding, then fix or mark stale.
- **Action:** per the per-slice contract in Phase 1's pattern.
- **Verification:** repro gate first (does it reproduce?); if stale, commit nothing and record evidence (sha + file:line). If real, targeted Playwright + 2 adjacent.
- **Dependency:** 0.5 (command table), 0.3 (is it already inside `5869d1a0`?).
- **Done when:** classified with evidence.

### Slice 2.2 — BO-013 correct re-fix (conditional-aware)

- **Depends entirely on 0.2's ruling.**
- **If 0.2 says "correctly reverted, close":** commit nothing; ledger one line.
- **If 0.2 says "conditional-not-value":** edit the predicate, not the literal. Regression test must assert **both** branches (the floor state still shows floor; the cap state shows cap). A test that only asserts the cap branch would let someone re-flatten it — which is exactly how `5869d1a0` shipped and how `1392e30f` had to revert.
- **Verification:** `npx playwright test tests/browser/career-cockpit.spec.ts -g "<both branch titles>" --workers=1` → 2 passed.
- **Done when:** both branches observed green and the regression test would fail if either branch were flattened.

### Slice 2.3 — BO-008 labeling fix (only if 0.7 found scope-difference)

- **Action:** one copy change on the trainee-select surface stating the scope ("Released globally — 66 of 133" or equivalent). No query change, no data change.
- **Deliverable:** one component edit + one assertion in the existing trainee-select spec.
- **Verification:** targeted Playwright on the label.
- **Dependency:** 0.7.
- **Done when:** the surface states its own scope and a reader cannot mistake it for the full catalogue.

### Slice 2.4 — BO-008 data correction (only if 0.7 found a genuine bug)

- **Action:** TBD from 0.7's query read. Likely one of: backfill `external_ref` on 6 rows, or fix the counting query.
- **Do not run `unsourced-rows.py` until 0.7 authorises it.**
- **Verification:** DB probe re-run showing the corrected count; targeted feature test.
- **Dependency:** 0.7, and a written statement of which side (catalogue or wizard) is authoritative.
- **Done when:** the two surfaces agree, or the disagreement is documented as intentional.

**Phase 2 exit:** the audit's "fixable in code" list is empty — every entry is fixed, stale, or escalated with an owner question attached.

---

## Phase 3 — Artwork pipeline (user-visible missing functionality)

**Objective:** a fresh install renders artwork without the user knowing `uma:fetch-art` exists, and a failed/empty mirror shows a placeholder with a retry hint.

This phase is the one that moves the product from "works if you know the incantation" to "works." It has three slices in dependency order.

### Slice 3.1 — Shared artwork component audit (read-only)

- **Read first:** `resources/js/components/ArtworkSlot.vue` (**peer-modified — check mtime before anything**), and `grep -rn "ArtworkSlot\|fetch-art\|art_path" resources/js/`.
- **Objective:** determine whether trainees and support cards share one artwork component or have divergent inline `<img>` handling. The SITREP lists `ArtworkSlot.vue` as peer-modified; `CardPicker.vue` and `SupportSlot.vue` are also modified.
- **Output:** a written finding: `shared component` or `N components`. If N ≥ 3, this phase is re-scoped before editing.
- **Verification:** the grep output and the component read, quoted.
- **Dependency:** peer mtime check; if `< 5 min`, skip and retry next slice.

### Slice 3.2 — Empty-state placeholder (item 6)

- **Read first:** the component(s) identified in 3.1, specifically the branch that currently renders nothing.
- **Action:** replace the empty branch with a sized placeholder (same dimensions as the real image, so layout does not shift), `alt="Artwork not mirrored"`, `title` with the retry hint. Copy must cover both today's cause ("user never ran it") and tomorrow's ("install-time fetch failed"):
  > "Artwork not mirrored — run `php artisan uma:fetch-art` to retry."
- **Deliverable:** one component edit (or N if 3.1 found divergence, in which case re-scope first) + one browser assertion.
- **Verification:** `npx playwright test <trainee|support spec> -g "artwork placeholder" --workers=1` → 1 passed. Fixture: a guaranteed-missing path.
- **Dependency:** 3.1; `ArtworkSlot.vue` mtime ≥ 5 min.
- **Done when:** an empty mirror is visibly "not yet mirrored," not silently blank.

### Slice 3.3 — Auto artwork mirror at install

- **Objective:** the artwork referenced by 3.2 is populated automatically for trainees and support cards during install.
- **Read first:** the existing `uma:fetch-art` command (its signature, source, failure modes), the install path (`composer.json` post-install, or the artisan installer, or both), and gametora's rate-limit behaviour if documented.
- **Planning output first (no code):** a short `docs/plan/artwork-auto-mirror.md` deciding: (a) where the hook lives — composer post-install vs artisan installer vs first-boot; (b) failure handling — abort install, warn-and-continue, or queue for retry; (c) rate limiting and caching; (d) whether partial mirrors are acceptable.
- **Action:** implement the chosen hook. It must **not** fail the install when the network is down — artwork is non-essential to a working app; a failed fetch must fall through to 3.2's placeholder.
- **Deliverable:** the install hook + a feature/unit test asserting the hook is idempotent (running twice does not re-download) and network-failure-tolerant.
- **Verification:** `vendor/bin/pest --filter ArtworkMirror` green; then a manual run with the network stubbed to fail and the app still boots.
- **Dependency:** 3.1, 3.2, and a written statement of whether gametora's terms permit automated fetching at install time.
- **Done when:** a clean checkout + install renders trainee and support artwork with no manual command.

**Phase 3 exit:** the "Artwork mirror is manual" and "Empty mirror is invisible" limitations in `QWEN.md` are both false and can be removed.

---

## Phase 4 — Trainer-supplied image uploads (future feature; planned, not built)

**Objective:** a planning artefact, then implementation only after owner sign-off. This is the largest new attack surface in the product and is **not** a quick slice.

### Slice 4.1 — Threat model and scope doc (no code)

- **Deliverable:** `docs/plan/image-uploads.md` covering: accepted MIME types and magic-byte validation; max dimensions and byte size; storage location (`storage/app/private` vs public) and symlink policy; filename sanitisation and path-traversal defence; cleanup on run deletion; whether uploads are per-run or global; EXIF stripping; and the interaction with the existing artwork pipeline (does a user upload override the mirrored artwork?).
- **Read first:** `PRD §6.13` (currently says "cut"), any existing file-handling code, and the Laravel filesystem config.
- **Dependency:** owner sign-off on the scope doc before Slice 4.2.
- **Done when:** the doc is ratified and `PRD §6.13` is rephrased `cut` → `planned, not in this build` (the UX-19 `9c50f139` pattern).

### Slice 4.2+ — Implementation

- **Deferred** until 4.1 is ratified. Not enumerated here, because enumerating implementation slices for an unratified threat model would be fabrication.

**Phase 4 exit (when it runs):** a trainer can attach an image to a run, it is validated, stored safely, displayed, and deleted with the run.

---

## Phase 5 — CI and quality gates (premise-check first)

**Objective:** decide whether "No CI" is a gap or a correct description, then act only if it is a gap.

### Slice 5.1 — Premise check (read-only)

- **Read:** `git remote -v`, `ls .github/workflows/`, `.gitlab-ci.yml` if present, and the peer commits that mention CI (`0cb81059`, `072bb52e`, `cc37e9bd` from the SITREP log — these are already CI-related, which suggests CI partially exists).
- **Objective:** answer three questions: (1) is there a remote to run CI on? (2) do the peer CI commits mean a pipeline already exists that `QWEN.md` is unaware of? (3) what does CI add that local gates do not?
- **Output:** a ruling — `CI not applicable (no remote)` / `CI exists, QWEN.md stale` / `CI genuinely missing, scope TBD`.
- **Verification:** the `git remote -v` and workflows-listing output quoted.
- **Dependency:** none.

### Slice 5.2 — `QWEN.md` accuracy pass

- **Read first:** `QWEN.md` (untracked — decide track vs ignore in 0.1's spirit).
- **Action:** correct the two lines this roadmap has already falsified or may falsify: "No image uploads" (pending 4.1) and "Artwork mirror is manual" (falsified by 3.3). Add "CI: <ruling from 5.1>."
- **Deliverable:** `QWEN.md` edit. If the file is tool-generated, note the regeneration risk in the commit body and fix the template if one exists.
- **Dependency:** 3.3 and 5.1, so the statements are true when written.
- **Done when:** `QWEN.md` contains no statement the tree contradicts.

---

## Phase 6 — Externally gated (tracked, not executed)

| Phase | Gate | Condition to unblock | Action when unblocked |
|---|---|---|---|
| 3 (devcontainer) | Docker absent, exit 127 | Docker installed | Read `.devcontainer/bootstrap-agent-controls.sh`, verify build, commit |
| 4 (tool configs) | No Kilo Code / MiniMax / DSH schemas | Schema delivered | Write configs against confirmed schema; no fabrication before then |

**No slices exist while the gates are closed.** Writing an unbuildable Dockerfile or a schema-less config is explicitly forbidden (L6 / law 4).

---

## Dependency graph (read top → bottom)

```
Phase 0 (gate)
 ├─ 0.1 index ruling ──────────────┐
 ├─ 0.2 BO-013 classification ─────┼──► 2.2
 ├─ 0.3 5869d1a0 audit ────────────┼──► 2.1, 2.3/2.4
 ├─ 0.4 e712598c lineage ──────────┼──► (design-token assumptions)
 ├─ 0.5 DesignTokensTest scope ────┼──► 1.1, 1.2 (and cancels 2 specs)
 ├─ 0.6 command table ─────────────┼──► every verification block
 ├─ 0.7 BO-008 reframe ────────────┼──► 2.3 or 2.4
 └─ 0.8 ledger S67 ────────────────┘
                                     │
Phase 1 ◄────────────────────────────┘
 1.1 token → 1.2 assertion → 1.3 erratum → 1.4 private-index commit
                                     │
Phase 2 ◄────────────────────────────┘
 2.1 slice 8 ─ 2.2 BO-013 ─ 2.3 BO-008-label ─ 2.4 BO-008-data
                                     │
Phase 3 ◄────────────────────────────┘
 3.1 audit → 3.2 placeholder → 3.3 auto-mirror
                                     │
Phase 4 ◄────────────────────────────┘
 4.1 threat model (owner sign-off) → 4.2+ implementation
                                     │
Phase 5 ◄────────────────────────────┘
 5.1 premise check → 5.2 QWEN.md accuracy
```

Phases 1 and 2 can run in parallel once Phase 0 closes, provided neither touches a peer-locked file. Phase 3 depends on Phase 2 only because `ArtworkSlot.vue` may carry BO findings; if 3.1 finds it clean, 3.1 can start alongside 2.

---

## Definition of "fully functional final product"

Trainer Desk v3.0.0 is complete when all of the following are true and **verified by a command, not by a report**:

1. `vendor/bin/pest` green, no skipped tests without a named reason.
2. `npx playwright test` green, with every BO finding that was "fixable in code" covered by an assertion that was observed failing before the fix and passing after.
3. `vendor/bin/pint --test` clean; `npm run typecheck` exit 0 on a tree where peer files are either committed or stashed.
4. Every WCAG pair in the design tokens passes AA, except the eva01 carve-out, which is documented with the owner's name against it.
5. A clean clone + install renders trainee and support artwork with no manual artisan command, and renders a labelled placeholder when the network is unavailable.
6. `QWEN.md` contains no statement the tree contradicts.
7. The ledger names every BO finding's terminal state: `fixed (sha)`, `stale (evidence)`, or `escalated (question)`.

Anything not meeting those seven is a progress report, not a finished product.

---

## What I could not inspect and what that costs you

I could not read `03-code-change-plan.md`, so **slice 8's BO ID and surface are unknown** (Slice 2.1 is a box I cannot fill). I could not read `DesignTokensTest`, so Phase 1's shape is a branch, not a fixed plan. I could not resolve `make` vs `composer` (Phase 0.6). I could not confirm whether `e712598c` is real (Phase 0.4). Each of these is deliberately a **Phase 0 task rather than a roadmap assumption** — because assuming any of them is the exact failure mode that produced the BO-013 revert and the BO-008 escalation. The roadmap is deliberately shaped so that the first thing the executing agent does is falsify it.
