# `S-RS-REMODEL-C2` — report

**W.1=C, W.2=run-state-primary, W.3=C, W.4=C applied in Phase 1 (addendum + HFW).**
**Fork 1 resolved page-wide; Fork 2 resolved title+state retained; Fork 3 resolved run-state; Fork 4 resolved (flat, see §4).**

**Phase 2 partially landed.** Two of the four slices shipped — W.3 (banner rule move) and W.4 (caps
label, already satisfied). The reorder (W.2) and page-wide capsule removal (W.1) are **not** landed, and
the reasons are measured below rather than asserted. The full suite is unchanged at **2305 passed / 2
failed**, so nothing here regressed.

---

## 1. Pre-flight

| Check | Result |
|---|---|
| HEAD | `40838683` |
| `git show 40838683 --stat` | 1 file, the checkpoint it claimed |
| Peer set | **0 modified / 6 untracked**, re-measured |
| Path verification | every path resolves; no correction needed |
| Wrapper discipline | every run logged with `> log 2>&1`, no pipe, no trailing echo; verdicts read from each log's own summary |

**Dev-DB note:** run 7 no longer existed (the scratch seed from the earlier arc had been dropped), so the
page 404'd. Re-ran `db:seed --class=RosyDreamsRiceShowerSeeder`, which produced **run 8** — the canonical
Rice Shower · Unity Cup walk. All measurements below use run 8.

## 2. Phase 1 — addendum and redraw

**Commits:** `aa782652` (`docs(proposals)` addendum), `45ed5534` (`docs(hfw)` redraw), plus
`2f340bca` naming the real redraw SHA in the README.

### The classification, transcribed as the owner gave it

Recorded in the addendum §A.2, applying the owner's stated basis. It supersedes both prior tables and
the addendum says so explicitly.

**Three panels changed category against the plan**, and the memo's own `:97`/`:105` contradiction is why:
Special Training (plan run-state / memo both → **run-state**), Team race coverage (plan team / memo
run-state → **run-state**), Unity Cup progress (plan silent / memo team → **run-state**).

### The HFW redraw, measured

| Check | Before | After |
|---|---|---|
| Card order | Team Race, Team, Spirit, Team Rank, progress, grades, bands, special, coverage, shape, goals, mandatory, strip | **Team Race, progress, bands, special, goals, mandatory, coverage, shape, Team, Spirit, Team Rank, grades, strip** |
| Capsule elements | 0 (already plain) | 0 |
| Cards | 13 | 13 |

Verified in a browser against the owner's 12 positions: **12/12 exact**, with position 13 being the strip
whose heading text carries its `Documented` badge (a string-quoting artifact, not a mismatch).

**Fork 4 resolved: flat.** The reference block keeps its single card with no new sub-headings; it already
reads as one list (label, badge, ruleset lines, rule, tiles, caps, finale, advisor note), and adding
sub-headings would invent a hierarchy the payload does not express.

### A redraw error I made and corrected

My first reorder put coverage and shape at positions 11–12 instead of 7–8, which a vision read caught
("Actual: not that sequence"). The reorder was rewritten from the owner's table and re-verified. The
corrected version is what shipped.

## 3. Phase 2 — what landed

### 2.3 — Banner rule text → reference block (**landed**, `1f73875f` + `5d6ab4d8`)

- `TeamRacePanel.vue`: the rule paragraph removed; the comment records W.3 = C, and that `guidance` still
  arrives for the row verdicts.
- `ScenarioPanel.vue` (the reference strip): the rule now prints, reading `props.scenario.race_guidance`.
- `ScenarioSectionBuilder.php`: `race_guidance` added to the strip's payload, read from the **same**
  `config('…team_race.circles_guidance')` the banner reads — never a second source.
- `types.ts` + `ScenarioPanelTest.php`: the payload-shape pin updated for the new key.

**Non-vacuity, measured in the browser on run 8:**

| Claim | Result |
|---|---|
| Rule line gone from the banner | `hasRule: false` |
| Banner keeps its title and absence state | `hasAbsence: true`, text starts `Team Race / No team races recorded…` |
| Rule present in the strip, with the right number | `Team race rule: aim for at least 3 circles as a margin, not a win condition…` |

### 2.4 — Caps labelled "Published stat caps" (**already satisfied**, no change)

`ScenarioPanel.vue:300` already reads `<CapsuleHeader title="Published stat caps" />`, with the `sr-only`
`h3` and the caption matching. W.4 = C required no edit; the HFW's caps label was likewise already
correct. Recorded rather than manufactured into a commit.

