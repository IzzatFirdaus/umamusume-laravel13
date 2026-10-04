@props(['run'])

@php
    // Unity Cup's own widget list carries spirit_bursts; the roster renders when the
    // scenario has the mechanic and never otherwise (D-221, gate G-34).
    if (! in_array('spirit_bursts', (array) config('scenarios.scenarios.'.$run->scenarioKey().'.widgets', []), true)) {
        return;
    }

    $roster = $run->spiritBurstRoster();

    /*
     * Six states, each with its own word and its own mark, and the mark is never the
     * only signal (D-12). The treatments are existing token pairs; nothing here opens a
     * new colour role, which is what keeps the contrast pass on pairs the system already
     * names.
     */
    $treatments = [
        \App\Enums\SpiritBurstState::Chargeable->value => ['mark' => '·', 'treat' => 'bg-sunken text-ink-muted'],
        \App\Enums\SpiritBurstState::Charged->value => ['mark' => '●', 'treat' => 'bg-raised text-ink ring-2 ring-ring'],
        \App\Enums\SpiritBurstState::Held->value => ['mark' => '▲', 'treat' => 'bg-pick text-on-pick'],
        \App\Enums\SpiritBurstState::NormalBurstSpent->value => ['mark' => '○', 'treat' => 'bg-sunken text-ink'],
        \App\Enums\SpiritBurstState::ExtremeChargeable->value => ['mark' => '◆', 'treat' => 'bg-green-tint text-ink border border-green-line'],
        \App\Enums\SpiritBurstState::ExtremeSpent->value => ['mark' => '◇', 'treat' => 'bg-risk text-on-chrome'],
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md border border-rule bg-panel p-3']) }}>
    {{-- x-capsule-header owns this anatomy for every panel (DESIGN.md §2.3). --}}
    <x-capsule-header title="Spirit Burst" class="mb-3" />

    @if ($roster === [])
        <p class="text-sm text-ink-muted" role="status">
            No teammate burst states recorded. The six states are chargeable, charged, held,
            normal spent, extreme chargeable and extreme spent.
        </p>
    @else
        <ul class="flex flex-col gap-1.5" aria-label="Teammate burst states">
            @foreach ($roster as $row)
                @php $treatment = $treatments[$row['state']->value]; @endphp
                <li class="flex items-baseline justify-between gap-3 rounded-md border border-rule bg-raised px-3 py-2 text-sm">
                    <span class="font-semibold text-ink-strong">{{ $row['teammate'] }}</span>
                    {{-- The word carries what the mark and the colour carry: "spent" is never
                         drawn as a dead end, because an extreme burst follows on the next
                         Unity Training (D-223, D-12). --}}
                    <span class="shrink-0 rounded px-2 py-0.5 font-mono text-xs font-bold {{ $treatment['treat'] }}">
                        {{ $treatment['mark'] }} {{ $row['state']->label() }}
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
