# Master roadmap execution record - 2026-11-18 session

Governing document: docs/roadmap/MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md (873 lines, 22 slices,
5 phases). This file records what was executed, what was measured, and what remains owner-gated.
Every claim below names the command or file that produced it.

Session basis: branch `master`, start HEAD `816e923e`, divergence `8	38` (`git rev-list
--left-right --count origin/master...HEAD`). The tree is shared and moved during the session; each
measurement below is a timestamp with its command beside it.

## Phase 0 - Land the tree

### S0.1 - UI/UX accessibility batch: NOT COMMITTED, owner-gated
The roadmap's own dependency row reads "owner names the pathspecs (standing rule)", and the owner's
standing instruction is that nothing is staged or committed without an explicit instruction naming
paths. The batch is intact in the working tree (`git status --porcelain -uall` shows it), and this
session added to it rather than replacing it. **No commit was made.** This is a stated partial, not
a pass.

### S0.2 - Gate script repaired: DONE (verified by run)
`tools/gates.sh` was repaired in this session and by a peer in the same window, so the final file is
a merge of both. What this session contributed and what was measured:

- `collect()` was added: `git diff --name-only HEAD` plus `git ls-files --others --exclude-standard`,
  filtered to `\.(php|blade\.php)$`. This is the KI-75 repair, because a bare `pint --test` collects
  every file that differs from HEAD, which on a shared worktree includes every peer's in-flight edit.
- `citation census` and `untracked files` steps were added, both named in AGENTS.md §9 and both absent.
- **One attempt was reverted on evidence, and the reason is recorded in the file.** This session tried
  scoping `phpstan` to the collected files with `--level=6`, then with a `paths`-less config. A peer
  measured the same question independently and recorded it at `tools/gates.sh:53-64`: a scoped run
  reports 32 invented errors on an UNMODIFIED test file, because Pest's stub types reach PHPStan
  through whole-project discovery. The peer's whole-project invocation is what stands. The orphan
  config this session wrote for the experiment was deleted.
- Measured: `bash tools/gates.sh --fast` runs 13 steps. `pint` passes on the collected set,
  `phpstan` reports `[OK] No errors` repo-wide, `adr-check.py` reports `0 finding(s)`.
- Exit code was NOT 0 at the last full run, because `pint` was still red on two of this session's own
  test files at that moment. Those were fixed and the pair re-ran green; the whole-gate re-run is
  recorded below.

### S0.3 - ADR drafts and document batch: NOT COMMITTED, owner-gated
Same reason as S0.1. Six ADR drafts (0031-0036), the audit walk, the sitreps and the roadmap files
are present and uncommitted.

### S0.4 - Empty-diff status noise: DONE
`git diff --numstat` was empty for both files and both carried 0 CRLF, which is the precondition the
slice names. `git checkout -- tests/browser/career-trainee-select.spec.ts tests/browser/wizard-flow.spec.ts`
ran clean (exit 0) and both rows left `git status` (`git status --porcelain -- <both>` -> empty).
`LineEndingGuardTest` had already been re-run green earlier in the session on the repaired tree.

### S0.5 - Reconcile with origin/master: OWNER-GATED, parked
Divergence is still `8	38`. Merging or rebasing 38 commits and pushing is owner act D-3. Not attempted.

## Phase 1 - Suite and document truth

### S1.1 - Full Pest baseline: DONE
```
php artisan test --compact < /dev/null
Tests:    2467 passed (37575 assertions), 2 failed
Duration: 503.91s
```
Both failures classified, neither a product defect:
1. `DocCitationParityTest` - `Failed asserting that 808 is equal to 708 or is less than 708`. This is
   the citation ratchet, expected red until S1.3 lands. Named as such, not claimed as a pass.
2. `LineEndingGuardTest` - `resources/js/spa.ts` carried CRLF. Repaired (the file is a peer's edit
   that arrived with CRLF endings); the guard re-ran green: `Tests: 1 passed (2 assertions)`.

