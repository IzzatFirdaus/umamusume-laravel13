@props(['source' => null])

{{-- Assistant dialogue, not a banner (§6.16). The badge ink is `--color-on-green`: the pair is
     #1F1508 on #7FCC09 in both themes and measures 9.02:1, where white on the same fill is
     1.99:1 (D-3). `source` names where the claim came from, so a recommendation carries its
     provenance rather than asserting it. --}}
<div {{ $attributes->merge(['class' => 'rounded-md border border-rule bg-raised px-3 py-2 text-sm text-ink']) }}>
    <span class="mr-1.5 rounded bg-green px-1.5 font-bold text-on-green">Hint</span>{{ $slot }}
    @if ($source !== null)
        <span class="block text-ink-muted">{{ $source }}</span>
    @endif
</div>
