{{-- Tokens only: no zinc utility and no `dark:` fork. See catalog/index for the
     reason and the gate this satisfies (D-101, G-18, G-19). --}}
<x-layout :title="$umamusume->name">
    <div class="flex items-baseline justify-between">
        <h1 class="text-2xl font-semibold text-ink-strong">
            {{ $umamusume->name }}
            @if ($umamusume->name_ja)
                <span class="ml-2 text-base text-ink-muted">{{ $umamusume->name_ja }}</span>
            @endif
        </h1>
        <a href="{{ route('catalog.index') }}" class="text-sm text-ink-muted hover:underline">Back to catalog</a>
    </div>

    <dl class="mt-6 grid grid-cols-2 gap-x-6 gap-y-3 rounded-md border border-rule bg-raised p-4 text-sm md:grid-cols-4">
        <div>
            <dt class="text-ink-muted">Release status</dt>
            <dd class="text-ink">{{ $umamusume->release_status->label() }}</dd>
        </div>
        <div>
            <dt class="text-ink-muted">JP debut</dt>
            <dd class="text-ink">{{ $umamusume->jp_debut_date?->toDateString() ?? 'Unknown' }}</dd>
        </div>
        <div>
            <dt class="text-ink-muted">Global debut</dt>
            <dd class="text-ink">{{ $umamusume->global_debut_date?->toDateString() ?? 'Unknown' }}</dd>
        </div>
        <div>
            <dt class="text-ink-muted">Edited by Trainer</dt>
            <dd class="text-ink">{{ $umamusume->is_manual ? 'Yes (engine will not overwrite)' : 'No' }}</dd>
        </div>
    </dl>

    {{-- The token set has no caution chrome: `up` means increase, `risk` means
         failure, and `pick` measures 1.60:1 on the raised surface so it cannot
         carry a boundary. The notice is therefore carried by its copy over a
         declared border token (`ink-faint`, 3.26:1 light / 4.21:1 dark) rather
         than by an invented colour. Adding a caution token is a DESIGN.md
         amendment, not a view-local choice. --}}
    @if ($umamusume->release_status === \App\Enums\ReleaseStatus::JapanOnly)
        <p class="mt-4 rounded-md border border-ink-faint bg-raised px-3 py-2 text-sm text-ink">
            Not yet released on Global. JP-sourced data below.
        </p>
    @endif

    <h2 class="mt-8 text-lg font-semibold text-ink-strong">Aliases</h2>
    @if ($umamusume->aliases->isEmpty())
        <p class="mt-2 text-sm text-ink-muted">No aliases yet.</p>
    @else
        <ul class="mt-2 flex flex-wrap gap-2 text-sm">
            @foreach ($umamusume->aliases as $alias)
                <li class="rounded-md bg-sunken px-2 py-1 text-ink">{{ $alias->alias }} <span class="text-ink-muted">({{ $alias->language->label() }})</span></li>
            @endforeach
        </ul>
    @endif

    <h2 class="mt-8 text-lg font-semibold text-ink-strong">Provenance</h2>
    @if ($umamusume->dataSources->isEmpty())
        <p class="mt-2 text-sm text-ink-muted">No fetched sources. This record was seeded or entered by hand.</p>
    @else
        <ul class="mt-2 space-y-1 text-sm">
            @foreach ($umamusume->dataSources as $source)
                <li class="text-ink">
                    <a href="{{ $source->url }}" class="text-ink-strong underline">{{ $source->url }}</a>
                    <span class="text-ink-muted">
                        · {{ $source->source_key }} · fetched {{ $source->fetched_at->timezone(config('uma.display_timezone'))->format('Y-m-d H:i T') }}
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</x-layout>