### S1.2 - Full browser sweep: NOT COMPLETED
Two sweep attempts were started and both were invalidated by the shared tree, which is the §15 trap
"the tree moves while it is read" arriving in its most expensive form:

- Sweep 1 (port 8201) ran 32 minutes. A peer rebuilt `public/build` at a different content hash
  mid-run, so the run was measuring two bundles. It was killed rather than reported, because a
  split-build sweep is not evidence.
- Sweep 2 (port 8201, fresh scratch DB) was started after that rebuild and was still running when
  this record was written; `resources/js` was modified again during it, so it may carry the same
  defect. It is reported as *running at hand-off*, not as a baseline.

What IS measured: `npx playwright test --list` -> 884 tests in 126 files. Four specs were run
individually and are green (below). A full-suite baseline at one bundle remains owed.

### S1.3 - Citation ratchet 808 -> <=708: PARTIAL
`python tools/doc_census.py` reads `808 (808 GONE, 0 UNTRACKED)` on the committed tree and
`347 (298 GONE, 49 UNTRACKED)` on the working tree, so the edits are real and the gate cannot see
them until they are committed.

- 156 citations across 9 files were repointed to the master that absorbed their target, in the form
  `` `<MASTER>.md` SS<original name> ``. The targets chosen were the ones `tools/doc_census.py`'s own
  `folded_sections()` resolves, so the replacement is the tool's own notion of where the content went.
- 228 further citations in 25 files were left alone: their target has no master section, so repointing
  them would be inventing a destination. They need either a tombstone with a dated note or a decision
  that the target should be resurrected.
- **The 708 bar is not met on the committed tree, and DOC_CITATION_BASELINE was not touched.** The
  working-tree drop from 694 to 347 is the evidence that the approach works; committing it is what
  turns it into a passing gate, and committing is owner-gated.

## Phase 2 - Open defects with a named code site

### S2.1 - page-header.spec.ts tier: DONE
`PageHeader.vue:68` renders `text-3xl font-semibold text-ink-strong` and `DESIGN.md:368` specifies the
same. The spec asserted the retired `text-[28px]` / `font-extrabold`. The three assertions were
corrected to the shipped tokens, and two negative assertions were added so a regression to the old
pair fails. Verified: `page-header.spec.ts` green in a 38-case batch.

### S2.2 - KI-167 Scenario Selection focus: DONE, root cause found
The spec asserted `toBeFocused()` after a keyboard selection and read `inactive`. The root cause is one
attribute in `ScenarioSelect.vue`:

```
:disabled="form.processing"      // on each scenario radio
```

`form.put()` sets `processing` synchronously, so the radio that had focus was disabled at the moment
of the keypress. A browser blurs a control it disables, so focus moved to `body` before the PUT
resolved; `onSuccess` then focused that same radio on `nextTick`, which was still disabled, and a
`.focus()` on a disabled control is a silent no-op. The spec was therefore reading the truth: focus
never landed anywhere.

Found by measurement, not by reading: a throwaway probe spec logged `document.activeElement` at four
points and installed a `focusin`/`focusout` listener.

```
after load:                    h1
after focus:                   input#scenario-radio-trackblazer
immediately after Space:       body            <-- before the visit resolves
after "Scenario set." visible: body
after 600ms settle:            body
EVENTS:                        (no focusin fired at all)
```

Fixed by removing the attribute and adding a re-entrancy guard in `choose()`, which is the correct way
to prevent a second submission: disabling the control under the Trainer's finger is not.

```
after load:                    h1
after focus:                   input#scenario-radio-trackblazer
immediately after Space:       input#scenario-radio-trackblazer
after "Scenario set." visible: input#scenario-radio-trackblazer
after 600ms settle:            input#scenario-radio-trackblazer
```

