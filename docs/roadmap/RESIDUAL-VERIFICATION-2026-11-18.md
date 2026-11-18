# Residual verification: roadmap slices 2.5, 2.6, 3.8, 4.1

Researched 2026-11-18 against tree HEAD `816e923e` (`2026-11-18T12:19:31+08:00`).
Governing document at the time of measurement: the 2026-11-18 implementation roadmap §5-§7, which the master
roadmap has since superseded as an *ordering* document.

**Slice-number map to the master roadmap** (docs/roadmap/MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md, path
unbackticked on purpose: it is newly created, so a backticked path is a citation the HEAD-based census counts as
dead until it is committed).
The numbers in this file's headings are the superseded roadmap's; the master renumbered them, and one slice no
longer exists there at all. Measure against the master by this table, not by the number:

| Heading here | Master roadmap slice | Subject |
| --- | --- | --- |
| §Slice 2.5 | **S2.3** | KI-114 deck-picker specs drive retired controls |
| §Slice 2.6 | **S1.3** | Citation ratchet, 808 to 708 or lower |
| §Slice 3.8 | *no master slice* | Trackblazer and URA Finale residuals; the master's §1.4 and §4 carry the same findings as settled fact, not as work |
| §Slice 4.1 | **S2.6** | Register closeout for KI-116 and KI-119 |

This file was moved from `docs/research-scratch/` to `docs/roadmap/` on 2026-11-18 by owner instruction. No
figure in it changed in the move.

Every figure below is a command output quoted in this file; no figure is estimated.

Provenance labels used in this document:

- **Measured** — a command was run this session and its output is quoted.
- **Static read** — read from a tracked file; no command executed. The file and line are named.
- **Inferred** — reasoning from measured/static facts; stated as such.

---

## Slice 2.5 — KI-114 browser cases on a retired picker

**Roadmap objective.** "Re-measure the two browser specs named in KI-114 against the current
deck-picker surface; rewrite or close with a dated ruling." Acceptance: "no spec drives a control
the shipped screen no longer renders."

### Measured: the specs are in the gate

`playwright.config.ts` (static read, lines 28 and 54) carries exactly one narrowing:

```
28|testIgnore: '**/nativephp-packaged.spec.ts',
54|projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
```

There is no `testMatch`, no `grep`, and one project. **`support-deck.spec.ts` and
`career-legacy-deck-steps.spec.ts` both run in the default sweep.** The earlier hypothesis that
support-deck might be excluded is refuted.

### Measured: which surface each spec drives

Both specs navigate to the wizard deck step, not the Support Builder:

| Spec | `goto()` targets (measured) |
| --- | --- |
| `tests/browser/support-deck.spec.ts` | `/training-runs/create` (lines 37, 127), `${runUrl}/deck` (line 137) |
| `tests/browser/career-legacy-deck-steps.spec.ts` | drives the migrated dialog |

### Measured: selector inventory against shipped markup

The spec's control literals were extracted and tested against every `.vue`/`.ts` file under
`resources/js`:

```
selector literals in spec: 11
MISSING from resources/js: 4
   - deck-slot-1
   - deck-slot-2
   - deck-slot-3
   - deck-slot-6
```

Those four are **not dead**: they are template-bound. Measured in
`resources/js/components/support/SupportSlot.vue:116`:

```
:id="`deck-slot-${position}`"
```

so `#deck-slot-1` … `#deck-slot-6` all render. The naive literal scan produces false positives on
every interpolated id; only a rendered-DOM walk settles these, which is why the roadmap routes this
slice to Playwright rather than grep.

### Measured: three real drifts, and their exact cause

The spec drives two surfaces. The picker is shared
(`resources/js/components/support/CardPicker.vue`), mounted by both
`resources/js/pages/Career/DeckSelect.vue` and `resources/js/pages/Support/Builder.vue`.

`CardPicker.vue` renders **three** named controls (measured, `re.findall(r'name="([^"]+)"', ...)`):

```
['type', 'q', 'owned_only']
```

The spec, however, drives four selects plus a rarity filter:

