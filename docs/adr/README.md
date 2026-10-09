# ADR index

## Errata convention

A dated document claim that later proves wrong is corrected by appending a dated erratum
that preserves the original sentence verbatim. The erratum states which part of the claim
still holds and which does not.

The point is not archival politeness. An ADR's status line and its consequence list are what later sessions
read *instead of* re-deriving the decision, so a silent rewrite destroys the only record that the reasoning
ever existed. `ADR-0008` established the form — its status was corrected the day it was accepted, with the
superseded sentence left standing and labelled as standing — and `ADR-0012` already numbers its corrections
("Erratum 3" and onward), which is the closest this repo came to naming the practice before this section.
`ADR-0015` is now the clearest case for it: its first consequence asserted that the bound a Trainer sees as a
bar end is the bound the form enforces, and that was false on one branch for a day. Deleting the claim would
have destroyed the evidence that a reasonable author believed it, which is what a reader needs in order to
trust the correction.

**The rule that came out of that erratum, promoted because it generalises beyond the one bug: unifying the
function is not unifying the call. Where one number is read by a validator and a renderer, the record must
say which *argument* both pass, not only which helper both call.** `ADR-0015` moved both readers onto
`ScenarioCaps` and stopped there, so the disagreement survived *inside* the single owner — the validator
passed `forRun($run)`, the band's caller passed `scenarioKey()`, and the ADR still read as though it had
succeeded. The same shape produced three findings in the pass that fixed it: `AGENTS.md` citing an `FR-x`
notation the PRD does not use (corrected at `539de59`), `PRD.md` not citing three shipped columns
(Slice 6's `P-4`), and two architecture listings omitting fields their own table has (`N-2`). Each is two
sides believing they pointed at the same fact. When an ADR claims one owner for a value, the consequence
list should name the call *and* the argument, or it has described an intention rather than a guarantee.

Five ADRs carry a correction of their own claim: `ADR-0001` (§5 superseded by `ADR-0003`), `ADR-0008`
(status), `ADR-0012` (numbered errata, four as of 2026-10-05), `ADR-0013` (withdrawn, original status
preserved in a footnote), and `ADR-0015`. `ADR-0014` matches the same grep and does **not** belong: it
supersedes `ADR-0005`, which is a different act. Neither do `ADR-0019` (it records a correction it *asks*
for, in §14 of `AGENTS.md`, and is still Proposed) nor `ADR-0021` (it corrects `ADR-0012` Decision 2 and says
so in `ADR-0012` Erratum 4, which is amending another decision, not one's own claim). Re-derive with
`grep -niE "erratum|superseded (by|on)" docs/adr/00*.md` and read which claim
each hit is about; a count typed straight from a grep is the failure this section exists to prevent.

## The index

Derived, not hand-maintained. Regenerate with:

```bash
for f in docs/adr/00*.md; do printf '%s\t%s\t%s\n' \
  "$(basename "$f" .md)" "$(head -1 "$f" | sed 's/^# //')" \
  "$(awk '/^Status:/{print substr($0,9,120); exit}' "$f")"; done
```text

| ADR    | Subject                                                                                                                       | Status as decided                                                                                                                                                                        |
| ------ | ----------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 0001   | Lift the no-prediction non-goal for Energy guidance                                                                           | Accepted **in part**; §5 schema superseded by `ADR-0003`                                                                                                                                 |
| 0002   | Scenario-aware stat caps exceed the validation bound                                                                          | Accepted, amended twice; **its validation bound is superseded by `ADR-0015`**                                                                                                            |
| 0003   | Consolidated Phase 1 schema expansion for Energy, Fans, events and races                                                      | Accepted by owner 2026-09-27                                                                                                                                                             |
| 0004   | Store aptitude letters and scenario stat caps as reference data                                                               | Accepted by owner 2026-09-27                                                                                                                                                             |
| 0005   | Support card entities — proposed, blocked on a PRD non-goal                                                                   | **Declined** for Phase 1 (R37); superseded by `ADR-0014`                                                                                                                                 |
| 0006   | Design authority and theme default                                                                                            | Accepted — Option 2, Light base with preference resolution                                                                                                                               |
| 0007   | C-7 loading-state scope for server-rendered views                                                                             | Accepted (owner ruling 2026-09-28)                                                                                                                                                       |
| 0008   | The character-card catalog layer, and a card reference on the run                                                             | Accepted 2026-09-29; carries a same-day erratum                                                                                                                                          |
| 0009   | Seeding `scenario_slots` — three sources, what each unlocks, what stays dark                                                  | **Ruled in part** (Option A, URA Finale)                                                                                                                                                 |
| 0010   | Record the Legacy Select read-back as one typed payload on the run                                                            | Accepted (Slice 15)                                                                                                                                                                      |
| 0011   | The skills reference import — what the export carries, what stays unrendered                                                  | Accepted **in part**                                                                                                                                                                     |
| 0012   | Card detail fields — stat arrays, images, and objectives                                                                      | Accepted 2026-09-29; numbered errata                                                                                                                                                     |
| 0013   | Character profile source — basic information at character grain                                                               | **Withdrawn**, superseded by `ADR-0012` Decision 4                                                                                                                                       |
| 0014   | Support card entities — authorized for Phase 1                                                                                | Accepted (Slice 2); supersedes `ADR-0005`                                                                                                                                                |
| 0015   | A stat's ceiling is its scenario's per-stat cap                                                                               | Accepted (owner decision 5); **2026-10-01 erratum**                                                                                                                                      |
| 0016   | Qualitative next-race readiness                                                                                               | **OPEN QUESTION** — no decision taken or implied; Slice 3 held                                                                                                                           |
| 0017   | A historical run imports as the CSV this app exports, through a web form                                                      | Accepted (Slice 4, built 2026-10-01)                                                                                                                                                     |
| 0018   | One facet contract for the three filter surfaces, and one page-size rule                                                      | Accepted (owner dispatch 2026-10-04)                                                                                                                                                     |
| 0019   | A semver label, a tag, and a release branch for a local-only tool                                                             | **Proposed** — version and non-`master` target set by owner; pre-release mechanism and the §14 correction owed                                                                           |
| 0020   | Trainer Desk 2.0 — SPA frontend, target-based Trainer Advisor, record-only Veteran library (race prediction stays deferred)   | Accepted (owner ruling 2026-10-04); amends §6.2, extends `ADR-0001`, builds on `ADR-0010`, reaffirms `ADR-0016`                                                                          |
| 0021   | Sourced character and support-card artwork, mirrored locally and derived from ids                                             | **Accepted (owner ruling 2026-10-05)**; supersedes `ADR-0012` Decision 2 for sourced artwork, preserves `PRD.md` §6.13; fetch half built 2026-10-05, display half open (`PRD.md` OQ-6)   |
| 0023   | Record the deck slot's owned-or-rented state on the slot                                                                       | **Proposed** — drafted 2026-10-09 for the owner's ruling (D3 remediation of the Rice Shower / Unity Cup walk); amends `ADR-0014` by dated erratum                                            |
| 0024   | Energy is recorded as exact, band or unknown beside its numeric column                                                         | **Accepted** (owner ruling 2026-10-09) — closes the §11 gap on the Phase 8.1 Energy migration; extends `ADR-0003`, does not reopen `ADR-0001`                                             |

The table is stale the moment a status line changes and no test guards it, so the command above is the
authoritative form and this prose is a snapshot. This table was stale when `ADR-0019` was written: it ended
at 0017 while `ADR-0018` was on disk, and the count below said seventeen. Twenty-one files: four not accepted
in full (0005 declined, 0013 withdrawn, 0016 an open question, 0019 proposed), two accepted in part (0001,
0011), one superseded within its own subject by a later ADR (0002's bound, by 0015), and one whose subject is
only half landed (0021: its fetch mechanism built the day it was accepted, its display slots still open on
`PRD.md` OQ-6, and no live pass run against the asset host).

*Dated note 2026-10-09: `0023` is on disk and indexed above. `0022` is on disk and is **not** indexed,
because its own status line reserves the number, the status and this regeneration to the owner, and the
ADR-0023 author did not land another draft's row. The count in the paragraph above therefore still reads
twenty-one and the table above it is a snapshot either way; re-run the command to derive the real one.*

This file holds no decisions. An ADR is written when a question is answered, not to hold a fork open — which
is why `ADR-0016` carries no verdict and the row above says so rather than smoothing it.