Verified: `career-scenario-select.spec.ts` + probe -> **11 passed, 0 failed**. The probe was deleted
after use (it is a diagnostic, not a gate case). KI-167 is CLOSED in the register with this evidence.

### S2.3 - KI-114 deck-picker specs: DONE, with the entry's premise refuted
This is the slice where the roadmap's own description turned out to be wrong, and the measurement is
worth recording in full.

The roadmap says to repair the specs "against the shipped `CardPicker.vue` vocabulary: `query` -> `q`,
`status` -> `owned_only`, drop the rarity filter". Measured instead:

- Every selector resolves to a LIVE site. `Level for ${label}` is `SupportSlot.vue:221`;
  `name="rarity"` is `Support/Builder.vue:443`; `#deck-picker-results` is `Builder.vue:494`;
  `name="query"` is `Builder.vue:455`.
- The specs navigate to `/training-runs/create` then the run's `/deck` step, which composes
  `Support/Builder.vue` + `SupportSlot.vue`. They never touch `CardPicker.vue`, which is what the
  roadmap's repair list describes. The earlier static read that produced "retired controls" had
  searched `Career/DeckSelect.vue` and `CardPicker.vue` only.
- Run: `npx playwright test support-deck.spec.ts career-legacy-deck-steps.spec.ts` -> **32 passed,
  0 failed** (13 + 19), once three genuine stale spec premises were repaired.

The three real defects, all in the specs or in one product line:
1. `career-legacy-deck-steps.spec.ts:774` asserted `toHaveValue('')` after a keyboard accept. The
   picker prints the committed value (`282e5437`: displayed value == stored value). Fixed to assert
   `'Cut and Drive!'`.
2. `career-legacy-deck-steps.spec.ts:352` asserted a server refusal that is now unreachable from the
   UI: `BaseSelect.vue:64-72` re-emits `change` (`282e5437`), so the category reset fires and the
   mismatched pair can no longer be built. Rewritten to assert the prevention (target clears on
   category change), with the server-side refusal left pinned by `SparkTargetValidationTest`.
3. `LegacySelect.vue:240` focused `#legacy-save` from `onSuccess`, while `form.processing` is still
   true inside that callback and the button carries `:disabled="form.processing"`. A browser refuses
   `.focus()` on a disabled control, so focus stayed on `body` and the WCAG 2.4.3 promise the comment
   makes was never kept. Changed to `onFinish`, which runs after the flag clears. **This one is a
   product fix, not a spec fix.**

### S2.4 - KI-127 Team Race Facility panel: ALREADY FIXED, row closed
The fix landed at `1119e437`: the panel root is `class="card p-3"` with an `<h2>` capsule header.
`tests/browser/team-race-facility-panel.spec.ts` passes 2/2 including the axe scan. The register row
said OPEN; it is now CLOSED with the commit named.

### S2.5 - KI-104 browser half: NOT RUN
`career-cockpit-forms.spec.ts` was not executed in this session; the ADR-0027 signature is owner act
D-1. Parked rather than claimed.

