{{--
    Same treatment as the 404: a standalone document, because the product's shell is
    `AppLayout.vue` now and the Blade shell it used to share was retired with the Inertia
    port (ADR-0020 §1).

    This tool has no login (ARCHITECTURE §8), so a 419 does not mean "sign in again": it
    means the page the form was opened from is older than the session behind it, which is
    what a tab left open across `migrate:fresh` or a restart produces. `$theme` is composed
    server-side by AppServiceProvider; the inline script is the fallback for no stored
    preference and stays in the head so first paint is not the wrong theme.
--}}
<!DOCTYPE html>
<html lang="en" @if (! empty($theme)) data-theme="{{ $theme }}" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign-in session expired</title>
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
    <a href="#main"
       class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:border-2 focus:border-rule focus:bg-raised focus:px-4 focus:py-2 focus:text-sm focus:font-bold focus:text-ink-strong">
        Skip to content
    </a>
    <main id="main" class="mx-auto max-w-5xl px-4 py-8">
        <h1 class="text-2xl font-semibold text-ink-strong">Sign-in session expired</h1>

        <div class="mt-6 rounded-md border border-dashed border-rule bg-panel p-6">
            <p class="text-sm font-semibold text-ink-strong">Nothing was saved</p>
            <p class="mt-1 text-sm text-ink-muted">
                The form you submitted came from an older page than the one this browser is holding a
                session for. Open the screen again and submit it there; use the browser's Back step to
                reach what you had typed.
            </p>
            <div class="mt-4 flex flex-wrap gap-3 text-sm">
                <a href="{{ route('runs.index') }}"
                   class="enamel inline-flex min-h-11 items-center rounded-full bg-chrome px-4 font-bold text-on-chrome">
                    Training runs
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
