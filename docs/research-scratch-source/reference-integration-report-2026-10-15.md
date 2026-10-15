# Integration Report: research-scratch findings into `UMAMUSUME_REFERENCE.md`

**Pass date:** 2026-10-15
**Target file:** `docs/UMAMUSUME_REFERENCE.md` (before: 2,647 lines; after: 2,710 lines)
**Directories surveyed:** `docs/research-scratch-source/`, `docs/research-scratch/`, `docs/research-scratch/screenshot-notes/`, `docs/research-scratch/screenshot-notes-extras/`, `docs/research-scratch/screenshot-registry/`
**Scope:** additive integration with dated corrections; no rewrite, no new top-level section, no `[JP]` value promoted.

---

## 1. Determination

| Outcome | Count |
| --- | --- |
| Facts integrated into an existing section | 22 |
| Facts found to be **duplicates** of content already in the reference | 5 clusters (see §6) |
| Facts **flagged for the owner** rather than integrated | 3 (see §5) |
| New conflict-log rows added (§7) | 4 (rows 53-56) |
| Existing conflict-log rows deleted | 0 |
| New top-level sections created | 0 |
| New subsections created under an existing section | 1 (§1.6.11) |
| Files changed by this pass | 1 (`docs/UMAMUSUME_REFERENCE.md`) |
| Files added by this pass | 1 (this report) |

---

## 2. Section-by-section changes

| Section | What was added |
| --- | --- |
| Preamble (lines 3, 7-8, 11, 17, 23) | New dated pass line ("Research-scratch integration pass 2026-10-15"); a "what the 2026-10-15 pass did, in one line" summary; the §1 contents row extended to "through 1.6.11"; the §7 status row re-derived from 46 to **56 rows**; the "What this document is" conflict-row count re-derived to 56. |
| §1.1.4 Skill point acquisition | Per-grade post-race payout table (0-RB base, both career families), the Race Bonus breakpoint ladder (10/20/34/50/60%), the 34% row confirmed against the live Game8 page, and a correction note tying the Artisan/Master Cleat Hammer figures to a second source. |
| §1.1.5 Training failure risk | Client-observed per-discipline failure rates (Guts 5%, Speed 3%, Power 2%, Stamina 1%; Wit blank), cited by frame and evidence id, marked a single-turn observation. |
| §1.1.6 Motivation | The mood-pill colour ramp (non-monotone: deep pink / burnt orange / gold / blue / purple), cited by frame and evidence id. |
| §1.2.2 Running strategies | The "style is a trainee attribute, not a race property" finding, and the **denominator clarification** for the per-style gated-skill counts (whole-export 107/220/166/115 vs `[Global]` 26/35/33/27). |
| §1.2.6 Grade tiers and entry gates | Re-derivation of the fan table over the 404 URA races; the career-year distribution (Y1 56, Y2 162, Y3 186); grade 700 read as "Listed"; the maiden-lock-is-run-level refinement; the 24-turn / 12-month × Early-Late career grid. |
| §1.3.4 Caps and soft caps | Client-observed URA Finale ceilings (Speed 1327 / Stamina 1322 / Power 1404 / Guts 1306 / Wit 1802), marked as a per-run observation distinct from the 1400 published cap. |
| §1.3.5 Where a number comes from | Growth-rate printing behaviour: an explicit `+0%` for zero entries, and the row as a per-run ceiling; frames named. |
| §1.6.11 (new) Client UI-state grammars | Training-HUD stacked bubbles, derived support rail, two-channel bond, late-run failure rendering; skill-selection states; event-choice branch tables with no probabilities; goal ladder; Team Showdown and team-race results; spark-resolution prose; Photo Album quota; aptitude as a first-class block. All cited by note line and frame. |
| §2.2.3 Trackblazer | Grade Points per-grade base table (G1 100 … Pre-OP 20), placement modifiers (2nd 60% … 6th+ 10%), and the Shop Coins placement table (100/60/30/0), with the grade-independent contrast recorded. |
| §3.3 Roster build | `race === 'uma'` filter (105 of 163); `va_en` is the romanisation of `va_ja`, not a dub cast; the seven profile keys; `rl` as an all-row nested C-4 trap with a death date; `characters_extended` as a real-animal registry, not aliases. |
| §7 Conflict log | Rows 53-56 added. |
| §8.2 Confidence per section | The §7 row re-derived (56 rows); the disposition line re-derived from "47 rows / 15-19-7" to "**56 rows / 20 High, 17 Medium, 4 Low** + 15 compound". |
| §8.3 Weakest source coverage | New item 5: no Trackblazer or Grand Concert frames in the corpus; missing result/support/shop/event detail modals and friend-support screens. |
| §8.5 Verification evidence | Corpus provenance (1,159 frames, 2026-07-14 to 2026-07-18, 750 clusters, 132 verified / 1,023 not_reviewed / 4 partial) and the two unresolvable-frame gaps plus the KI-57 typo. |

---

## 3. New conflict-log rows

