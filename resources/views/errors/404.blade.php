{{--
    A standalone document, deliberately not a shared shell: the product's shell is
    `AppLayout.vue` now, a client component that assumes a page payload, and the Blade
    shell it used to share (`components/layout.blade.php`) was retired with the Inertia
    port (ADR-0020 §1). The 404 keeps the same ground plane, the same theme order, and a
    way back to work rather than leaving the Trainer on a framework page.

    Structure is the HFW error-404.html: the data-loss question answered in the lede, then
    the three exits with one filled, then the region that says what this page does not do.
    Its `.keybox` annotation block is wireframe documentation and is not shipped.

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
    <title>That page does not exist</title>
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
        <p class="text-xs font-semibold uppercase tracking-wider text-ink-muted">404</p>
        <h1 class="mt-1 text-3xl font-semibold text-ink-strong">That page does not exist</h1>
        <p class="mt-2 max-w-3xl text-sm text-ink-muted">
            The address did not match any screen. Nothing was changed and nothing was lost.
        </p>

        <div class="mt-6 rounded-md border border-dashed border-rule bg-panel p-6 text-center">
            <h2 class="text-lg font-semibold text-ink-strong">Where would you like to go?</h2>
            <p class="mx-auto mt-1 max-w-3xl text-sm text-ink-muted">
                If you followed a link from inside the app, that link is broken and worth reporting.
                If you typed the address, check it against one of these.
            </p>
            <div class="mt-4 flex flex-wrap justify-center gap-3 text-sm">
                <a href="{{ route('home') }}"
                   class="inline-flex min-h-11 items-center rounded-full bg-primary-action px-4 font-bold text-on-primary-action">
                    Dashboard
                </a>
                <a href="{{ route('runs.index') }}"
                   class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-4 font-semibold text-ink hover:text-ink-strong">
                    Careers
                </a>
                <a href="{{ route('database.index') }}"
                   class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-4 font-semibold text-ink hover:text-ink-strong">
                    Database
                </a>
            </div>
        </div>

        <div class="mt-4 rounded-md border border-rule bg-panel p-6">
            <h2 class="text-lg font-semibold text-ink-strong">What this page does not do</h2>
            <p class="mt-1 max-w-3xl text-sm text-ink-muted">
                It does not redirect you somewhere else automatically. A silent redirect hides the
                broken link that sent you here, which is the thing worth fixing.
            </p>
        </div>
    </main>
</body>
</html>
