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
            <input type="text" name="search" value="{{ $search }}" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink" placeholder="Name (normalized)">
        </label>
        <label class="flex flex-col gap-1">
            <span class="text-ink-muted">Release status</span>
            <select name="status" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                <option value="">All</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($currentStatus === $status)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="enamel rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">Filter</button>
    </form>

    @if ($umamusumes->count() === 0)
        <p class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted">
            No Umamusume match. The catalog is filled by seed data or `php artisan uma:fetch`.
        </p>
    @else
        <ul class="mt-6 divide-y divide-rule rounded-md border border-rule bg-raised">
            @foreach ($umamusumes as $umamusume)
                <li class="flex items-baseline justify-between px-4 py-3">
                    <a href="{{ route('catalog.show', $umamusume->slug) }}" class="font-medium text-ink-strong hover:underline">
                        {{ $umamusume->name }}
                        @if ($umamusume->name_ja)
                            <span class="ml-2 text-sm text-ink-muted">{{ $umamusume->name_ja }}</span>
                        @endif
                    </a>
                    <span class="text-xs text-ink-muted">{{ $umamusume->release_status->label() }} · {{ $umamusume->aliases_count }} aliases</span>
                </li>
            @endforeach
        </ul>

        <div class="mt-4 text-sm">
            {{ $umamusumes->links() }}
        </div>
    @endif
</x-layout>
