# Known issues

**Pointer for the historical register, and still the live one. Its full text moved on 2026-10-03 to
`docs/research-scratch/AUDIT-AND-VERIFICATION.md`, section "KNOWN-ISSUES.md (defect register)":
2,655 lines covering KI-01 to KI-37, each entry naming the command or file that proves it and the
commit that closed it.**

## This file is still the append target

`AGENTS.md` and every slice record write into `KNOWN-ISSUES.md`, so this file was a poor
consolidation candidate and only its history moved. Keep appending new entries here, in the format
the embedded snapshot uses:

1. `### KI-nn — title`, with `nn` continuing from the highest existing number, never reusing one.
2. A Status line saying what is open, closed, or accepted-as-known.
3. The command or file that proves it, not just the symptom.
4. On closure, the commit hash, and what the closure explicitly does not cover.

When a register pass changes the counts, update the `**Status (...)**` line at the top of this file.
The embedded snapshot is a dated record and is not edited to follow; it states 35 filed, 24 closed,
11 open as of 2026-09-30, and this file is where the current number lives. Two numbers disagreeing
across a dated snapshot and a live register is the intended arrangement, not a defect to reconcile.

Tracked files cite `KNOWN-ISSUES.md` by name, including `PRD.md`,
`tests/Feature/DocSchemaDriftTest.php` and `tests/Feature/CharacterCardParserTest.php`. Do not
delete it, and do not renumber existing entries: the renumbering history (KI-16 left as a hole,
KI-30 and KI-31 renumbered from KI-16 and KI-17) is what made the old numbers cheap to stop using.

### KI-57 The screenshot manifest's Race result row names a frame that does not exist, because its date ellipsis is wrong - FILED 2026-10-03 (Phase A closure pass, screenshot pipeline), OPEN

`docs/research-scratch/DESIGN-CORPUS.md` section `## SCREENSHOT-MANIFEST.md`, the Race result/live
row of the Screen-type coverage table (line 4207 at filing time), lists its representatives as
"`2026-07-18 000919`, `235511`". The ellipsis on the second frame inherits `2026-07-18` from the
first, but the frame on disk is `Screenshot 2026-07-17 235511.png`. Taken literally the manifest
names `Screenshot 2026-07-18 235511.png`, which is in no directory, in no cluster, and in no
signature record.

Reproducer: `ls docs/game-screenshots/ | grep 235511` returns exactly one name,
`Screenshot 2026-07-17 235511.png`. `Screenshot 2026-07-18 235511.png` appears nowhere in
`docs/game-screenshots/`, `docs/design-research/_scratch/clusters.json` or
`docs/design-research/_scratch/signatures.json`.

Remedy: append a dated erratum beneath the manifest row stating the second representative is
`Screenshot 2026-07-17 235511.png`, and do not edit the historical line in place. First found by
the Phase A inventory pass on 2026-10-03, which initially propagated the phantom frame into
`research-scratch/screenshots-inventory.csv` and corrected it there; the CSV row now cites the
real frame.

### KI-58 `tools/gate.py`'s hex allowlist lost its document leg silently when the design master was folded, and the guard for that condition cannot fire - FILED 2026-10-03 (Phase A closure pass, from the priors pass finding), OPEN

`tools/gate.py:94` reads `docs/design-research/DESIGN.md` as leg one of `load_token_hexes()`. The
re-baseline folded that document into `docs/research-scratch/DESIGN-CORPUS.md` and deleted it, and
`tools/gate.py:95-96` handles a missing document by returning an empty set with no report. The
guard at `tools/gate.py:120-121` was written to catch exactly this and cannot fire, because leg
two, `docs/design-research/_scratch/tokens.json`, alone yields 176 ramp values, far over the
40-value threshold the guard checks. The enforced allowlist (`tools/gate.py:229-232`) is therefore
`tokens.json`'s ramps plus the three hardcoded values at `tools/gate.py:116-118`, unchanged since
commit `e0e043c`.

Reproducer: three hexes that `resources/css/app.css` declares as current token values are in
neither `tokens.json` nor the hardcoded three, so an artifact using any of them fails G-4:
`--color-up: #B45309` (`app.css:168`), `--color-down: #0667B0` (`app.css:169`), `--color-ink-muted:
#6E6459` (`app.css:48`). Their prior values `#FF9A2C`, `#0088E0` and `#7A7067` remain recoverable at
`git show e0e043c:docs/design-research/DESIGN.md`.

Fix options, in priority order: repoint `tools/gate.py:94` at `resources/css/app.css` and read the
`@theme static` block plus the `html[data-theme='dark']` block directly, so the allowlist tracks
the shipped theme; or regenerate `tokens.json` with `tokens.py` against the current `app.css` so
the anchor is refreshed. Not fixed here; this entry files the finding. First recorded in
`docs/research-scratch/DESIGN-CORPUS.md` section "scratch-priors.md" §2.1 (2026-10-03, priors pass;
the priors file moved there on 2026-10-03 when root `research-scratch/` was emptied).

### KI-59 The catalog detail Identity slot emits a portrait URL the artwork mirror does not hold, so the frame renders broken instead of absent - FILED 2026-10-05 (Task B1 closure pass, from the Playwright run), OPEN

`resources/js/pages/Catalog/Show.vue:128` passes `:url="trainee.artworkURL"` to `ArtworkSlot`
unconditionally, and `resources/js/components/ArtworkSlot.vue:80-85` renders an `<img>` whenever
`url` is truthy, with no check that the mirrored file exists. The prop comes from the row, not from
the disk, so on a host where the manual `uma:fetch-art` (`ADR-0021`) has not run the page still
prints a `size-16` portrait frame pointing at a path that is not there. `DESIGN.md` §4.7 calls that
exact case out: an absent file renders no frame and no placeholder. The mirror is gitignored
(`storage/app/private/.gitignore:1`), so the rendered outcome is host state rather than repository
state, which is what makes this a defect and not a fixture difference.

Reproducer: `npx playwright test tests/browser/catalog-detail.spec.ts`. On this host
`storage/app/private/artwork/card_portrait` does not exist, and
`tests/browser/catalog-detail.spec.ts:146` fails `toHaveCount(0)` with `Received: 1`, resolving to
one element on 33 consecutive polls. The sibling case at `:113` passes because it guards on the
rendered state before asserting (`if (await frame.isVisible())`), and it confirms the URL is
emitted in the documented shape, `/artwork/card_portrait/\d+$`.

`tests/browser/catalog-detail.spec.ts:140-152` is correct as written and should not be weakened: it
encodes §4.7's absence state. The defect is that the absence state is unreachable whenever the prop
is populated. Whether the prop should be gated on file existence, or the slot should treat an
unsatisfiable URL as absent, is a design call for the Architect; `AGENTS.md` §8 still describes the
`ADR-0021` display half as unbuilt, which no longer matches
`resources/js/components/ArtworkSlot.vue`, so that line is stale in the same way. Not fixed here;
this entry files the finding.
