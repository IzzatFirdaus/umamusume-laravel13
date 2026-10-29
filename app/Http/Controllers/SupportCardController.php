<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CardRarity;
use App\Http\Requests\SupportCardSearchRequest;
use App\Models\DeckSlot;
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
        // Five rows: the wireframe's page size (`support-cards.html:86-141` draws five), a page a
        // Trainer can compare without scrolling. `?pageSize=` still wins, so the old 25-row view is a
        // URL away.
        return $this->renderPage('SupportCards/Index', $this->indexProps($request, 5));
    }

    /**
     * The same support-card catalog as the Database hub's Supports area (SCREEN-023, plan §8 D17).
     * Same query, same row mapper, shared rather than redirected: the slice brief refuses a redirect
     * here because no owner ruling authorises one. The pager keeps the request's own path.
     */
    public function databaseIndex(SupportCardSearchRequest $request): Response
    {
        return $this->renderPage('Database/Supports', $this->indexProps($request));
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function renderPage(string $page, array $props): Response
    {
        return Inertia::render($page, $props);
    }

    /**
     * The catalog page's data, shared by `index()` and `databaseIndex()`.
     *
     * @return array<string, mixed>
     */
    private function indexProps(SupportCardSearchRequest $request, int $perPage = PageSize::DEFAULT): array
    {
        $q = $request->validated('q');
        $rarity = $request->validated('rarity');
        $type = $request->validated('type');
        $status = $request->validated('status');
        // The wireframe scopes the library to one server and defaults to Global
        // (`support-cards.html:76`: a Global/JP select with Global selected), so the default view is
        // the Global catalogue rather than every row the export holds.
        // Availability and server state the same fact one way or the other: `JP-only` is a card the
        // export has not marked released, which is what the `JP` server scope selects. A status given
        // without a server therefore names the server, so `?status=JP-only` answers the card it names
        // instead of returning nothing. An explicit `server` still wins, and `Unreleased` names no
        // server, so it keeps the wireframe's Global default.
        $server = $request->validated('server')
            ?? match ($status) {
                'JP-only' => 'JP',
                default => SupportCardSearchRequest::SERVERS[0],
            };
        $sort = $request->validated('sort');

        // Read once for the page, not once per row: twenty-five cards ask the same 35 dictionary rows
        // the same question, which is the N+1 the deck panel avoids the same way.
        $dictionary = SupportCardEffects::dictionary();

        // §7-9 chose one page-size rule for all three surfaces over this screen's fixed constant,
        // so `?pageSize=` honours and clamps here as it does on the catalog.
        $cards = $this->ordered($this->filtered($q, $rarity, $type, $status, $server), $sort)
            ->paginate(PageSize::clamp($request->query('pageSize'), $perPage))
            ->withQueryString()
            ->through(function (SupportCard $card) use ($dictionary): array {
                $effects = SupportCardEffects::atCap($card, $dictionary);

                return [
                    'id' => $card->id,
                    'name' => $card->displayName(),
                    // The name and the epithet are two source fields, and the row prints them as the two
                    // lines the card face carries. `name` above stays the joined label the row link and
                    // the accessible-name contract are built on.
                    'char_name' => $card->char_name ?? $card->name_ja ?? 'Card '.$card->support_id,
                    'title' => $card->title_en,
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
                    // The export's key beside its client word, because the type mark draws a different
                    // glyph per key and the word alone cannot select it (`SupportTypeMark.vue`).
                    'type' => $card->type,
                    'type_label' => $card->typeLabel(),
                    'release_status' => $card->release_status,
                    'effects' => $effects,
                    // The friendship bonus is the one effect the index compares on, so it gets its own
                    // cell instead of being read out of the chip run (`support-cards.html` annotation 2:
                    // a Pal card's absence is the deciding fact for that type, not a missing chip).
                    'friendship' => $this->friendshipBonus($effects),
                ];
            });

        return [
            'cards' => $cards,
            'q' => $q,
            'rarity' => $rarity,
            'type' => $type,
            'status' => $status,
            'server' => $server,
            'servers' => SupportCardSearchRequest::SERVERS,
            // The scope line the wireframe's pager carries: "251 cards on [Global]"
            // (`support-cards.html:148`). Counted on the server facet alone, so a type or rarity
            // narrowing does not change the number that says how big the server's catalogue is.
            'serverCount' => $this->serverCount($server),
            'sort' => $sort,
            'rarityWords' => $this->rarityWords(),
            'typeWords' => $this->typeWords(),
            'availabilities' => SupportCard::AVAILABILITIES,
            'sorts' => $this->sortLabels(),
            // Three whole-table reads on a 559-row table, taken once per page rather than once per row
            // (`support-cards.html` annotations 3 and 4). They state facts about the catalogue that no
            // single row can: how the seven types divide, which effects decide a deck slot, and which
            // vocabulary effects no card carries.
            'typeCounts' => $this->typeCounts(),
            'decidingEffects' => $this->decidingEffects($dictionary),
            'uncoveredEffects' => $this->uncoveredEffects($dictionary),
            'totalCount' => SupportCard::query()->count(),
            // How much of the catalogue the default Global view is a view of. Query source:
            // `support_cards` rows where `release_global` is not null - the same predicate the stored
            // `release_status` column resolves to 'Global' (migration
            // 2026_09_30_142618_create_support_cards_and_support_effects_tables.php:46-52). The
            // dispatch named this `global_count`; it ships camel-cased so the top-level props keep
            // one naming rule beside `totalCount` and `typeCounts`. `release_en` is the source
            // document's key for the same fact and is not a column on this table.
            'globalCount' => SupportCard::query()->whereNotNull('release_global')->count(),
            'askedFor' => $this->describeAsk($q, $rarity, $type, $status),
        ];
    }

    /**
     * The export's own id for Friendship Bonus, the effect the deck-slot comparison turns on.
     *
     * An id and not a label: `support_effects` is the join target for `support_cards.effects`, so the
     * lookup is by the source's key rather than by a display string that a later fetch could re-word
     * (AGENTS.md §5, "no matching on display strings"). The dictionary row this id names is
     * "Friendship Bonus" on every committed body, and a test pins that pairing.
     */
    private const FRIENDSHIP_EFFECT_ID = 1;

    /**
     * The friendship bonus at cap, or null when the card's effects do not state one.
     *
     * @param  list<array{effect_id: int, name: string|null, display: string, value: int, symbol: string|null, calc: string|null}>  $effects
     * @return array{display: string}|null
     */
    private function friendshipBonus(array $effects): ?array
    {
        foreach ($effects as $effect) {
            if ($effect['effect_id'] === self::FRIENDSHIP_EFFECT_ID) {
                return ['display' => $effect['display']];
            }
        }

        return null;
    }

    /**
     * The five effects that decide a deck slot, in the wireframe's order (`support-cards.html:170-179`).
     *
     * The ids are the export's, never the printed names: `support_effects` is the join target for
     * `support_cards.effects`, and a later fetch could re-word a label (AGENTS.md §5). Each name is
     * resolved through the dictionary, so an id the vocabulary drops removes its row rather than
     * printing an unnamed one.
     *
     * @param  array<int, array{name: string, symbol: string|null, calc: string|null}>  $dictionary
     * @return list<array{effect_id: int, name: string, description: string}>
     */
    private function decidingEffects(array $dictionary): array
    {
        $effects = [];

        foreach (self::DECIDING_EFFECTS as $effectId => $description) {
            $entry = $dictionary[$effectId] ?? null;

            if ($entry === null) {
                continue;
            }

            $effects[] = ['effect_id' => $effectId, 'name' => $entry['name'], 'description' => $description];
        }

        return $effects;
    }

    /**
     * The five rows above, keyed by the export's effect id. Descriptions are the wireframe's mechanics
     * copy (`support-cards.html:173-177`), not data: the catalogue states values, not why an effect
     * matters.
     *
     * @var array<int, string>
     */
    private const DECIDING_EFFECTS = [
        1 => "Multiplies the tile's payout. Two cards multiply: 1.25 × 1.30 = 1.625.",
        14 => 'Raises the gauge the card starts with, so it can reach orange sooner.',
        19 => 'Raises how often the card appears at its preferred facility.',
        27 => 'Shrinks the failure roll on a shared tile.',
        31 => 'Scales the Energy Wit friendship training returns.',
    ];

    /**
     * The seven types with their per-rarity counts and the Global SSR subset, in `SupportCard::TYPES`
     * order.
     *
     * The wireframe's rule (`support-cards.html:155-166`): the JP column counts records in the export,
     * the Global column counts cards the Global client has received. In this schema those are one
     * table's `rarity` and `release_status` columns, so all four numbers come from one grouped read.
     *
     * @return list<array{type: string, label: string, r: int, sr: int, ssr: int, global_ssr: int}>
     */
    private function typeCounts(): array
    {
        $rows = SupportCard::query()
            ->selectRaw("type, sum(case when rarity = 1 then 1 else 0 end) as r, sum(case when rarity = 2 then 1 else 0 end) as sr, sum(case when rarity = 3 then 1 else 0 end) as ssr, sum(case when rarity = 3 and release_status = 'Global' then 1 else 0 end) as global_ssr")
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        return array_map(
            static fn (string $type): array => [
                'type' => $type,
                'label' => SupportCard::typeWord($type),
                'r' => (int) ($rows->get($type)->r ?? 0),
                'sr' => (int) ($rows->get($type)->sr ?? 0),
                'ssr' => (int) ($rows->get($type)->ssr ?? 0),
                'global_ssr' => (int) ($rows->get($type)->global_ssr ?? 0),
            ],
            SupportCard::TYPES,
        );
    }

    /**
     * The vocabulary effects that no card in this catalogue carries.
     *
     * A card row that names an effect on every anchor as `-1` states no value, so it does not carry the
     * effect either; that is the same rule `atCap()` applies when it drops such a row. An effect with
     * zero cards is a documented mechanism the data cannot show, and the page states it rather than
     * silently omitting the family (`support-cards.html` annotation 4).
     *
     * @param  array<int, array{name: string, symbol: string|null, calc: string|null}>  $dictionary
     * @return list<array{effect_id: int, name: string|null}>
     */
    private function uncoveredEffects(array $dictionary): array
    {
        $carried = [];

        foreach (SupportCard::query()->pluck('effects') as $rows) {
            foreach ($rows ?? [] as $row) {
                $values = array_slice($row, 1);

                if ($values !== [] && array_filter($values, static fn (int $value): bool => $value !== -1) !== []) {
                    $carried[(int) $row[0]] = true;
                }
            }
        }

        $uncovered = [];

        foreach ($dictionary as $effectId => $entry) {
            if (! isset($carried[$effectId])) {
                $uncovered[] = ['effect_id' => $effectId, 'name' => $entry['name']];
            }
        }

        return $uncovered;
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
                'type' => $card->type,
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
            // The stage table's rows: the levels the source states a value at, and the last of them
            // (`support-card-detail.html` annotation 1). A level between two anchors is absent rather
            // than zero, which is why the screen cannot print one cell per break.
            'effectStages' => SupportCardEffects::stages($card, $dictionary),
            // The client's card-level cap for this rarity, so the detail can print what a fully broken
            // copy reaches without inventing a stored level: the export's effect ladder stops at 50 for
            // SSR, 45 for SR and 40 for R (`UMAMUSUME_REFERENCE.md` §1.4.2), which is the same set the
            // in-client capture confirms.
            'levelCap' => $this->levelCap($card->rarity),
            // Runs own their six slots, so one card can sit in several runs without conflict. The
            // detail answers "where is this used" from the slots rather than from a card-level owner,
            // and each row's level is the level that run recorded, not a catalogue figure.
            'usedBy' => $this->usedBy($card),
            'hinted' => $this->skillList($card->hint_skills),
            'events' => $this->skillList($card->event_skills),
            'trainee' => $trainee === null ? null : [
                'name' => $trainee->name,
                'url' => route('catalog.show', $trainee->slug),
            ],
        ]);
    }

    /**
     * The level a card of this rarity reaches at four breaks.
     *
     * Four breaks at +5 each from the unbroken base (SSR 30, SR 25, R 20) give 50 / 45 / 40, and the
     * export's own effect ladder stops at exactly those levels per rarity, so the cap is a property of
     * the rarity rather than a stored column (`UMAMUSUME_REFERENCE.md` §1.4.2).
     */
    private function levelCap(CardRarity $rarity): int
    {
        return match ($rarity) {
            CardRarity::ThreeStar => 50,
            CardRarity::TwoStar => 45,
            CardRarity::OneStar => 40,
        };
    }

    /**
     * The runs whose deck holds this card, newest first.
     *
     * A run has no name of its own: it is identified by the trainee and the scenario, which is the
     * pair the careers list prints too. `card_level` is the level the slot recorded for that run, and
     * null there means the Trainer never stated one, which the page prints as an absence rather than
     * as the catalogue's cap.
     *
     * @return list<array{run_id: int, trainee_name: string, scenario_label: string, status_label: string, card_level: int|null, url: string}>
     */
    private function usedBy(SupportCard $card): array
    {
        $scenarioLabels = array_map(static fn (array $def): string => $def['label'], config('scenarios.scenarios'));

        return DeckSlot::query()
            ->where('support_card_id', $card->id)
            ->with(['trainingRun:id,umamusume_id,scenario,status', 'trainingRun.umamusume:id,name'])
            ->get()
            ->sortByDesc(static fn (DeckSlot $slot): int => $slot->trainingRun->id)
            ->map(static fn (DeckSlot $slot): array => [
                'run_id' => $slot->trainingRun->id,
                'trainee_name' => $slot->trainingRun->umamusume->name,
                'scenario_label' => $slot->trainingRun->scenario ? ($scenarioLabels[$slot->trainingRun->scenario] ?? $slot->trainingRun->scenario) : 'No scenario set',
                'status_label' => $slot->trainingRun->status->label(),
                'card_level' => $slot->card_level,
                // The run's own door is the cockpit, not the retired compatibility doorway a
                // link would send a Trainer through a redirect to reach.
                'url' => route('runs.cockpit', $slot->trainingRun),
            ])
            ->values()
            ->all();
    }

    /**
     * What the Trainer narrowed the catalog by, for the no-results state to name the ask instead of
     * repeating a query back at them. An empty set of conditions cannot reach that state: with rows in
     * the table and no filter, the first page has rows.
     */
    private function describeAsk(?string $q, ?string $rarity, ?string $type, ?string $status): string
    {
        $parts = array_filter([
            $q === null ? null : "the name \"{$q}\"",
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
    private function filtered(?string $q, ?string $rarity, ?string $type, ?string $status, ?string $server = null): Builder
    {
        return SupportCard::query()
            ->when($q !== null, function (Builder $query) use ($q): void {
                // The row prints `char_name` beside `title_en`, and a search field a Trainer types the
                // printed label into has to answer both, including the Japanese name the source stores.
                // SQLite has no default LIKE escape character, so the clause names one and `%`/`_` are
                // escaped first: unescaped, a `%` would return the whole catalogue (KI-26's trap).
                $pattern = '%'.addcslashes($q, '\\%_').'%';

                $query->where(static fn (Builder $inner): Builder => $inner
                    ->whereRaw("char_name LIKE ? ESCAPE '\\'", [$pattern])
                    ->orWhereRaw("title_en LIKE ? ESCAPE '\\'", [$pattern])
                    ->orWhereRaw("name_ja LIKE ? ESCAPE '\\'", [$pattern]));
            })
            ->when($rarity !== null, fn (Builder $query): Builder => $query->where('rarity', (int) $rarity))
            ->when($type !== null, fn (Builder $query): Builder => $query->where('type', $type))
            ->when($status !== null, fn (Builder $query): Builder => $query->where('release_status', $status))
            // Global means the Global client has the card; JP means it has not received it yet, which
            // is the export's own split (`release_global` null). A two-server facet, not a third state.
            ->when($server === 'Global', fn (Builder $query): Builder => $query->whereNotNull('release_global'))
            ->when($server === 'JP', fn (Builder $query): Builder => $query->whereNull('release_global'));
    }

    /**
     * How many cards the selected server holds, ignoring the other facets.
     */
    private function serverCount(string $server): int
    {
        return SupportCard::query()
            ->when($server === 'Global', fn (Builder $query): Builder => $query->whereNotNull('release_global'))
            ->when($server === 'JP', fn (Builder $query): Builder => $query->whereNull('release_global'))
            ->count();
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
