# S-RS-COMPLETE-1 lint re-measure (KI-145) — 2026-11-10

**Command:** `npm run lint:ui` at `ad3f8f40` (working tree; see peer-set note), logged to
`.scratch-uma/complete-1/lint.log`.

**Lore gate, same pass:** docs mode 584 hits / 75 exempt (was 583/75 at last recorded figure; delta
+1). The delta is this dispatch's own `tests/browser/career-section-nav.spec.ts:133`
("run-scoped tail because Inertia v2 renders the `Link` href absolute") — class 2, an ordinary-word
substring, the same word and class as the already-ruled `support-deck.spec.ts:351` line it mirrors.
Ruled, not edited. `composer lore-code` 319 hits same-day baseline; this dispatch added no code hit
(`php tools/lore.php code` greps name no file this pass touched).

**Measured finding count: 202 arbitrary-value findings. Baseline: 209 (`tools/ui-lint-baseline.json`).**
Exit code 0 — the gate is green and the count sits **7 under** the baseline.

## Classification

All 202 findings are `[arbitrary-value]` (literal `px`/`em`/`rem`/unitless values bypassing the token
layer). No `[card-in-card]` findings. No new arbitrary values were introduced by this dispatch's own
commits: the two files the dispatch touched that the lint names — none. Measured with:

```text
grep -cE 'bypasses the token layer' .scratch-uma/complete-1/lint.log
  202
```

Top concentration is unchanged from the last recorded pass: `resources/js/pages/Veterans/Index.vue`
(`:273`, `:283`, `:520`, each multiple values) — same debt the baseline's `reason` field already
carries as deferred.

## Drift vs the recorded figure

- `tools/ui-lint-baseline.json` `count: 209`, dated 2026-11-02.
- The drift 209 -> 202 is **downward** (7 values retired by the database UI/UX audit's type-scale
  pass and later cleanups, not by this dispatch). Per the dispatch's Slice E.2 rule the count is at
  or under baseline, so **KI-145 closes as a stale-figure**: the feared over-baseline state does not
  exist at HEAD.
- The baseline file is NOT edited: lowering the recorded count is an owner-only action per §5
  escalation 4, and nothing in this dispatch requires it. The next over-baseline failure threshold
  remains the 210th finding.

## What closure does not cover

A future slice that adds an arbitrary value to a file this dispatch did not audit still fails at
210; and the 202 existing findings remain deferred debt with the named intent in the baseline's
`reason` field, unchanged by this measurement.
