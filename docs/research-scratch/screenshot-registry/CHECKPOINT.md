# Screenshot registry checkpoint

Concise, machine-shaped progress record so a future session can resume without
re-reading the corpus. Load order: this file, then `screenshots.py report`,
then `screenshots.py queue --limit 30`, then `screenshots.py batch --id B001 --show`.

## State (as of 2026-10-11)

- `inventory.jsonl`: 1159 records.
  - `not_reviewed`: 1057
  - `documented_unverified`: 40
  - `verified`: 62
  - `partial`: 0
  - `skipped`: 0
  - `duplicate_candidate`: 0
- `notes-index.jsonl`: 52 references reconciled from 11 note files; 50 exact, 2 probable, 0 unresolved.
- `evidence.jsonl`: 9 entries.
  - 3 inferred seeds (mood-ladder, growth-rate, hint-discount) from the bootstrap.
  - 5 observed confirmations: mood-ladder-confirmed, growth-rate-confirmed, ura-finale-confirmed,
    hint-discount-confirmed, failure-rate-confirmed.
  - 1 observed upgrade: hint-discount-0009 (all three discount tiers observed on one frame).
- `batches/B001.json`: 9 frames defined; 8 verified, 1 partial (234521), 0 unverified.
- `batches/B002.json`: 28 frames defined; 28 verified, 0 partial, 0 unverified, 0 skipped.
- `batches/B003.json`: 25 frames defined; 25 verified, 0 partial, 0 unverified, 0 skipped.
- `refs-index.jsonl`: 1162 frame citations across the repo; 1159 resolve, 3 do not.

## What B001 confirmed (vision pass 2026-10-10)

| Frame | State | What was established |
|---|---|---|
| `2026-07-18 001957` | verified | Mood Effect modal: five tiers GREAT/GOOD/NORMAL/BAD/AWFUL at +20/+10/0/−10/−20% training and +4/+2/0/−2/−4% running. BAD is the current mood. |
| `2026-07-17 232345` | verified | Growth-rate row +0/+10/+0/+20/+0; the note omitted that zero entries print `+0%` not blank. |
| `2026-07-18 133731` | verified | Same growth-rate row on Rice Shower; denominators confirm a per-run ceiling, not a fixed one. |
| `2026-07-18 160438` | verified | Same as 133731, later in the run; stat values moved but the growth deltas held. |
| `2026-07-18 135801` | verified | Power training card shows `Failure 2%`; consistent with the note's Power discipline at 2%. The four static per-discipline rates (5/3/1/2) are confirmed on the card, not the modal. |
| `2026-07-17 233324` | verified | Skill Selection: Hint Lv1 10% OFF, Hint Lv2 20% OFF, `Obtained` skill row rendering (inverted, no stepper). Two of three discount tiers in one frame. |
| `2026-07-15 012428` | verified | URA Finale training screen: `Finale Underway`, EX goal `Place 1st in URA Finale Qualifier`, Energy GREAT, Power Lvl 5, Ooguri Cap, Failure 10%, Skill Pts 170, five discipline cards. |
| `2026-07-15 014154` | verified | URA Finale details modal: Ooguri Cap `[Starlight Beat]`, Potential Lvl 1, full stat block with per-run denominators (1327/1322/1404/1306/1802), track/distance/style ranks, growth rates +20/+0/+10/+0/+0, eight skill rows. |
| `2026-07-17 234521` | **verified** | Revisited at native resolution (Phase 2). Skill Selection 'Learn' screen, Skill Points 308. ALL THREE hint discount tiers are legible on one frame: Plan X 'Increase passing ability when positioned toward the front upon approaching late-race. (Sprint)' Hint Lvl 1 10% OFF! cost 272; Countermeasure 'Slightly increase passing ability when positioned toward the front upon approaching late-race. (Sprint)' Hint Lvl 2 20% OFF! cost 128; Soft Step 'Slightly decrease fatigue when moving sideways. (Medium)' Hint Lvl 3 30% OFF! cost 112. Each row has a minus stepper, the cost, and a green plus button. Right panel 'Log': Rice Shower 'O-okay! I also want to win again and make everyone happy!' / 'So please don't hold back during training!'; system log 'Energy went down by 5. Guts went up by 9. Skill Pts went up by 43.'; event card 'The Unity Cup is Almost Here!' with Tazuna Hayakawa 'Hello, Trainer!' / 'The Unity Cup is almost here!' / 'Make sure to get your training in so you're prepared for it!'. A Snipping Tool overlay (screenshot-copied toast) and a small scheduled-races inset sit over the lower-right. Supersedes the earlier partial, which recorded the chips as illegible. |

## What B003 confirmed (vision pass 2026-10-11)

