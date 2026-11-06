# Flows — Review Queue

Date: 2026-11-06. HEAD: `67a4830`. Documentation only; no code changed.
Evidence: `app/Http/Controllers/ReviewController.php`, `app/Actions/ResolveMatchCandidate.php`, `app/Actions/PromoteMatchedRecord.php`, `app/Enums/{CandidateStatus,MatchTier}.php`, `app/Http/Requests/ResolveMatchCandidateRequest.php`, `app/Models/MatchCandidate.php`.

The pipeline-facing queue where a Trainer rules on proposals the matcher could not settle. It is not career-facing: nothing here touches a run.

---

## 1. Why candidates exist

The match stage proposes one of four tiers (`MatchTier`):

| Tier | Outcome |
| --- | --- |
| `Exact` | auto-promote |
| `Alias` | auto-promote |
| `Fuzzy` (above `config('uma.match.fuzzy_threshold')`) | land in `match_candidates` |
| `None` | land in `match_candidates` |

A row that lands here is stored with its proposed name, its source key, the fetch timestamp, and the suggested `umamusume_id` when the matcher had one.

---

## 2. Routes

| Method | URI | Name | Action |
| --- | --- | --- | --- |
| GET | `/review` | `review.index` | `index` — the queue |
| GET | `/review/{candidate}` | `review.detail` | `show` — one candidate |
| POST | `/review/{candidate}` | `review.resolve` | `resolve` — a verdict |
| POST | `/review/{candidate}/reopen` | `review.reopen` | `reopen` — back to Pending |

The detail GET is declared before the POST. The two differ by method and either order binds the same `{candidate}`, so the ordering is for readability rather than correctness.

---

## 3. Index

Rows: `{id, proposed_name, proposed_name_ja, match_tier, match_tier_label, source_key, created_at (from created_by_fetch_at), suggestion, suggested_umamusume_id}`.

The Dashboard's `Needs you` panel reads the same predicate this list does — `MatchCandidate::where('status', 'Pending')->count()` — so the number beside the door and the list behind it cannot disagree.

---

## 4. Detail

Props: `candidate` (`{id, proposed_name, proposed_name_ja, match_tier, match_tier_label, source_key, created_at, suggestion, suggested_umamusume_id, status}`), the suggested row when one exists (`id`, `slug`, `name`, `name_ja`, `costume`), and `otherFormsOf()` — up to ten other catalogue forms of the same base name.

Two helpers make disambiguation possible:

- `costumeOf($name)` extracts a bracketed costume prefix (`[Rosy Dreams] Rice Shower` → `Rosy Dreams`) so two forms of one trainee are distinguishable by the piece that differs.
- `otherFormsOf($suggested)` strips the costume prefix and searches the base name, ordered by name, limited to 10. This is why a `Fuzzy` candidate for one costume can be ruled on against its siblings.

---

## 5. Resolving

`ResolveMatchCandidateRequest` accepts `status` (`Confirmed|Aliased|Rejected|Pending`), optional `umamusume_id`, optional `alias_language`.

`ResolveMatchCandidate::handle()` runs the whole verdict in one transaction:

| Status | Effect |
| --- | --- |
| `Confirmed` | `PromoteMatchedRecord::handle(payload, existing: $target, sourceKey, url)` — promotes into the suggested row when there is one, creating a row otherwise |
| `Aliased` | records the proposed name as an `UmamusumeAlias` of the target, with the alias language |
| `Rejected` | closes the candidate; no catalogue change |
| `Pending` | no-op (a re-open through the resolve route) |

The target is `umamusume_id` from the request when present, else `candidate->suggestedUmamusume`. The candidate's `status` is set and saved after the effect, inside the same transaction, so a partial promotion cannot leave the queue claiming a result it did not achieve.

`payload['url']` falls back to the literal `resolved-from-review-queue` when the stored payload carries none — the provenance row still names where the fact came from.

---

## 6. Reopening

`POST /review/{candidate}/reopen` sets `status = Pending` and returns to the index with `Candidate returned to Pending.` It is the reversal path (ReviewDetail-F-01, SHR-6 / H3) — a wrong verdict is recoverable without editing the database.

Reopening does **not** undo a promotion. A `Confirmed` candidate that already wrote catalogue rows stays written; the re-opened candidate can be re-ruled, but the earlier promotion is the record of what happened.

---

## 7. Boundaries

- The queue is pipeline-facing: it exists because the matcher proposed a name, so every row names its `source_key`.
- Promotion respects `is_manual = true` (the Floor) — a hand-corrected row is never overwritten.
- Every promoted fact writes one `data_sources` provenance row, inside the transaction.
- Nothing in the queue is cached; the list is small by construction.

---

## 8. Verification surface

| Claim | Pinned by |
| --- | --- |
| Index, detail payload, resolve, reopen | `ReviewQueueTest`, `ReviewDetailPayloadTest` |
| Form accessibility | `ReviewFormAccessibilityTest` |
| Matcher tiers feeding the queue | `CrossReferenceMatcherTest`, `FetchPipelineTest` |
| Promotion and provenance | `FetchPipelineTest`, `StoreActionBatchAtomicityTest` |
| Browser | `tests/browser/review-detail.spec.ts` |