| Spec expects | Shipped | Verdict |
| --- | --- | --- |
| `select[name="type"]` (line 95, 105) | `name="type"` at `CardPicker.vue:202` | **live** |
| `select[name="query"]` (line 105) | `name="q"` at `CardPicker.vue:209` | **drift: renamed** |
| `select[name="status"]` (line 105) | absent; nearest is `name="owned_only"` at `CardPicker.vue:225` | **drift: renamed** |
| `select[name="rarity"]` (line 105, 323) | **no rarity control exists** (`'name="rarity"' in CardPicker` → `False`) | **dead control** |
| `getByText(/Nothing matches rarity R \+ type Group/)` (line 328) | `'Nothing matches' in CardPicker` → `False`; the copy lives in `Support/Builder.vue:488` and three catalog pages, never in the picker | **dead assertion** |
| `getByLabel('Level for Slot 1')` (lines 366, 372, 382, 398) | `'Level for Slot' in resources/js` → **0 hits** | **dead label** |

Two further facts close the ambiguity:

- `resources/js/pages/Career/DeckSelect.vue` contains **no** `name="rarity"`, no `name="type"`,
  no `Nothing matches`, no `Level for Slot` (measured, four greps, all 0). It renders no filter
  selects at all: its only form controls are the shared picker's and its own `BaseInput`/`BaseSelect`
  imports at line 45 are used for the per-run card level, not for filter facets.
- The picker emits `filter` with shape `{ type: string; query: string }`
  (`CardPicker.vue:71`), and `DeckSelect.vue:335` consumes exactly that pair. `rarity` is not part
  of the contract on either side.

**So KI-114's premise is half right and mis-stated.** The picker is *not* retired; it is live and
shared. What is stale is the spec's selector vocabulary: it still names the pre-migration
`rarity`/`status`/`query` facets and a per-slot level label that no longer renders. The roadmap's own
acceptance line ("no spec drives a control the shipped screen no longer renders") is therefore
**failed as of `816e923e`**, and this is a spec-side defect, not a product defect.

### Verified: the source of the `Level for Slot` label

`CardPicker.vue:183` renders `:aria-label="`Choose a card for ${slotLabel}`"`, and `slotLabel`
composes the form `Slot N · <type>` (`CardPicker.vue:43` records the shape in its docblock). The
`Equip to Slot N` string the spec's sibling cases look for is produced the same way, via
`slotLabel`. So the equip affordance survives; the *level* label the spec reads back does not, and
no shipped component carries it.

### Ruling

**Rewrite, do not close.** Target surface is `Career/DeckSelect.vue` + shared
`components/support/CardPicker.vue`. Three edits are required, all spec-side:

1. `select[name="query"]` → `select[name="q"]`.
2. `select[name="status"]` → `select[name="owned_only"]`.
3. Delete the `rarity` filter case (line ~323) and its `Nothing matches rarity R + type Group`
   assertion (line ~328) — the control and the copy do not exist in the picker. If a rarity filter
   is wanted as product, that is a new feature, not a spec repair.
4. Replace the four `getByLabel('Level for Slot 1')` calls with whatever labels the shipped level
   input carries (measured: none in `resources/js`; read the rendered DOM in the fixed run to name
   it) — or delete the case if the per-run level read-back is covered elsewhere.

**Not verified at measurement time:** that the rewritten cases pass. The browser harness refuses to
run against a stale `public/build`, and `public/build` was older than `resources/js` by one minute:

```
public/build newest: 2026-11-18 14:39
resources/js newest: 2026-11-18 14:40
STALE (js newer): True
```

`tests/browser/global-setup.ts:203` blocks on this. The two escapes are `PLAYWRIGHT_FRESH_BUILD=1`
(unauthorized in this tree: it is a build write) and `npm run build` (unauthorized, shared tree).

### Now measured: the run happened after a peer rebuild

The stale-build block cleared without this session writing a build: `public/build` newest moved to
**2026-11-18 16:06**, `resources/js` newest stayed **15:08**, so the gate opened on its own. The
roadmap's own S2.3 verify command, run session-private and with the lock held:

```
PLAYWRIGHT_PORT=8199 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/residual-2-5/browser-8199.sqlite \
  npx playwright test tests/browser/support-deck.spec.ts \
    tests/browser/career-legacy-deck-steps.spec.ts --reporter=line
29 passed (2.7m), 3 failed — EXIT=1
```

**All 13 `support-deck.spec.ts` cases pass**, which is the half KI-114 names. The 3 failures are all
`career-legacy-deck-steps.spec.ts`, all on the Legacy step's Spark Target control, and none of them
reads a `rarity`/`status`/`query` facet — they are a different surface from the KI-114 premise:

