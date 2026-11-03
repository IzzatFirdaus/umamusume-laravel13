# Owner decisions pending — Q1, Q2, Q4, Q5, Q6

Date: 2026-11-03 (final closure pass)

Each question below blocks a specific future code slice. Options are listed with a recommendation; the owner picks one per question. Status is `pending` until ruled.

---

## Q1 — Manifest authorization

**Question:** Authorize creation of a manifest that records each audit's ID, filename, date, and status?

**Context:** The audit program has hit ID collisions five times (Save Veteran, KI-149 duplicate, KI-151, KI-152). A manifest would prevent future collisions. Small artifact, high durable value.

**Options:**
- (a) Authorize and create in the same commit
- (b) Defer
- (c) Reject

**Recommendation:** (a) — authorize. The collision cost is real and recurring; the manifest is a small, cheap, high-value artifact.

**Status:** pending owner

---

## Q2 — RENDERED_EVIDENCE re-probe of 30 `FALSE` reports

**Question:** Re-probe the 30 reports claiming `RENDERED_EVIDENCE = FALSE` against the current sandbox to determine which findings could be upgraded to `OBSERVED_RENDERED`?

**Context:** ~30 findings are currently classified `RENDERED_EVIDENCE = FALSE` because they were assessed without rendered verification (Chromium cannot launch under this sandbox). Re-probing would upgrade some to `OBSERVED_RENDERED`.

**Options:**
- (a) Authorize re-probe (each ~5 min; total ~2.5 h)
- (b) Accept current classifications
- (c) Re-probe only the ~15 behavioral findings

**Recommendation:** (c) — the ~15 behavioral findings benefit from rendered verification; the source-only findings don't. Halves the cost while capturing the high-value upgrades.

**Status:** pending owner

---

## Q4 — Phase 5 owner-gated findings (~15)

**Question:** Proceed with Phase 5 owner-gated findings, or leave deferred?

**Context:** Phase 5 contains ~15 owner-gated findings of mixed severity. Some may warrant immediate work; others can wait indefinitely. The group should be triaged per-finding rather than all-or-nothing.

**Options:**
- (a) Proceed — implement all ~15
- (b) Leave deferred
- (c) Pick specific findings to proceed with

**Recommendation:** (c) — triage per-finding. The owner-gated group mixes severity levels; treating them as one block risks either over-investing in low-value findings or under-investing in high-value ones.

**Status:** pending owner

---

## Q5 — `CatalogIndex-F-01` schema change

**Question:** Resolve the diacritic/full-width katakana search mismatch with (a) schema change, (b) copy documentation fallback, or (c) leave open?

**Context:** `CatalogController.php:251-256` has a search mismatch for diacritic and full-width katakana characters. A schema change (normalized column) would fix it; documentation would note the limitation.

**Options:**
- (a) Schema change — ADR + migration + digest update
- (b) Document the limitation in column headers
- (c) Leave open

**Recommendation:** (b) — the schema change is disproportionate for the two affected columns. Document the search limitation and accept.

**Status:** pending owner

---

## Q6 — Fixtures for behavioral verification

**Question:** Authorize running behavioral verification fixtures for Career setup F-04, F-05, F-07 in the current environment?

**Context:** These fixtures exist and running them is verification, not new work. They exercise specific Career setup behaviors that are currently unverified.

**Options:**
- (a) Authorize
- (b) Defer
- (c) Provide fixture data

**Recommendation:** (a) — authorize. These are existing fixtures; running them is verification work, not feature work.

**Status:** pending owner
