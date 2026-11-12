# Trainer Desk 3.x — Phase 0.4 closing record

**Date:** 2026-11-12
**Inspected commit at start:** `f029a806`
**Scope:** close Phase 0. Commit the corrected evidence boundary **before** any UI or test depends on it.
No application code changed by this dispatch.

---

## 1. Why this closes before 4.3

Ruling J is not an implementation permission that happens to have a citation. It is a **documentation and
evidence correction**: row 31's residual asserted that no first-party string existed, and the tree contradicts
that. If 4.3 landed first, the four refusal tests would flip against a register still saying the name is
unverified, and the next session would read a contradiction.

So the order is: repair the evidence record, then change the code that reads it. This dispatch does the first
half.

---

## 2. What changed

### 2.1 `docs/UMAMUSUME_REFERENCE.md` — row 31 erratum

Appended as prose immediately before `### 8.5 Verification evidence`, preserving everything already written:

| Preserved | Detail |
| --- | --- |
| Row 31 in the conflict table | Untouched, still 62 rows; the table is not renumbered |
| Its `Dissolved 2026-10-05` verdict | Standing and correct: a scenario-versus-finale distinction |
| The residual sentence | Quoted in strikethrough and explicitly kept as the 2026-10-05 read, per the dated-record rule |

The erratum records the two grounds that make the residual premise false, with the tiers kept separate:

| Fact | Source | Tier |
| --- | --- | --- |
| Japanese name `トゥインクルスタークライマックス` (4 occurrences) | `https://umamusume.jp/contents/game/scenario/climax/` | **S** — Cygames' own domain |
| Three-race points finale; 「勝利Pt」totals decide the placing; 「全３レースのポイント制」 | same page | **S** |
| Pro Shop, Shop Coins, rivals, Best Umamusume Award as publisher nouns | same page (`特別ショップ`, `ショップコイン`, `ライバル`, `最優秀ウマ娘`) | **S** |
| **English title `Twinkle Star Climax`** | committed `en/missions/playertitle` id 600414 | **B — first-party client export** |
| "Replaces the URA Finale"; `Result Points`; aptitude-dependent requirement | DotGG reproduction of the Global announcement | **Third-party corroboration only** |

**The wording distinction that matters**, and the reason the export is cited at tier B rather than as a publisher
string: the Global client export establishes that the **English title string exists in committed client data**. A
mission the game itself renders to a Trainer carries it. That is not the same claim as "Cygames published an
English web page naming the finale", and the erratum says so in those words. The export is first-party *client*
evidence; DotGG is the only source for the Global announcement's English prose, and it self-declares as
unaffiliated, so it never becomes the publisher of record.

**Four mechanics nouns got a stronger basis than they had.** `特別ショップ`, `ショップコイン`, `ライバル` and
`最優秀ウマ娘` are now cited to the publisher's own page rather than to guide transcriptions. That is a by-product
of reading the page, not a new claim.

### 2.2 The boundary this erratum does not cross

Stated in the erratum itself, in one paragraph, because the failure mode is that a name ruling gets read as a
mechanics ruling:

| Mechanic | Status, unchanged |
| --- | --- |
| Lesson costs, eligibility | `BLOCKED BY EVIDENCE` (`SCREEN_SPEC.md` §7-19) |
| Song catalogue, titles, costs | `BLOCKED BY EVIDENCE` (§7-21) |
| Live Bonus names and magnitudes | `BLOCKED BY EVIDENCE` (§7-22) |
| Promo Concert schedule, lifecycle | `BLOCKED BY EVIDENCE` (§7-23) |
| Per-track Result Point thresholds | Single transcription, `config/scenarios.php:667-669`, `standard` only |
| Shop Coin amounts, prices, rotation | No run column, `N/A` under D-220/KI-154 |

### 2.3 Evidence register pointer repaired

Row 31's evidence cell named `en/missions/trainerexam-limited`. That file has **never been captured**
(`docs/research-scratch-source/data/json/` holds no `trainerexam*`). The erratum points at
`en__missions__playertitle.json` id 600414, which is committed and carries the string in both directions. The
manifest still lists `en/missions/trainerexam-limited` → `a280f7bb`, so a future pass can capture it if wanted;
nothing depends on it now.

### 2.4 `docs/research-scratch/PLANS-AND-BRIEFS.md`

Two appended sections from earlier in this pass, neither edited since:

- `## trainer-desk-3x-roadmap-rulings-2026-11-12.md` — rulings A–J, +346 lines.
- `## ruling-j-twinkle-star-climax-2026-11-12.md` — J's resolution, +72 lines. The original J body is preserved
  in place and the summary row reads `RESOLVED 2026-11-12`; nothing was rewritten.

