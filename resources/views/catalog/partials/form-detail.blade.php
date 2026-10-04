{{--
    One costume form's body, included by `x-form-tabs` once per form and included directly by
    `catalog/show.blade.php` when a trainee has exactly one and the strip is not drawn.

    Expected data: `$card` (App\Models\CharacterCard) and `$trainee` (App\Models\Umamusume).

    **Why this is a partial and not inline markup in the tab component:** a Blade component has
    one `$slot` and this needs N bodies, one per form. Keeping the body here also means the
    single-form path renders the same markup as the multi-form path, which is the whole point of
    the `> 1` threshold: one form gains no chrome, not different content.
--}}
<div class="rounded-md border border-rule bg-raised p-4">
    {{-- The title is verbatim client copy, brackets included: the rule in
         docs/research-scratch/GOVERNANCE.md (CONSTRAINTS section, "Lore banned patterns")
         keeps such a string as source data and puts the lore guard on the display path,
         so nothing here normalises, trims or re-cases it. --}}
    <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
        <h3 class="text-base font-semibold text-ink-strong">{{ $card->title }}</h3>
        <span class="flex flex-wrap items-baseline gap-3 text-xs text-ink-muted">
            <x-rarity-chip :rarity="$card->rarity" />
            @if ($card->is_debut_form)
                <span>debut form</span>
            @endif
            {{-- `text-risk` on `bg-raised` is a pair DESIGN.md §3.4 measures and owns; the ratios
                 live there and not here, so this line cannot go stale against them. No second
                 badge and no new token: the flag is words, and the chip names the stars. --}}
            @if ($card->unconfirmed)
                <span class="font-semibold text-risk">Not confirmed by two sources</span>
            @endif
            {{-- A date-only column stays date-only: no zone conversion here, the same convention
                 the tree row and the debut rows follow. --}}
            <time datetime="{{ $card->global_release_date->toDateString() }}">
                Released (Global) {{ $card->global_release_date->format('M j, Y') }}
            </time>
        </span>
    </div>

    {{-- D-33, one level deeper than the trainee's own provenance list: this is *this form's*
         fetch. The snapshot leads where one exists because it is the in-tree copy this fetch
         read, and it is a disk path rather than an address, so it is named and not linked. The
         URL follows as link text that is the address itself, because that string came from
         `config('uma.sources')` and not out of a fetched body. `fetched_at` is nullable, so an
         un-fetched row prints no read date instead of an empty one, and a stored instant renders
         through the display zone. --}}
    <p class="mt-2 text-xs text-ink-muted">
        from
        @if ($card->snapshot_path)
            {{ $card->snapshot_path }} ·
        @endif
        <a href="{{ $card->source_url }}" class="text-ink-strong underline">{{ $card->source_url }}</a>
        @if ($card->fetched_at)
            · read {{ $card->fetched_at->timezone(config('uma.display_timezone'))->format('M j, Y') }}
        @endif
    </p>

    {{-- Aptitudes moved to the top-level Aptitudes section in `catalog/show.blade.php` (WS-2's
         binding order puts them second, under Identity). Rendering the grid here drew the same ten
         letters once per form on a trainee with several forms; they belong to the trainee, not to
         a form, so they render once above. --}}
</div>
