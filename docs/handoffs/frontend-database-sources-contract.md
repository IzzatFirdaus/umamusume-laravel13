# Frontend Handoff — Database Sources Payload (TD-08 / N6.2 / N2.11 / N6.5)

Authoritative contract for `Database/Sources` Inertia props. No competing contracts. Consume exactly as below.

## Top-level props

| Field | Type | Nullability | Notes |
|---|---|---|---|
| `docName` | `string` | non-null | Always `"the reference guide"`. |
| `fetched` | `FetchedRow[]` | non-null | Fetched datasets (one per `uma.sources` key with a URL). |
| `transcribed` | `TranscribedRow[]` | non-null | Transcribed reference sections. |
| `conflicts` | `ConflictRecord[]` | non-null | **Open-only** conflict rows. Count tile derives from this. |
| `conflict_history` | `ConflictRecord[]` | non-null | **Resolved-only** conflict rows (additive, TD-08). |
| `conflict_evidence_unavailable` | `boolean` | non-null | `true` when ≥1 conflict row has `evidence_available: false`. Screen must show "evidence unavailable" rather than a clean source. |
| `matrixVerifiedAt` | `string` | non-null | Scenario matrix verification date (ISO). |

## FetchedRow

| Field | Type | Nullability | Notes |
|---|---|---|---|
| `name` | `string` | non-null | Source key (e.g. `gametora-race-catalog`). |
| `url` | `string` | non-null | Publisher URL. |
| `fetched_at` | `string \| null` | nullable | Newest fetch for this source (N2.11 union of ledger + inline). `null` if never fetched. |
| `channel` | `'inline' \| 'data_sources' \| null` | nullable | N2.11. Where the fetch date came from. |
| `tier` | `string \| null` | nullable | N6.5. Corpus's own weight class. `null` if unrecorded. |
| `conflicts` | `string[]` | non-null | **Open** conflict ids naming this source. `[]` = no recorded open involvement. |
| `open_conflict_count` | `number` | non-null | `conflicts.length`. Separate from historical involvement. |
| `in_open_conflict` | `boolean` | non-null | `conflicts.length > 0`. |
| `history_involvement` | `'open' \| 'resolved' \| 'mixed' \| 'none' \| 'unknown'` | non-null | TD-08 five-state. See below. |
| `resolved_conflicts` | `string[]` | non-null | Resolved conflict ids naming this source. |
| `history_involvement_note` | `string` | non-null | Human-readable state explanation. |
| `note` | `string` | non-null | N6.2 Supplies phrase (parser-derived) or generic pipeline fallback. |

## TranscribedRow

Same shape as FetchedRow except: no `fetched_at`, `channel`, `tier` may be `null`; adds `anchored_at: string`. `conflicts`/`resolved_conflicts` always `[]`; `history_involvement` is `unknown` when `conflict_evidence_unavailable` else `none`.

## ConflictRecord

| Field | Type | Nullability | Notes |
|---|---|---|---|
| `id` | `string` | non-null | e.g. `conflict-distance-band-1400m`. |
| `question` | `string` | non-null | |
| `claims` | `string[]` | non-null | |
| `state` | `'open' \| 'resolved'` | non-null | |
| `affected_routes` | `string[]` | non-null | |
| `affected_sources` | `string[]` | non-null | Filtered to held names. `[]` when evidence unavailable. |
| `evidence_available` | `boolean` (optional) | `false` only | Present and `false` when the record could not establish its sources. |

## history_involvement states

| State | Meaning |
|---|---|
| `open` | Named by ≥1 open conflict. |
| `resolved` | Named only by resolved conflicts; no live dispute. |
| `mixed` | Named by both open and resolved. |
| `none` | No record names it; all records establish sources. |
| `unknown` | No record names it; ≥1 record has `evidence_available: false`. Missing evidence, **not** a clean bill. |

`unknown` outranks `none`. Known involvement (`open`/`resolved`/`mixed`) is stated as fact; the global `conflict_evidence_unavailable` flag independently discloses that additional associations remain unavailable — known involvement is **not** implied exhaustive.

## Filter semantics

**Server-side.** Filters execute in `DatabaseController::sources()` before the payload is built. The frontend receives already-filtered `fetched`/`transcribed`/`conflicts`. Do not re-filter client-side. A filter option that matches nothing is dropped server-side (`heldNames` check) rather than printed as a dead option.

## Supplies (N6.2)

`note` on FetchedRow carries the parser-derived Supplies phrase. No invented coverage: a source without a recorded phrase gets the generic pipeline sentence. Do not present the generic fallback as a specific capability.

## Channel (N2.11)

`channel` = `inline` when the fetch date came from the row's own inline stamp, `data_sources` when from the union ledger, `null` when no date. Additive; existing `fetched_at` unchanged.