### 2.5 The two 0.x audit records

- `docs/audits/trainer-desk-v3x-roadmap-baseline-2026-11-12.md` (0.1, 269 lines)
- `docs/audits/trainer-desk-v3x-change-boundary-2026-11-12.md` (0.3, 241 lines)
- `docs/audits/trainer-desk-v3x-phase0-close-2026-11-12.md` (this file)

---

## 3. Verification actually run

| Check | Command | Result |
| --- | --- | --- |
| Erratum is additive | `git diff --numstat docs/UMAMUSUME_REFERENCE.md` | `451 66` — 70 of the 451 are this dispatch; the −66 is the pre-existing reconciliation, unaltered |
| Conflict table untouched | `grep -c '^| [0-9]' docs/UMAMUSUME_REFERENCE.md` | 466, unchanged by the erratum (it is prose, not a row) |
| Row 31 still present | `sed -n '/^| 31    | 1.6/p'` | present, `[Global]` name of the third scenario |
| Markdown fences | `python tools/md_fence_check.py docs/UMAMUSUME_REFERENCE.md` | `0 invalid closing fence(s) in 1 file(s)` |
| Markdown fences, rulings master | same, `PLANS-AND-BRIEFS.md` | `0 invalid` |
| Line endings, rulings master | byte count | 8,188 CRLF = 8,188 LF, 0 LF-only |
| Line endings, reference | byte count | 3,316 LF, 0 CRLF — this file is LF-native and stayed so |
| Lore gate | `php tools/lore.php` | `599 hits / 75 exempt`; delta from the 595 recorded at 0.1 is +4, this pass's own prose, ruled in §4 |
| JP page claims | `web_extract` + cache read | `トゥインクルスタークライマックス` ×4, `勝利Pt` ×2, `ポイント制` ×1, `特別ショップ` ×1, `ショップコイン` ×1, `ライバル` ×2, `最優秀ウマ娘` ×1, and the section heading `Feature systems of "Twinkle Star Climax"` |

**Not run, and not claimed:** the full test suite and the browser suite. No application code moved, so a Level 3
run at this point would be a cost with no signal; it belongs to 4.3 and the 9.7 gate.

---

## 4. Lore rulings for the delta

The one hit this pass adds is the word `rivals` — ordinary English, and in this file it names the scenario
mechanic the Cygames page calls 「ライバル」. Class 2 (a substring inside ordinary language), not a violation.
Every other added line measures 0 under the pattern list.

---

## 5. What Phase 0 leaves behind

| Deliverable | State |
| --- | --- |
| Baseline (0.1) | Written; four of its verdicts corrected in 0.3 §1 |
| Rulings (0.2) | A–I accepted; J resolved with per-fact provenance |
| Change boundary (0.3) | 36 DONE / 14 NARROWED / 9 OPEN / 1 PARKED |
| Evidence boundary (0.4) | This record + the row 31 erratum |

**Still owner-gated, and not blockers for 4.3:** 3.1 (whether the 1,157-line Cockpit split is wanted) and 6.3
(whether a completion-date filter justifies a column + ADR). Neither touches Trackblazer.

---

## 6. Files this dispatch owns

Staged by explicit paths only, never `git add -A`:

- `docs/UMAMUSUME_REFERENCE.md`
- `docs/research-scratch/PLANS-AND-BRIEFS.md`
- `docs/audits/trainer-desk-v3x-roadmap-baseline-2026-11-12.md`
- `docs/audits/trainer-desk-v3x-change-boundary-2026-11-12.md`
- `docs/audits/trainer-desk-v3x-phase0-close-2026-11-12.md`

**Explicitly not staged, peer-owned** (`post-remediation-verification-handoff.md` §C):

- `docs/audits/trainer-desk-v3-ux/` (8 reports + `scenario-research/` + 59 PNG)
- `docs/audits/trainer-desk-v3-ux-retest/` (10 reports + 72 PNG)
- `docs/audits/RS-UC-2026-13/`, `docs/audits/RS-UC-SCENARIO-DETAIL-*`, `docs/research/RS-UC-*`,
  `docs/research/scenario-detail-design-language-survey-*`
- `docs/audits/audit-methodology-and-remediation-specs.md`, `post-remediation-verification-handoff.md`,
  `progress-report-2026-11-12.md`, `progress-report-f-series-2026-11-12.md`,
  `remediation-phase-progress-report.md`, `walk-8-follow-up-plan.md`

**Ownership unresolved, and therefore untouched:** `docs/framework-ui-ux/_audit-prompt.md` (the handoff claims it,
a peer note disputes it) and `docs/research/ui-ux-application-context-brief.md`.
