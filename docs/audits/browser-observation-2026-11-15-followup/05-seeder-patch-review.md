# 05 — Seeder patch review (owner proposal, 2026-11-17)
**Run-14 reference note (2026-11-15 dev DB reseed):** Run 14 references throughout this package refer to the pre-correction dev DB row (card 33 Winning Ticket, `deck_slots` empty). On 2026-11-15 the corrected `RosyDreamsRiceShowerSeeder` was applied to the shared dev DB under a one-time EX-12a override (`07-ex12a-override.md`), superseding run 14 with **run 34** (card 31 `[Rosy Dreams]`, six deck slots including Super Creek Rented, two turn events at turn 67). Screenshots remain valid evidence of the state they capture; live URLs should use run 34.

The owner proposed a replacement `run()` for `RosyDreamsRiceShowerSeeder` to populate the deck, legacy, and events, and
reported a "major cross-scenario UI leak". I verified every claim before touching anything. **I have not applied the
patch**, and this page says why. Nothing in the repo was modified for this page except this file.

## Verdict in one line

**One claim is right and important (the card), one is wrong in a way that matters (the "leak"), and the patch as
written would not run or would write wrong data in four places.**

---

## Claim 1 — Wrong trainee card: **CONFIRMED, and worse than described**

The owner is right that the cockpit is not showing the Rosy Dreams card. Measured:

| Fact | Value |
|---|---|
| Run 14 `umamusume_id` | **30** = `Rice Shower` ✓ correct |
| Run 14 `character_card_id` | **33** = `[Get to Winning!]`, trainee **Winning Ticket** ✗ wrong |
| The actual Rosy Dreams card | **id 31**, `card_id` 103001, `umamusume_id` 30, title `[Rosy Dreams]` |

So the run is a Rice Shower run wearing **Winning Ticket's costume form**. The seeder's `CHARACTER_CARD_ID = 33` is
the bug, exactly as reported.

**But the proposed fix does not work.** The patch resolves by `CharacterCard::where('title', 'Rosy Dreams')`, and the
stored title is **`[Rosy Dreams]` — with brackets**. Measured: `where('title','Rosy Dreams')` returns **0 rows**. The
patch's own guard then fires and the seeder **returns without writing anything**, which is a silent no-op dressed as a
fix. The correct literal is `'[Rosy Dreams]'`, or a `where('title','like','%Rosy Dreams%')`, or simply the id **31**
which needs no lookup at all.

## Claim 2 — Empty deck: **CONFIRMED**. Patch is directionally right, two defects

`deck_slots` holds 0 rows for run 14; the screen correctly renders six `Not equipped`.

**Defect A — the card ids are the wrong column.** The patch writes `support_card_id => 30003, 30016, …`. Those values
exist in `support_cards.support_id`, **not** `support_cards.id` (whose range is 1–559). Writing `30003` into
`deck_slots.support_card_id` produces a dangling foreign key or an FK violation. The six rows resolve as:

| `support_id` | `id` (the FK value) | Card |
|---|---|---|
| 30003 | **250** | `[Dream Big!]` |
| 30016 | **263** | `[Piece of Mind]` |
| 30018 | **265** | `[Even the Littlest Bud]` |
| 30019 | **266** | `[Urara's Day Off!]` |
| 30020 | **267** | `[Double Carrot Punch!]` |
| 30030 | **277** | `[Just Keep Going]` |

**Defect B — ownership values.** The patch uses `'owned'` / `'rented'`. The column is a string with no enum
constraint at the DB level, but the rest of the app reads ownership through a domain vocabulary; writing bare
lowercase strings into it needs checking against the app enum before it ships, not assumed.

## Claim 3 — Empty legacy: **CONFIRMED**. Patch shape is unverified

`legacy_selection` is `NULL` for run 14 and the Inheritance screen correctly shows both parent slots open.

The patch writes a hand-shaped array of `parents` / `grandparents` with `name` and `rank` keys. **I did not verify that
shape against the `LegacySelectionPayload` the app reads**, and I am not going to assert it works. `TrainingRun`
casts `legacy_selection` as `array`, and there is a `LegacySelectionPayload` compatibility class (ADR-0010) that owns
the read side — a payload invented at the seeder must match what that class parses, or the Inheritance screen will
render empty while the column is non-null. **This needs the payload contract read before the patch is trusted.**