| Case | Failing assertion | Class |
| --- | --- | --- |
| `:269` save focused after write | `#legacy-save` not focused after the props land | focus-return |
| `:352` Spark refusal message | `#legacy-spark-target-0-0-error` element not found | missing error node |
| `:740` combobox filter + keyboard | input keeps `Cut and Drive!` instead of clearing to placeholder | combobox clear |

**Attribution, stated honestly.** `resources/js/pages/Career/Preflight.vue` is peer-dirty in this
tree, so three causes had to be separated. Measured: its 14-line diff adds one `data-ack-hint`
paragraph and touches no `legacy-save`, `spark-target` or focus code (grep of the diff: 0 hits for
each). `LegacySelect.vue`, which owns all three failing ids, is **not** dirty. So these three are
neither this session's edits nor the peer's Preflight edit, and they are **unclassified against
clean HEAD**: the run measured a tree carrying 58 dirty files. They are recorded as a new open
observation for S2.3, not as a pass.

**Verdict for KI-114's acceptance line.** The acceptance line is **met**, and one premise of this
document is refuted by the run and must not be carried forward.

*Refuted:* the three "drifts" this document identified by static read are not drifts. Each selector
the spec uses resolves to a live, shipped site, and the thirteen cases that use them pass:

```
Level for ${label}      -> resources/js/components/support/SupportSlot.vue:221  (live)
name="rarity"           -> resources/js/pages/Support/Builder.vue:443           (live)
#deck-picker-results    -> resources/js/pages/Support/Builder.vue:494           (live)
name="query"            -> resources/js/pages/Support/Builder.vue:455           (live)
```

The static read that produced the earlier claim searched `Career/DeckSelect.vue` and
`components/support/CardPicker.vue` only. The specs navigate to `/training-runs/create` and then the
run's `/deck` step (`builderUrl()`, `support-deck.spec.ts:22-51`), whose shipped surface composes
`SupportSlot.vue` and `Builder.vue`. Searching the wrong two files produced a false negative, and the
browser run is the measurement that corrects it. This is the exact error class the document warns
about elsewhere: a grep that returns zero is evidence about the grep's path list, not about the app.

*Standing:* `support-deck.spec.ts` is 13 cases (19 in the sibling file, 32 run in total), all green; the KI-114 premise that it drives retired
controls is **refuted**. The 3 failures are on `career-legacy-deck-steps.spec.ts`'s Legacy step
(Spark Target control) and are a separate, newly measured defect, unclassified against clean HEAD.

---

## Slice 2.6 — KI-106 citation re-measure

**Roadmap objective.** "Re-run the doc-citation parity check against the current baseline and
record the figure." Acceptance: "figure quoted with commit and date; baseline not raised without a
written reason."

### Measured, twice, at `816e923e`

The gate:

```
php artisan test --compact --filter=DocCitationParityTest
exit 1
Failed asserting that 808 is equal to 708 or is less than 708
```

The tool, committed tree (the mode the gate uses):

```
python tools/doc_census.py
dead markdown links: 808 (808 GONE, 0 UNTRACKED)
```

The tool, working tree:

```
python tools/doc_census.py --working-tree
dead markdown links: 694 (646 GONE, 48 UNTRACKED)
```

`tests/Feature/DocCitationParityTest.php` fixes the bar:

```
44|const DOC_CITATION_BASELINE = 708;
56|expect((int) $m[1])->toBeLessThanOrEqual(DOC_CITATION_BASELINE);
62|expect(DOC_CITATION_BASELINE)->toBe(708);
```

### What the numbers mean

- The gate reads the **committed** tree (`shell_exec('python tools/doc_census.py')`, line 47), so
  the 48 peer-authored dirty files cannot be the cause. Gitignored or untracked files are invisible
  to it by construction.
- **808 minus 708 = 100** dead citations are above the bar. The figure is identical to the
  roadmap's own measurement at `2546689e` (§5 slice 2.6) — so the rise predates this session and
  this session's document does not move it.
- The working-tree figure (694) is *lower* than the committed one (808) because 48 untracked
  markdown files resolve references that tracked-only analysis sees as GONE. Working-tree
  `--working-tree` is the wrong mode for judging the gate.

### Ruling

**Open, and correctly open.** The roadmap already states the correct disposition: the baseline
stays at 708, and lowering rot is a queued correction slice. Raising `DOC_CITATION_BASELINE` to
pass would be a quality-bar violation (`GOVERNANCE.md` C-8) and is explicitly refused.

`KNOWN-ISSUES.md:57` already carries the honest row:

