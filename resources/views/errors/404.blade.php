<x-layout title="Page not found">
    <h1 class="text-2xl font-semibold text-ink-strong">Page not found</h1>

    {{-- A 404 inside the product still has to read as the product: the shell gives it the
         same ground plane, the same nav, and the same theme, and the action below returns
         the Trainer to work rather than leaving them on a framework page with no way back.
         Named states, not "No data" (D-220). --}}
    <div class="mt-6 rounded-md border border-dashed border-rule bg-panel p-6">
        <p class="text-sm font-semibold text-ink-strong">Nothing to show here</p>
        <p class="mt-1 text-sm text-ink-muted">
            Trainer Desk has no page at this address. The run, Umamusume or review candidate you
            were looking for may have been deleted, or the link may be mistyped.
        </p>
        <div class="mt-4 flex flex-wrap gap-3 text-sm">
            <a href="{{ route('runs.index') }}"
               class="enamel rounded-full bg-chrome px-4 py-1.5 font-bold text-on-chrome">
                Training runs
            </a>
            <a href="{{ route('catalog.index') }}"
               class="rounded-full border-2 border-rule px-4 py-1.5 font-semibold text-ink-strong hover:underline">
                Catalog
            </a>
            <a href="{{ route('review.index') }}"
               class="rounded-full border-2 border-rule px-4 py-1.5 font-semibold text-ink-strong hover:underline">
                Review
            </a>
        </div>
    </div>
</x-layout>