## Claim 4 — Empty events: **CONFIRMED**, and the patch adds events at impossible turns

`turn_events` has 0 rows for run 14. The Events screen is empty. True.

**Defect C — turns 65 and 66 do not exist in this run.** Run 14 has exactly **one** `turn_entries` row, at **turn 67**.
Seeding events at turns 65 and 66 places two events before the only recorded turn, on a career whose snapshot begins at
Senior Early October. The Events screen may render them, but the timeline will describe a run that has events with no
corresponding turns — manufacturing a data inconsistency to make a screen look populated.

**Defect D — the fields do not all exist.** `TurnEvent`'s `#[Fillable]` is
`training_run_id, turn, event_type, source_name, choice_index, choice_label, deltas, support_card_name, bond_delta,
origin_note`. The patch's two events use `event_type => 'Support Card'` / `'Trainee'`, which are free strings against a
typed column — acceptable at the DB level, unverified against the app's own event vocabulary. `bond_delta` is fillable,
so those two writes would succeed; the payload is structurally valid but semantically invented.

## Claim 5 — The "URA Finals on a Unity Cup run" leak: **WRONG, and this is the important one**

The owner reports the Cockpit leaking URA Finale content into a Unity Cup career. I reproduced the *text* and the
conclusion does not follow. Measured on run 14 (Unity Cup), from the DOM in document order:

```
H3  This turn
H3  Still to come
LI  ! Mandatory URA Finals Qualifier        G1  Finale Year
LI  ! Mandatory URA Finals Semifinal        G1  Finale Year
LI  ! Mandatory URA Finals Final (Aoharu)   G1  Finale Year
```

**The label is not a leak. `(Aoharu)` is Unity Cup's own finale.** The comparison that settles it — the same block on
an Active URA Finale run (run 23, run 26):

| Run | Scenario | Third item reads |
|---|---|---|
| 14 | Unity Cup | `URA Finals Final **(Aoharu)**` |
| 23 | URA Finale | `URA Finals Final **(URA)**` |
| 26 | URA Finale | `URA Finals Final **(URA)**` |

The app forks the final row by scenario, exactly as it should. And the Qualifier/Semifinal stages are **shared by
design**, not leaked. The repo's own reference says so:

> `docs/scenarios/09-global-race-calendar.md`: "Unity Cup | the tables above | … → **Final (Aoharu)** | Same slots,
> different final row."

> the same file: "Unity Cup share one calendar is **confirmed by the data**, and the confirmation is structural: the two
> scenarios have no separate slot rows at all, only separate final rows, and the final fork is the single place the data
> distinguishes them."

The catalogue bears it out: of four `*URA Final*` rows, **two carry `scenario_key = NULL`** (Qualifier 405, Semifinal
406 — shared) and two are scenario-scoped (408 `unity_cup` "Final (Aoharu)", 410 `ura_finale` "Final (URA)").

**So this is not a bug and must not go into `KNOWN-ISSUES.md`.** Filing it would create a phantom defect and send
someone to "fix" correct scenario forking. The `!` marker and the "Finale Year" tag are the app saying these are
mandatory calendar obligations, which they are for both scenarios.

**Fair criticism that survives.** The *heading* "Still to come" gives no hint that Qualifier and Semifinal are shared
while the final is scenario-specific. The block above it is grouped `EVERY SCENARIO` / `UNITY CUP ONLY`, which the
items here do not visibly repeat. A trainer could read the three items as URA content on a Unity Cup run — which is
precisely what happened to a careful reader. That is a **possible Informational copy finding about labelling**, not a
scenario-gating bug. It is not filed, because it needs eyes I do not have (the method-property header applies), and
because the first thing to check is whether the grouping labels are visually adjacent to these items.

## Claim 6 — "Run this and re-walk": **the patch cannot affect the current DB**

`TrainingRun::where('import_source', $source)->exists()` returns **true** for run 14 (its `import_source` is
`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md (Run Rosy Dreams Rice Shower)`). The patched `run()` starts with that same
guard and returns immediately. **On the seeded dev file the patch is a no-op**: the deck, legacy and events would not
be written to run 14, and the walk would show the same empty screens, which would then read as "the fix failed".

It only takes effect on a **fresh** database. To apply it to the current file, the run must be deleted first (or the
guard bypassed with an explicit update path) — which is a destructive step needing its own decision.

