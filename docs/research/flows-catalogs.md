# Flows — Catalogs (read-only)

Date: 2026-11-06. HEAD: `67a4830`. Documentation only; no code changed.
Evidence: `app/Http/Controllers/{CatalogController,SkillController,SupportCardController}.php`, `app/Http/Requests/{CatalogSearchRequest,SkillSearchRequest,SupportCardSearchRequest}.php`, `app/Services/PageSize.php`, `resources/js/pages/{Catalog,Skills,SupportCards}/**`.

Three read-only catalogue surfaces. None writes anything; the only mutating door from any of them is the trainee "New run" action, which posts to the wizard's draft.

---

## 1. Trainee catalog

### 1.1 Routes

| Method | URI | Name | Controller |
| --- | --- | --- | --- |
| GET | `/umamusume` | `catalog.index` | `CatalogController::index` |
| GET | `/umamusume/{slug}` | `catalog.show` | `CatalogController::show` |
| GET | `/database/trainees` | `database.trainees` | `CatalogController::databaseIndex` |

`index()` and `databaseIndex()` return the **same** props and differ only in page name and the path the pager and filter form write back to (`route('catalog.index')` vs `route('database.trainees')`). A redirect was the smaller diff and is refused by the slice brief: no owner ruling authorises borrowing the URL, so the body is shared instead.

### 1.2 Filters (`CatalogSearchRequest`)

`status` (`ReleaseStatus` cases), `surface` (`turf|dirt`), `distance` (`sprint|mile|medium|long`), `style` (`front_runner|pace_chaser|late_surger|end_closer`), `search`, `pageSize`.

Search normalises through `NameNormalizer` and matches `match_key` — never a display string (**the Floor**).

### 1.3 Rows

Each roster row carries `{id, slug, name, name_ja, release_status, release_status_label, max_rarity{label,stars}, default_form{title,rarity_label,rarity_stars,rarity_word,is_debut_form}, aptitude_summary, form_count, cards[]}`.

- `max_rarity` is computed across the trainee's forms, not stored.
- `default_form` is the chosen form when the run had one, else the debut/first card.
- `cards[]` items carry `{id, title, rarity_label, rarity_stars, is_debut_form, unconfirmed, global_release_date, global_release_date_display}`.

### 1.4 Paging and caching

- `PageSize::clamp($request->query('pageSize'), 6)` — the trainee roster's own default is 6, the floor and ceiling are the shared 1..100.
- `cached()` wraps the id query behind `catalog:version` and keys on status, search key, page, page size, unconfirmed flag, surface, distance and style. The cache TTL and the counter are owned by `config('uma.cache')`; promotion bumps `catalog:version`.

### 1.5 Detail

Props include `carry_url` (`career.trainee.carry`), a `trainee` block (`id`, `slug`, `name`, `name_ja`, `release_status_label`, `is_japan_only`, `japanese_name`, `jp_debut_date`), the four skill lists (`skills_unique`, `skills_innate`, `skills_awakening`, `skills_event`) each with a named absence, the trainee's objective ladder (`ADR-0026`, `TraineeGoal`), and artwork through `ArtworkSlot.vue` + the loopback `artwork.show` route.

**Copy defect (R6-22):** the profile heads a section `Scenarios this trainee is linked to` and prints the config's whole scenario set for every trainee; its own note on the same screen says `No per-trainee link list is carried on this payload, so the scenarios are shown as available rather than as links.`

---

## 2. Skills catalog

### 2.1 Routes

| Method | URI | Name | Controller |
| --- | --- | --- | --- |
| GET | `/skills` | `skills.index` | `SkillController::index` |
| GET | `/skills/{skill}` | `skills.show` | `SkillController::show` |
| GET | `/database/skills` | `database.skills` | `SkillController::databaseIndex` |

### 2.2 Scope — the important axis

Three scopes, offered in the select in this order (`SCOPES`):

| Scope | Meaning |
| --- | --- |
| `global` (default) — label `Global only` | `Skill::availableOnGlobal()` — the `[Global]` subset (`ADR-0011` §2) |
| `all` — label `Every skill` | the full catalogue |
| `japan_only` — label `Japan only` | rows the Global client has not received |

No row can reach the default screen that `availableOnGlobal()` rejects. At `japan_only` the catalogue prints the source's own Japanese string and no English name or description (Ruling A) — an invented English gloss would be a translation the tool does not have.

### 2.3 Filters (`SkillSearchRequest`)

`search` (max 120), `type` (`GametoraSkillsParser::CATEGORIES` plus `Unspecified`), `unique` (boolean), `distance` (`0..4`), `scope`, `pageSize`.

`undefined`/`Unspecified` is a real category: the source publishes rows with no category, and hiding them would make the counts disagree with the table.

### 2.4 Rows and detail

List rows carry `{id, name, name_ja, description, release_status, is_unique, type, sp_cost, conditions[]}`; `description` passes through the row's `displayDescription()` gate rather than being read raw.

Detail merges: `skill`, four holder sets (`uniqueHolders`, `innateHolders`, `awakeningHolders`, `eventHolders`), `catalogueCount`, `hintLadder` (`HintDiscount::priceLadder($sp_cost)` plus `measuredAt`), and `related` (`sameCondition` counts). The ladder's caption strings come from `HintDiscount::ladder()` (`Hint Lvl 1` … `Hint Lvl Max`), which is exactly the source the planner's duplicated prefix fails to reconcile (R6-15).

