# Round 7 — owner calls (`RS-UC-R7-OC`)

**Status:** decision document. Four slices do not open without a written ruling recorded here. Every option below is
quoted from the implementation dispatch; no option is invented, and none is implemented on the executor's opinion.

**Opened:** 2026-11-07. **Tree:** `05be6616`. **Register:** `docs/audits/rice-shower-unity-cup-round7-reconciliation.md`.

A ruling of "the dispatch's recommendation" is a ruling. It is recorded verbatim in the relevant slice-audits section
and the slice proceeds.

---

## §1 S1 — `Turn *` default and label (`R7-20`)

**Blocking row.** Slice S1.

### The measured starting point

`app/Http/Controllers/Career/TrainingDecisionController.php:574` reads:

```php
'turn' => $run->nextTurnNumber(),
```

**The default is already the run's next turn, not a hardcoded `1`.** Walk 7 observed `Turn *` showing `1` on a
wizard-created run — where `nextTurnNumber()` is `1`, correctly. The defect is the **label and the affordance**, not the
value:

- `Turn *` reads as "the turn index this run is at"; the value is "the turn to play".
- A mid-career user has no prompt on the wizard path saying to change it.
- The primary reads `Record turn N` without stating that N is the turn to play rather than the run's position.

### Options

| Option | Content | Trade-off |
| --- | --- | --- |
| **(a)** *recommended* | Keep `nextTurnNumber()`. Relabel the field `Turn to play (the run's next turn)`; add the helper `A career imported at a later position is recorded by entering the client's calendar turn here; the run will be positioned at that turn.` | Closes the affordance gap with no behaviour change. Field semantics stay "next to play" |
| **(b)** | Rename the field `Position this run at turn`, keep the server default. Typing 67 positions the run at 67 | More invasive; changes the field's semantics from "next to play" to "position this run" |
| **(c)** | Both — rename **and** add the helper | Largest; same semantic change as (b) |

### Why (a)

The dispatch recommends **(a)** because (b) and (c) change what the field *means*. That is a product decision, not a
slice decision. The observed harm — a user commits a position they did not intend — is closed by making the field's
meaning legible, which is what (a) does.

**RED under any ruling** is a label/helper assertion in `tests/Feature/CareerTrainingDetailTest.php`, never an
assertion that the field opens at `1` (it does not).

**Ruling:** **(a)** — relabel + helper. Keep `nextTurnNumber()`'s value; the field's label becomes
`Turn to play (the run's next turn)` and a helper sentence states what the turn index means. Options (b) and (c)
(renaming the field to `Position this run at turn`) are **refused**: the tool does not position a run, it records a
turn.

---

## §2 S2 — maiden lock (`R7-26`)

**Blocking row.** Slice S2. **Contract decision; the executor does not invent one.**

A mid-career run cannot have a Debut or Maiden win (grade `800`/`900`) recorded, so `hasClearedMaidenLock()` never
returns true and `maidenGates()` locks every standard race permanently.

| Option | Content | Trade-off |
| --- | --- | --- |
| **(1)** | A run-level `debut_cleared` boolean. The wizard asks `Did this career clear its Debut or Maiden?`; the lock reads the flag | One column, one form field; the source's `Debut`/`Maiden` fact becomes representable |
| **(2)** | An off-calendar entry does not require Debut/Maiden. The off-calendar form accepts `grade_code` in `[800, 900]` and, on save, the lock clears | No new column, no wizard change; requires the off-calendar form to accept a grade field it does not currently have |
| **(3)** | An explicit "cannot be entered" state. The screen stops offering `Enter race`, prints `This career did not record its Debut or Maiden. Races cannot be entered until one is recorded.`, and links a recording path | Honest, no new column; requires a small dedicated recording screen or inline form |

The register's §9 records (2) as "the honest fix" and (1) as "the workaround". **The dispatch states no opinion.**

**Ruling:** **(4)** — derive the lock from the run's effective position. `hasClearedMaidenLock()` returns true when
the qualifying `race_entries` row exists **or** the run's effective position is past turn 12. Boundary is `> 12`, not
`>= 12`: the turn-12 Debut may not have resolved yet; by turn 13 it has. Options (1), (2) and (3) are **refused** as
larger than the fact requires — position already encodes what they would add a column, a form field, or a screen to
record.

---

## §3 S11 — Legacy spark editor (`R7-36`, `R7-68`)

**Largest slice.** No spark input exists anywhere on the Legacy step, so every parent prints
`This parent carries no recorded spark.` and the Inheritance screen's absence is honest but caused upstream.

| Option | Content | Trade-off |
| --- | --- | --- |
| **(t)** *recommended* | Per-node editors: each of the six ancestor nodes gets a `Sparks <node>` disclosure with `Add Spark` rows of `Kind` / `Category` / `Target` / `Stars` | Matches the source's tree; high fidelity; high cost |
| **(u)** | One table editor: columns `Node` (Parent A, Grandparent A1, A2, Parent B, Grandparent B1, B2), one row per spark, no per-node disclosure | Lower cost; the payload key `ancestors_sparks` must be built from the table's `node` column rather than per-ancestor |

**The dispatch recommends (t).**

**Ruling:** **(t)** — six per-node editors, because the payload already keys `ancestors_sparks` per ancestor and
the source describes per-node chains. Option (u) (single table with a `node` column) is **refused**: it is a shape the
client does not have and it loses the tree the source records.

---

## §4 S18 — timeline shape (`R7-83`)

**Split into S18a and S18b** (see `round7-path-corrections.md` §2.4). `R7-99` — the snapshot review's omitted
Scenario and Trainee read-back — needs no ruling and is landable as **S18a** immediately.

| Option | Content | Trade-off |
| --- | --- | --- |
| **(i)** *default* | The timeline's row set joins `race_entries` with `kind: 'race'`, beside the existing turn rows | One list, one sort; recorded races appear in place in the auditable record |
| **(ii)** | A parallel race rail beside the timeline | Two surfaces; races stay visually distinct but the record is no longer one chronological list |

**The dispatch defaults to (i) unless the owner rules (ii).**

**Ruling:** **(i)** — the timeline's row set joins `race_entries` as `kind: 'race'`, beside the existing turn rows.
Option (ii) (a parallel race rail) is **refused**: the client has one chronological log, and a second surface would
duplicate the sort, filters and phase grouping. **`R7-99` needs no ruling** and lands separately as S18a.

---

## §5 Rulings received

| Slice | Ruling | Received | Recorded in |
| --- | --- | --- | --- |
| S1 | **(a)** relabel + helper | 2026-11-07 | `#s1--r7-20` |
| S2 | **(4)** derive the lock from the run's effective position (`> 12`) | 2026-11-07 | `#s2--r7-26` |
| S11 | **(t)** six per-node spark editors | 2026-11-07 | `#s11--r7-36-r7-68` |
| S18b | **(i)** the timeline row set joins `race_entries` as `kind: 'race'` | 2026-11-07 | `#s18b--r7-83` |
| S18a | no ruling required — `R7-99` is a settled defect with a named file | 2026-11-07 | `#s18a--r7-99` |

**All four held slices are unblocked.** The dispatch's recommendations were accepted for S1 and S11; S2 was ruled an
option the dispatch did not carry (derive from position, not from a new column); S18 was split so the unblocked half
lands without waiting.