| Row | From | Conflict in one line |
| --- | --- | --- |
| 53 | 1.2.2 | The per-style gated-skill counts (107/220/166/115) are whole-export figures; the reference never stated the denominator, so a `[Global]` view could print them wrongly. Measured `[Global]`-only: 26/35/33/27. Both kept. |
| 54 | 3.3 | `va_en` as "English dub cast" vs. the source's own rows showing one performer in three scripts. Resolved in favour of the source: `va_en` is the romanisation. |
| 55 | 1.2.6 | Grade codes 200/300/700 unlabeled in the export-alone reading vs. labeled by two out-of-export publishers. Row-level labels adopted; the export's nulls kept as a record. |
| 56 | 1.2.6 | The maiden lock as a per-row schema column vs. a run-level rule the source states once. Resolved: the rule is run-level; the column is a faithfulness defect for the owner. |

---

## 4. Contradictions logged, not resolved

- **Row 55** (tier labels): resolution is a sourcing standard the owner owns — "either accept out-of-export sources and restore the seeder's labels, or rule that only the export counts". The document adopts the parser's row-level labels for its own display while keeping the seeder's nulls, but the underlying standards question is not settled by this pass.
- **Row 56** (maiden lock): the schema fix is a column decision the owner owns. The reference records the correct model; it does not change the schema.
- **Grade 700's label.** The report labels 700 as "Listed"; uma.guide conflates "OP/L (Open/Listed)"; the export alone carries no label. The reference records all three and leaves 700 `❌ UNVERIFIED`, matching its existing stance.

---

## 5. Flagged for the owner (not integrated)

1. **Skill activation DSL.** `docs/research-scratch/SKILLS-MECHANICS.md` carries a full per-skill condition-group DSL (623 `[Global]` skills, verbatim `condition_groups` text, effect codes, token counts) extracted from GameTora. The reference's §1.2.2 covers style gating conceptually and §1.4.4 covers hint output; neither has room for a 623-row activation table. Placing it would be a **structural decision** (a new subsection under §1.2 or §1.4), not an additive one, and this pass was scoped to additive integration. The gating *counts* and the denominator correction were the reference-worthy extract and are integrated; the per-skill DSL is flagged.
2. **`en/race-fans` course-keyed fan ladder (61 ids).** The export carries a full 1st-to-18th placement fan ladder (`race-fans.json`, `en__race-fans.json`) keyed by **course**, not by race instance. The report notes it could not be mapped per-race because the export exposes no course-to-instance join. The reference §1.2.6 carries the per-instance `fans_gain` (which the export does expose) and could add the ladder if a course join is found, but the join is an app-side question, not a reference fact. Flagged.
3. **Per-race win probability / difficulty scalars.** `docs/research-scratch-source/race-field-data-report.md` §4 returns `null` and cites `ADR-0016` (Next Race Readiness) as blocked race requirement data. This is an app ADR question, not a reference mechanic; the reference already states no publisher publishes per-race win rates. No text added; recorded here as a confirmed gap, not a closure.

---

## 6. Duplicates found (already in the reference — nothing added)

1. **The fan-gain table itself.** The `race-field-data-report.md` grade table (`100`=48, `200`=58, `300`=108, `400`=164, `700`=26; identical `fans_needed` and `fans_gain` ranges) reproduces §1.2.6's existing table exactly. This pass re-derived it from the export (confirmed) and added only the career-year distribution the table lacked.
2. **The 34%-breakpoint SP payout.** The report's G1 60 / G2 46 / G3 46 / OP 40 figures match the live Game8 page; the reference had no per-grade table, so this was integrated (not a duplicate) — but the report's own framing was a **derivation from a community archive**, and this pass replaced it with a direct read of the live page.
3. **The support-card dictionary, interpolation rule, Unique Perk, 35-effect list, `calc` facts, zero-carrier effects, and Pal-cards-carry-no-Friendship-Bonus.** All of `docs/research-scratch/SUPPORT-CARDS.md`'s reference-worthy mechanics are already in §1.4.1, §1.4.7 and §1.4.8 (those passes were its source). No integration performed.
4. **The mood ladder percentages and hint discount tiers.** Both were already settled (2026-09-27 and 2026-10-03) and the new screenshot frames **corroborate** rather than add; the frames were attached where the claim already lived.
5. **The maiden-rule text itself.** §1.2.6 already quoted the export's maiden sentence; this pass added only the "run-level, not per-row" modelling consequence.

---

## 7. Counts re-derived

| Count | Old value in the file | New value | Where |
| --- | --- | --- | --- |
| Conflict-log rows | 46 (preamble ×2, §8.2) and 47 (disposition) | **56** | Preamble line 23, §8.2 row and disposition |
| Strict-single-word ratings | "15 High, 19 Medium, 7 Low" | **20 High, 17 Medium, 4 Low** | §8.2 disposition |
| Compound/revised ratings | "five" | **15** | §8.2 disposition |
| §1 contents | "through 1.6.10" | "through 1.6.11" | Preamble line 17 |
| Document length | 2,647 lines (stale §8.5 says ~2,200) | 2,710 lines | §8.5 header (left "past 2,200" as the stale-run note; the real figure is not asserted there) |

