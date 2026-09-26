<x-layout title="Catalog">
    <h1 class="text-2xl font-semibold">Umamusume catalog</h1>

    <form method="GET" action="{{ route('catalog.index') }}" class="mt-4 flex flex-wrap items-end gap-3 text-sm">
        <label class="flex flex-col gap-1">
            <span>Search</span>
            <input type="text" name="search" value="{{ $search }}" class="rounded border border-zinc-300 px-2 py-1" placeholder="Name (normalized)">
        </label>
        <label class="flex flex-col gap-1">
            <span>Release status</span>
            <select name="status" class="rounded border border-zinc-300 px-2 py-1">
                <option value="">All</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($currentStatus === $status)>{{ $status->value }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="rounded bg-zinc-900 px-3 py-1.5 text-white">Filter</button>
    </form>

    @if ($umamusumes->count() === 0)
        <p class="mt-8 rounded border border-dashed border-zinc-300 bg-white p-6 text-sm text-zinc-600">
            No Umamusume match. The catalog is filled by seed data or `php artisan uma:fetch`.
        </p>
    @else
        <ul class="mt-6 divide-y divide-zinc-200 rounded border border-zinc-200 bg-white">
            @foreach ($umamusumes as $umamusume)
                <li class="flex items-baseline justify-between px-4 py-3">
                    <a href="{{ route('catalog.show', $umamusume->slug) }}" class="font-medium hover:underline">
                        {{ $umamusume->name }}
                        @if ($umamusume->name_ja)
                            <span class="ml-2 text-sm text-zinc-500">{{ $umamusume->name_ja }}</span>
                        @endif
                    </a>
                    <span class="text-xs text-zinc-500">{{ $umamusume->release_status->value }} · {{ $umamusume->aliases_count }} aliases</span>
                </li>
            @endforeach
        </ul>

        <div class="mt-4 text-sm">
            {{ $umamusumes->links() }}
        </div>
    @endif
</x-layout>
