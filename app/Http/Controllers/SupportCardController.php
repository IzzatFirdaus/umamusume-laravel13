<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CardRarity;
use App\Http\Requests\SupportCardSearchRequest;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Models\Umamusume;
use App\Services\DataPipeline\ArtworkMirror;
use App\Services\PageSize;
use App\Services\SupportCardEffects;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The support-card catalog, `/support-cards` and `/support-cards/{card}` (PRD FR-B; ADR-0014).
 *
 * Read path only. The engine writes `support_cards` and a Trainer writes `deck_slots`; nothing here
 * writes.
 *
 * **Unlike Screen D this screen does not filter to `[Global]`.** All 559 published cards import,
 * including the 308 Global has not received, because `release_status` is a stored generated column whose
 * whole purpose is to state availability per row (see `GametoraSupportCardParser`). Pre-filtering here
 * would put a second, silent copy of that decision on the read side and leave the column with nothing to
 * say. Availability is therefore a facet a Trainer can pick, not a condition applied to them.
 *
 * **No cache**, for the reason `SkillController` records: `catalog:version` is bumped by promotion and
 * support cards bypass `match_candidates`, so a cached page here would keep answering after a re-fetch.
 * The table is 559 rows and the effect dictionary is 35.
 */
class SupportCardController extends Controller
{
    public function index(SupportCardSearchRequest $request): Response
    {
        $rarity = $request->validated('rarity');
        $type = $request->validated('type');
        $status = $request->validated('status');
        $sort = $request->validated('sort');

        // Read once for the page, not once per row: twenty-five cards ask the same 35 dictionary rows
        // the same question, which is the N+1 the deck panel avoids the same way.
        $dictionary = SupportCardEffects::dictionary();

        // §7-9 chose one page-size rule for all three surfaces over this screen's fixed constant,
        // so `?pageSize=` honours and clamps here as it does on the catalog. The default is the
        // number the constant carried, 25, so a filter nobody touched looks the same as before.
        $cards = $this->ordered($this->filtered($rarity, $type, $status), $sort)
            ->paginate(PageSize::clamp($request->query('pageSize')))
            ->withQueryString()
            ->through(static fn (SupportCard $card): array => [
                'id' => $card->id,
                'name' => $card->displayName(),
                'url' => route('support-cards.show', $card),
                // The row's thumbnail slot (`ADR-0021` read half, `design-2.0` §45a "Support-card
                // index, card row"). `support_id` is the publisher's number and the only key the
                // mirror's storage path answers to; the `url` above is built from the local primary
                // key, because `show()` binds on that. The two must not be interchanged: a swapped
                // `support-id` finds no file and the slot silently disappears, which reads as "the
                // pictures never arrived" rather than as a bug. A null is the mirror's normal
                // partial answer and the page renders no frame for it (`DESIGN.md` §4.7).
                'artworkURL' => app(ArtworkMirror::class)->url('support_thumb', (int) $card->support_id),
                'rarity_label' => $card->rarity->label(),
                'rarity_stars' => $card->rarity->stars(),
                'rarity_word' => $card->rarityWord(),
                'type_label' => $card->typeLabel(),
                'release_status' => $card->release_status,
                'effects' => SupportCardEffects::atCap($card, $dictionary),
            ]);

        return Inertia::render('SupportCards/Index', [
            'cards' => $cards,
            'rarity' => $rarity,
            'type' => $type,
            'status' => $status,
            'sort' => $sort,
            'rarityWords' => $this->rarityWords(),
            'typeWords' => $this->typeWords(),
            'availabilities' => SupportCard::AVAILABILITIES,
            'sorts' => $this->sortLabels(),
            'totalCount' => SupportCard::query()->count(),
            'askedFor' => $this->describeAsk($rarity, $type, $status),
        ]);
    }

