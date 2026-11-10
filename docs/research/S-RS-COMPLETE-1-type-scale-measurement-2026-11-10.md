# S-RS-COMPLETE-1 type-scale measurement (KI-149, KI-151) — 2026-11-10

**Commands** (session-private harness, `PLAYWRIGHT_FRESH_BUILD=1` where the tree was mid-rebuild):
probes written under `.scratch-uma/complete-1/` (`type-scale-probe.spec.ts`, `zz-ts2`, `zz-ts3`),
run at ports 8239-8245, scratch DBs `.scratch-uma/complete-1/ts*.sqlite`. Logs:
`.scratch-uma/complete-1/ts.log`, `ts2.log`, `ts3.log`.

## `--text-kpi` today

`resources/css/app.css:78`: `--text-kpi: clamp(1.25rem, 2.2vw, 1.75rem)` with `--text-kpi--line-height: 1.2`.
Computed on Dashboard: **20px at 320/768px, 28px at 1280px** — the clamp band behaves; the four-tier
scale it should align to per `DESIGN.md` is unchanged from the 2026-11-02 database-audit pass that
ratified the tiers, and this pass found no screen where the KPI size needs to move.

## Per-surface overflow readings (WCAG 1.4.10 reflow, `scrollWidth - clientWidth`)

| Surface | 320px | 768px | 1280px |
| --- | --- | --- | --- |
| Dashboard | 0 | 0 | 0 |
| Careers | 0 | 0 | 0 |
| Legacy Index/Compare | 0 | 0 | 0 |
| Review, Preferences, Help | 0 | 0 | 0 |
| Veterans, Support Cards, Skills, Database | 0 | 0 | 0 |
| Career Cockpit | 0 | 0 | 0 |
| DeckSelect, Timeline | 0 | 0 | 0 |
| **Race Decision** | **59** | 0 | 0 |
| **Career Cockpit @640px** (KI-160) | **88** (from race-strip spec probe) | — | — |

## Findings

1. **No row-wrap regressions from the `.card > h2` step**: no surface measured a wrap-induced
   overflow at any width. The 15->16px step's earlier fix held.
2. **KI-160 filed** (`KNOWN-ISSUES.md`): Cockpit overflows 88px at 640px (640x512, the
   200-percent-of-1280x1024 width), attributed to `NextDecision.vue`'s Obligations table and its
   grid column; plus the companion reading, Race Decision 59px at 320px. Both are layout debts
   outside this dispatch's slices, recorded rather than fixed here (second-defect stop rule).
3. **KPI tier**: unchanged and correct; the two KI entries close as measured with one layout KI
   filed for the two overflow readings.

## What closure does not cover

The 640px crossing of other career screens (Races at 640 was not measured; only 320/768/1280 were),
and the cockpit at 640 with a completed run rather than an active one.
