@props(['run', 'cards'])

@php
    /*
     * The deck is run-scoped context, not scenario-composed: every scenario has six support card
     * slots, so this panel carries no `panels.*` self-gate and must not be wrapped in the
     * `hasScenario()` guard the scenario panels sit under. A run that names no scenario still had
     * a deck.
     */
    $slots = $run->deckSlots->keyBy('slot_position');
    $scenarioKey = $run->scenarioKey();

    // The effect dictionary is read once for the panel, not once per slot: six equipped cards ask the
    // same 35 rows the same question, and a query per card here is the N+1 the catalog reads avoid
    // elsewhere by loading the small table once and keying it in PHP.
    $effectNames = \App\Services\SupportCardEffects::dictionary();

    // A card already equipped is offered even when it is not Global-released, so a Trainer logging an
    // older or JP-only deck is never shown a slot they cannot re-select their own card in.
    $options = $cards->concat($run->deckSlots->pluck('supportCard')->filter())
        ->unique('id')
        ->sortBy(['type', 'char_name', 'title_en'])
        ->values();
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md border border-rule bg-panel p-3']) }}>
    {{-- No chrome-bar title here, unlike the scenario panels: the run screen already carries an
         `h2 Support deck` above this component, and a second one inside it reads as two sections with
         the same name. The Skills block beside it is the closer analogue, being a Trainer-owned editor
         form under its own heading rather than a scenario surface. --}}

    @if ($run->deckSlots->isEmpty())
        <p class="text-sm text-ink-muted" role="status">
            No support cards recorded for this run. The deck is the six cards equipped before the run
            started, and it decides training yield as much as the turns do, so a logged run with no deck
            cannot be re-read later. Choose a card for each slot below, or leave a slot on
            "Not equipped" if the Trainer does not remember it.
        </p>
    @else
        <ul class="mb-4 flex flex-col gap-1.5" aria-label="Equipped support cards">
            @foreach (\App\Models\DeckSlot::POSITIONS as $position)
                @php $slot = $slots->get($position); @endphp
                @continue($slot === null)
                <li class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 rounded-md border border-rule bg-raised px-3 py-2 text-sm">
                    <a href="{{ route('support-cards.show', $slot->supportCard) }}" class="font-semibold text-ink-strong hover:underline">
                        {{ $slot->supportCard->displayName() }}
                    </a>
                    <span class="flex shrink-0 flex-wrap items-baseline gap-3 font-mono text-xs text-ink-muted">
                        {{-- Position is the slot's own identity, and six is the friend slot whatever
                             card sits in it (ADR-0014 correction 1). --}}
                        <span>{{ $position === 6 ? 'Friends' : 'Slot '.$position }}</span>
                        <span>{{ $slot->supportCard->rarityWord() }} {{ $slot->supportCard->typeLabel() }}</span>
                        @if ($slot->supportCard->isScenarioLink($scenarioKey))
                            <span class="font-bold text-ink-strong">Scenario Link</span>
                        @endif
                    </span>
                    {{-- The effect facts sit on the record of the choice, not in the picker: a repeater
                         pays their bytes once per row, this list pays them once per equipped card
                         (`skills-section-phase-b2-2026-10-01.md` §3.1 and §11). A card whose vector states
                         nothing renders no line, because an empty line is a control that says nothing. --}}
                    @php $effects = \App\Services\SupportCardEffects::atCap($slot->supportCard, $effectNames); @endphp
                    @if ($effects !== [])
                        <span class="flex w-full flex-wrap gap-x-3 gap-y-0.5 font-mono text-xs text-ink-muted">
                            @foreach ($effects as $effect)
                                {{-- A dictionary row that is absent is marked, not labelled: the id is the
                                     source's own number, so naming it states a fact rather than inventing a
                                     word (D-20, UMAMUSUME_REFERENCE.md §1.4.8). --}}
                                <span>{{ $effect['name'] ?? '[Unverified] effect '.$effect['effect_id'] }} {{ $effect['display'] }}</span>
                            @endforeach
                        </span>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('runs.deck.sync', $run) }}" class="max-w-3xl space-y-3 rounded-md border border-rule bg-raised p-4 text-sm">
        @csrf
        @php
            /*
             * One option list, not six. Each slot used to carry the whole card catalogue, measured here as
             * 1,512 `option` nodes for a deck with nothing else wrong with it, and 7,997 of them against
             * 8,941 elements on the run page overall. Those nodes are not decoration a Trainer can see,
             * they are the no-script path, so the fix keeps the path and stops paying for it six times.
             *
             * The open slot is the one the query names, else the one a rejected submission put an error
             * beside, else the first. A closed slot still posts its own value, through a hidden input under
             * the same field name, so StoreDeckRequest receives exactly the shape it received before: six
             * keys, and blanks dropped by its own prepareForValidation rather than by a rule that had to
             * be told about them. Switching which slot is open is a query-string link, built the way
             * x-race-calendar builds its year tabs, so it answers a click with scripting off.
             */
            $requested = (int) request('deck_slot');
            $errored = 0;

            foreach (\App\Models\DeckSlot::POSITIONS as $position) {
                if ($errors->has("deck.{$position}.support_card_id")) {
                    $errored = $position;

                    break;
                }
            }

            $open = in_array($requested, \App\Models\DeckSlot::POSITIONS, true)
                ? $requested
                : ($errored > 0 ? $errored : \App\Models\DeckSlot::POSITIONS[0]);
        @endphp

        @foreach (\App\Models\DeckSlot::POSITIONS as $position)
            @php
                // Rehydrated from `old()` so a rejected submission does not wipe the six picks the
                // Trainer just made (D-3's finding: the rail and the escape hatch disagreed about
                // what to do with input the server had refused).
                $selected = old("deck.{$position}.support_card_id", $slots->get($position)?->support_card_id);
                $chosen = (string) $selected === '' ? null : $options->firstWhere('id', (int) $selected);
            @endphp
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex min-w-0 flex-1 flex-col gap-1">
                    @if ($position === $open)
                        <label for="deck-slot-{{ $position }}" class="text-ink-muted">
                            {{ $position === 6 ? 'Slot 6 · Friends' : 'Slot '.$position }}
                        </label>
                        {{-- One accessible name per control, paired by id rather than wrapped: a wrapping
                             label over a group leaves the sibling controls unnamed (KI-36). --}}
                        <select id="deck-slot-{{ $position }}" name="deck[{{ $position }}][support_card_id]"
                            class="min-h-11 rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                            <option value="">Not equipped</option>
                            @foreach ($options as $card)
                                <option value="{{ $card->id }}" @selected((string) $selected === (string) $card->id)>
                                    {{ $card->displayName() }} · {{ $card->rarityWord() }} {{ $card->typeLabel() }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        {{-- The value posts and the name is readable, which is all a closed slot owes: the
                             catalogue is one click away, and it is a link rather than a control so the
                             screen has one option list in it at a time. --}}
                        <p class="flex flex-wrap items-baseline gap-x-2 text-sm">
                            <span class="text-ink-muted">{{ $position === 6 ? 'Slot 6 · Friends' : 'Slot '.$position }}:</span>
                            <span class="text-ink">{{ $chosen?->displayName() ?? 'Not equipped' }}</span>
                            <a href="{{ request()->fullUrlWithQuery(['deck_slot' => $position]) }}"
                               class="inline-flex min-h-11 items-center underline">Change slot {{ $position }}</a>
                        </p>
                        <input type="hidden" name="deck[{{ $position }}][support_card_id]" value="{{ $selected }}">
                    @endif
                </div>
                @error("deck.{$position}.support_card_id")
                    <p class="w-full text-risk">{{ $message }}</p>
                @enderror
            </div>
        @endforeach

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="enamel rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">Save deck</button>
            @if ($run->deckSlots->isNotEmpty())
                {{-- A deck is replaced whole, so clearing needs its own explicit action rather than
                     being what happens when a Trainer submits the form after a page they misread. --}}
                <span class="text-xs text-ink-muted">Set every slot to "Not equipped" to clear the deck.</span>
            @endif
        </div>
        @error('deck')<p class="w-full text-risk">{{ $message }}</p>@enderror
    </form>

    <p class="mt-3 font-mono text-xs text-ink-muted">
        {{ $options->count() }} card{{ $options->count() === 1 ? '' : 's' }} offered · Global releases plus any card this run already uses ·
        hard limit of one copy per card, so a duplicate is refused rather than silently kept.
        {{-- D-256: a displayed figure names the rule behind it. The figure is a stated anchor, so the
             rule is that it is the card's highest published value and not the value at some level this
             run holds, because no level is stored (ADR-0014: identity, not collection). --}}
        Effect figures are each card's highest stated anchor, the value the source publishes at its top
        level, not a figure for a level this run records.
    </p>
</div>
