{{-- A standalone document, deliberately not a shared shell: this page is what a Trainer sees when part
     of the app is already failing, and the theme composer reads the stored preference from the
     `preferences` table (D-104). A dead database would turn the error page into a second error.
     The theme order this app documents is stored value, then operating system, then light; with the
     store unreachable the first term is unavailable, so the inline fallback is what keeps the first
     paint themed (G-20). It stays in the head for the reason every root document keeps it there: a
     bundled script runs after first paint and would flash the wrong theme. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Something broke on this machine</title>
    <script>
        (function () {
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.dataset.theme = 'dark';
            }
        })();
    </script>
    {{-- The stylesheet only: the shell's JavaScript entry went away with the Inertia port, and a page
         that draws itself without the database should not be the last thing pinned to it. --}}
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-page text-ink">
    <main id="main" class="mx-auto max-w-5xl px-4 py-8">
        <h1 class="text-2xl font-semibold text-ink-strong">Something broke on this machine</h1>

        <div class="mt-6 rounded-md border border-dashed border-rule bg-panel p-6">
            <p class="text-sm font-semibold text-ink-strong">This request did not finish</p>
            <p class="mt-1 text-sm text-ink-muted">
                Trainer Desk hit an error it was not expecting. The turn, run or catalog row you
                were looking at is unchanged by a request that failed here, and your own data is
                still on this machine.
            </p>
            <p class="mt-3 text-sm text-ink-muted">
                The reason is written to <code class="rounded border border-rule bg-raised px-1 text-ink">storage/logs/laravel.log</code>.
                Nothing in this page names it, because a page that can query the database is not the
                page to trust when the database is what failed.
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
            </div>
        </div>
    </main>
</body>
</html>