### S2.6 - Register closeout + vendor the auditor: DONE
`tools/audit_status_register.py` was copied into the repo from the verification skill (it existed
only there, so the slice's acceptance command was unrunnable from a clean checkout). Against
`KNOWN-ISSUES.md` it now reports:

```
index rows   : 111
body entries : 117 headings / 111 distinct ids
id range     : 57..167 | missing numbers: 0 (contiguous)
census       : {'OTHER:ONWARD': 1, 'CLOSED': 106, 'OPEN': 4}
OPEN (4)     : KI-104, KI-106, KI-162, KI-167
CONTRADICTIONS: none
RESULT: clean
```

Five rows changed state in this pass, each with the evidence in the row itself: KI-114, KI-116,
KI-119 and KI-127 closed (**the estate said OPEN; the code says otherwise**), KI-167 added (it had a
body entry and no index row, which is the "NO INDEX ROW" finding the tool exists to catch).

## Phase 3 - Missing product capability

### S3.1 - Independent Training door: DONE
This was the roadmap's "one real functional gap", and it was real: the backend was complete
(`Lifecycle::start()` called from `PreflightController@store`, `StartCareerRequest` validating
`independent`/`policy`/`priority_skills`) while `independent_training_runs` held 0 rows, because the
wizard's controls were cut in Phase E2 and never restored.

Implemented:
- `PreflightController@show` now ships an `independent` prop: the three `IndependentTrainingPolicy`
  cases with their own labels, the ceiling from `IndependentTrainingRun::MAX_PRIORITY_SKILLS`, the
  default policy, and the Global-released skill list (`ReleaseStatus::GlobalReleased`) so a priority
  skill is one the shipped client can award.
- `Preflight.vue` gained the three controls inside a closed disclosure: the idle-session toggle, the
  policy select, and the capped priority-skill picker. Rotation is deliberately not offered (a null
  rotation is the scenario's own target races, ADR-0032's documented default).
- The disclosure sits in its OWN card, not inside Commit, and that was a measured decision rather than
  a preference: `career-preflight.spec.ts` holds the Commit card to a 25-descendant and 900-character
  ceiling ("the commit card stays a card, not a document"), and three controls plus their help text
  broke both. The first attempt put them inside Commit and the ceiling case caught it.
- Two specs pinned the Phase E2 cut as an invariant and had to be inverted, which is the honest
  consequence of reversing a cut: `career-preflight-indep.spec.ts` asserted the absence of the mode
  switch, policy radios and the words "Independent Training", and `career-preflight.spec.ts` asserted
  the same from its own walk. Both now assert the restored door -- present, closed by default, and
  revealing exactly three policy options when opened -- and both record why the assertion flipped.
  A third case the roadmap does not mention, `career-preflight.spec.ts`'s commit-card ceiling, is what
  enforced the card split.
- The three values ride the page's existing single PUT. `useForm` was given a real shape so a refusal
  prints under its own control rather than behind an untyped error map.

Verified:
```
php artisan test --compact --filter=CareerPreflightTest
Tests: 33 passed (390 assertions)      # was 30; 3 new cases
```
The three new cases are the ones the acceptance line names: an overlay row is created from the
wizard PUT with the policy and priority list; no overlay row is written when the flag is absent; an
unknown policy and an eleventh priority skill are refused by the request, not by the control.

Browser half: `career-preflight-indep.spec.ts` + `career-preflight.spec.ts` ->
**15 passed, 0 failed** (port 8213), including the commit-card ceiling case.

### S3.2 - Hint-level copy: DONE
`resources/js/pages/Skills/Show.vue` carried two sentences that denied a column which has existed
since `2026_11_03_155641`: "No run stores a hint level" and "No table stores a hint level". Both now
state the recorded fact. `SkillsPlanner.vue`'s header comment carried the same stale claim and was
corrected with them. A regression case was added that reads the template and asserts both stale
strings are absent and both replacement sentences are present:
`php artisan test --compact --filter='hint level|SkillDetailTest'` -> `14 passed (280 assertions)`.

### S3.3 - Japanese search key: DONE
ADR-0036's open item was that the column is never back-filled in the migration. The dev database was
already complete (`match_key_ja` nulls: 0 across 1910 skills), but no case proved a Japan-only row is
reachable by the name the client prints. Two cases added to `SkillSearchScreenTest`:
`?search=<Japanese name>&scope=japan_only` returns the row with `name_ja` as its label and `name`
null; and the same search in the default scope returns nothing, so a Japan-only row does not leak into
the Global catalogue. `php artisan test --compact --filter=SkillSearchScreenTest` -> `30 passed (482
assertions)`.

### S3.4 - Maiden-lock fallback: OWNER-GATED, parked
ADR-0031's open item is a ruling (record the inference as the rule, or store the fact). No code was
written, because either branch needs the signature first. Parked as D-1.

### S3.5 - Capability register: OWNER-GATED, no code
Seven rows, each needing a ruling before any code. None started, as the roadmap's rule requires.

## Phase 4 - Release truth and tag

### S4.1 - VERSION.md re-baselined: DONE
Rewritten against measurements taken this session. What changed and why:
- `laravel/framework` **13.32.0 -> 13.42.0** (`composer show` -> `versions : * v13.42.0`; lock agrees).
- The suite baseline is now recorded: **2467 passed / 2 failed**, with both failures named.
- The tag row said two conditions blocked it and named `full-career-journey.spec.ts` as red and the
  full browser suite as never run. Both statements were stale and are replaced with the measured
  state: divergence `8  38`, no `3.0.*` tag, and the tag owner-gated as D-12.
- The Phase-1 criterion (d) row said the ratchet was satisfied at 698. It is not: it reads 808. The
  row now says so and names `GOVERNANCE.md` C-8 as the reason the limit cannot be raised instead.
- Five `Â§` mojibake sequences and one duplicated sentence were removed.

### S4.2 - CHANGELOG for the 3.0.0 range: DONE
Added the entries the roadmap names and the range was silent on: the fan ladder (KI-165), the
Independent Training wizard door (S3.1), the six ADR drafts, the UI/UX accessibility batch with both
new specs named, and two fixes from this session (the Legacy save focus and the hint-level copy).
One `## [Unreleased]` heading preserved (`grep -c` -> 1).

### S4.3 - ADR hygiene: DONE (agent half); signatures owner-gated
`python tools/adr-check.py docs/adr` -> `2 finding(s)`: ADR-0026 Decision 529 words, ADR-0029 546
words, both over the 500 cap. Both trimmed to their rulings with the repeated justification removed;
no decision was dropped, only prose that restated one. Result: `0 finding(s)`. The eight missing
signatures are owner act D-1 and are not simulated.

### S4.4 - Cut v3.0.0: OWNER-GATED, not attempted
`git tag -l 'v3*'` is empty and stays empty; tagging is D-12.

## Phase 5 - After the tag

Not started, and correctly so: every row is either owner-gated (S5.1 D-9, S5.2 D-8, S5.3 D-11, S5.4
D-10, S5.6 OQ-6) or depends on Phase 0-4 landing first (S5.7, S5.8, S5.9). One item was checked
rather than assumed: S5.5's ADR-0018 half is already built (`app/Services/PageSize.php` exists and
ADR-0018 is Accepted), so that row is narrower than the roadmap states.

