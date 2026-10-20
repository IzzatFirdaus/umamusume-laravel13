# Phase 6.4 — Dashboard verify, closed by measurement

**Date:** 2026-10-21
**Baseline:** `0ec490ff` (ahead 1 of `origin/master` `440b2ef8`)
**Outcome:** closed by measurement. **No code change.**

## Per-claim verdict

| # | Claim | Verdict | Evidence |
|---|---|---|---|
| 1 | Active career card with a primary Resume | already-satisfied | `Dashboard.vue:216-227` (`v-if="props.activeCareer && !isCardHidden('active_career')"`, `data-card="active_career"`); `DashboardController:62`; case `dashboard.spec.ts:142` |
| 2 | Quick actions as outline buttons | already-satisfied | `Dashboard.vue:90` (`quickActions`), `:156` (`quick_actions` span), `DashboardController:69` |
| 3 | Recent Veterans from the fixed `veteranRow()` | already-satisfied | `DashboardController:68` (`recentVeterans` → `recentVeterans($veterans)` → `veteranRow()`), refactored to delegate at `27f68fd8` |
| 4 | "Recent builds" hidden until data exists | **dropped by ruling** | no data prop exists; the dashboard names the absence instead — `Dashboard.vue:10` ("has no source"), `:512` ("Recent builds: none yet.") |
| 5 | Multi-career notice when >1 active | already-satisfied | `Dashboard.vue:87` (`activeCareerCount`), `DashboardController:66` (count-only), cases `dashboard.spec.ts:190` and `:219` (the `>1` threshold from `274f47e4`) |
| 6 | `ResourceStrip` retirement | **dropped by ruling** | `ResourceStrip.vue` exists but is not referenced from `Dashboard.vue` — it never rendered there |

Four claims landed under `274f47e4` (and claim 3's helper under `27f68fd8`); two were dropped by ruling. Nothing to build.

## The walk

```
port 8631 · .scratch-umma/6-4-verify/browser.sqlite · --workers=1 --timeout=45000
dashboard.spec.ts · veterans.spec.ts · veteran-compare.spec.ts · career-cockpit.spec.ts
58 cases · 56 passed · 2 failed
```

**`dashboard.spec.ts` ran FIRST** — the walk-order rule from `D3`. It held for six of its seven
previously-contaminated cases: the empty-runs cases are green when nothing has created a run yet.

### The two reds, both attributed, neither dispatch-caused

1. **`veterans.spec.ts:74`** — peer-caused. The aside carries 11 destinations; the case asserts 10.
   Standing since the peer's `feat(database/reference)`; not the arc's to fix.

2. **`dashboard.spec.ts:370`** ("measures the three shell widths, the bento shape and the card voids
   at a 1920 viewport") — **pre-existing intra-file state leakage**, and it passes in isolation
   (49.9 s). Cause: `:300` ("honours the dashboard layout preference") and `:338` ("renders the wide
   shell when Wide is selected") both write the persisted layout preference through `/preferences`
   → **Save preferences**, and neither restores the default. `:370` therefore measures against
   whatever the preceding case left behind.

## What this adds to the walk-order rule

The rule had four dimensions: membership, file-path composition, text composition, order (reader
specs before writer specs). `:370` shows a fifth: **state restoration inside a spec file**. A case
that mutates persisted state — a preference row, a status, a saved veteran — is a *writer* for every
later case in its own file, not just for later files. Running `dashboard.spec.ts` first does not help
when the writer and the reader live in the same file.

`dashboard.spec.ts:370` belongs in the Phase 8.7 walk-hygiene cluster alongside the file's
zero-run isolation problem: both are "the spec assumes state it did not establish".

## Not touched

`Dashboard.vue`, `DashboardController.php`, `ResourceStrip.vue`, `VeteranRow.php`,
`dashboard.spec.ts` — no file was modified. The tree at `0ec490ff` was already correct.

## 6.3 close — re-scoped to zero remaining (2026-10-21)

The plan's "Legacy Lab merge into tabbed Veterans" was measured against the tree and refuted:

- Legacy Lab is a three-route, four-method surface (`index` / `builder(TrainingRun $run)` /
  `compare` / `update`) — a run-scoped ancestry editor, not a veteran-library page.
- `legacy.spec.ts:128` pins the Legacy Lab nav item's existence; removing it would red an existing
  passing test.
- Two different Compare surfaces: run-ancestry alignment (`Legacy/Compare.vue`) vs the veteran
  career matrix (`Veterans/Compare.vue`). Same word, different feature.

Per-change disposition:

- (1) Tab shell: dropped — domain-object mismatch (run vs veteran)
- (2) Nav removal + redirects: dropped — `legacy.spec.ts:128` asserts the nav item exists
- (3) Library DataTable: dropped — sweep showed 0 consumers of `ui/DataTable.vue`
- (4) Newest/Oldest → Select: dropped — `veterans.spec.ts` bans combobox (count 0)
- (5) Compare fixes: dropped — `7.14b` ruled the current shapes deliberate
- (6) `veteranRow()` delegation: **landed** (`27f68fd8`)

**One of six landed, five dropped, zero remaining. Phase 6 closes.**

Companion reconciliation: `veterans.spec.ts:74` narrowed from `count(10)` to enumerated
destinations. The 11th link was a hardcoded "Data sources" footer link (`AppLayout.vue:339`,
`href="/database/sources"`) inside the aside, next to the ten-item nav loop — not a nav
destination, but a live desktop link, so the whole-aside count was brittle to any addition.
