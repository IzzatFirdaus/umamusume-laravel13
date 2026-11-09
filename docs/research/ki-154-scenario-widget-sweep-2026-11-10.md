# `S-RS-KI154-1` — the scenario widget sweep, and the Spirit Bursts fix

**Slice:** `S-RS-KI154-1`. Research first (the owner asked for "research codebase", not a one-line
change), then the fix, then the HFW.
**Base:** `6493326a`. **Defect:** KI-154.

**Outcome in one line:** `ScenarioSectionBuilder::scenarioSection()` writes `null` for **six of the
seven** widget keys it labels, not just `spirit_bursts`; the fix fills exactly the one whose value
the payload already carries, through the same accessor the Cockpit strip and the bands panel already
read. Two sibling widgets stay `null` and are **correct** absences.

---

## 1. The problem, restated from measurement

`app/Services/Career/ScenarioSectionBuilder.php:84` is the only writer of `widget_values`:

```php
$values[$widget] = $widget === 'team_rank' ? $team['rank'] : null;
```

`config/scenarios.php:63-71` labels seven keys: `turn`, `energy`, `fans`, `team_rank`,
`spirit_bursts`, `grade_points`, `shop_coins`. Only `team_rank` passes the ternary.

### 1.1 The full call-site sweep (B.1 item 1)

There is **one** assignment site, at `:84`, inside a `foreach ($widgets as $widget)` loop gated on
`isset($widgetLabels[$widget])`. There is no second writer and no per-arm override. So the sweep's
real question is not "which sites are wrong" but "which keys this loop can ever fill".

| Key | Declared by | Data source that exists today | Correct value? |
|---|---|---|---|
| `team_rank` | `unity_cup` (`:400`) | `$team['rank']` ← `TrainingRun::latestTeamRank()` | **Filled correctly** |
| `spirit_bursts` | `unity_cup` (`:400`) | `$team['combined_bursts']` ← recorded tallies | **WRONG — always `null`** (KI-154) |
| `grade_points` | `trackblazer` (`:628`) | none stored | `null` is **correct** |
| `shop_coins` | `trackblazer` (`:628`) | none stored | `null` is **correct** |
| `turn` | baseline (`:173`, `:1042`) | `$turn` in the cockpit's own `values` map | **not this path** — see 1.3 |
| `energy` | baseline | `$latest?->energy` | **not this path** |
| `fans` | baseline | `$latest?->fans` | **not this path** |

So the defect is **one key**, and the five `null`s around it split into two defensible groups and
one excluded group. That distinction is the whole reason this needed a sweep rather than a patch:
a fix written as "make `:84` read the run" would have invented `grade_points` and `shop_coins`.

### 1.2 Data provenance — does a run-record field feed it? (B.1 item 2, and the stop condition)

**Yes.** The stop condition ("if data isn't in the run record, fix has no input") does **not** fire.

- `training_runs.spirit_bursts_count` and `training_runs.extreme_bursts_count` are columns, both
  filled on run 7 as `6` and `5`.
- `TeamPanelBuilder::teamSection()` derives the combined figure at `:585-587`, and it is already
  null-safe in exactly the way this tool's absence rule requires:

  ```php
  $combined = ($run->spirit_bursts_count !== null && $run->extreme_bursts_count !== null)
      ? $run->spirit_bursts_count + $run->extreme_bursts_count
      : null;
  ```

  with the comment "an unrecorded one is not a zero (D-220), so a run that stated one of the two
  gets no combined figure". So the accessor already implements "absent, not zero" — the widget path
  simply never asked it.

- Measured on run 7: `widget_values = {"team_rank":"S","spirit_bursts":null}` while
  `combined_bursts = 11` and `burst_band = "10–12"` ride the same payload.

### 1.3 The second path, and why it is not duplication to fix here (B.1 item 1, continued)

`CockpitController` composes a **separate** `values` map at `:509-524` for a different surface — the
header resource strip — and it already does the right thing:

```php
'bursts' => $team['combined_bursts'] ?? null,
```

with the comment "KI-97: the strip captions this box 'normal plus Extreme', and the panel already
owns that combined figure, so the rail reads the same value instead of recomputing it or printing an
absence over a recorded pair."

That is the precedent this fix follows, and it is the strongest piece of evidence in the sweep: the
same codebase already decided that this exact figure is a *recorded reading*, on a sibling surface,
and wrote it down. `scenarioSection()`'s `widget_values` is the one place that did not get the
message. **KI-154 is therefore a divergence between two payloads built from one run, not a missing
feature.**

Note the key-name difference: the strip's key is `bursts`, the widget's is `spirit_bursts`. They are
not the same map and neither is a copy of the other; the fix must fill the widget's own key.

