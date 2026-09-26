<x-layout :title="$umamusume->name">
    <div class="flex items-baseline justify-between">
        <h1 class="text-2xl font-semibold">
            {{ $umamusume->name }}
            @if ($umamusume->name_ja)
                <span class="ml-2 text-base text-zinc-500">{{ $umamusume->name_ja }}</span>
            @endif
        </h1>
        <a href="{{ route('catalog.index') }}" class="text-sm hover:underline">Back to catalog</a>
    </div>

    <dl class="mt-6 grid grid-cols-2 gap-x-6 gap-y-3 rounded border border-zinc-200 bg-white p-4 text-sm md:grid-cols-4">
        <div>
            <dt class="text-zinc-500">Release status</dt>
            <dd>{{ $umamusume->release_status->value }}</dd>
        </div>
        <div>
            <dt class="text-zinc-500">JP debut</dt>
            <dd>{{ $umamusume->jp_debut_date?->toDateString() ?? 'Unknown' }}</dd>
        </div>
        <div>
            <dt class="text-zinc-500">Global debut</dt>
            <dd>{{ $umamusume->global_debut_date?->toDateString() ?? 'Unknown' }}</dd>
        </div>
        <div>
            <dt class="text-zinc-500">Edited by Trainer</dt>
            <dd>{{ $umamusume->is_manual ? 'Yes (engine will not overwrite)' : 'No' }}</dd>
        </div>
    </dl>

    @if ($umamusume->release_status === \App\Enums\ReleaseStatus::JapanOnly)
        <p class="mt-4 rounded border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-800">
            Not yet released on Global. JP-sourced data below.
        </p>
    @endif

    <h2 class="mt-8 text-lg font-semibold">Aliases</h2>
    @if ($umamusume->aliases->isEmpty())
        <p class="mt-2 text-sm text-zinc-500">No aliases yet.</p>
    @else
        <ul class="mt-2 flex flex-wrap gap-2 text-sm">
            @foreach ($umamusume->aliases as $alias)
                <li class="rounded bg-zinc-100 px-2 py-1">{{ $alias->alias }} <span class="text-zinc-500">({{ $alias->language->value }})</span></li>
            @endforeach
        </ul>
    @endif

    <h2 class="mt-8 text-lg font-semibold">Provenance</h2>
    @if ($umamusume->dataSources->isEmpty())
        <p class="mt-2 text-sm text-zinc-500">No fetched sources. This record was seeded or entered by hand.</p>
    @else
        <ul class="mt-2 space-y-1 text-sm">
            @foreach ($umamusume->dataSources as $source)
                <li>
                    <a href="{{ $source->url }}" class="text-blue-700 hover:underline">{{ $source->url }}</a>
                    <span class="text-zinc-500">
                        · {{ $source->source_key }} · fetched {{ $source->fetched_at->timezone(config('uma.display_timezone'))->format('Y-m-d H:i T') }}
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</x-layout>
