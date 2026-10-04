@props(['run'])

@php
    if (! $run->composesPanel('epithet_routes')) {
        return;
    }

    $rows = $run->epithetProgress();
    $seen = $run->completedRaceTitles();

    // Three states, three words. `unverifiable` is the one that must not be folded into
    // `open`: a route this surface cannot see is not a route the Trainer has not earned
    // (D-220, D-256).
    $treatments = [
        'earned' => 'bg-green-tint text-ink border border-green-line',
        'open' => 'bg-sunken text-ink',
        'unverifiable' => 'bg-raised text-ink-muted border border-rule',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md border border-rule bg-panel p-3']) }}>
    {{-- x-capsule-header owns this anatomy for every panel (DESIGN.md §2.3). --}}
    <x-capsule-header title="Epithet routes" class="mb-3" />

    <p class="text-xs text-ink-muted">
        Derived
        @if ($seen === [])
            from no entered races yet, so every named race below reads as outstanding
        @else
            from {{ count($seen) }} entered race {{ count($seen) === 1 ? 'name' : 'names' }}:
            {{ implode(', ', $seen) }}
        @endif
        . Requirements are transcribed from the Trackblazer guide; nothing here is inferred
        about a race the tool cannot see.
    </p>

    <ul class="mt-3 flex flex-col gap-1.5" aria-label="Epithet routes">
        @foreach ($rows as $row)
            <li class="rounded-md border px-3 py-2 text-sm
                       {{ $row['state'] === 'earned' ? 'border-green-line bg-green-tint' : 'border-rule bg-raised' }}">
                <span class="flex flex-wrap items-baseline justify-between gap-x-3">
                    <span class="font-semibold text-ink-strong">{{ $row['epithet'] }}</span>
                    <span class="shrink-0 rounded px-2 py-0.5 font-mono text-xs font-bold {{ $treatments[$row['state']] }}">
                        {{ $row['state'] }}
                    </span>
                </span>
                <span class="mt-0.5 block text-xs text-ink-muted">
                    {{ $row['route'] }} · {{ $row['reward'] }}
                    @if ($row['note'] !== null)
                        · needs {{ $row['note'] }}, which this surface cannot read
                    @elseif ($row['state'] !== 'earned' && $row['missing'] !== [])
                        · outstanding: {{ implode(', ', $row['missing']) }}
                    @endif
                </span>
            </li>
        @endforeach
    </ul>
</div>