## Verification summary

| Gate | Result | Command |
| --- | --- | --- |
| Full Pest suite, first run | 2467 passed, 2 failed (both classified) | `php artisan test --compact < /dev/null` |
| Full Pest suite, final run | **2478 passed, 4 failed**, each classified by owner (see below) | same |
| Focused: CareerPreflightTest | 33 passed, 3 new | `--filter=CareerPreflightTest` |
| Focused: SkillSearchScreenTest | 30 passed, 2 new | `--filter=SkillSearchScreenTest` |
| Focused: hint-level copy | 14 passed, 1 new | `--filter='hint level\|SkillDetailTest'` |
| Browser: support-deck + legacy | 32 passed, 0 failed | session-private port 8199 |
| Browser: page-header | green in the 38-case batch | port 8199 |
| Browser: career-scenario-select + probe | 11 passed, 0 failed | port 8209 (KI-167 fix) |
| Browser: preflight + preflight-indep | 15 passed, 0 failed | port 8213 (S3.1 door) |
| Browser: team-race-facility-panel | 2 passed (incl. axe) | port 8199 (S2.4) |
| typecheck | clean | `npm run typecheck` |
| build | clean, 21-41s | `npm run build` |
| pint (collected set) | passed | `vendor/bin/pint --test --format agent <files>` |
| phpstan | `[OK] No errors` repo-wide | `vendor/bin/phpstan analyse --memory-limit=1G` |
| ADR checker | `0 finding(s)` | `python tools/adr-check.py docs/adr` |
| Register auditor | `CONTRADICTIONS: none`, `RESULT: clean`, OPEN = KI-104/106/162 | `python tools/audit_status_register.py KNOWN-ISSUES.md --index-lines 130` |
| Citation census | 808 committed / 348 working tree (baseline 708) | `python tools/doc_census.py` |