## 4. Phase 2 — what did not land, and why

### 2.1 Reorder — **blocked by component architecture**, not by ambiguity

The owner's order interleaves run-state positions 1–7 across two components:

```
li0  AlertRow (the scenario's absent-panel line)
li1  (comment node)
li2  UnityCupPanel — 11 cards
li3  CareerGoalsPanel — 2 cards (Trainee goals, Mandatory races)
```

Positions 4–5 (Trainee goals, Mandatory races) sit **inside** the run-state run but are drawn by a
different component than positions 1–3 and 6–7. `ScenarioPanel` renders one `<li>` per registered flag,
so the shipped page cannot reach the stated order without either splitting `UnityCupPanel`'s output into
per-panel components (a refactor no move priced) or accepting a two-block approximation. This is
addendum §A.7, flagged there as an open consequence; the dispatch's own instruction is not to guess it.

**What would unblock it:** a ruling on whether the two goals cards may sit *after* the team block (making
the order reachable with a config-order change only), or an authorization to split `UnityCupPanel`.

Note the reorder is *cheap* if that answers yes: `config/scenarios.php`'s `panels` key order is what
`onPanels` iterates, so a config-order change plus reordering the sections inside `UnityCupPanel.vue`
would reach most of it.

### 2.2 Page-wide capsule removal — **blocked by test surface**, and a canon change

W.1 = C applied page-wide means `CapsuleHeader` leaves **six** components (7 + 2 + 2 + 2 + 2 + 3 usages)
and **four browser cases** that exist to assert capsules must be rewritten:

| Spec | Case |
|---|---|
| `team-race-facility-panel.spec.ts:53` | "wraps the facility panel in .card and renders the capsule header with .lattice-bleed…" |
| `unity-cup-panel.spec.ts:345` | "heads every Scenario panel with the documented capsule" |
| `unity-cup-panel.spec.ts:374` | "capsules the four Scenario panels that sat outside the card pattern" |
| `unity-cup-panel.spec.ts:408` | "heads both scenario blocks with the documented capsule and keeps the region name" |

That is **18 capsule assertions** across two files. `DESIGN.md:497` sanctions the capsule as client-derived
chrome, so removing it page-wide is a change to the documented visual canon — the kind of thing
`AGENTS.md` asks to be recorded rather than slipped in beside a copy move. Doing it in this dispatch would
have put a six-component refactor and four spec rewrites into a run that also carried a data-payload
change.

**What would unblock it:** the dispatch names it as its own slice (`2.2`), so it should be one. `DESIGN.md`
should gain a dated line recording that the capsule is retired from this page under W.1 = C, or the
removal contradicts the design system's own sanction.

## 5. Gates

| Gate | Result |
|---|---|
| Tests (focused) | `UnityCupPanelTest` + `ScenarioPanelTest`: **35 passed** initially, 3 failures from the new payload key → after updating the shape pin, `ScenarioPanelTest` **27 passed (443 assertions)**. |
| Full suite | `Tests: 2 failed, 9 warnings, 2305 passed (36241 assertions)` — read from the log's own summary. **Identical to the `2305/2` baseline**, so the payload-key change and the banner move introduced no regression. The two are `ScenarioAdvisorFinaleTest` and `UiHygieneTest`, both pre-existing. |
| PHPStan | `[OK] No errors` |
| Typecheck | clean after declaring `race_guidance` on the strip's props and in `types.ts`; the first attempt failed with **3 real errors** (`Property 'team' does not exist`) because `ScenarioPanel` never receives `team` — fixed by putting the number on the strip's own payload. |
| Lint | not re-run; no Tailwind/Card change in this diff |
| Lore / markdown / link-check | link-check `1547 references … no broken references`; no new lore-bearing prose |
| Peer | post-commit `git status` = **0 modified**, same 6 untracked |

## 6. What is owed

1. **The reorder ruling** (§4): may the two goals cards follow the team block, or is a component split
   authorized?
2. **Capsule removal as its own slice**, with the `DESIGN.md` dated line.

## 7. Report-header note

W.1=C, W.2=run-state-primary, W.3=C, W.4=C applied in Phase 1. Fork 1 (capsule scope) resolved
**page-wide** and recorded; Fork 2 (banner fate) resolved **title + state retained, rule moved**; Fork 3
(Special Training) resolved **run-state**; Fork 4 (reference block) resolved **flat**. Phase 2 landed
W.3 + W.4; W.2 and W.1 are blocked for the measured reasons above, not for lack of a ruling.