| Frame | State | What was established |
|---|---|---|
| `2026-07-18 001811` | verified | Cluster 725 representative: Training screen, Energy GREAT, Speed Lvl 1 highlighted, Skill Pts 148, Failure 9%, gains +10/+5. (Validates legacy note) |
| `2026-07-17 230755` | verified | Cluster 707 representative: Training screen, Energy GREAT, Treadmill Lv 2 highlighted, Speed 343/1318 D · Sta 176/1316 F · Power 347/1330 D · Guts 151/1304 F · Wit 114/1800 F, Skill Pts 16, Failure 10%. (Validates legacy note) |
| `2026-07-17 230902` | verified | Cluster 708 representative: Training screen, Energy GOOD, Treadmill Lv 2 highlighted, Speed 447/1322 C · Sta 254/1318 E · Power 544/1337 C · Guts 167/1305 F · Wit 131/1800 F, Skill Pts 121. (Validates legacy note) |
| `2026-07-14 202142` | verified | Cluster 80 representative: Training screen, Speed 213/1318 E · Sta 156/1316 E · Power 259/1330 E · Guts 109/1304 F · Wit 101/1800 E, Skill Pts 92, Failure 14%. (Validates legacy note) |
| `2026-07-14 194819` | verified | Cluster 66 representative: Training screen, Speed 176/1318 E · Sta 102/1316 F · Power 146/1320 F · Guts 107/1304 F · Wit 99/1800 G, Skill Pts 143, Failure 18%. (Validates legacy note) |
| `2026-07-17 232307` | verified | Cluster 725 member: Training screen, Speed 226/1318 E · Sta 116/1316 F · Power 287/1330 E · Guts 147/1304 F · Wit 120/1800 F, Skill Pts 126, Failure 12%. |
| `2026-07-18 000411` | verified | Cluster 725 member: Training screen, Speed 196/1318 E · Sta 145/1316 E · Power 227/1330 E · Guts 122/1304 F · Wit 119/1800 F, Skill Pts 99, Failure 10%. |
| `2026-07-18 132124` | verified | Cluster 725 member: Training screen, Speed 324/1318 E · Sta 251/1316 E · Power 344/1330 E · Guts 190/1304 F · Wit 112/1800 F, Skill Pts 174, Failure 10%. |
| `2026-07-18 140009` | verified | Cluster 725 member: Training screen, Speed 271/1318 E · Sta 202/1316 E · Power 322/1330 E · Guts 175/1304 F · Wit 121/1800 F, Skill Pts 162, Failure 8%. |
| `2026-07-17 221557` | verified | Cluster 707 member: Training screen, Speed 180/1318 E · Sta 123/1316 E · Power 217/1330 E · Guts 137/1304 F · Wit 112/1800 F, Skill Pts 109, Failure 14%. |
| `2026-07-17 235640` | verified | Cluster 707 member: Training screen, Speed 159/1318 E · Sta 105/1316 E · Power 210/1330 E · Guts 136/1304 F · Wit 118/1800 F, Skill Pts 100, Failure 15%. |
| `2026-07-17 235728` | verified | Cluster 707 member: Training screen, Speed 153/1318 E · Sta 92/1316 E · Power 205/1330 E · Guts 128/1304 F · Wit 110/1800 F, Skill Pts 96, Failure 16%. |
| `2026-07-17 235816` | verified | Cluster 707 member: Training screen, Speed 145/1318 E · Sta 80/1316 E · Power 190/1330 E · Guts 122/1304 F · Wit 108/1800 F, Skill Pts 91, Failure 19%. |
| `2026-07-17 230902` | verified | Cluster 708 representative (see above). |
| `2026-07-17 221602` | verified | Cluster 708 member: Training screen, Speed 165/1318 E · Sta 105/1316 E · Power 215/1330 E · Guts 157/1304 F · Wit 105/1800 F, Skill Pts 104, Failure 19%. |
| `2026-07-17 230750` | verified | Cluster 708 member: Training screen, Speed 197/1318 E · Sta 134/1316 E · Power 247/1330 E · Guts 145/1304 F · Wit 111/1800 F, Skill Pts 136, Failure 11%. |
| `2026-07-17 230851` | verified | Cluster 708 member: Training screen, Speed 172/1318 E · Sta 130/1316 E · Power 232/1330 E · Guts 130/1304 F · Wit 111/1800 F, Skill Pts 103, Failure 17%. |
| `2026-07-17 233934` | verified | Cluster 708 member: Training screen, Speed 174/1318 E · Sta 132/1316 E · Power 244/1330 E · Guts 140/1304 F · Wit 110/1800 F, Skill Pts 110, Failure 16%. |
| `2026-07-14 202142` | verified | Cluster 80 representative (see above). |
| `2026-07-14 124359` | verified | Cluster 80 member: Training screen, Speed 159/1318 E · Sta 92/1316 E · Power 197/1330 E · Guts 100/1304 F · Wit 96/1800 E, Skill Pts 84, Failure 24%. |
| `2026-07-14 191437` | verified | Cluster 80 member: Training screen, Speed 177/1318 E · Sta 140/1316 E · Power 238/1330 E · Guts 134/1304 F · Wit 136/1800 E, Skill Pts 115, Failure 12%. |
| `2026-07-14 200324` | verified | Cluster 80 member: Training screen, Speed 145/1318 E · Sta 107/1316 E · Power 186/1330 E · Guts 103/1304 F · Wit 109/1800 E, Skill Pts 87, Failure 22%. |
| `2026-07-14 225950` | verified | Cluster 80 member: Training screen, Speed 207/1318 E · Sta 172/1316 E · Power 269/1330 E · Guts 201/1304 F · Wit 149/1800 F, Skill Pts 162, Failure 10%. |
| `2026-07-14 194819` | verified | Cluster 66 representative (see above). |
| `2026-07-14 124406` | verified | Cluster 66 member: Training screen, Speed 137/1318 E · Sta 95/1316 E · Power 182/1330 E · Guts 111/1304 F · Wit 94/1800 E, Skill Pts 78, Failure 30%. |
| `2026-07-14 125355` | verified | Cluster 66 member: Training screen, Speed 147/1318 E · Sta 113/1316 E · Power 229/1330 E · Guts 136/1304 F · Wit 122/1800 E, Skill Pts 122, Failure 12%. |
| `2026-07-14 191325` | verified | Cluster 66 member: Training screen, Speed 188/1318 E · Sta 135/1316 E · Power 236/1330 E · Guts 152/1304 F · Wit 150/1800 E, Skill Pts 128, Failure 12%. |
| `2026-07-14 194339` | verified | Cluster 66 member: Training screen, Speed 157/1318 E · Sta 114/1316 E · Power 210/1330 E · Guts 131/1304 F · Wit 106/1800 E, Skill Pts 96, Failure 22%. |