### The final suite's four failures, classified by owner

`git status --porcelain -- <the file each test reads>` decides each row. Three of the four arrived
with peer edits made during this session.

| Failure | Cause | Owner |
| --- | --- | --- |
| `DocCitationParityTest` | 808 vs 708 on the committed tree; the working tree reads 347 | this session's S1.3, uncommitted |
| `CareerLegacyDeckStepsTest` "shapes the six nodes" | `AncestryGraph::build()` now emits a `label` key the test's expected array does not carry | **peer**: `app/Services/Legacy/AncestryGraph.php` is ` M`, the test is clean |
| `UiHygieneTest` bare N/A | `SparkSummary.vue:108` renders `N/A` without a `title` | **peer**: `SparkSummary.vue` is ` M` |
| `UnityCupSurfacesTest` "where Team Rank is recorded" | `UnityCupEditor.vue` lost the sentence the test requires and gained `name="team_rank"` | **peer**: `UnityCupEditor.vue` is ` M` |

The last row is the one worth naming precisely, because it inverts a ruling. That test asserts the
editor must NOT have a rank field: "a rank field appearing here would be a second writer for one
fact". The peer's in-flight edit adds exactly that field. It is a design decision mid-flight, not a
defect this session introduced, and it is reported rather than patched. Editing a peer's uncommitted
file to make a test pass is the working-copy-drift rule's exact failure mode.

None of the four is this session's regression. None is claimed as a pass.

## What is NOT done, stated plainly

1. **Nothing is committed.** Every change below is in the working tree. The roadmap's Phase 0 says
   owner names the pathspecs; no pathspec was named, so no commit was made. This is the single
   largest gap between this record and a finished roadmap.
2. **The citation ratchet is not met on the committed tree** (808 vs 708). The working tree reads 347,
   so committing the 156 repoints would clear it.
3. **No full browser baseline.** Both sweeps were invalidated by the shared tree: the first measured
   two bundles and was killed, the second was still running when the third peer rebuild landed.
   `npx playwright test --list` -> 884 tests in 126 files is measured; a green-at-one-bundle run is
   not. Six spec files were run individually and are green (table above).
4. ~~KI-167's root cause is not established.~~ **Closed.** Found and fixed; the cause was
   `:disabled="form.processing"` on the scenario radio (see S2.2). Left here struck through so the
   change of state is visible rather than silently erased.
5. **Owner-gated slices parked, not simulated:** S0.1, S0.3, S0.5, S2.5, S3.4, S3.5, S4.3
   signatures, S4.4, and every Phase 5 row.
6. S0.4 was completed late in the session; it is listed under Phase 0 above rather than here.

## Dated erratum 2026-11-20 (N4.2) — citation ratchet and commit state

The failure rows above naming `DocCitationParityTest` as red (808 vs 708), and the close-out note
calling the ratchet unmet, were true at the 2026-11-18 HEAD and are preserved as the record of that
session. At HEAD `8482c112` the citation repoints are committed: `python tools/doc_census.py` reads
`ratchet: 546 <= 708` and `php artisan test --filter=DocCitationParity` reports 2 passed / 5
assertions. The 708 baseline was not raised. "Nothing is committed" is likewise a snapshot of that
session: the current tree is committed and pushed with divergence `0 0`. This erratum changes no
earlier row; it records what supersedes them.
