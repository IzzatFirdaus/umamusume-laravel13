# ADR-0018: One facet contract for the three filter surfaces, and one page-size rule

<!-- ADR-CONTRACT
  decision: accepted (owner dispatch 2026-10-04) — an unknown facet value is refused and lands on the canonical URL; pageSize is honoured and clamped on all three surfaces
  code: built — App\Services\PageSize::clamp(); CatalogSearchRequest, SkillSearchRequest, SupportCardSearchRequest; tests/Feature/FilterContractTest.php
  superseded-by: —
  amended-by: 0011 (the skill-type vocabulary this screen filters on), 0014 (the support-card availability column)
  open: three copies of the page-size clamp remain outside PageSize — SkillController and the three /api/v1 controllers — and their numerical equality is stated once rather than enforced
-->

Status: **Accepted** (owner dispatch, 2026-10-04). Resolves `SCREEN_SPEC.md` §7-9, which recorded the
inconsistency and said the choice was owed.

## Decision

**An unknown facet value is refused, and the refusal lands on the canonical URL for that screen.** Each
surface keeps its own accepted vocabulary and validates against it: `ReleaseStatus::cases()` plus the
catalog's own `all` token; `GametoraSkillsParser::CATEGORIES` plus `Unspecified`; `SupportCard::TYPES`,
`SupportCard::AVAILABILITIES`, `CardRarity` values and `SORTS`. The vocabulary stays per-surface because it
*is* per-surface, and each list is read off the constant the schema also reads rather than restated here.

**`pageSize` is honoured and clamped on all three, never refused.** The rule lives in one place,
`App\Services\PageSize::clamp()`, with `MIN` 1, `MAX` 100, `DEFAULT` 25.

Both halves are pinned together in `tests/Feature/FilterContractTest.php`.

## Why the two previous choices differed, and what each one was actually defending

The catalog's ignore was not carelessness. `CatalogController::index()`'s own docblock said "Unknown status
values are ignored rather than erroring, and `status=all` is the way back to the unfiltered list", and
`SCREEN_SPEC.md` recorded it as a documented deviation. The defence was that a URL is not a form: the picker
can only submit one of four values, so anything else came from a hand-typed or stale address, and a page
that errors at a typed address punishes a Trainer for a typo. That reasoning holds for the *empty* value,
which is why `status=` is still the no-filter default and not an error. It does not hold for a value that
names a plausible-looking status that does not exist: the Trainer gets the default list, the select shows a
selection they never made, and the page looks filtered by something they asked for. `status=Bogus` and
`status=` are different questions and were getting the same answer.

Skills and support cards already refused, and the reasoning between them diverged on where to send the
refusal. `SupportCardSearchRequest` names its route and documents why: the default `back()` with no referer
resolves to `/` and sends the Trainer to the run list from a filter they typed on the card catalog.
`SkillSearchRequest` left it at the default, so its redirect resolved to the referer in a browser and to `/`
under test. The named route wins on the argument, not the precedent: a refusal must land where the field
error can be rendered beside the picker that caused it, and `previous()` cannot promise that.

## Alternatives considered

- **Ignore everywhere.** Cheapest, and rejected on the same ground as the catalog's own case: a filter that
  silently answers with the default is a filter that lies.
- **Refuse `pageSize` too.** Consistent-looking and rejected. A page size is a display argument, not a
  statement about the data, and it most often arrives from a pasted or restored URL. `ApiV1ValidationEnvelopeTest`
  already states the rule for the API ("clamped rather than validated, so a nonsense value is a smaller
  page"), and the skill search pages with `pageSize=3` on a URL-built link. Refusing it would turn a display
  preference into an error page.
- **Redirect to the current URL minus the bad facet, keeping the other keys.** Tempting, since the views
  already build in-page links with `request()->fullUrlWithQuery([...])`. Rejected because the landing page
  must be one the form can produce: the select has no `Bogus` option, so a URL that keeps the other keys
  renders a control showing a value that is not in its own list. The canonical route is the state the picker
  can actually be in.
- **A base `FormRequest` shared by the three requests.** Rejected in favour of the smaller shape. The
  requests agree on `authorize()` and on folding empty strings to null, and disagree on everything that
  matters (which facets, which vocabulary, which route). What was genuinely shared, the arithmetic, is a
  static service in `app/Services/`, the same shape as `ScenarioCaps` and `SupportCardEffects`, which
  `SCREEN_SPEC.md` §8 already names as the one ceiling arithmetic owner shared by form, band and preview.
- **Fold the whole catalog query into the new request.** Partly taken. `CatalogSearchRequest` now owns
  `status`, which is the boundary `AGENTS.md` asked for on a surface that had none. `search`, `page` and
  `show_unconfirmed` are still read from the request object in the controller: they are not facets, and
  moving them would turn this ADR into a refactor of the cache key builder, which embeds the raw status and
  page size and is separately pinned.

## Consequences

- `CatalogController::index()` returns `View|RedirectResponse`, and its docblock's claim that unknown statuses
  are ignored is replaced. `SCREEN_SPEC.md` SCR-CAT-001 and §6 carry the same sentence and are updated with it.
- `resources/views/catalog/index.blade.php` gains the `@error('status')` line it never had, because a refusal
  with nowhere to show is worse than the silence it replaced.
- `/support-cards` now honours `?pageSize=`; its `PER_PAGE` constant is gone and the default is the same 25
  the constant carried.
- **Three copies of the clamp are intentionally not moved**: `SkillController`, because that file carries
  another session's uncommitted work and committing it here would land their change under this message; and
  the three `/api/v1` controllers, because §7-9 is about the filter surfaces and the API's envelope is a
  separate contract. All four are numerically identical to `PageSize` today, and that equality is now stated
  once, so the drift is visible to whoever picks it up.
- `status=all` is a catalog sentinel rather than a `ReleaseStatus` case. `CatalogRosterTreeTest` has two cases
  that depend on it, which is the evidence that it is load-bearing and not an accident.

## Links

- `SCREEN_SPEC.md` §7-9, §8, §6 and SCR-CAT-001; `AGENTS.md` (Form Request classes, never inline validation
  in controllers)
- `App\Services\PageSize`; `CatalogSearchRequest`, `SkillSearchRequest`, `SupportCardSearchRequest`
- `tests/Feature/FilterContractTest.php`, `tests/Feature/SkillSearchScreenTest.php`,
  `tests/Feature/CatalogRosterTreeTest.php`, `tests/Feature/SupportCardPageTest.php`,
  `tests/Feature/CatalogCacheRenderTest.php`, `tests/Feature/ApiV1ValidationEnvelopeTest.php`
- `ADR-0011` (the skill-type derivation), `ADR-0014` (the availability column), `ADR-0022` (the all-of
  narrowing the tag filter keeps)
