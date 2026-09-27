@props([
    'scenario' => 'ura_finale',
    'current' => null,
    'selected' => null,
    'choices' => [],
    'preview' => [],
    'energy' => 0,
    'confirmRoute' => null,
])

@php
    $def = config('scenarios.scenarios.'.$scenario);

    if ($def === null) {
        throw new InvalidArgumentException("Unknown scenario [{$scenario}] for x-guided-step.");
    }

    // Step order is config, so Trackblazer's extra shop step and Unity Cup's extra
    // facility step come from data rather than from a conditional in the template.
    $steps = $def['steps'];
    $current ??= $steps[0];

    $stepLabel = [
        'facility' => 'Choose facility',
        'training' => 'Choose activity',
        'shop' => 'Spend Shop Coins',
        'outcome' => 'Record outcome',
        'skill' => 'Review skills',
        'confirm' => 'Confirm',
    ];

    $index = array_search($current, $steps, true);
    $index = $index === false ? 0 : $index;
    $segments = max(0, min(5, (int) round((int) $energy / 20)));
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md border border-rule bg-panel p-3']) }}>
    <div class="mb-3 flex flex-wrap items-center gap-2">
        <div class="flex gap-1.5" role="group" aria-label="Guided turn progress">
            @foreach ($steps as $i => $step)
                <span class="h-1.5 w-8 rounded {{ $i <= $index ? 'bg-green' : 'bg-idle' }}"
                      title="{{ $stepLabel[$step] ?? $step }}"></span>
            @endforeach
        </div>
        <span class="text-xs font-semibold text-ink-muted">
            Step {{ $index + 1 }} of {{ count($steps) }} · {{ $stepLabel[$current] ?? $current }}
        </span>
        <span class="ml-auto text-xs text-ink-muted">{{ $def['label'] }}</span>
    </div>

    @if ($choices !== [])
        <div class="flex flex-col gap-2" role="radiogroup" aria-label="Turn choice">
            @foreach ($choices as $choice)
                @php $key = (string) ($choice['key'] ?? ''); @endphp
                <button type="button" role="radio" aria-checked="{{ $key === $selected ? 'true' : 'false' }}"
                        class="flex items-center gap-3 rounded-md border-2 px-3 py-2.5 text-left
                               {{ $key === $selected ? 'border-pick bg-raised' : 'border-rule bg-raised hover:border-green-line' }}">
                    <span class="grid size-6 shrink-0 place-items-center rounded border border-rule
                                 bg-sunken text-xs font-bold text-ink-strong">{{ $loop->iteration }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-base font-bold text-ink-strong">{{ $choice['label'] ?? '' }}</span>
                        @isset($choice['detail'])
                            <span class="block text-xs text-ink-muted">{{ $choice['detail'] }}</span>
                        @endisset
                    </span>
                    @if (($choice['facility_level'] ?? null) !== null)
                        <span class="rounded border border-rule px-2 py-0.5 text-xs font-semibold text-ink-muted">
                            Lv {{ $choice['facility_level'] }}
                        </span>
                    @endif
                    @if (($choice['present'] ?? null) !== null)
                        <span class="flex gap-0.5" role="img" aria-label="{{ $choice['present'] }} teammates on this tile">
                            @for ($i = 0; $i < 5; $i++)
                                <span class="h-3 w-2 {{ $i < (int) $choice['present'] ? 'bg-up' : 'bg-idle' }}"></span>
                            @endfor
                        </span>
                    @endif
                </button>
            @endforeach
        </div>
    @endif

    @if ($preview !== [])
        <div class="mt-3 rounded-md border border-rule bg-raised p-3">
            <h4 class="mb-2 text-xs font-bold uppercase tracking-widest text-ink-muted">Preview</h4>
            <div class="flex flex-wrap gap-4">
                @foreach ($preview as $delta)
                    {{-- increase is orange, decrease is blue. Green is reserved for actions. --}}
                    <span class="font-mono text-sm font-bold tabular-nums
                                 {{ ($delta['direction'] ?? 'up') === 'down' ? 'text-down' : 'text-up' }}">
                        {{ $delta['text'] ?? '' }}
                    </span>
                @endforeach
            </div>
            <p class="mt-2 text-xs text-ink-muted">
                Recorded as entered. No outcome is projected and no odds are shown, because no source
                publishes them. Anything you did not log is not claimed.
            </p>
        </div>
    @endif

    {{-- The gauge sits beside the control that spends it, never only in the header (D-171, G-30). --}}
    <div class="mt-3 flex flex-wrap items-center justify-end gap-4">
        <div class="flex items-center gap-2.5">
            <span class="flex gap-1" role="img" aria-label="Energy {{ (int) $energy }} of 100">
                @for ($i = 0; $i < 5; $i++)
                    <span class="h-2 w-6 rounded-sm {{ $i < $segments ? 'bg-green' : 'bg-idle' }}"></span>
                @endfor
            </span>
            <span class="font-mono text-sm tabular-nums text-ink-muted">Energy {{ (int) $energy }}/100</span>
        </div>
        <div class="flex gap-2">
            <button type="button" class="rounded-full border-2 border-rule px-4 py-2 text-sm font-bold text-ink-strong">
                Change
            </button>
            {{-- Enamel sheen: the client's buttons are glossy enamel, and this is the one
                 primary action on the screen, so the sheen marks it rather than decorating.
                 Hard-edged single split, never a feathered ramp (DESIGN.md §2.3, research §6.1). --}}
            <button type="button"
                    class="enamel rounded-full bg-chrome px-5 py-2 text-sm font-bold text-on-chrome">
                Confirm turn
            </button>
        </div>
    </div>
</div>
