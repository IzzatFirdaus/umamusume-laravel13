# GP-01 Measurement verification

Method: WCAG 2.x relative luminance `L = 0.2126·R + 0.7152·G + 0.0722·B`, channels linearised
`c/12.92` if `c <= 0.03928` else `((c+0.055)/1.055)^2.4`; contrast `= (L_hi+0.05)/(L_lo+0.05)`.
Computed in Python from file-read hex; nothing quoted from the trigger. State A = `ce90b0fa`
(the tree the trigger measured; token lines `app.css:136-144,152,165,169`, `shell.css:10-14`).
State B = HEAD `d8238c87` (`app.css:136-141,152,168,173`; only `page`/`panel` moved).

## Tokens: HFW vs app @State A (trigger's diff table verified at source)

| Token | shell.css | app.css | Same |
|---|---|---|---|
| page | `#F4F4FF` :10 | `#F4F5F9` :136 | no |
| panel | `#FBFBFD` :10 | `#FFFFFF` :137 | no |
| raised | `#FFFFFF` :10 | `#FFFFFF` :138 | yes |
| sunken | `#EDEDF1` :10 | `#EDEDF1` :139 | yes |
| idle | `#D2D2DB` :11 | `#E2E4F0` :140 | no |
| disabled | `#D0D1D0` :11 | `#D1D3E0` :141 | no |
| ink | `#6A5641` :13 | `#322A2F` :142 | no |
| ink-strong | `#482720` :13 | `#000000` :143 | no |
| ink-muted | `#6A6A75` :13 | `#625D7D` :144 | no |
| ink-faint | `#988F87` :13 | `#8C8A9A` :152 | no |
| rule | `#84828C` :14 | `#8C8A9A` :165 | close, not equal |
| rule-soft | `#D9D8E2` :14 | `#D7D8E0` :169 | close, not equal |

Matches the trigger's table row for row (only `raised`/`sunken` byte-identical).

## The 16 reported ratios, recomputed (State A)

| Measurement | Computed | Trigger | Verdict |
|---|---|---|---|
| HFW `panel` luminance | 0.9660 | 0.9660 | MATCH |
| app `panel` luminance | 1.0000 | 1.0000 | MATCH |
| `page→panel` HFW | 1.057 | 1.057 | MATCH |
| `page→panel` app | 1.089 | 1.089 | MATCH |
| `panel→sunken` HFW | 1.130 | 1.130 | MATCH |
| `panel→sunken` app | 1.168 | 1.168 | MATCH |
| `ink on panel` HFW | 6.73 | 6.73 | MATCH |
| `ink on panel` app | 13.93 | 13.93 | MATCH |
| `ink on page` HFW | 6.37 | 6.37 | MATCH |
| `ink on page` app | 12.78 | 12.78 | MATCH |
| `ink-muted on panel` HFW | 5.17 | 5.17 | MATCH |
| `ink-muted on panel` app | 6.21 | 6.21 | MATCH |
| `rule on page` HFW | 3.46 | 3.46 | MATCH |
| `rule on page` app | 3.10 | 3.10 | MATCH |
| `rule-soft on page` HFW | 1.29 | 1.29 | MATCH |
| `rule-soft on page` app | 1.30 | 1.30 | MATCH |

**16/16 MATCH.** Two sample computations (all rows computed identically):
HFW ink `#6A5641` on panel `#FBFBFD`: channels (0.4157,0.3373,0.2549) → linear (0.1444,0.0937,0.0533)
→ L=0.1008; (0.9660+0.05)/(0.1008+0.05) = **6.73:1**.
App ink `#322A2F` on `#FFFFFF`: linear (0.0319,0.0231,0.0289) → L=0.0254; 1.05/0.0754 = **13.93:1**.

## Post-change predictions, verified against State B (HEAD)

| Prediction | Trigger | Recomputed | Verdict |
|---|---|---|---|
| `panel #FBFBFD → sunken` | ~1.13 | **1.130:1** | MATCH; clears the 1.05 light floor (`DESIGN.md:190`, test `DesignTokensTest.php:903`) |
| `rule #8C8A9A` on `page #F4F4FF` | needs re-measure | **3.093:1**, margin **+0.093** over 3:1 (WCAG 2.2 SC 1.4.11) | measured, not assumed |
| new `page→panel` | — | 1.057:1 | equals HFW's step exactly |
| new `page→sunken` | — | 1.069:1 | same ramp order as HFW |

## Brand-hairline open item (closed)

`app.css:162` (2026-10-24 comment) says the hairline `#D1D3E0` measured "1.41:1 on the new page",
surface unnamed in the trigger. Recomputed against every candidate:

| Surface | Ratio | The one? |
|---|---|---|
| `page #F8F9FD` (2026-10-24 brand palette, `5ba966ed`) | **1.415:1** | **yes** |
| `page #F4F5F9` (later, `9fa70510`) | 1.367:1 | no |
| `page #F4F4FF` (current, `bc102252`) | 1.363:1 | no |
| `panel/raised #FFFFFF` | 1.489:1 | no |
| `sunken #EDEDF1` | 1.275:1 | no |

The figure was true when written; the page moved twice afterwards (`5ba966ed` → `9fa70510` →
`bc102252`). `5ba966ed`'s commit body quotes the same 1.41:1. No discrepancy to report.

## Method note for Phase 2 carry-over

Neither stylesheet reassigns surface tokens in a media query (`shell.css` media blocks :182, :405,
:406, :913, :959, :978, :1044, :1085, :1126 are layout/motion only; `app.css` declares
`--color-page`/`--color-panel` only in the three theme blocks :136/:137, :484/:485, :618/:619).
Desktop grey verdicts carry to the 390x844 captures subject to the dashboard mobile spot check (02).
