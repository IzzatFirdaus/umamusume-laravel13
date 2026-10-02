@props(['title'])

{{-- 44px (h-11) pill. The fill stays on the accessible `chrome` step and never the client's
     bright lime, because the capsule carries a word: white on #7FCC09 measures 1.99:1
     (DESIGN.md §2.3, D-3). `lattice-bleed` is the existing argyle motif utility. --}}
<div {{ $attributes->merge(['class' => 'lattice-bleed flex h-11 items-center rounded-full bg-chrome pr-5 pl-20']) }}>
    <span class="text-base font-bold text-on-chrome">{{ $title }}</span>
</div>
