@props([
    'slots' => [],
    'legend' => [],
])

@php
    /*
     * Six slots in a 2x3 grid. Each slot: name, rarity, type, limit_break (0..4), level.
     * `legend` is the seven type chips with counts, keyed by the type word. Distinct from
     * x-deck-panel, which owns the write form; this is the read shape.
     */
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md border border-rule bg-panel p-3']) }}>
    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
        @for ($i = 0; $i < 6; $i++)
            @php $slot = $slots[$i] ?? null; @endphp
            <div class="relative flex min-h-24 flex-col gap-1 rounded-md border border-rule bg-raised p-2">
                @if ($slot === null)
                    <span class="text-xs text-ink-muted">Not equipped</span>
                @else
                    {{-- Rarity ribbon, top-right. --}}
                    <span class="absolute top-0 right-0 rounded-bl-md bg-chrome px-1.5 text-xs font-bold text-on-chrome">
                        {{ $slot['rarity'] ?? '' }}
                    </span>
                    <span class="pr-8 text-sm font-bold text-ink-strong">{{ $slot['name'] ?? '' }}</span>
                    <span class="w-fit rounded border border-rule px-1.5 text-xs font-semibold text-ink-muted">{{ $slot['type'] ?? '' }}</span>
                    <span class="flex gap-0.5" role="img" aria-label="Limit break {{ (int) ($slot['limit_break'] ?? 0) }} of 4">
                        @for ($d = 0; $d < 4; $d++)
                            <span class="size-2 rotate-45 {{ $d < (int) ($slot['limit_break'] ?? 0) ? 'bg-pick' : 'bg-idle' }}"></span>
                        @endfor
                    </span>
                    <span class="font-mono text-xs tabular-nums text-ink-muted">Lv {{ (int) ($slot['level'] ?? 1) }}</span>
                @endif
            </div>
        @endfor
    </div>

    @if ($legend !== [])
        <div class="mt-3 flex flex-wrap gap-1.5">
            @foreach ($legend as $type => $count)
                <span class="rounded-full border border-rule px-2 py-0.5 text-xs font-semibold text-ink-muted">{{ $type }} {{ (int) $count }}</span>
            @endforeach
        </div>
    @endif
</div>