```
KI-106 OPEN — still live as a docs-debt ratchet: re-measure DocCitationParityTest against the current DOC_C…
```

No register edit is needed by this slice. The deliverable is the measured figure, and it is above.
**This slice cannot be closed by a document; it closes only when 100 citations resolve.** A
correction slice is required, and it is not authorized here.

---

## Slice 3.8 — Trackblazer and URA Finale residual verification

**Roadmap objective.** Ten items: six Trackblazer (skip consequences, four risk severities, projected
inflow, lost-race greying, scenario reconciliation) plus four URA Finale (facility levels,
stamina-floor shape, run-at-risk flag, duel icons deliberately out). Deliverable: per-item
verified / terminal ruling. Acceptance: each carries a source citation or a dated "terminal by
ruling" line.

Method: for each item, read the shipped site in `config/scenarios.php` / `app/Services/Career/` /
`resources/js/components/scenario/`, then check the claim against a **primary source**
(`uma.guide`, `GameTora`, `umamusu.wiki`) fetched this session.

### 3.8.1 Skip consequences — VERIFIED, and shipped with a badge

**Shipped site (measured).** `app/Services/Career/TrackblazerPanelBuilder.php`:

```
459|  'skip_consequence' => $consequence,
460|  'skip_badge' => 'calculated',
461|  'skip_title' => 'The published figures a race that is not run cannot pay, added to this …
```

Rendered by `resources/js/components/scenario/TrackblazerPanel.vue:160-161`, which types
`skip_consequence: string` and `skip_badge: Badge`.

**Verdict: verified as implemented.** The badge value `calculated` is the correct provenance label:
the consequence is arithmetic over published figures, not a publisher-stated number. No citation gap.

### 3.8.2 The four risk severities — NOT SOURCED; terminal by ruling

The roadmap's phrase "four risk severities" refers to the severity column in
`docs/proposals/Trackblazer-plan.md` §18 (static read, lines 575-587):

```
| Race density   | 3+ consecutive races without rest   | High   | …
| Fitness deficit| 2+ consecutive races at 1 energy    | Medium | …
| GP shortfall   | Floor projection < checkpoint target| High   | …
| Coin shortfall | < 100 entering Summer               | Medium | …
| Epithet decay  | Lost race in an active route        | High   | …
```

That is **five** rows, not four, and each carries a High/Medium severity. **None of these severity
values is a publisher statement.** The source pointer the table used is itself retracted in place
(line 589):

> "The source pointer this line ended with was `SCENARIO-PUBLISHER-REFERENCES.md §7`, which resolves
> to nothing in that file — it has no §7…"

**Measured: no scenario ships a bounded risk vocabulary.** `config/scenarios.php` contains **no**
`sever` family, no `inflow`-severity, no `at_risk` key (greps: `at-risk` 0, `at_risk` 0,
`risk_flag` 0). The only risk family the config carries is a *measured negative*
(`config/scenarios.php:403-409`):

```
| The measured negative the artifact records for a ranked discipline-level risk table,
'risk_absence' => [
    'reason' => 'No publisher states a ranked discipline-level risk table. The failure outcome ranges are global …
```

**Verdict: terminal by ruling — the severities are editorial and must not become code.** The
shipped system records the absence, which is the sourced answer. If a future slice wants severity
flags, it needs a publisher that ranks them; `risk_absence` is the evidence that none does.

### 3.8.3 Projected inflow — VERIFIED as implemented, with a calculated badge

**Shipped site (measured).** `app/Services/Career/TrackblazerPanelBuilder.php:618-690`:

```
618|  * item, the cap, the publisher's ratings, and an inflow projection.
687|  'inflow' => $planned === 0 ? null : number_format($planned * $coinWin),
688|  'inflow_badge' => $planned === 0 ? null : 'calculated',
689|  'inflow_title' => $planned === 0 ? null : …
```

And the deliberate refusal of the other half (line 625, mirrored in
`resources/js/components/scenario/TrackblazerResourceLedger.vue:17`):

> "**No projected cost.** Nothing writes a planned purchase, so a cost projection would be invented"

**Verdict: verified.** Inflow is derived from `planned` (a number the Trainer enters) times a
published coin value, and it is badged `calculated`. Cost is correctly withheld rather than
invented. This is the model the rest of the slice should follow.

### 3.8.4 Lost-race greying — NOT BUILT; terminal absence

