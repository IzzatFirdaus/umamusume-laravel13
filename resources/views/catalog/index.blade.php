{{--
    Tokens, not the skeleton's zinc utilities. The shell already renders from
    `--color-page` / `--color-ink`; a page body in `bg-white text-zinc-600` left
    the dark theme a white page with dark-mode-proof borders, which is the
    G-18 failure. `html[data-theme='dark']` flips the variables, so no `dark:`
    utility appears here (D-101, G-19) and neither theme needs a second set of
    classes.
--}}
<x-layout title="Catalog">
    <h1 class="text-2xl font-semibold text-ink-strong">Umamusume catalog</h1>

    <form method="GET" action="{{ route('catalog.index') }}" class="mt-4 flex flex-wrap items-end gap-3 text-sm">
        <label class="flex flex-col gap-1">
            <span class="text-ink-muted">Search</span>
            <input type="text" name="search" value="{{ $search }}" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink" placeholder="Trainee or card name">
        </label>
        <label class="flex flex-col gap-1">
            <span class="text-ink-muted">Release status</span>
            <select name="status" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                <option value="all" @selected($showAllStatus)>{{ $allStatusesLabel }}</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($currentStatus === $status)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </label>
        {{-- A GET form, so this opt-in is a reload rather than live typing. The card
             rows below it stay hidden until this is asked for. --}}
        <label class="flex items-center gap-2 pb-1.5">
            <input type="checkbox" name="show_unconfirmed" value="1" @checked($showUnconfirmed) class="rounded border-rule">
            <span class="text-ink-muted">Show unconfirmed cards</span>
        </label>
        <button type="submit" class="enamel rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">Filter</button>
    </form>

    @if ($umamusumes->count() === 0)
        <p class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted">
            No Umamusume match. The catalog is filled by seed data or `php artisan uma:fetch`.
        </p>
    @else
        {{-- No collapse control ships with this tree, decided by row count: 68 trainees at
             the 25-per-page default is three pages, each already fully expanded, so a
             disclosure widget would hide nothing while adding a keyboard stop (G-11). The
             lever if that ever changes is the `pageSize` clamp, already at 100. --}}
        <ul class="mt-6 space-y-4">
            @foreach ($umamusumes as $umamusume)
                <li class="rounded-md border border-rule bg-raised">
                    {{--
                        The badge and the form count beside it answer one question, "what is on
                        this screen", so both read the collection the controller's card scope
                        loaded: a hidden card moves the max rarity with it, and
                        `show_unconfirmed=1` moves both back together.
                    --}}
                    <h2 class="flex flex-wrap items-baseline justify-between gap-x-4 px-4 py-3">
                        <a href="{{ route('catalog.show', $umamusume->slug) }}" class="font-semibold text-ink-strong hover:underline">
                            {{ $umamusume->name }}
                            @if ($umamusume->name_ja)
                                <span class="ml-2 text-sm font-normal text-ink-muted">{{ $umamusume->name_ja }}</span>
                            @endif
                        </a>
                        <span class="flex items-baseline gap-3 text-xs text-ink-muted">
                            @if ($umamusume->cards->isNotEmpty())
                                {{-- max() over enum instances would compare objects, not stars,
                                     and from() wants an int, so the value is cast twice over --}}
                                <x-rarity-chip :rarity="\App\Enums\CardRarity::from((int) $umamusume->cards->max(static fn ($card) => $card->rarity->value))" />
                                <span>{{ $umamusume->cards->count() }} {{ \Illuminate\Support\Str::plural('form', $umamusume->cards->count()) }}</span>
                            @else
                                {{-- One disclosure, in words, on screen. A bare 0 reads as a
                                     count; the absence of a badge says the same thing truer. --}}
                                <span>no forms recorded</span>
                            @endif
                            <span>{{ $umamusume->release_status->label() }}</span>
                        </span>
                    </h2>

                    {{-- The forms the filter let through, in the order the controller's card
                         scope declared: debut first, then Global release date. --}}
                    @if ($umamusume->cards->isNotEmpty())
                        <ul class="divide-y divide-rule border-t border-rule">
                            @foreach ($umamusume->cards as $card)
                                <li class="flex flex-wrap items-baseline justify-between gap-x-4 px-4 py-2">
                                    <h3 class="text-sm font-medium text-ink">{{ $card->title }}</h3>
                                    <span class="flex items-baseline gap-3 text-xs text-ink-muted">
                                        <x-rarity-chip :rarity="$card->rarity" />
                                        @if ($card->is_debut_form)
                                            <span>debut form</span>
                                        @endif
                                        @if ($card->unconfirmed)
                                            <span class="font-semibold text-risk">Not confirmed by two sources</span>
                                        @endif
                                        <time datetime="{{ $card->global_release_date->toDateString() }}">
                                            Released (Global) {{ $card->global_release_date->format('M j, Y') }}
                                        </time>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>

        <div class="mt-4 text-sm">
            {{ $umamusumes->links() }}
        </div>
    @endif
</x-layout>
