@props(['run'])

@php
    /*
     * Self-gating on the composition matrix, the same way the grade meter does: a shop
     * panel on a scenario without one would be describing a mechanic the run does not
     * have (D-221, D-241, gate G-34).
     */
    if (! $run->composesShop()) {
        return;
    }

    $rotation = (int) config('scenarios.scenarios.'.$run->scenarioKey().'.shop.rotation_turns', 0);

    // Purchases are events, not a table: read the typed payload off each turn_event and
    // ignore the events that carry a different shape (D-226, ADR-0003).
    $purchases = $run->turnEvents
        ->map(fn (\App\Models\TurnEvent $event): ?\App\Models\TurnEvents\ShopPurchasePayload => $event->purchasePayload())
        ->filter()
        ->values();
@endphp

<div {{ $attributes->merge(['class' => 'rounded-md border border-rule bg-panel p-3']) }}>
    <div class="lattice-bleed mb-3 flex h-11 items-center rounded-full bg-chrome pl-16 pr-4 text-sm font-bold text-on-chrome">
        <span>Shop</span>
    </div>

    <div class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1 text-sm">
        {{-- The countdown is entered, never computed. `turn % rotation` would be a claim
             about a rotation this build does not model, and `0 turns` would say the lineup
             changes on the next turn (D-232, D-220). --}}
        <p class="text-ink">
            <span class="font-bold text-ink-strong">Rotation:</span>
            @if ($run->shop_resets_in === null)
                <span class="text-ink-muted">countdown not recorded</span>
            @else
                <span class="font-mono tabular-nums">resets in {{ (int) $run->shop_resets_in }}
                    {{ (int) $run->shop_resets_in === 1 ? 'turn' : 'turns' }}</span>
            @endif
            <span class="text-xs text-ink-muted">of a {{ $rotation }}-turn rotation</span>
        </p>

        {{-- Buying at a higher rank overwrites the lower one, and the corpus says the
             unspent balance dies with the run: both are warnings the panel owes before
             the Trainer commits, not footnotes after it (D-232). --}}
        <p class="text-xs text-ink-muted">
            Buying at a higher rank overwrites the lower one, and unspent Shop Coins do
            not survive the run.
        </p>
    </div>

    @if ($purchases->isEmpty())
        {{-- Empty, not zero: a run that has not bought anything has no rows, and an
             empty list is the truth. The name of the writer is stated because this
             build renders purchases recorded as payloads and offers no purchase form
             yet (C-7 empty state, D-220). --}}
        <p class="mt-3 rounded-md border border-rule bg-raised px-3 py-2 text-sm text-ink-muted" role="status">
            No purchases recorded for this run.
        </p>
    @else
        <ul class="mt-3 flex flex-col gap-1.5" aria-label="Recorded purchases">
            @foreach ($purchases as $purchase)
                <li class="flex items-baseline justify-between gap-3 rounded-md border border-rule bg-raised px-3 py-2 text-sm">
                    <span class="min-w-0 flex-1">
                        <span class="font-semibold text-ink-strong">{{ $purchase->item }}</span>
                        <span class="mt-0.5 block text-xs text-ink-muted">{{ $purchase->effect }}</span>
                    </span>
                    <span class="shrink-0 font-mono text-xs tabular-nums text-ink-muted">
                        {{ number_format($purchase->cost) }} coins
                    </span>
                </li>
            @endforeach
        </ul>
    @endif

    <div class="mt-3 flex flex-wrap gap-x-6 gap-y-1 border-t border-rule pt-3 text-xs text-ink-muted">
        {{-- A spend total is a sum of entered costs. A balance would need the earning side,
             which no turn records, so it is named as missing rather than subtracted toward
             zero (Planner Rule 5). --}}
        <span>Spent: <span class="font-mono tabular-nums text-ink">{{ number_format($run->shopSpendTotal()) }}</span> coins</span>
        <span>Shop Coins: not yet recorded</span>
    </div>

    <form method="POST" action="{{ route('runs.update', $run) }}" class="mt-3 flex flex-wrap items-end gap-3 rounded-md border border-rule bg-raised p-3 text-sm">
        @csrf
        @method('PUT')
        {{-- Carried through so the shared request does not blank the fields this form is
             not editing (Slice 5's update contract). --}}
        <input type="hidden" name="umamusume_id" value="{{ $run->umamusume_id }}">
        <input type="hidden" name="status" value="{{ $run->status->value }}">
        <input type="hidden" name="scenario" value="{{ $run->scenario }}">
        <input type="hidden" name="current_objective_index" value="{{ $run->current_objective_index }}">
        <label class="flex flex-col gap-1">
            <span class="font-medium text-ink">Turns until rotation</span>
            <select name="shop_resets_in" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                <option value="">Not recorded</option>
                @foreach (range(0, $rotation) as $turns)
                    <option value="{{ $turns }}" @selected($run->shop_resets_in === $turns)>{{ $turns }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="rounded-full border-2 border-rule px-4 py-2 font-bold text-ink-strong">Report rotation</button>
        @error('shop_resets_in')<p class="w-full text-risk">{{ $message }}</p>@enderror
    </form>
</div>