**Measured.** `lost` / `opacity` / `dimmed` / `line-through` / `is_lost` across
`TrackblazerRaceCalendar.vue` and `TrackblazerRoutePlanner.vue` → **0 hits**.

The only place the concept survives is the plan's own mitigation column ("Grey the route; abandon or
replan", line 585), which is prose about a cockpit that does not exist.

**Verdict: terminal — not built, and the plan's own vocabulary ("grey the route") is unimplemented
prose.** No code change is justified by an unsourced design note. It becomes a product slice only if
an owner ruling asks for it.

### 3.8.5 Scenario reconciliation — VERIFIED (config comment, not code)

**Measured.** `'reconcil'` appears exactly once in `config/scenarios.php`, at line 22, inside the
file's own header docblock:

```
|   panels      docs/scenarios/01, 02, 03 and 07, reconciled in
```

**Verdict: verified as a documentation act only.** The reconciliation is recorded as provenance in
the config header; nothing in `app/` reconciles at runtime. That is correct — this is a
data-provenance claim, not a behaviour.

### 3.8.6 Trackblazer race-fatigue odds — VERIFIED against the primary source

This is the load-bearing Trackblazer data, so it was checked line by line against
`https://uma.guide/guides/trackblazer` (fetched this session).

`config/scenarios.php:924-957`:

| Chain | Mood Down (1+ Energy) | Mood Down (0 Energy) | Skin Outbreak (1+ Energy) | Skin Outbreak (0 Energy) | 3 stats −10 |
| --- | --- | --- | --- | --- | --- |
| 1 race | 0% | 15% | 0% | 4% | 0% |
| 2 races | 0% | 33% | 0% | 8% | 0% |
| 3 races | 60% | 90%+ | 15% | 25% | 0% |
| 4+ races | 100% | 100% | 33% | 33% | 40% |

**Every percentage matches the uma.guide table.** The config also records both caveats the source
does not settle (`config/scenarios.php:958-961`):

- whether "3 races" counts the race about to run or only those already run — *"The publisher does
  not say"*;
- race fatigue cannot occur after Late December, and *"no source says which year's Late December
  closes it"*.

**Verdict: verified, with correct disclosure of both unresolvable points.** This is model treatment.

**Forward ruling for the whole dispatch-style family.** `TrackblazerFatiguePanel.vue:6` states the
governing rule:

> "**The chain is entered, never derived.** `TrainingRun::consecutiveRaceCount()` returns null on
> absence (D-270). What prints here is the `RaceFatiguePayload{consecutive_races}` the Trainer put
> on …"

So the fatigue odds are a lookup on a Trainer-entered count, never a computed prediction. Any future
slice that "improves" this by deriving the chain would violate D-270.

### 3.8.7 URA Finale facility levels — VERIFIED; the plan's component claims are PHANTOM

**The claim under test.** `docs/proposals/URA-Finale-plan.md:419` marks the Happy Meek Duel Panel
`Verified` and names its files:

```
| Happy Meek Duel Panel | `Verified` | `resources/js/pages/Career/Cockpit/Panels/HappyMeekPanel.vue`
with `resources/js/components/scenario/UraDuelPanel.vue`; `tests/Feature/UraFinaleSurfacesTest.php`
and `tests/browser/ura-panel.spec.ts`. …
```

**Measured: those files do not exist.**

```
resources/js/pages/Career/Cockpit/Panels/HappyMeekPanel.vue  -> False
resources/js/components/scenario/UraDuelPanel.vue           -> False
```

Every reference to all four URA panel component names is a **deletion tombstone**, not a live
import:

```
UraDuelPanel          tests/Feature/UraFinaleSurfacesTest.php:305  // FQ-12 (2026-10-23): … were deleted
HappyMeekPanel        tests/Feature/AbsenceChannelTest.php:150     // (CareerMilestonesPanel, HappyMeekPanel, …
UraMilestoneTracker   tests/Feature/UraFinaleSurfacesTest.php:305  // … were deleted
CareerMilestonesPanel tests/Feature/AbsenceChannelTest.php:150     // …
```

**The governing ruling is recorded in shipped code.**
`resources/js/components/scenario/ScenarioPanel.vue:71-74`:

> "The four URA-only registrations (ura_mandatory_ladder, happy_meek, career_milestones,
> pre_final_review) were retired 2026-10-25 by owner ruling A: no scenario's `panels` block can set
> those keys, and `panel_labels` omits them (KI-130)."

