@props(['run'])

@php
    if (! $run->composesPanel('epithet_routes')) {
        return;
    }

    $fatigue = $run->latestFatigue();
    $hideAfterKey = config('scenarios.scenarios.'.$run->scenarioKey().'.race_fatigue.hide_after');

    /*
     * A map, not `str_replace('_', ' ', $key)`: `late_december` is this tool's identifier
     * for a moment in the calendar, and reshaping it in the page leaks the storage name
     * into prose. An unmapped key still renders as itself so a new value cannot silently
     * disappear from the sentence (the same lesson KI-18 is about).
     */
    $hideAfterLabels = ['late_december' => 'late December'];
    $hideAfterLabel = $hideAfterKey === null ? null : ($hideAfterLabels[$hideAfterKey] ?? $hideAfterKey);
@endphp

{{-- D-230 publishes the bands as percentages (0-15 / 0-33 / 60-90+ / 100) against 1 / 2 /
     3 / 4+ consecutive races, and one source is not two. The chip therefore says a word
     and names where the number lives, never a percentage the tool would be inventing. --}}
<div {{ $attributes->merge(['class' => 'rounded-md border border-rule bg-raised px-3 py-2 text-sm']) }}>
    <p class="flex flex-wrap items-baseline gap-x-3">
        <span class="font-bold text-ink-strong">Race fatigue</span>
        @if ($fatigue === null)
            <span class="text-ink-muted">no consecutive-race reading recorded</span>
        @else
            <span class="text-ink">
                {{ $fatigue->consecutiveRaces }}
                {{ $fatigue->consecutiveRaces === 1 ? 'race' : 'races' }} in a row, a mood
                downgrade is <span class="font-bold">{{ $fatigue->riskWord() }}</span>
            </span>
        @endif
    </p>
    <p class="mt-1 text-xs text-ink-muted">
        The percentages and the countermeasures live in docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md section "Gameplay Flow & Race Fatigue".
        The table stops being quoted after late December, and the final three races pay
        coins instead of fatigue
        @if ($hideAfterLabel !== null)
            (this scenario's calendar hides the reading after {{ $hideAfterLabel }})
        @endif
        .
    </p>
</div>
