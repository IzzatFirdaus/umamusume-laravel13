@props([
    // No default, for the same reason as x-resource-strip: a named default puts a
    // scenario in the view, and a step rail with no scenario has no steps to order.
    'scenario',
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
        'team_race' => 'Choose opponent',
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
                               {{ $key === $selected ? 'border-pick-line bg-raised' : 'border-rule bg-raised hover:border-green-line' }}">
                    <span class="grid size-6 shrink-0 place-items-center rounded border border-rule
                                 bg-sunken text-xs font-bold text-ink-strong">{{ $loop->iteration }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-base font-bold text-ink-strong">{{ $choice['label'] ?? '' }}</span>
                        @if (($choice['unverified'] ?? false) === true)
                            {{-- The client label for a mood adjustment is not confirmed
                                 as one string or the other, so the gap is shown rather
                                 than resolved by picking one (D-20). --}}
                            <span class="ml-1 inline-block rounded border border-down px-1 align-middle text-xs font-bold text-down"
                                  title="Not confirmed as the Global client string">[Unverified]</span>
                        @endif
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

    {{--
        The two scenario-owned steps. Each is gated twice: the step key decides
        whether to draw the body, and the scenario's own panel flag decides whether
        this scenario is allowed to have one. The second gate is not belt-and-braces
        — a fifth scenario can arrive with a `shop` step in its config and no shop
        behind it, and an empty till would be worse than no till (D-220, gate G-40).
    --}}
    @if ($current === 'shop' && ($def['panels']['shop'] ?? false) === true)
        <div class="mt-3 rounded-md border border-rule bg-raised p-3">
            <h4 class="text-xs font-bold uppercase tracking-widest text-ink-muted">Shop</h4>

            {{-- D-232: the rotation is the shop's primary number. Unspent coins die
                 with the run, so "how long until this lineup changes" is the decision
                 the Trainer is making; a balance would be the reassuring number. --}}
            <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-xs text-ink-muted">
                <span class="font-semibold text-ink">
                    Rotation resets in {{ (int) $def['shop']['rotation_turns'] }} turns
                </span>
                <span>Shop Coins: not yet recorded</span>
                <span>Up to {{ (int) $def['shop']['max_copies_per_item'] }} copies of one item</span>
                @if ($def['shop']['locked_until_debut'] === true)
                    <span>Locked until debut</span>
                @endif
            </div>

            <ul class="mt-3 flex flex-col gap-1.5">
                @foreach ($def['shop_items'] ?? [] as $item)
                    <li class="flex items-baseline justify-between gap-3 rounded-md border border-rule bg-panel px-3 py-2 text-sm">
                        <span class="min-w-0 flex-1">
                            <span class="font-semibold text-ink-strong">{{ $item['name'] }}</span>
                            <span class="block text-xs text-ink-muted">{{ $item['effect'] }}</span>
                        </span>
                        {{-- The client puts Sale top-left and Limited top-right on the
                             shop button, so a Trainer scans corners. Held counts are
                             run state, and no rotation is modelled yet, so nothing is
                             flagged and no count is claimed. --}}
                        <span class="flex shrink-0 items-center gap-1.5">
                            @if (($item['sale'] ?? false) === true)
                                {{-- Blue is the palette's "decrease", and a discount is
                                     the one priced thing on this row that goes down.
                                     Orange would read as a stat increase. --}}
                                <span class="rounded border border-down px-1.5 text-xs font-bold text-down">Sale</span>
                            @endif
                            @if (($item['limited'] ?? false) === true)
                                <span class="rounded border border-idle px-1.5 text-xs font-bold text-ink-muted">Limited</span>
                            @endif
                            <span class="font-mono text-sm tabular-nums text-ink-strong">{{ (int) $item['cost'] }}c</span>
                        </span>
                    </li>
                @endforeach
            </ul>

            <p class="mt-2 text-xs text-ink-muted">
                held: not yet recorded · A multi-turn item cannot be used again while active, and
                buying a weaker effect than the one running overwrites the active one, so the order
                of two purchases is a real, lossy decision.
            </p>
            <p class="mt-1 text-xs text-ink-muted">
                Flags sit where the client puts them: Sale top-left, Limited top-right. This build
                does not model the rotation, so no offer is flagged and the rows above are the item
                catalogue rather than a current lineup.
            </p>
        </div>
    @endif

    @if ($current === 'team_race' && ($def['panels']['team_race'] ?? false) === true)
        <div class="mt-3 rounded-md border border-rule bg-raised p-3">
            <h4 class="text-xs font-bold uppercase tracking-widest text-ink-muted">
                Opponent · one of {{ (int) $def['team_race']['opponent_count'] }}
            </h4>

            <ul class="mt-2 flex flex-col gap-1.5">
                @foreach ($def['team_race']['opponents'] as $opponent)
                    <li class="flex items-baseline justify-between gap-3 rounded-md border border-rule bg-panel px-3 py-2 text-sm">
                        <span class="min-w-0 flex-1">
                            <span class="font-semibold text-ink-strong">{{ $opponent['name'] }}</span>
                            <span class="block text-xs text-ink-muted">{{ $opponent['tier'] }}</span>
                        </span>
                        {{-- The circles are the client's own estimate, shown before you
                             commit. This tool records what the Trainer saw and never
                             computes it, so the number is not here to be guessed at. --}}
                        <span class="shrink-0 text-xs text-ink-muted">circles not yet recorded</span>
                    </li>
                @endforeach
            </ul>

            <p class="mt-2 rounded-md border border-rule bg-panel px-3 py-2 text-xs text-ink">
                Before you commit, the game shows a circle-based win-odds estimate per category.
                Source guidance: aim for at least {{ (int) $def['team_race']['circles_guidance'] }} circles
                in total as a margin, not a win condition, because a loss lowers league rank and
                beating a stronger team raises it further. Opponent names are sample data: the
                client names its own teams, and this tool has no source for them.
            </p>
            <p class="mt-1 text-xs text-ink-muted">
                A Team Race comes every {{ (int) $def['team_race']['occurs_every_months'] }} months.
                @if ($def['team_race']['loss_retryable_with_alarm_clock'] === true)
                    A loss can be retried with an Alarm Clock item, so a bad race day is not
                    permanent. Older guidance that says it is, is out of date.
                @endif
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
