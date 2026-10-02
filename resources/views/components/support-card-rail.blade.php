@props(['cards' => []])

@php
    /*
     * Run-scoped context, like x-deck-panel: every scenario has six support slots, so the rail
     * carries no scenario self-gate. Each entry: name, bond (0..100|null), friend (bool),
     * burning (bool). A bond the run has not recorded renders an empty track, not a full one.
     */
@endphp

<aside {{ $attributes->merge(['class' => 'flex w-16 flex-col gap-1 rounded-md border border-rule bg-panel p-1']) }}
       aria-label="Support cards">
    @foreach ($cards as $card)
        @php
            $bond = $card['bond'] ?? null;
            $name = (string) ($card['name'] ?? '');
        @endphp
        <div class="flex flex-col items-center gap-0.5 rounded border border-rule bg-raised p-1">
            <span class="grid size-8 place-items-center rounded-full bg-sunken font-mono text-xs font-bold text-ink-strong"
                  aria-hidden="true">{{ $name === '' ? '?' : mb_substr($name, 0, 1) }}</span>

            @if (($card['friend'] ?? false) === true)
                {{-- Friendship: the double chevron, the client's own mark. --}}
                <span class="text-xs leading-none font-bold text-green-deep" title="Friendship" aria-hidden="true">»</span>
            @endif

            <span class="h-1 w-8 overflow-hidden rounded bg-sunken" role="img"
                  aria-label="{{ $bond === null ? 'Bond not recorded' : 'Bond '.((int) $bond).' of 100' }}">
                <span class="block h-1 rounded bg-green-deep"
                      style="width: {{ $bond === null ? 0 : max(0, min(100, (int) $bond)) }}%"></span>
            </span>

            @if (($card['burning'] ?? false) === true)
                <svg class="size-3 fill-risk" viewBox="0 0 16 16" role="img" aria-label="Burning">
                    <path d="M8 1c2 3 5 4 5 8a5 5 0 0 1-10 0c0-2 1-3 2-4 0 2 1 3 2 3 1-2-1-4-1-7z" />
                </svg>
            @endif
        </div>
    @endforeach
</aside>
