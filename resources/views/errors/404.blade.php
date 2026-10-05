{{--
    A standalone document, deliberately not a shared shell: the product's shell is
    `AppLayout.vue` now, a client component that assumes a page payload, and the Blade
    shell it used to share (`components/layout.blade.php`) was retired with the Inertia
    port (ADR-0020 §1). The 404 keeps the same ground plane, the same theme order, and a
    way back to work rather than leaving the Trainer on a framework page.

    Named states, not "No data" (D-220). `$theme` is composed server-side by
    AppServiceProvider so the first paint is already the chosen theme; the inline script
    is the fallback for a Trainer with no stored preference, and it stays in the head
    because a bundled script runs after first paint and would flash the wrong theme.
--}}
<!DOCTYPE html>
<html lang="en" @if (! empty($theme)) data-theme="{{ $theme }}" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page not found</title>
    @empty($theme)
        <script>
            (function () {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.dataset.theme = 'dark';
                }
            })();
        </script>
    @endempty
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-page text-ink">
    {{-- The shell's first Tab used to walk the whole nav before reaching the work. This
         page has no nav, but the target exists and the link keeps the same first-paint
         behaviour the rest of the product ships. --}}
    <a href="#main"
       class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:border-2 focus:border-rule focus:bg-raised focus:px-4 focus:py-2 focus:text-sm focus:font-bold focus:text-ink-strong">
        Skip to content
    </a>
    <main id="main" class="mx-auto max-w-5xl px-4 py-8">
        <h1 class="text-2xl font-semibold text-ink-strong">Page not found</h1>

        <div class="mt-6 rounded-md border border-dashed border-rule bg-panel p-6">
            <p class="text-sm font-semibold text-ink-strong">Nothing to show here</p>
            <p class="mt-1 text-sm text-ink-muted">
                Trainer Desk has no page at this address. The run, Umamusume or review candidate you
                were looking for may have been deleted, or the link may be mistyped.
            </p>
            <div class="mt-4 flex flex-wrap gap-3 text-sm">
                <a href="{{ route('runs.index') }}"
                   class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-4 font-bold text-on-chrome">
                    Training runs
                </a>
                <a href="{{ route('catalog.index') }}"
                   class="inline-flex min-h-11 items-center rounded-full border-2 border-rule px-4 font-semibold text-ink-strong hover:underline">
                    Catalog
                </a>
                <a href="{{ route('review.index') }}"
                   class="inline-flex min-h-11 items-center rounded-full border-2 border-rule px-4 font-semibold text-ink-strong hover:underline">
                    Review
                </a>
            </div>
        </div>
    </main>
</body>
</html>
