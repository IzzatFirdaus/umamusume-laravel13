@props([
    // The trainee. The letters are hers, not the card's: ADR-0008 refused aptitude at card
    // grain precisely because it would be a second, disagreeing answer to a question already
    // answered once (FR-A-5), so this component takes the trainee and never a card.
    'umamusume',
])

{{--
    The ten aptitude letters in the export's own element order, as `GametoraCharacterParser`
    stores them: surface, the four distances, the four running styles.

    **A two-row grid, not a ten-item list, because the question is two questions.** "How does she
    run" and "how fast does she go" are separate reads, and a single ten-column row makes a
    Trainer scan all ten to answer either. Row one is where she stands, row two is how she runs.

    **Absent is absent (D-220).** The parser writes all ten or none: `aptitudes()` returns an
    empty array unless the source carried a complete, well-formed set, so a trainee either has ten
    letters or has none, and a half-rendered grid would be a shape this source cannot produce. When
    there are none the component renders the sentence that says so, which is the one section whose
    absence is itself informative.
--}}
@php
    $surface = [
        'Turf' => $umamusume->aptitude_turf,
        'Dirt' => $umamusume->aptitude_dirt,
    ];
    $distances = [
        'Sprint' => $umamusume->aptitude_sprint,
        'Mile' => $umamusume->aptitude_mile,
        'Medium' => $umamusume->aptitude_medium,
        'Long' => $umamusume->aptitude_long,
    ];
    $strategies = [
        'Front runner' => $umamusume->aptitude_front_runner,
        'Pace chaser' => $umamusume->aptitude_pace_chaser,
        'Late surger' => $umamusume->aptitude_late_surger,
        'End closer' => $umamusume->aptitude_end_closer,
    ];
    $any = $surface + $distances + $strategies;
@endphp

<div class="mt-3">
    @if (in_array(null, $any, true))
        <p class="text-sm text-ink-muted">Aptitude not published for this trainee.</p>
    @else
        <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm sm:grid-cols-4">
            @foreach ([$surface, $distances, $strategies] as $row)
                @foreach ($row as $label => $letter)
                    <div class="flex items-baseline justify-between gap-2 border-b border-rule pb-1">
                        <dt class="text-ink-muted">{{ $label }}</dt>
                        {{-- The letter is the readout and it is the client's own grading, so it
                             is never re-worded here. `text-ink-strong` on `bg-raised` is a pair
                             DESIGN.md §3.4 already measures. --}}
                        <dd class="font-mono font-bold text-ink-strong">{{ $letter }}</dd>
                    </div>
                @endforeach
            @endforeach
        </dl>
    @endif
</div>