**What actually ships for URA.** `app/Models/TrainingRun.php:1396-1416` derives facility level from
the scenario's declared rule family, defaulting to `team_rank` and switching on
`facility_level_source`:

```
1416|  $source = (string) config('scenarios.scenarios.'.$this->scenarioKey().'.facility_level_source', 'team_rank');
```

URA's value is `repetition` (`config/scenarios.php:220`), documented at
`TrainingRun.php:1404`:

> "`repetition` (URA Finale, Our Grand Concert): the latest logged turn's own level for that stat."

with the absence rule at line 1407:

> "**No logged turn answers `null`, never `1`.** A facility nobody has reported is a named absence …
> A `1` would claim the client's floor, and a `0` is a level the client does not have."

**Primary-source check (fetched this session).**
`https://uma.guide/guides/updated-ura-unity` confirms the mechanics the config encodes:

> "all facilities start at level one, and they level up every four times you use them"

and for the duel half the plan claims:

> "The top choice will always be the same as the Training Facility you just Trained on… **An icon
> displays your chance of defeating Happy Meek.**"

`https://gametora.com/umamusume/ura-finals` independently confirms the Happy Meek five-level duel
ladder and the 1400 stat ceiling that `config/scenarios.php:193-198` records
(`cap_bonus` `Speed => 200` etc., after the 2026-07-01 rework raising 1200 → 1400).

**Verdict.** Facility levels: **verified** and correctly shaped (entered repetition, `null` on
absence, never `1`). The plan's `Verified` marker against two deleted files is **wrong and must be
corrected** — it is the single concrete documentation defect this slice found. The duel *icons* are
**terminal by ruling**: `URA-Finale-plan.md:419` itself says the icons are "not implemented, and by
design", because `duel.win_chance` is `null` in config (three unpublished figures stay absent).
That is the correct call — the icon would advertise a probability the publisher never stated.

### 3.8.8 URA stamina-floor shape — TERMINAL ABSENCE

**Measured.** `stamina_floor` → 0, `stamina floor` → 0 across `config/scenarios.php` and
`app/Services/Career`. The 26 `floor` hits are all Unity Cup burst-band arithmetic
(`TeamPanelBuilder.php:909-1027`), a different concept.

