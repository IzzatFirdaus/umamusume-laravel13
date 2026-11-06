# Flows — JSON API (`/api/v1`, read-only)

Date: 2026-11-06. HEAD: `67a4830`. Documentation only; no code changed.
Evidence: `routes/api.php`, `app/Http/Controllers/Api/V1/*.php`, `app/Http/Resources/*.php`, `app/Services/PageSize.php`, `tests/Feature/ApiV1*.php`.

A read-only JSON surface. Every endpoint is `GET`; the write side lives entirely in the web UI.

---

## 1. Routes

| Method | URI | Name | Controller |
| --- | --- | --- | --- |
| GET | `/api/v1/umamusume` | `api.v1.umamusume.index` | `Api\V1\UmamusumeController::index` |
| GET | `/api/v1/umamusume/{slug}` | `api.v1.umamusume.show` | `Api\V1\UmamusumeController::show` |
| GET | `/api/v1/training-runs` | `api.v1.training-runs.index` | `Api\V1\TrainingRunController::index` |
| GET | `/api/v1/training-runs/{run}` | `api.v1.training-runs.show` | `Api\V1\TrainingRunController::show` |
| GET | `/api/v1/support-cards` | `api.v1.support-cards.index` | `Api\V1\SupportCardController::index` |
| GET | `/api/v1/support-cards/{supportCard}` | `api.v1.support-cards.show` | `Api\V1\SupportCardController::show` |

The whole group is `prefix('v1')->name('api.v1.')`. There is no `/api/v1` write verb anywhere.

---

## 2. Response envelope

List responses:

```json
{
  "data": [ ... ],
  "pagination": { "page": 1, "pageSize": 25, "totalItems": 190, "totalPages": 8 }
}
```

Detail responses: `{"data": { ... }}`.

Errors: `{"error": {"code": "...", "message": "..."}}`. The shape is applied **centrally** in `bootstrap/app.php` (`:46-88`) for every non-2xx response on this prefix, with four codes:

| Situation | `code` | Status |
| --- | --- | --- |
| Validation failure | `VALIDATION_ERROR` (message = the first field error) | 422 |
| Not found (`NotFoundHttpException`, `ModelNotFoundException`) | `NOT_FOUND` | 404 |
| Any other `HttpException` | `HTTP_ERROR` | the exception's own status |
| Unexpected throwable | `INTERNAL` (`Internal error. Check application logs.`) | 500 |

A controller therefore never hand-builds an error body; the one exception is `UmamusumeController::show()`, which answers a missing slug itself:

```json
{"error": {"code": "NOT_FOUND", "message": "No umamusume with slug 'xyz'."}}
```

with HTTP 404 — a message naming the slug it could not resolve.

---

## 3. Paging and validation rules

- `pageSize` is **clamped**, never refused: `min(100, max(1, ...))`, default 25. `ApiV1ValidationEnvelopeTest` states the rule for the whole prefix — a nonsense size is a smaller or bigger page, not a rejected request, because the value usually arrives from a URL a Trainer pasted or a tab restored and the filter they were working with is worth more than the argument.
- The web surfaces share the same rule through `PageSize::clamp()` (`ADR-0018`); the API controllers carry the inline equivalent.
- Query parameters are read defensively: an unrecognised enum value is dropped (`ReleaseStatus::tryFrom(...)?->value`), not rejected.

---

## 4. Per-resource notes

### 4.1 Umamusume

- List filters: `status` (`ReleaseStatus`), `search`. `search` normalises through `NameNormalizer` and matches `match_key` — never a display string.
- Ordering: `name`, then `id`.
- Eager loads `aliases`.
- Detail loads `aliases` and `dataSources` (latest `fetched_at` first), so provenance travels with the record.

### 4.2 Training runs

- List: `TrainingRunResource::collection` over the newest `id` first.
- Detail: eager loads `umamusume`, `turnEntries`, `skills`, `deckSlots.supportCard` — the run, its history, its skill states and its deck in one read.

### 4.3 Support cards

- List and detail mirror the web catalogue's own projections. Detail resolves through the model binding `{supportCard}`.

---

## 5. What the API is not

- It is not an auth surface. There is no token, no session check, no rate limiting beyond the loopback bind (`PRD.md` §6.10, `NFR-1`). The app is local-only and must not be exposed.
- It is not a write path. Nothing under `/api/v1` accepts `POST`, `PUT`, `PATCH` or `DELETE`.
- It is not a career-entry route. Runs are recorded through the web screens; the API reads what those wrote.
- It is not versioned by content negotiation. `v1` is a path segment and the contract is the envelope above.

---

## 6. Verification surface

| Claim | Pinned by |
| --- | --- |
| Envelope shape, both list and detail | `ApiV1Test` |
| Paging clamp and defaults | `ApiV1RunIndexPaginationTest`, `ApiV1ValidationEnvelopeTest` |
| Error envelope on non-2xx | `ApiV1ValidationEnvelopeTest` |
| Support-card projections | `ApiV1SupportCardTest` |
| Route inventory | `RouteCoverageTest` |
