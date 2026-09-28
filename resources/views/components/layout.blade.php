<!DOCTYPE html>
{{--
    Theme resolution order, so the first paint is already correct (CONSTRAINTS.md D-104, G-20):
    a stored preference wins, then the OS, then light as the base palette. The preference is
    rendered server-side by `$theme` via `AppServiceProvider` composer — owner ruling 2026-09-27
    puts preferences in SQLite, not the browser, because PRD §6.12 cuts browser-side storage as a
    second source of truth. The inline script is the fallback when no row exists; it must stay
    inline and in the head: a bundled script runs after first paint and would flash the wrong theme.
--}}
<html lang="en" @if (! empty($theme)) data-theme="{{ $theme }}" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Trainer Companion' }}</title>
    @empty($theme)
        <script>
            (function () {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.dataset.theme = 'dark';
                }
            })();
        </script>
    @endempty
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
{{--
    The shell is the ground plane for both themes, so it renders from tokens, not from the
    skeleton's zinc utilities: with system-follow active, `bg-zinc-50 text-zinc-900` kept the
    page near-white while `--color-page` had already resolved to #0D0C0F, i.e. a dark theme with
    a light body. Page-level tables and forms still use zinc utilities (root DESIGN.md §2.1
    implementation status); this is the shared fix, not the whole migration.
--}}
<body class="min-h-screen bg-page text-ink">
    {{-- D-55 and G-11: the flow must be completable without a pointer, and the first Tab
         from a keyboard Trainer should not have to walk the whole nav to reach the work.
         The link is visually hidden until it has focus, which is the only state in which it
         is useful; `focus:not-sr-only` reveals it in place rather than leaving it invisible
         to the person who just pressed Tab. --}}
    <a href="#main"
       class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:border-2 focus:border-rule focus:bg-raised focus:px-4 focus:py-2 focus:text-sm focus:font-bold focus:text-ink-strong">
        Skip to content
    </a>
    <nav class="border-b border-rule bg-panel">
        <div class="mx-auto flex max-w-5xl gap-6 px-4 py-3 text-sm font-medium">
            <a href="{{ route('catalog.index') }}" class="hover:underline">Catalog</a>
            <a href="{{ route('runs.index') }}" class="hover:underline">Training runs</a>
            <a href="{{ route('review.index') }}" class="hover:underline">Review</a>
        </div>
    </nav>

    <main id="main" class="mx-auto max-w-5xl px-4 py-8">
        @if (session('status'))
            <p class="mb-4 rounded border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800">{{ session('status') }}</p>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
