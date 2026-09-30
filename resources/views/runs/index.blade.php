<x-layout title="Training runs">
    <div class="flex flex-wrap items-baseline justify-between gap-3">
        <h1 class="text-2xl font-semibold text-ink-strong">Training runs</h1>
        <div class="flex items-center gap-3">
            {{-- Import sits beside New run rather than in a menu: it creates a run too, and a Trainer
                 with a finished career in a file has to find it on the same glance. --}}
            <a href="{{ route('runs.import') }}" class="text-sm text-ink-muted hover:underline">Import a historical run</a>
            <a href="{{ route('runs.create') }}"
               class="enamel rounded-full bg-chrome px-4 py-1.5 text-sm font-bold text-on-chrome">
                New run
            </a>
        </div>
    </div>

    @if ($runs->count() === 0)
        {{-- An empty list is a real state, not a failure, and it has to say what
             happens next. "No data" alone would leave the Trainer guessing. --}}
        <div class="mt-6 rounded-md border border-dashed border-rule bg-panel p-6">
            <p class="text-sm font-semibold text-ink-strong">No runs yet</p>
            <p class="mt-1 text-sm text-ink-muted">
                A run holds the turns you log against one Umamusume in one scenario, so there is
                nothing to list until you start one.
            </p>
        </div>
    @else
        <ul class="mt-6 divide-y divide-rule rounded-md border border-rule bg-panel">
            @foreach ($runs as $run)
                @php
                    // The scenario key is a storage value; the label is config's. Printing
                    // the key here would show an internal slug where a name belongs. The
                    // absence comes from `hasScenario()`, the model's one ruling: testing
                    // `=== null` here let a blank scenario through and printed a bare
                    // "· " separator with no name after it.
                    $scenarioLabel = $run->hasScenario()
                        ? (config('scenarios.scenarios.'.$run->scenario.'.label') ?? $run->scenario)
                        : null;
                @endphp
                <li class="flex flex-wrap items-baseline justify-between gap-2 px-4 py-3 text-sm">
                    <a href="{{ route('runs.show', $run) }}" class="font-semibold text-ink hover:underline">
                        {{ $run->umamusume->name }}
                        @if ($scenarioLabel !== null)
                            <span class="font-normal text-ink-muted">· {{ $scenarioLabel }}</span>
                        @endif
                    </a>
                    <span class="font-mono text-xs tabular-nums text-ink-muted">
                        {{ $run->status->label() }} · {{ $run->created_at->toDateString() }}
                    </span>
                </li>
            @endforeach
        </ul>

        <div class="mt-4 text-sm">{{ $runs->links() }}</div>
    @endif
</x-layout>