## Also unverified in the patch

- `$this->command->error(...)` — `$command` is **nullable** in this repo's seeders; the two existing seeders that use
  it guard with `if ($this->command !== null)`. The patch calls `->error()` unguarded, which fatals if the seeder is
  invoked in a context where `$command` is unset.
- `'career_position_source' => 'imported'` — not in the fillable list I read; needs checking before it is written.
- `RunMode::Snapshot` on a run created `Active` — unverified against how the app treats a snapshot-mode Active run.

## What I recommend instead

1. **Fix the card first, alone.** `CHARACTER_CARD_ID = 33` → **31**. That is a one-line, verified, highest-value fix:
   the cockpit currently names the wrong costume form. It needs a decision on the existing row (delete and reseed, or
   correct in place), because of the guard above.
2. **Deck**: use the `id` column values (250, 263, 265, 266, 267, 277), not the `support_id` values.
3. **Legacy**: read `LegacySelectionPayload` and shape the array to it before writing one.
4. **Events**: write at turn 67 (the only recorded turn), and match the app's event-type vocabulary.
5. **Drop the leak item entirely.** It is correct behaviour; filing it would cost someone a day.
6. **Re-run against a fresh scratch DB**, per EX-12a (private copy), not the shared file.

None of this is applied. It is a proposal review, and the decisions above are the owner's.

---

## Addendum, dated after the owner's mea culpa: the payload contract is now read, and it is worse than "renders empty"

The review's recommendation 3 said *"read `LegacySelectionPayload` and shape the array to it before writing one."*
That read is now done (`app/Models/Legacy/LegacySelectionPayload.php`, 619 lines, strict `fromArray` validation).
**The proposed patch's `legacy_selection` array would not render empty — it would throw an
`InvalidArgumentException` when the Inheritance screen reads it**, because the model accessor
(`TrainingRun::legacySelection()`, lines 260-264) calls `fromArray` on every read and the class's own docblock
records that a wrongly-shaped payload is exactly what *"made the Inheritance page throw on a UI-created run."*
A write succeeds (the seeder bypasses payload validation); the screen 500s.

**The verified contract**, so the roadmap item is closed rather than delegated:

| Patch writes | Contract requires |
|---|---|
| top-level keys `parents` / `grandparents` | exactly **`legacies`** + **`affinity`** (`KEYS`) — both patch keys are unknown and throw |
| each parent `{name, rank:'B+'}` | each legacy exactly **`rank` (nullable int), `is_guest` (bool), `ancestors` (list of string\|null), `sparks`** — plus optionals `rank_letter`, `name`, `pick`, `ancestors_meta`, `ancestors_sparks`, `ancestors_rank_letters`, `ancestors_rank` |
| `rank` as a letter string | `rank` is a **nullable int**; the letter form belongs in **`rank_letter`** from `RANK_LETTERS = ['G','F','E','D','C','B','A','S','B+','A+','S+','UG']` — 'B+' and 'UG' both legal *there* |
| grandparents as objects | `ancestors` holds **names only** — the guard at lines 211-220 rejects a `{slot,name}` record as *"a shape no writer produces"* |
| no `is_guest`, no `sparks` | both **required**; `sparks` entries need exactly `{kind, target, stars}` with `kind` in `blue/pink/green/white/scenario` and `stars` 1–3 |
| no `affinity` | optional but must be `△ / ○ / ◎` or absent |

All-or-nothing: `assertKeys` rejects unknown and missing keys in one check, so no partial shape slips through.

**The convenient part the verification also surfaced:** the canonical payload for this exact run is already on file.
The class docblock cites `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md:203-205` for the `UG` rank letters, and the
doc's §1.5 carries the whole six-ancestor tree with its six blue sparks (Stamina ★★★, Power ★★★, Speed ★★☆ ×2,
Guts ★☆☆, Power ★☆☆). The seeder should transcribe that section into the contract above rather than invent a
shape — the doc is the Trainer's read-back, which is precisely what this payload records.

**Roadmap status: all four items now verified against source.** Card 31 (read), deck FK ids 250/263/265/266/267/277
(queried), this payload contract (read), events at turn 67 (queried). Nothing in the proposed fix remains
unverified, and the fifth item — the leak — stays withdrawn.
