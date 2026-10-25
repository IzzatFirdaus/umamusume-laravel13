# Drift Audit — pre-HFW tag `0ef1506c` to `f7438c31`

**Status:** audit complete for S1 and S2; S3a and S4a captured, comparison pending.
**Author:** side-tasks session, 2026-10-26.
**Scope:** the slices landed since the pre-HFW tag. New slices are blocked until this report exists (§0).

## 1. Landed slices under audit

```
f7438c31 fix(dashboard): align layout, header, and KPI grid with HFW
8a8c5ae9 feat(review): add the resolved-today count and drop the queue's dead prop (S10 groundwork)
2c765828 feat(dashboard): complete HFW parity with KPI sublines and domain counts (S2 polish)
15ab1e6c feat(wizard): HFW scenario step and stepper (S4a)
f0e89dbd feat(runs): HFW careers list with inline filters (S3a)
3aabb8e5 fix(ui): sentence-case Recent veterans heading (S2)
78b25e33 feat(shell): grouped rail, crumb topbar and HFW dashboard (S1-S2)
```

`2f0b34b6`, `d116bc4d`, `59340099` are docs-only and out of visual scope.

## 2. Reset decision

**No reset to `0ef1506c`.** Measured: S1 shell is correct on every structural claim
(brand in the rail head, grouped rail, crumb, 2px stripe, mobile bar); S3a's list screen was
verified against `runs-index.html` by screenshot before commit; S2's head, KPI strip and
Needs-you card match after `f7438c31`. Drift is concentrated in **S2's region arrangement and
resume-card composition** (§4 below), which is a bounded fix on two files, not a reason to
discard five correct slices.

## 3. Evidence captured

| Pair | App | HFW |
|---|---|---|
| S2 dashboard 1280 | `.scratch-uma/hfw-current/S2-dashboard/current-1280.png` | `.scratch-uma/hfw-baseline/S2-dashboard/hfw-1280.png` |
| S2 dashboard 390 | `.scratch-uma/hfw-current/S2-dashboard/current-390.png` | `.scratch-uma/hfw-baseline/S2-dashboard/hfw-390.png` |

**Committed-screenshot exception.** `.scratch-uma/` is gitignored by `AGENTS.md` §9 (session-private
scratch), and §1 of this run ranks repository instructions above the run's own protocol. The pairs
are therefore referenced by path here and not force-added; a reviewer can regenerate them with
`.scratch-uma/hfw-s12/audit-dashboard.cjs` (same script, both widths).

DOM fact sheet at 1280 (from the same capture):

```
h1 "Trainer Desk" | eyebrow "Dashboard" | 2px stripe | barBg rgb(93,143,3)
brand in rail true, in bar false | help link false
primary "Open cockpit" bg rgb(93,143,3) | KPI tiles 4, all top=659 (one row)
region headings: Pick up where you left off / Quick actions / Recent veterans / Needs you
console errors 0
```

## 4. Deviations, with disposition

### Fixed before this report (proved by the fact sheet and the current screenshot)

| # | Deviation | Fix |
|---|---|---|
| D1 | eyebrow `OVERVIEW`, `h1` "Dashboard" | `f7438c31`: "Dashboard" / "Trainer Desk" |
| D2 | KPI row 2x2 | `2c765828` + `f7438c31`: `.g-4`, four tiles on one row, sublines beneath |
| D3 | primary action "Resume career" | `f7438c31`: "Open cockpit" |
| D4 | topbar light with an underline | `f7438c31`: filled chrome, `text-on-brand-chrome`, 2px accent stripe |
| D5 | brand in the bar | moved to `.rail-top` in S1 |

### Open — real drift, next work item

| # | Deviation | Evidence | Plan |
|---|---|---|---|
| D6 | **Region arrangement at 1280.** The HFW lays out: resume (wide) + Needs you (rail); KPI row; Recent veterans (wide) + Quick actions (rail). The app flows resume, KPI, Quick actions, Recent veterans, Needs you, because the placement classes are `@7xl` container queries and the 1280 content box (~1088px) is below 80rem, so they never apply. | both `*-1280.png` | Give the grid explicit `xl:` placements that resolve at the standard measure, keeping DOM order as reading order. |
| D7 | **Resume-card composition.** The HFW puts the portrait, a scenario pill, the Japanese name and the position on one row with **"Open cockpit" top-right**; the app stacks a 30px name, tiles, rail, a next-step sentence and the button bottom-left. | both `*-1280.png` | Rebuild the card's internal layout to the HFW row; keep the turn rail (dashboard.html) and the absence note. |
| D8 | **Recent veterans populated state.** HFW is a 3-column table with portraits, scenario and saved date, footer "N veterans · M shown" + All veterans. The app renders a `<ul>` of name links. The captured app state is empty (0 veterans in the audit database), so only the empty branch is proved. | `hfw-1280.png` vs current | Port the table; the empty branch is already correct and stays. |
| D9 | **`? Help` topbar link missing.** | `helpLink: false` | Ships with S11's Help route; a link to a missing route is an inert control (§7). |

### HFW OVERRIDE — intentional, with the ranked source

| # | Element | Why it differs | Source ranked above the HFW |
|---|---|---|---|
| O1 | Topbar fill and primary-button colour are grass `#5D8F03` (dark `#83B503`), not the wireframe's blue `#1A3FCC`. | `shell.css` composes `.topbar{background:var(--brand-chrome)}` and `.btn-primary{background:var(--primary-action)}`; the app uses the same token names with owner-supplied values. | `DESIGN.md` §2.1 "2026-10-25 — definitive synthesized palette" (owner-supplied): brand chrome is the grass green and `--color-primary-action: var(--color-brand-chrome)`. Repainting blue would hardcode a value the token layer rejects (`ui-framework-check` baseline 0). |
| O2 | `+ STATIC WIREFRAME` chip absent. | Wireframe-only artifact. | §7 of this run; plan §3.3 item 5. |
| O3 | Table of contents absent. | Wireframe navigation aid, not product UI. | §7 of this run; plan §4.2 item 5. |
| O4 | Roster card absent. | `index.html` has no Roster card; `dashboard.html`'s figures are carried by the KPI strip. | Plan §4.1 merge ruling and §4.3 ("index.html drops the Roster card"). §0's list names it as missing; the canonical wireframe does not contain it, so adding it would introduce content the HFW does not show. |
| O5 | Verified date lives in the page head, not the Needs-you tile. | A recorded operator ruling pins it page-level, and `dashboard.spec.ts` asserts both directions. | 2026-10-21 operator ruling (cited in the spec); open conflict with `index.html`'s tile for the owner to settle. |

## 5. Test-rewrite ledger (§7.1)

No test was weakened or skipped in the audited slices. Rewrites made, intent preserved:

- `dashboard.spec.ts`: region names → the HFW's card names; `Save and continue` → `Continue to trainee`; `Resume career` → `Open cockpit`; `h1` now "Trainer Desk"; the stat-cell height check moved from the rail card's `dl` to the KPI tiles that now own the cells; the compact height budget re-derived as a 1,100–1,350 band because required regions now render.
- `runs.spec.ts`: page actions scoped to `[role="banner"]` (PageHeader's real placement), `Import a run` label, `h1` "Training runs".
- `shell.spec.ts` / `preferences.spec.ts`: the shell's new landmark name and mobile-bar label.

## 6. Verdict

S1 **done**. S2 **not done**: D6–D8 are visible drift and must land before S4b. S3a **done** (screenshot-verified
at commit). S4a **captured, comparison not yet performed** — blocked behind S2 per §0, not a claim of parity.
S10 groundwork is prop-only and carries no visual surface.