    /**
     * One card's own page: the fields the source states, its effects at cap, the two skill lists it
     * publishes, the trainee its `char_id` resolves to, and its provenance.
     *
     * The parameter is the local `support_cards.id`, the key a row on this tree can name. The source's
     * own `support_id` is not a route key: nothing in the UI prints it, and a URL that changed meaning
     * when the publisher renumbered would be a URL that breaks.
     */
    public function show(SupportCard $card): Response
    {
        $dictionary = SupportCardEffects::dictionary();
        $trainee = $this->trainee($card);

        return Inertia::render('SupportCards/Show', [
            'card' => [
                'id' => $card->id,
                'name' => $card->displayName(),
                // The header thumbnail (`design-2.0` §45a "Support-card detail, header"), keyed on
                // `support_id` for the same reason the index row's is: it is the publisher's number
                // and the only key the mirror's storage path answers to, while the route binds on the
                // local `id`.
                'artworkURL' => app(ArtworkMirror::class)->url('support_thumb', (int) $card->support_id),
                'name_ja' => $card->name_ja,
                'title_ja' => $card->title_ja,
                'rarity_label' => $card->rarity->label(),
                'rarity_stars' => $card->rarity->stars(),
                'rarity_word' => $card->rarityWord(),
                'type_label' => $card->typeLabel(),
                'release_status' => $card->release_status,
                // A calendar date stays a calendar date: converting it through the display timezone
                // would move a card released on the 24th to the 24th at 09:00 and, on the wrong side
                // of midnight, to a day the source never stated.
                'release_jp_display' => $card->release_jp?->format('M j, Y'),
                'release_global_display' => $card->release_global?->format('M j, Y'),
                'char_name' => $card->char_name,
                'source_url' => $card->source_url,
                'fetched_at_display' => $card->fetched_at->timezone(config('uma.display_timezone'))->format('M j, Y'),
                'is_manual' => $card->is_manual,
            ],
            'effects' => SupportCardEffects::atCap($card, $dictionary),
            'hinted' => $this->skillList($card->hint_skills),
            'events' => $this->skillList($card->event_skills),
            'trainee' => $trainee === null ? null : [
                'name' => $trainee->name,
                'url' => route('catalog.show', $trainee->slug),
            ],
        ]);
    }

    /**
     * What the Trainer narrowed the catalog by, for the no-results state to name the ask instead of
     * repeating a query back at them. An empty set of conditions cannot reach that state: with rows in
     * the table and no filter, the first page has rows.
     */
    private function describeAsk(?string $rarity, ?string $type, ?string $status): string
    {
        $parts = array_filter([
            $rarity === null ? null : 'rarity '.CardRarity::from((int) $rarity)->word(),
            $type === null ? null : "type {$type}",
            $status === null ? null : "availability {$status}",
        ]);

        return $parts === [] ? 'those conditions' : implode(' + ', $parts);
    }

    /**
     * The rarity facet's words, keyed by the value the query string carries, so the picker offers the
     * client's R / SR / SSR and not the enum's "Three Star" (`CardRarity::word()`).
     *
     * The keys are `int` and not `string`: PHP coerces a numeric string used as an array key, so a map
     * built over `1` / `2` / `3` cannot keep them as text. The view casts on the way back rather than
     * comparing an int to the query string's `'3'`, which would silently select nothing.
     *
     * @return array<int, string>
     */
    private function rarityWords(): array
    {
        $words = [];

        foreach (CardRarity::cases() as $case) {
            $words[(string) $case->value] = $case->word();
        }

        return $words;
    }

    /**
     * The type facet's words, keyed by the export key the column stores, so the picker offers Wit and
     * Pal rather than `intelligence` and `friend` (`SupportCard::typeWord()`).
     *
     * @return array<string, string>
     */
    private function typeWords(): array
    {
        return array_combine(
            SupportCard::TYPES,
            array_map(static fn (string $type): string => SupportCard::typeWord($type), SupportCard::TYPES)
        );
    }

    /**
     * The ordering facet's labels, keyed by the token the query string carries.
     *
     * The `match` is deliberately not exhaustive-with-default: a token added to
     * `SupportCardSearchRequest::SORTS` without a label here throws, which is the loud failure the two
     * lists need, since a picker that cannot name an ordering it accepts is a control that misreads
     * itself. Same structural agreement `GametoraSupportCardParser` keeps with `SupportCard::TYPES`.
     *
     * @return array<string, string>
     */
    private function sortLabels(): array
    {
        return array_combine(
            SupportCardSearchRequest::SORTS,
            array_map(
                static fn (string $sort): string => match ($sort) {
                    'rarity' => 'Rarity',
                    'released' => 'Release date',
                },
                SupportCardSearchRequest::SORTS
            )
        );
    }

