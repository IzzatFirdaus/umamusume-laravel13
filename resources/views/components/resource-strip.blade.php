@props([
    'scenario' => 'ura_finale',
    'run' => [],
])

@php
    $config = config('scenarios');
    $def = $config['scenarios'][$scenario] ?? null;

    if ($def === null) {
        throw new InvalidArgumentException("Unknown scenario [{$scenario}] for x-resource-strip.");
    }

    /*
     * The widget list comes from config. A scenario that owns no team rank renders
     * no team rank box: absence, not an empty slot. This is the whole of D-220, and
     * it is why there is no `if ($scenario === 'unity_cup')` anywhere in the tree.
     */
    $widgets = $def['widgets'];
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap gap-2']) }}>
    @foreach ($widgets as $widget)
        @php
            // Each branch produces presentation only. Adding a scenario never touches this loop.
            [$label, $value, $sub] = match ($widget) {
                'turn' => ['Turn', number_format((int) ($run['turn'] ?? 0)), $def['label']],
                'energy' => ['Energy', ((int) ($run['energy'] ?? 0)) . '/100', null],
                'fans' => ['Fans', number_format((int) ($run['fans'] ?? 0)), 'next event gate 60,000'],
                'team_rank' => ['Team Rank', (string) ($run['team_rank'] ?? 'G'), 'drives facility level'],
                'spirit_bursts' => ['Spirit Bursts', number_format((int) ($run['bursts'] ?? 0)), 'normal plus Extreme'],
                'grade_points' => ['Grade Points', ((int) ($run['grade_points'] ?? 0)) . '/300', 'surplus does not carry over'],
                'shop_coins' => ['Shop Coins', number_format((int) ($run['shop_coins'] ?? 0)), 'rotation in 2 turns'],
                default => throw new InvalidArgumentException("Unknown widget [{$widget}] in scenario config."),
            };

            $segments = $widget === 'energy'
                ? max(0, min(5, (int) round(((int) ($run['energy'] ?? 0)) / 20)))
                : null;
        @endphp

        <div class="min-w-36 flex-1 rounded-md border border-rule bg-raised px-3 py-2">
            <span class="block text-xs font-semibold uppercase tracking-wide text-ink-muted">{{ $label }}</span>
            <span class="block font-mono text-xl font-extrabold tabular-nums text-ink-strong">{{ $value }}</span>

            @if ($segments !== null)
                <span class="mt-1 flex gap-1" role="img" aria-label="Energy {{ $run['energy'] ?? 0 }} of 100">
                    @for ($i = 0; $i < 5; $i++)
                        <span class="h-1.5 flex-1 rounded-sm {{ $i < $segments ? 'bg-green' : 'bg-idle' }}"></span>
                    @endfor
                </span>
            @elseif ($sub !== null)
                <span class="block text-xs text-ink-faint">{{ $sub }}</span>
            @endif
        </div>
    @endforeach
</div>