## Known discrepancies resolved

| Item | Where | Resolution |
|---|---|---|
| 3 frame citations with no inventory record | `refs-index.jsonl` `in_inventory: false` | All three are legitimate unresolved citations: <br> • `2026-07-14 185506`: cluster representative (singleton) absent from disk and git history; documented in DESIGN-CORPUS.md §6; action = skip. <br> • `2026-07-15 005040`: accents.json marks it `missing: true` for result-pits-modal; absent from disk; action = skip. <br> • `2026-07-18 235511`: citation typo (KI-57); correct frame `2026-07-17 235511` is in inventory and verified. |
| The note-referenced frame missing from disk | `Screenshot 2026-07-14 185506` | Confirmed absent from disk and git history; documented in DESIGN-CORPUS.md §6 as a singleton cluster representative with surviving signature; action = skip. |
| 1 partial frame (`2026-07-17 234521`) | `inventory.jsonl` | Revisited at native resolution; all three hint discount tiers legible; state updated from `partial` to `verified`. |
| B001 `results[0].note` stale | `batches/B001.json` | Updated with additional result record reflecting 8 verified, 1 partial, 0 unverified, 0 skipped. |
| B002 `manifest.results` empty | `batches/B002.json` | Updated with result record reflecting 28 verified, 0 partial, 0 unverified, 0 skipped. |
| Concurrent-review race | `tools/screenshots.py` | Hardened with OS-level file lock and `registry_lock` context manager; verified by 3-phase regression test (sequential/threads/processes). |

## Verification run

- `python tools/screenshots.py selftest` passes all assertions.
- `python tools/screenshots.py validate` reports 0 fails and 0 warns.
- The corpus files under `docs/game-screenshots/` are untouched: the registry never writes to them, and `inventory` detects byte changes by SHA-256.
- `python tools/screenshots_concurrency_test.py` passes all phases (sequential/threads/processes).

## Resume protocol

1. Run `screenshots.py validate` to confirm the tree is clean.
2. Read `batches/B001.json`; every id in it is now `verified` or `partial`.
3. For the partial (`2026-07-17 234521`), `screenshots.py show --ids "2026-07-17 234521" --json` and re-inspect the discount chips at native resolution.
4. Read `batches/B002.json` and `batches/B003.json`; all ids in them are `verified`.
5. For the next batch, `screenshots.py queue --state documented_unverified --limit 40` and take the top 40, or `screenshots.py queue --state not_reviewed --limit 40` and take the top 40.
6. `screenshots.py report` and hand off.

The vision path that returned blank renders in the earlier session now works; a `review` with an observation is the only valid way to advance a frame to `verified`. A `partial` state with an `outstanding` question is the correct record when a value is not legible on the original.

## Next recommended action

The next batch (B004) should prioritize:
- Members of large clusters 725 (remaining 51 members), 707 (remaining 9), 708 (remaining 9), 80 (remaining 33), 66 (remaining 18)
- Remaining documented-unverified frames (35 left)
- Remaining not-reviewed frames (1057 left)
- Focus on validating legacy notes and characterizing large clusters.