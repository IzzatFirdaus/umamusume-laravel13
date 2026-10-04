@props([
    // No default, for the same reason as x-resource-strip: a named default puts a
    // scenario in the view, and a step rail with no scenario has no steps to order.
    'scenario',
    // See x-resource-strip: the baseline key is what the rail composes steps from, not
    // a claim that the run runs that scenario (audit F-3, D-220).
    'declared' => true,
    'current' => null,
    'selected' => null,
    'choices' => [],
    'preview' => [],
    // Whether this response *is* a preview. Deliberately not read off `preview`:
    // a first turn previews to an empty delta list, because there is no stored row to
    // subtract, and an empty list used to mean both "not a preview" and "a preview of
    // nothing", which left a run's first turn uncommittable through the rail (D-1).
    'previewed' => false,
    // null is a real state, not a missing one: a run that has logged no turn has no
    // Energy reading. Defaulting to 0 would tell the Trainer the trainee is exhausted,
    // and draw five empty cells to say it (D-220).
    'energy' => null,
    // Present means this card is the live door and it owns a form. Absent means the
    // review surface: same card, same states, nothing to submit to.
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
    $segments = $energy === null ? null : max(0, min(5, (int) round((int) $energy / 20)));

    /*
     * The band word, its fill and its ink, in one table, so the treatment that is chosen
     * here is the treatment the contrast pass measures (D-10). 50 is the only sourced
     * Energy threshold (D-204); the 30 line is an owner ruling, which is why the Danger
     * state has to say so out loud rather than let a red chip imply it is a game fact.
     *
     * `text-on-chrome` is not a mistake on a risk chip: it is the repo's one theme-flipping
     * ink, white on the dark light-theme red and near-black on the light dark-theme red,
     * which is the same job it does on the enamel button.
     */
    $band = $energy === null ? null : (match (true) {
        $energy > 50 => ['word' => 'Safe', 'treat' => 'bg-green-tint text-ink'],
        $energy >= 30 => ['word' => 'Caution', 'treat' => 'bg-pick text-on-pick'],
        default => ['word' => 'Danger', 'treat' => 'bg-risk text-on-chrome'],
    });
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md border border-rule bg-panel p-3']) }}>
    {{-- The form exists only when there is somewhere to send it. Stage one and stage two
         are both POSTs to the same endpoint and differ by one submitted button's name,
         which is what keeps D-51's "committing is a separate action" true with no script
         loaded at all. --}}
    @if ($confirmRoute !== null)
        <form method="POST" action="{{ $confirmRoute }}">
            @csrf
    @endif

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
        @if ($declared)
            <span class="ml-auto text-xs text-ink-muted">{{ $def['label'] }}</span>
        @endif
    </div>

    @if ($confirmRoute !== null && $choices !== [])
        {{-- The shortcuts are only honest if they are on screen: a key binding nobody can
             discover is folklore. The count comes from the choices themselves, so the line
             cannot claim a key that the rail does not offer. --}}
        <p class="-mt-1 mb-3 text-xs text-ink-muted">
            Keys 1 to {{ count($choices) }} choose an activity, arrow keys move between them,
            Enter previews the turn, Escape returns to the choices.
        </p>
    @endif

    @if ($choices !== [])
        {{--
            A real radio group wearing the client's banner shape, and both halves are load-
            bearing.

            The radio: a `role="radio"` button carries no value on submit, so the rail could
            only ever have been a picture of a form, and the audit's deferred accessibility
            finding was exactly that - the group claimed a semantics its children did not
            have. Native radios also rove focus with the arrow keys for free, which is the
            half of D-55 a script would otherwise have to reimplement badly.

            The banner: §6.10 and §8.6 both say choices are banner buttons and "never radio
            inputs", which is a rule about the shape a Trainer reads, not about the element
            that holds state. So the input is the zero-size semantic layer and the label is
            the §6.1 banner, selected by `peer-checked` and ringed by `peer-focus-visible` -
            the global `:focus-visible` rule would otherwise draw its outline on an element
            with no box to draw on.
        --}}
        <div class="flex flex-col gap-2" role="radiogroup" aria-label="Turn choice">
            @foreach ($choices as $choice)
                @php $key = (string) ($choice['key'] ?? ''); @endphp
                <label class="block">
                    {{-- 1px, not 0. A zero-size box is "not visible" to every tool that
                         measures visibility, Playwright's click included, which would make
                         the rail untestable at the control that carries its state; `sr-only`
                         uses the same 1px clip for the same reason. The banner is what a
                         person sees and clicks, and the label forwards the click here. --}}
                    <input type="radio" name="choice" value="{{ $key }}" class="peer size-px opacity-0"
                           @checked($key === $selected)>
                    <span class="flex cursor-pointer items-center gap-3 rounded-md border-2 border-rule bg-raised px-3 py-2.5 text-left
                                 hover:border-green-line
                                 peer-checked:border-pick-line
                                 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-ring">
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
                    </span>
                </label>
            @endforeach
        </div>
    @endif

    {{-- Everything the mounted rail needs beyond the choice cards - the numbers, the mood,
         the outcome - arrives as the slot, from the screen that knows the route. The card
         stays a card; it does not learn what a turn entry costs. --}}
    {{ $slot }}

    @if ($preview !== [])
        <div class="mt-3 rounded-md border border-rule bg-raised p-3">
            <h3 class="mb-2 text-xs font-bold uppercase tracking-widest text-ink-muted">Preview</h3>
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
            <h3 class="text-xs font-bold uppercase tracking-widest text-ink-muted">Shop</h3>

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
            <h3 class="text-xs font-bold uppercase tracking-widest text-ink-muted">
                Opponent · one of {{ (int) $def['team_race']['opponent_count'] }}
            </h3>

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
        <div class="flex flex-wrap items-center gap-2.5">
            @if ($segments === null)
                {{-- Absent, not zero: an empty five-cell gauge would say the trainee is
                     exhausted on a run that has not logged a turn (D-220). --}}
                <span class="font-mono text-sm text-ink-muted">Energy not yet recorded</span>
            @else
                <span class="flex gap-1" role="img" aria-label="Energy {{ (int) $energy }} of 100">
                    @for ($i = 0; $i < 5; $i++)
                        <span class="h-2 w-6 rounded-sm {{ $i < $segments ? 'bg-green' : 'bg-idle' }}"></span>
                    @endfor
                </span>
                <span class="font-mono text-sm tabular-nums text-ink-muted">Energy {{ (int) $energy }}/100</span>
            @endif

            @if ($band !== null)
                <span class="rounded border border-transparent px-2 py-0.5 text-xs font-bold {{ $band['treat'] }}">
                    {{ $band['word'] }}
                </span>
            @endif
        </div>

        @if ($band !== null && $band['word'] === 'Danger')
            <p class="w-full text-right text-xs text-ink-muted">
                Danger starts below 30, and that line is this tool's own ruling: the client
                publishes no Energy threshold below 50.
            </p>
        @endif

        @if ($band !== null && (int) $energy < 50)
            {{-- The advisory is dialogue, not a banner (§6.16), and it carries its
                 arithmetic: the constant and the stored value that produced the line,
                 so a Trainer can check it (D-256, Planner Rule 5). --}}
            <p class="w-full rounded-md border border-rule bg-raised px-3 py-2 text-right text-xs text-ink">
                {{-- The badge is green because §6.16 says the client's hint is green. The ink
                     used to be borrowed: `text-on-pick` on `bg-green` measured 6.65:1 in light
                     and 9.51:1 in dark, so it passed while wearing the name of the gold fill.
                     Slice 6 named the pair instead, because a passing borrow is how a component
                     grows a dependency nobody documented. `--color-on-green` is #1F1508 and
                     `--color-green` is the same #7FCC09 in both themes, so one value serves
                     both: 9.02:1 measured on this badge in each
                     (`slice-6-2026-09-28.md` §3). White on the same fill is 1.99:1, which is
                     the combination D-3 forbids. --}}
                <span class="mr-1.5 rounded bg-green px-1.5 font-bold text-on-green">Hint</span>
                Wit costs 0 Energy and you are at {{ (int) $energy }}. Rest refills Energy,
                and a rest can backfire, so it is a choice rather than a safe button.
                <span class="block text-ink-muted">GameWith guidance, 2026-09-25.</span>
            </p>
        @endif

        <div class="flex gap-2">
            @if ($confirmRoute === null)
                {{-- The review surface has no route to post to, so it shows the card's two
                     footer shapes as shapes. On a mounted rail both become real submits. --}}
                <button type="button" class="rounded-full border-2 border-rule px-4 py-2 text-sm font-bold text-ink-strong">
                    Change
                </button>
                <button type="button"
                        class="enamel rounded-full bg-chrome px-5 py-2 text-sm font-bold text-on-chrome">
                    Confirm turn
                </button>
            @else
                <button type="submit" name="stage" value="preview"
                        class="rounded-full border-2 border-rule px-4 py-2 text-sm font-bold text-ink-strong">
                    Preview this turn
                </button>

                @if ($previewed)
                    {{-- The gate on confirming is a rendered marker, not a script: the field
                         only exists in a response that has already shown a preview. It is a
                         UX guard with no auth behind it, which is all NFR-1's local-only tool
                         can ask for, and it is enough to make D-51's "always" the server's
                         rule instead of a suggestion.

                         The condition is "this response is a preview", not "this response has
                         deltas to show". A run's first turn has no previous row, so its
                         preview is empty by arithmetic, and gating on the list made the first
                         turn of every run uncommittable here - the Trainer had to use the raw
                         escape hatch to start a run at all, which is the path D-53 requires to
                         be reachable rather than default. --}}
                    <input type="hidden" name="previewed" value="1">
                    <button type="submit" name="stage" value="confirm"
                            class="enamel rounded-full bg-chrome px-5 py-2 text-sm font-bold text-on-chrome">
                        Confirm turn
                    </button>
                @endif
            @endif
        </div>
    </div>

    @if ($confirmRoute !== null)
        </form>
    @endif
</div>
