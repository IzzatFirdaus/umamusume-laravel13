# 02 — Cross-cutting index

## Held

- **Focus continuity across page changes.** BO-001 was the only class that reproduced. The wizard already had a local solution; the persistent shell lacked the equivalent. One global listener now owns the policy, with explicit guards for same-page visits and validation errors.
- **Measured accessibility baselines held.** Contrast (20 readings), reflow (160 readings across crawl and contrast passes), single-`h1` (140 visits), console cleanliness (140 visits), keyboard focus visibility (24 stops, each with a solid 2 px outline) and empty-state status messages (31 in the crawl) all passed where measured.

## Cleared classes

- Target size: the apparent violations were inline table links covered by the WCAG 2.5.8 spacing exception, radios inside large labels, and controls inside closed disclosures. No change made.
- Accessible names: every apparent unnamed control was inside a closed `<details>` and returned `checkVisibility() === false`. No change made.
- Table naming: unlabelled tables carried preceding headings and passed existing axe scans. No change made.
- Navigation feedback: status/alert roles were present on the surfaces measured; the empty search printed `Nothing matches “zzzznotarealskill”. Clear returns the full catalog.`; Preferences persisted after reload with `All changes saved`.

## Not assessable this pass

Visual craft (density, hierarchy feel, whether a panel reads as raised), real-user mental models, and keyboard traversal of every one of the 58 screens. The vision instrument returned 429 and no human review was performed, so no visual judgement is recorded.