### 2.5 Paging

`$perPage = min(100, max(1, (int) ($request->query('pageSize') ?? 25)))` — inline rather than through `PageSize`, behaviourally identical.

---

## 3. Support cards

### 3.1 Routes

| Method | URI | Name | Controller |
| --- | --- | --- | --- |
| GET | `/support-cards` | `support-cards.index` | `SupportCardController::index` |
| GET | `/support-cards/{card}` | `support-cards.show` | `SupportCardController::show` |
| GET | `/database/supports` | `database.supports` | `SupportCardController::databaseIndex` |

`SUPPORT_CARDS_PER_PAGE = 25`, so the two index routes share a page size and the Database door matches the catalogue door.

### 3.2 Filters (`SupportCardSearchRequest`)

`q` (max 120), `rarity`, `type` (`SupportCard::TYPES`), `status` (`SupportCard::AVAILABILITIES`), `server` (`Global|JP`), `sort` (`name|type|rarity|released|friendship_bonus|initial_friendship_gauge`), `trainee` (max 80), `effects` (list of ints), `used_in_deck` (bool), `owned` (bool), `pageSize`.

- `used_in_deck` and `owned` are **calculated** filters, not stored flags: `owned` derives from `deck_slots.ownership` across saved runs, which is why the control's own label carries `(Calculated)`.
- `effects` is a multi-select over the effect dictionary; the page serialises it as a comma string in the URL and the request parses it back to a list.

### 3.3 Compare

The index supports selecting up to **3** cards, then opens `CardCompare.vue`:

- Rows are the union of every selected card's effect ids, ordered by first appearance in the first card that carries them.
- Cells print each card's stated anchors and its cap (`highest stated anchor`), never an interpolation.
- A card missing an effect prints `Not recorded`, not `N/A` — the difference between "this card does not carry the effect" and "the value is unknown".
- The panel carries `role="dialog"` + `aria-labelledby`, no `aria-modal` (the background is not inert), Escape closes it, and the Close button is `min-h-11 min-w-11` and autofocused (AUD-20261105-001 F-04).
- Selection cap copy: `3 maximum — deselect one to add another`.

### 3.4 Detail

Effect tables reuse the same anchor rendering the compare panel borrows. `SupportCardEffects::stages()` emits `{effect_id, name, anchors[], cap}`; `SupportCardEffects::atLevel()` interpolates a reached value for the deck analysis, and that value is tagged `basis: 'catalog'` or `basis: 'level'` so a reader can tell a published figure from a computed one.

### 3.5 Known count mismatch (R6-24)

The Dashboard prints `SupportCard::count()` (the whole catalogue, 559 rows on the audited database) while the deck picker filters `whereNotNull('release_global')` (251 rows on the same database). Nothing on either screen reconciles the two, and the picker list is unpaginated — one scroll container over the whole filtered set.

---

## 4. Artwork

Every card frame is `ArtworkSlot.vue` fed a **server-composed loopback URL** (`artwork.show`, registered in `bootstrap/app.php` outside the `web` group so an asset request cannot age a redirect's flash — R3-31). Files come from the manual `uma:fetch-art` mirror; an unrun mirror renders nothing and no error (`DESIGN.md` §4.7).

A miss from the asset host returns 27,150 bytes of `text/html` at HTTP 404, so `SourceFetcher::fetchAsset()` returns null on any non-2xx and the mirror refuses to store the body. "Bytes came back" is never the test.

---

## 5. Shared conventions

| Convention | Where |
| --- | --- |
| Search matches `match_key`, never a display string | all three controllers |
| `pageSize` clamped 1..100, never refused (`ADR-0018`) | `PageSize` or the inline equivalent |
| Empty, loading and error states on every data view | `ADR-0007` |
| Unrecorded values print `N/A` with a `title`, never a default or a dash | `AGENTS.md` §5 |
| No identifier printed in place of a label | `UiHygieneTest` |
| No scenario name hardcoded in a page | `config/scenarios.php` is the only source |

---

## 6. Verification surface

| Area | Tests |
| --- | --- |
| Trainee roster and detail | `CatalogTest`, `CatalogRosterTreeTest`, `CatalogDetailPageTest`, `CatalogIndexPortraitTest`, `CatalogDetailArtworkSlotTest`, `CatalogSkillListsTest`, `CatalogCacheRenderTest`, `CatalogVersionCounterTest` |
| Skills list and detail | `SkillSearchScreenTest`, `SkillDetailTest`, `SkillScopeFilterTest`, `SkillComparisonTest`, `SkillPoolNonLeakageTest`, `SkillPoolNonLeakageGuardTest`, `RangeOperatorCountTest` |
| Support cards | `SupportCardTest`, `SupportCardPageTest`, `SupportCardCompareTest`, `SupportCardOwnedFilterTest`, `SupportCardEffectVocabularyTest`, `SupportCardsArtworkSlotTest` |
| Paging contract | `FilterContractTest`, `ApiV1RunIndexPaginationTest` |
| Browser | `tests/browser/support-cards.spec.ts`, `skills.spec.ts`, `catalog*.spec.ts` |
