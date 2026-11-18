# C5 — audit §6 unmeasured register, measured 2026-11-18

```json
{
 "date": "2026-11-18",
 "method": "CDP-driven Chromium at 127.0.0.1:8000 (fresh build); per-route text-contrast probe (WCAG 4.5:1 / 3:1 large), interactive-element 24x24 target sweep (2.5.8), document overflowX at 1440x900",
 "routes_probed": 12,
 "results": {
  "contrast_fails": "0 across 12 routes (400 text nodes sampled per route, both dark themes' computed colours)",
  "document_overflow": "0 on all 12",
  "small_targets": {
   "exempt": "Skip-to-content link (1x1, visually hidden until focus \u2014 standard skip-link pattern)",
   "radio_inputs_16x13_16x16": "/preferences + /career/setup/*: native checkbox/radio inputs; each sits inside a >=44px label per the earlier remeasure (remeasure.cjs) \u2014 2.5.8 exception",
   "database_race_links_16px_tall": "/database/races table links are 16px tall but >=46px wide with 8px+ spacing \u2014 fails the house 44px floor in isolation; spacing exception applies for 24px minimum, house-floor debt stands (recorded, not fixed this pass)",
   "help_inline_links": "same class as above"
  },
  "reduced_motion": "source rule verified at app.css @media (prefers-reduced-motion: reduce); accessibility.spec.ts emulated case green in the 29/30 run",
  "keyboard_focus": "navigation-focus.spec.ts + wizard h1 focus green in the 29/30 run",
  "error_views_404": "rendered correctly with one h1 in the 54-route sweep (this pass and 2026-11-18 first pass)",
  "drag_alternatives": "no drag handlers in resources/js (grep 0) \u2014 2.5.7 satisfied by absence",
  "loading_error_frames": "6 specs touch loading; forced-error frames per family remain unbuilt \u2014 RETAINED AS UNMEASURED"
 },
 "verdict": "Six of the audit \u00a76 rows close measured; loading/error frames per family and the house 44px floor on inline text links remain open debt, recorded not waived."
}
```