    /**
     * @return Builder<SupportCard>
     */
    private function filtered(?string $rarity, ?string $type, ?string $status): Builder
    {
        return SupportCard::query()
            ->when($rarity !== null, fn (Builder $q): Builder => $q->where('rarity', (int) $rarity))
            ->when($type !== null, fn (Builder $q): Builder => $q->where('type', $type))
            ->when($status !== null, fn (Builder $q): Builder => $q->where('release_status', $status));
    }

    /**
     * The chosen ordering, with the label a Trainer reads as the tiebreaker in every case.
     *
     * A tiebreaker is not cosmetic: two cards of the same rarity have no defined order without one, and
     * a pagination link that reshuffles rows between pages loses cards the Trainer never saw.
     *
     * `released` orders on `release_global` descending, so the newest card Global has is first and a
     * card Global has not received sorts last rather than appearing brand new — SQLite ranks NULL below
     * every value, and descending puts the nulls at the end.
     *
     * @param  Builder<SupportCard>  $query
     * @return Builder<SupportCard>
     */
    private function ordered(Builder $query, ?string $sort): Builder
    {
        return (match ($sort) {
            'rarity' => $query->orderBy('rarity'),
            'released' => $query->orderByDesc('release_global'),
            default => $query,
        })->orderBy('char_name')->orderBy('title_en');
    }

    /**
     * One published skill list, resolved to the rows this catalog can actually link.
     *
     * **An id that does not resolve is counted, not dropped.** The list is the source's fact; a Trainer
     * who counts seven hints on the card face and sees five here has been told a lie by silence. The
     * read starts from `availableOnGlobal()` because the detail route refuses a row it rejects, so a link
     * to a JP-only or third-party-named skill would be a link that 404s (ADR-0011 §2). Measured on the
     * committed bodies, that is 771 of 3,971 hint ids and 350 of 1,528 event ids.
     *
     * The source's own order is kept, because a card's hint list is the order the game prints it in.
     *
     * @param  array<int, int>|null  $ids  null is "no list stored", which is not an empty list
     * @return array{stored: bool, skills: list<array{name: string, sp_cost: int|null, is_unique: bool, url: string}>, unlinked: int}
     */
    private function skillList(?array $ids): array
    {
        if ($ids === null) {
            return ['stored' => false, 'skills' => [], 'unlinked' => 0];
        }

        $resolved = $ids === []
            ? collect()
            : Skill::query()
                ->availableOnGlobal()
                ->whereIn('export_id', $ids)
                ->get(['id', 'export_id', 'name', 'is_unique', 'sp_cost'])
                ->keyBy('export_id');

        $skills = [];
        $unlinked = 0;

        foreach ($ids as $id) {
            $skill = $resolved->get($id);

            if ($skill === null) {
                $unlinked++;

                continue;
            }

            $skills[] = [
                'name' => $skill->name,
                'sp_cost' => $skill->sp_cost,
                'is_unique' => $skill->is_unique,
                'url' => route('skills.show', $skill),
            ];
        }

        return ['stored' => true, 'skills' => $skills, 'unlinked' => $unlinked];
    }

    /**
     * The trainee this card's character id names, or null.
     *
     * `char_id` is deliberately not a foreign key (migration `2026_09_30_151945` point 2): it addresses
     * GameTora's character space, which includes the 9000-block staff who have no trainee row at all.
     * The join is on `umamusume.external_ref`, the column the character parser writes as
     * `gametora:char:{id}`, so the two documents meet on the publisher's own number rather than on a
     * name that could be spelled differently in each. The join misses two ways: the 9000-block staff have
     * no trainee row at all, and a card can name a trainee this catalog does not track. Measured
     * 2026-10-03 against the working database, 322 of the 559 published cards resolve and 237 do not. The
     * second figure moves with the roster, so the view names the absence instead of printing a count.
     */
    private function trainee(SupportCard $card): ?Umamusume
    {
        if ($card->char_id === null) {
            return null;
        }

        return Umamusume::query()
            ->where('external_ref', 'gametora:char:'.$card->char_id)
            ->first(['id', 'slug', 'name']);
    }
}