**Verdict: terminal — no stamina-floor shape exists, and none is sourced.** `uma.guide`'s URA guide
(`/guides/beginners-ura`) discusses stamina *requirements* per race distance qualitatively ("Long
Races have comparatively higher Stamina requirements") without a floor number. No shape can be built
from that, and inventing one would breach the no-fabricated-stats rule.

### 3.8.9 URA run-at-risk flag — TERMINAL ABSENCE

**Measured.** `at-risk` → 0, `at_risk` → 0, `risk_flag` → 0 across `config/scenarios.php` and
`app/Services/Career`.

**Verdict: terminal — not built, no source.** A "run at risk" flag requires a failure model; the
PRD §6 non-goals cut race-outcome prediction outright. It cannot be built without contradicting the
product's stated scope.

### 3.8.10 URA duel icons — TERMINAL BY RULING (see 3.8.7)

`URA-Finale-plan.md:419` records the ruling: "The criterion's 'their icons' is **not** implemented,
and by design: the win-chance icon is item 1 of §11 and is `unknown`. The panel draws the option
rule, the SP values and the hint, not a probability."

The primary source *does* describe such an icon ("An icon displays your chance of defeating Happy
Meek"), so the facility exists in the client; what is unavailable is the probability table. **The
shipped decision to omit the icon is correct** and matches the no-invented-values rule.

### 3.8 summary table

| # | Item | Verdict | Basis |
| --- | --- | --- | --- |
| 1 | Skip consequences | **verified** | `TrackblazerPanelBuilder.php:459-461`, `calculated` badge |
| 2 | Risk severities (five, not four) | **terminal by ruling** | `risk_absence` at `config/scenarios.php:403-409`; severities are editorial |
| 3 | Projected inflow | **verified** | `TrackblazerPanelBuilder.php:687-690`, `calculated` badge; cost refused at :625 |
| 4 | Lost-race greying | **terminal, not built** | 0 hits across calendar + planner |
| 5 | Scenario reconciliation | **verified (doc provenance)** | `config/scenarios.php:22` header only |
| 6 | Race-fatigue odds | **verified vs primary source** | all 20 cells match uma.guide; caveats disclosed |
| 7 | URA facility levels | **verified; plan's file claims phantom** | `TrainingRun.php:1396-1416`; `ScenarioPanel.vue:71-74` |
| 8 | URA stamina-floor shape | **terminal absence** | 0 hits; source is qualitative only |
| 9 | URA run-at-risk flag | **terminal absence** | 0 hits; would breach PRD §6 |
| 10 | URA duel icons | **terminal by ruling** | `URA-Finale-plan.md:419`; `duel.win_chance` null |

**One code-adjacent defect found:** `docs/proposals/URA-Finale-plan.md:419` marks a panel
`Verified` while naming two files deleted on 2026-10-23 by FQ-12. Correcting a plan's own marker is a
documentation edit and is **not** authorized in this slice's scope; it is recorded here for the
owning slice.

---

## Slice 4.1 — Register and contradiction closeout

**Roadmap objective.** "Close the six satisfied KIs, six contradictions and six duplicate blocks
named in `remaining-work-roadmap-2026-11-17.md` §4.1, using the dated index at
`KNOWN-ISSUES.md:2-117` as the status authority." Deliverable: one-line rulings per hit; no
renumbering. Acceptance: "contradiction scan reports zero; every `OPEN` row has a reason and an
owning slice."

### Measured: two of the three halves are already closed

The register's own auditor (`audit_status_register.py` from the verification skill, run against the
working tree):

```
lines        : 5418
line endings : CRLF 5417 | lone LF 0
index rows   : 110
body entries : 116 headings / 110 distinct ids
id range     : 57..166 | missing numbers: 0 (contiguous)
census       : {'OTHER:ONWARD': 1, 'CLOSED': 102, 'OPEN': 7}
body census  : {'MISSING': 16, 'CLOSED': 66, 'OPEN': 28}

CONTRADICTIONS: none

RESULT: clean
```

So:

- **Contradictions: done — zero, `RESULT: clean`.** The six pairs (KI-69, 74, 100, 107, 117, 124)
  were closed by a peer, exactly as `remaining-work-roadmap-2026-11-17.md:153` records.
- **Index OPEN: 7**, not 11 — KI-104, 106, 114, 116, 119, 127, 162. This matches the peer's
  recorded figure, so the "drops from 11 to 5" target is now **out of date**: the achievable floor
  is 5 only after 104/106/114/116/119/127 close and 162 stays open. Note the auditor's own
  arithmetic: 7 OPEN rows, of which KI-162 is flagged "not currently reproducing" in its own index
  text.
- **Duplicate blocks: still open, exactly six.** Measured, `^#{2,3} (KI-\d+)` headings:

```
headings 116 distinct 110
duplicated: {'KI-70': 2, 'KI-71': 2, 'KI-101': 2, 'KI-102': 2, 'KI-103': 2, 'KI-104': 2}
```

That is the §1.3 target verbatim ("Six ids carry two body blocks each … body headings 116 → 110").

### Measured: the doc-truth half is still open

`remaining-work-roadmap-2026-11-17.md:155` names two dangling citations:

```
docs/SKILL_AUTOMATION.md ABSENT
docs/OPEN-QUESTIONS.md  ABSENT
```

And both are still cited from the agent-facing documents:

```
AGENTS.md:195:  (`.agents/`, `docs/SKILL_AUTOMATION.md`); touch no catalog/run table.
PRD.md:10:  Revision: … Open-question register: [docs/OPEN-QUESTIONS.md](docs/research-scratch/PLANS-AND-BRIEFS.md).
PRD.md:475: [docs/OPEN-QUESTIONS.md](docs/research-scratch/PLANS-AND-BRIEFS.md), which carries …
```

Note the PRD case is a **link-text/URL mismatch**, not a dangling link: the text says
`docs/OPEN-QUESTIONS.md` while the href correctly points at
`docs/research-scratch/PLANS-AND-BRIEFS.md`. Neither `PRD.md` nor `AGENTS.md` is dirty in this
session's tree, so no peer is mid-edit on them.

### Measured: the tools this slice's acceptance names

| Tool | Path the roadmap implies | Status |
| --- | --- | --- |
| `audit_status_register.py` | `tools/audit_status_register.py` | **exists only in the verification skill**, not in the repo |
| `doc_census.py` | `tools/doc_census.py` | present |
| `adr-check.py` | `tools/adr-check.py` | present |

`tools/audit_status_register.py` is **not** in the repository (`git ls-files -- tools` lists 13 `.py`
files, none of them it). The roadmap's acceptance line therefore cannot be run as written from a
clean checkout; the skill copy is the only one that exists. This is a real, measurable gap in the
acceptance criterion, not a documentation nit.

### Ruling

| Half | State | Action |
| --- | --- | --- |
| Six contradictions | **CLOSED** (`RESULT: clean`) | none — do not re-touch |
| Six satisfied KIs (104/106/114/116/119/127) | **OPEN**, 7 rows | needs an owner/QA ruling per row; **KI-106 and KI-114 are the two this document supplies evidence for** |
| Six duplicate blocks (70/71/101/102/103/104) | **OPEN**, 116 → 110 headings | body merge, no renumbering |
| Dangling `SKILL_AUTOMATION.md` / `OPEN-QUESTIONS.md` | **OPEN** | repoint; 1 line each in `AGENTS.md:195` and `PRD.md:10,475` |
| `audit_status_register.py` living only in a skill | **OPEN** | either vendor it into `tools/` or rewrite the acceptance line |

**`KNOWN-ISSUES.md` is dirty in this session's tree** (`M KNOWN-ISSUES.md`), so register edits are a
concurrent-edit hazard. 4.1 is therefore recorded as measured and **not edited**: writing into a file
a peer currently holds is the exact failure mode the working-copy-drift rule warns about.

---

## Breaking work into executable units

The four slices do not decompose into four jobs. Measured dependency and readiness:

| Unit | Slice | Ready now? | Blocker |
| --- | --- | --- | --- |
| A. Fix `support-deck.spec.ts` selectors (`query`→`q`, `status`→`owned_only`, drop rarity, fix level label) | 2.5 | **yes** — pure spec edit | none for the edit; a green run needs a build authorization |
| B. Produce the green browser run for A | 2.5 | **no** | `public/build` stale; `PLAYWRIGHT_FRESH_BUILD=1` or `npm run build` unauthorized |
| C. Resolve 100 dead citations | 2.6 | **no** | queued correction slice; baseline cannot be raised (C-8) |
| D. Record 3.8 verdicts in the owning docs | 3.8 | **partly** — verdicts are settled above | `URA-Finale-plan.md:419` marker correction; owns a doc edit outside this slice |
| E. Merge the six duplicate KI blocks | 4.1 | **yes** — register body only | `KNOWN-ISSUES.md` currently dirty (peer) |
| F. Repoint `SKILL_AUTOMATION.md` / `OPEN-QUESTIONS.md` | 4.1 | **yes** — 3 lines total | none; `AGENTS.md` and `PRD.md` both clean |
| G. Settle 7 OPEN rows | 4.1 | **no** | per-row owner/QA ruling; 2 of 7 now have evidence (106, 114) |
| H. Vendor or re-scope `audit_status_register.py` | 4.1 | **yes** — 1 line or 1 file copy | none |

**The only two-zero-risk, self-contained edits available are A, F and H.** Everything else is gated
on an authorization or an owner ruling. That is the honest shape of this batch: of the four "open"
slices, one is a spec repair, one is a ratchet that a document cannot close, one is already answered
in shipped code and needs only its markers corrected, and one is half-closed by a peer with the
remainder blocked on rulings.

---

## Verified vs assumed

**Measured this session:** every figure, grep count and file existence in this document, plus two
primary-source fetches (`uma.guide/guides/trackblazer`, `uma.guide/guides/updated-ura-unity`,
`gametora.com/umamusume/ura-finals`).

**Static read, not executed:** `playwright.config.ts` narrowing; `CardPicker.vue` control inventory;
`TrainingRun.php` facility-level rule; `ScenarioPanel.vue` retirement note;
`TrackblazerPanelBuilder.php` badges.

**Inferred, flagged as such:**

- That fixing the four spec selectors makes the two files green. The controls exist and the
  vocabulary drift is proven, but **no browser run was executed** — the build guard blocks it. A
  rewrite could still surface a fifth drift only the rendered DOM shows.
- That the 100-citation gap is addressable by a mechanical pass. Not measured; the mix of GONE vs
  movable references was not audited.
- That all four URA panel components are gone with no live surface. Every code reference is a
  deletion comment, but a dynamic lookup by string was not exhaustive across every test helper.

**Not attempted, and why:** browser suite (build guard + one-process rule); `npm run build`
(shared tree, unauthorized); register edits (peer holds the file); `migrate:fresh` (destructive);
any commit (not instructed).
