# 02 — Stat-ink re-measure

**Evidence captured 01:29–01:36, 2026-11-15, against build manifest mtime 01:26:56** (narrow follow-up: timestamped
evidence per the owner's ruling). Light and dark themes; 1440x900 and 390x844; 26 screenshots.

## The question

The peer added seven `--color-stat-*-ink` tokens *after* the 2026-11-14 pass measured contrast. Did stat text colours
change? Only that.

## Verdict: **declared, built, resolving — and wired to nothing.**

All three tests agree:

| Test | Result |
|---|---|
| Declared in source (`resources/css/app.css`) | **yes** — 21 `-ink` declarations |
| Present in the built CSS (`app-CjBb8k6B.css`) | **yes** — `#166EAF` present in the shipped bundle |
| Resolving at runtime on `:root` | **yes** — `--color-stat-speed-ink: #166eaf`, stamina `#ad3e3b`, power `#a15b10`, guts `#ac3b63`, wit `#0c784f`, pal `#946300`, group `#498219` |
| **Used by any rendered element** | **NO — zero matches across 5 screens × 2 themes** |

I enumerated every leaf element carrying stat-family text on each named surface (186 leaf text nodes on the Cockpit
alone) and compared each computed `color` against the seven resolved token values. **Zero elements matched.** The
tokens reach the stylesheet and the cascade and stop there.

## Per-screen measurements

Light theme. `color` / `background-color` as computed, ratio computed from those two values.

| Screen | Route | Stat text colour | Container bg | Ratio |
|---|---|---|---|---|
| Cockpit (stat rail) | `/training-runs/14/cockpit` | `#000000` | `rgb(251,251,253)` | **20.32** |
| Training (5 options) | `/training-runs/14/training` | `#322A2F` | `rgb(251,251,253)` | **13.48** |
| Result (5 stat rows) | `/training-runs/15/result` | `#625D7D` | `rgb(255,255,255)` | **6.21** |
| Scenario detail (caps table) | `/training-runs/14/scenario` | `#322A2F` | `rgb(251,251,253)` | **13.48** |
| Dashboard (KPI tiles) | `/` | — | — | no stat-family *text* nodes (the tiles are numeric KPIs, mono, not stat ink) |

Dark theme:

| Screen | Stat text colour | Container bg | Ratio |
|---|---|---|---|
| Cockpit | `#FFFFFF` | `rgb(42,42,42)` | **14.35** |
| Training | `#FCFCFC` | `rgb(42,42,42)` | **13.99** |
| Result | `#A0A0A0` | `rgb(51,51,51)` | **4.83** |
| Scenario detail | `#FCFCFC` | `rgb(42,42,42)` | **13.99** |
| Dashboard | — | — | no stat-family text nodes |

## Comparison against the prior pass

Every pair is **identical** to the values the 2026-11-14 pass recorded. No stat-text contrast pair changed, in either
theme, because no stat-text colour is driven by the new tokens.

**Therefore, per the dispatch's own rule, one line:**

> The seven `--color-stat-*-ink` tokens are declared but do not reach rendered stat text; prior contrast claims stand.

**No `02-cross-cutting.md` row needs updating.** No pair fails that previously passed. No finding is filed against the
palette change — it is the peer's work and it is currently inert.

## The one pair worth watching when it *is* wired

Result/dark is already the weakest stat-text pair at **4.83:1** against `#A0A0A0` on `rgb(51,51,51)`. It passes (≥4.5)
but with 0.33 of headroom. If the peer wires `--color-stat-*-ink` into the result rows next, that pair is the one to
re-measure first — the dark ink values are chosen for the tinted panel (`#2A2A2A`), not for the `#333333` result
surface. **Recorded as a watch item, not a finding**: nothing fails today.

## Method note

Colour pairs above are computed from rendered `getComputedStyle` values, not read from the stylesheet — the token
being present in the bundle is exactly what would have made a stylesheet-only check answer the wrong question.

## Excluded from this page, deliberately

No general palette re-measure was run. The prior pass's 246-pair light sweep and 14-pair dark sweep stand; this page
only asks whether the stat family moved, and it did not.

## Per-theme token resolution (measured, both themes)

The seven tokens are not theme-neutral: each theme block redeclares them with its own values. Measured on `:root`:

| Token | Light | Dark |
|---|---|---|
| `--color-stat-speed-ink` | `#166eaf` | `#2f94ee` |
| `--color-stat-stamina-ink` | `#ad3e3b` | `#f05b4f` |
| `--color-stat-power-ink` | `#a15b10` | `#f7820c` |
| `--color-stat-guts-ink` | `#ac3b63` | `#f45e96` |
| `--color-stat-wit-ink` | `#0c784f` | `#1aae77` |
| `--color-stat-pal-ink` | `#946300` | `#f9a31c` |
| `--color-stat-group-ink` | `#498219` | `#26b864` |

**This strengthens the verdict rather than weakening it.** Both theme ramps resolve correctly, and **neither** is
consumed by any rendered element. So the tokens are fully wired into the cascade in both themes and used by zero
elements in either — not "wired in light, missing in dark".

**Correction to a first read on my part.** The `app.css` diff shows 21 `-ink` lines, which momentarily looked like the
dispatch's "seven tokens" was an undercount. It is not: 21 = **7 names × 3 theme blocks**. The dispatch is right.

---

## CORRECTION, appended 01:58–02:05 — the verdict above is now stale

**The "unwired" verdict was true at 01:29–01:36. It is no longer true.** While this package was being written, a peer
shipped the wiring:

| Change | Time | What |
|---|---|---|
| `resources/js/components/StatBadge.vue` | 01:53:42 | **new file**; maps `text-stat-{speed,stamina,power,guts,wit,pal,group}-ink` to the seven stat labels |
| `resources/js/pages/Career/TrainingDetail.vue` | 02:00:47 | **37** `-ink` references, 3 `StatBadge` uses |
| `resources/js/pages/Career/Snapshot/Setup.vue` | 01:5x | 7 `-ink` references, 1 `StatBadge` use |
| `public/build/manifest.json` | 02:02:27 | rebuilt after the edits |

Re-measured at **02:05**, both themes, same four screens:

| Screen | Stat labels | Using an ink token | Verdict |
|---|---|---|---|
| Training `/training-runs/14/training` | 8 | **3** | **NOW WIRED** |
| Cockpit `/training-runs/14/cockpit` | 6 | 0 | still unwired |
| Result `/training-runs/15/result` | 5 | 0 | still unwired |
| Scenario detail `/training-runs/14/scenario` | 8 | 0 | still unwired |

**Contrast on the newly-wired pair: no failure.** The Training screen's ink-coloured labels sit on
`rgb(251,251,253)` in light and `rgb(42,42,42)` in dark; every stat-label pair I measured is ≥ 4.5:1. **Failing pairs
across all four screens, both themes: NONE.** So the dispatch's rule still resolves the same way — no contrast
regression, no finding filed against the palette change.

**What changes:** the one-line verdict. It should now read, for the surfaces actually measured:

> The seven `--color-stat-*-ink` tokens are declared and resolve in both themes. They are **wired to stat labels on
> the Training screen** and **still unwired on Cockpit, Result and Scenario detail**. No contrast pair fails anywhere.

**What does not change:** the prior pass's contrast claims still stand, because no pair regressed. The Result/dark
`#A0A0A0` on `rgb(51,51,51)` = **4.83:1** watch item still stands and is still the weakest stat-text pair — and it is
still *not* ink-driven, which is why it did not move when the Training screen did.

**The honest cost of this correction:** the moment a peer can ship between my measurement and my write-up, a
"measured at T" claim decays in minutes. That is exactly what the owner's timestamped-evidence ruling anticipated, and
it is why this section is dated to the minute rather than presented as current state.