### 1.4 Bands definition — does 11 land in `10–12`? (B.1 item 3)

Yes, and the label the page prints is the config's own. `config/scenarios.php` `unity_cup`
`burst_bands` declares four rows; the third carries `total` `"10–12"`. `TeamPanelBuilder::burstBandFor()`
(`:773`) returns that `total` string by numeric comparison, and returns `null` for a null combined
figure. `burst_bands_this_run` marks the row. So `11 → "10–12"` is the config's arithmetic, already
computed, and the panel prints `10–12 (this run)`.

The widget labels the same fact. For them to agree, the widget must read `11` — the combined total,
the same number the band row is placed by. It must **not** read `6` (normal only) or `5` (extreme
only), because the strip's own caption for the pair is "normal plus Extreme" (KI-97).

### 1.5 The adjacent bare `N/A` — same class, not the same code path (B.1 item 4)

`UnityCupPanel.vue:324` renders a bare `N/A` inside a `<span title>` rather than through the shared
absence component. Measured this pass:

- **Different file, different language, different mechanism.** KI-154 is a PHP service writing
  `null` where a value exists; `:324` is a Vue template choosing its own absence markup for a value
  that is *genuinely* absent.
- **Not a one-line sibling of the same code path.** No shared writer, no shared accessor. Fixing
  `:84` does not change what `:324` renders, and cannot.
- **Verdict: separate follow-up.** Named in the plan below as a discrete slice, not implemented here.
  The dispatch's own instruction is to state the determination, not to bundle it.

### 1.6 The stop conditions, checked

| Stop condition | Status |
|---|---|
| B.1.2 — no run-record field feeds Spirit Bursts | **Not fired.** Two columns, both populated on run 7; `combined_bursts` derives from them. |
| The fix changes payload shape in a way that breaks `UiHygieneTest` differently | **Not fired.** That test fails on `TeamPanelBuilder.php:620` (`bands_source` carrying a source-file path), a line in `teamSection()` that this slice does not touch. Re-measured before and after; same single failure, same line. |

---

## Implementation plan

### Slice `S-RS-KI154-1a` — source `spirit_bursts` from the run

**File:** `app/Services/Career/ScenarioSectionBuilder.php`, the ternary at `:84` and its comment.

**Change:** replace the single-key ternary with an explicit map from widget key to the payload value
that already exists, keeping every other key `null` and saying so:

```php
$values[$widget] = match ($widget) {
    'team_rank' => $team['rank'],
    'spirit_bursts' => $team['combined_bursts'] ?? null,
    default => null,
};
```

`?? null` because `$team` is typed `array<string, mixed>` and a caller could omit the key; the
absent-key behaviour then stays the honest absence rather than an undefined-index warning.

**Why not more:** `grade_points` and `shop_coins` have no stored reading (1.1), so filling them would
invent data. `turn`/`energy`/`fans` are the baseline three, which `scenarioWidgets()` subtracts out
before this loop ever sees them (`CockpitController:1319-1327`), so they cannot arrive here.

**Acceptance:** a run with both tallies renders `11`; a run with one tally renders the absence, not
`6` or `5`; a run with neither renders the absence. Payload assertion plus a rendered assertion.

### Slice `S-RS-KI154-1b` — the HFW says 11

**File:** `docs/design-research/scenario-detail-hfw-2026-11-09/scenario-detail-hfw.html`.

**Change:** the Spirit Bursts tile prints `11` (combined), with the same `11` the bands card places
in `10–12 (this run)`. Plus the three row-3 staleness sites the earlier span fix created.

### Slice `S-RS-KI154-1c` — close KI-154

`KNOWN-ISSUES.md:4575`, closed with the fix commit and a statement of what closure does **not**
cover (the Vue-side bare `N/A` at `:324`, and the `+30` / `15/15/20/20` content corrections, which
are separate rows).

### Named, not implemented: the `:324` bare `N/A`

Separate slice. It is a rendering-vocabulary defect in a Vue template, with its own acceptance
(absence through the shared component, `title` preserved), and bundling it would put a template
change inside a service fix's commit.

---

## What this sweep found that the dispatch did not ask for

**The same figure is computed twice, in two surfaces, and only one was fixed.** The cockpit strip
reads `combined_bursts` (KI-97, correct); the scenario widget did not (KI-154). Both are built from
the same `$team` payload in the same request. That is a structural risk worth naming for a future
dossier: any new "resource strip" surface should read the team payload rather than re-derive, and
`scenarioSection()`'s widget map is the one place where a `null` default silently outlived a
recorded value.