The rating counts were re-derived by parsing the finished table (a PHP one-liner over the `## Section 7` block), not from recollection. The 46→56 change is the first re-derivation of these two prose counts since the table outgrew them; both now carry a "re-derive from the table" instruction that was already present and is now true again.

**Not re-derivable in this pass:** §8.5's nine pre-existing verification rows (roster recomputation, server-status consistency, dead citations, etc.). They describe runs over earlier revisions and were left marked stale rather than re-run, per §8.5's own header. No new sweep was executed against the finished document.

---

## 8. Fetch log

| # | URL | Purpose | Outcome | Tier assigned | Used in |
| --- | --- | --- | --- | --- | --- |
| 1 | `https://game8.co/games/Umamusume-Pretty-Derby/archives/572831` | Confirm the per-grade SP payout and Race Bonus ladder the report cited from a community archive | **Confirmed.** Live page (last updated 2026-06-26) prints the 0-RB base table, the 10/20/34/50/60% breakpoint rows, the 34% row matching the report (G1 60, G2 46, G3 46, OP 40, Pre-OP 40), and the Cleat Hammer percentages (+20% Artisan, +35% Master). The report's row-alignment caveat is real (the low row's icons shift OP+Pre-OP → G2+Pre-OP above 10%). | A, `[Global]` | §1.1.4 |
| 2 | local `docs/research-scratch-source/data/json/ura-races.json` + `races.json` | Verify the report's fan_gain table and career-year distribution | **Confirmed.** 413 rows in `ura-races.json`; joined to `races.json` grades: 100→48, 200→58, 300→108, 400→164, 700→26 (404 total, 9 special rows excluded). Ranges match the report and the existing §1.2.6 table exactly. | B (export) | §1.2.6 |
| 3 | local `docs/research-scratch-source/data/json/race-fans.json`, `en__race-fans.json` | Check whether the course-keyed fan ladder could be mapped per-race | **Not usable per-race.** The ladders are keyed by course id (61 rows), not race instance; no course-to-instance join is exposed. Recorded as a flag, not integrated. | B (export) | §5, not used |

No fetch returned 403 or failed. Fetch 1 is the only network read; fetches 2-3 are local committed exports (tier B), which the reference already treats as a source class.

---

## 9. Lore gate result

**Command:** `php tools/lore.php`
**Output (tail):** `lore-docs: 444 hit(s), 75 exempt line(s)`

Every hit inside `docs/UMAMUSUME_REFERENCE.md` falls into one of the three permitted classes the file's §8.8 already records:

1. Naming the ban to forbid it (the lore policy statement, §8.8, and the §2.2 lore rulings).
2. Banned substrings inside ordinary words or real names — the highest-volume class, e.g. "desired" (line 156, "desired skills"), "damaged", and the Umamusume `Red Desire` with its URL slug.
3. Verbatim skill names (the Section 3 row 105 unique skill, kept as source data and gated on export rather than edited).

**New hits attributable to this pass: 0.** The new material was checked for the pattern families it could touch: the §3.3 additions say "real-world race animals / namesakes" and "death record", not the banned nouns; the §1.6.11 additions say "spark icon / spark-resolution / Sparks" (none in the pattern list); the §1.2.2 addition says "trainee". The one hit inside an edited region (line 156, "desired skills") is **pre-existing text not written by this pass**.

---

## 10. Evidence gaps and unrun gates, stated plainly

- **No structural sweep was run** against the finished document (no table-column scan, no dead-link check, no `final_measure.py`-style pass). This change is additive prose and four table rows; the hand-off order's later gates were not executed because this is a documentation change with no code, migration, or asset surface. The table integrity of §7 was verified structurally (56 rows parse; the ratings column totals 20+17+4+15). The markdown fence check reports six pre-existing ```` ```text ```` blocks the checker calls unclosed; all six are present in `HEAD` and none was added here.
- **The §1.6.11 observations rest on 1 capture each** for several facts (the mood ramp, the failure-rate set, the spark-resolution event, the Photo Album quota). They are marked tier B and single-read in place, per §8.3's rule.
- **`migrate:status` and the test suite were not run** — out of scope for a reference-document change and not applicable.
- **The corpus coverage figures** (132 verified / 1,023 not_reviewed / 4 partial) are as of the registry's 2026-10-11 pass, not re-measured here.

---

## 11. Explicit stop

Every reference-worthy fact from the five directories is either integrated into an existing section, logged in §7, or flagged above with a reason. Every integrated fact carries its source, tier, server and date. Every new contradiction has a §7 row. The preamble's anchor and summary line are updated. The §8 counts are re-derived or marked not re-derivable. The lore gate reports no new hits. This report is written. `docs/UMAMUSUME_REFERENCE.md` and this report are the only files changed by this pass.
