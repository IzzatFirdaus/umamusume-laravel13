{{--
    The 500 is the one error document that may not reach for the database: it renders while
    the app is already failing, so it is deliberately absent from AppServiceProvider's
    theme-composer list and draws its own document with no `$theme`. `ErrorPageViewsTest`
    counts the queries this view makes and fails if the number is not zero, with a canary
    case that proves the counter counts something.

    Structure is the HFW error-500.html: the not-your-fault sentence in the lede, the two
    exits with one filled, then the table that says what this page shows and what it
    withholds. Its `.keybox` annotation block is wireframe documentation and is not shipped.

    The documented theme order is stored value, then operating system, then light. With the
    store unreachable the first term is unavailable, so the inline script below is what keeps
    the first paint themed (G-20), and it stays in the head because a bundled script runs
    after first paint. The stylesheet is the only asset this page pulls: the shell's
    JavaScript entry went away with the Inertia port, and a page that draws itself without
    the database should not be the last thing pinned to it.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Something went wrong on our side</title>
    <script>
        (function () {
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.dataset.theme = 'dark';
            }
        })();
    </script>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-page text-ink">
    <a href="#main"
       class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:border-2 focus:border-rule focus:bg-raised focus:px-4 focus:py-2 focus:text-sm focus:font-bold focus:text-ink-strong">
        Skip to content
    </a>
    <main id="main" class="mx-auto max-w-5xl px-4 py-8">
        <p class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Server error</p>
        <h1 class="mt-1 text-3xl font-semibold text-ink-strong">Something went wrong on our side</h1>
        <p class="mt-2 max-w-3xl text-sm text-ink-muted">
            The screen could not be built. This is not something you did, and no data was
            changed by the failed request.
        </p>

        <div class="mt-6 rounded-md border border-dashed border-rule bg-panel p-6 text-center">
            <h2 class="text-lg font-semibold text-ink-strong">Try again, or go somewhere safe</h2>
            <p class="mx-auto mt-1 max-w-3xl text-sm text-ink-muted">
                If it keeps happening on the same screen, that screen has a bug worth reporting.
                Note what you were doing when it failed.
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
                {{-- The third exit the HFW draws (`error-500.html`): one filled action and two outline
                     doors, so a Trainer whose screen broke has a known-good place to read from while
                     the failed one is investigated. Database is read-only, so it cannot fail the same
                     way a write surface can. --}}
                <a href="{{ route('database.index') }}"
                   class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-4 font-semibold text-ink hover:text-ink-strong">
                    Database
                </a>
            </div>
        </div>

        <div class="mt-4 rounded-md border border-rule bg-panel p-6">
            <h2 class="text-lg font-semibold text-ink-strong">What is shown and what is not</h2>
            <table class="mt-2 w-full border-collapse text-sm">
                <tbody>
                    <tr class="border-b border-rule">
                        <th scope="row" class="py-2 pr-3 text-left font-semibold text-ink-strong">Your data</th>
                        <td class="py-2 text-ink-muted">Unchanged by the failed request.</td>
                    </tr>
                    <tr class="border-b border-rule">
                        <th scope="row" class="py-2 pr-3 text-left font-semibold text-ink-strong">Error detail</th>
                        <td class="py-2 text-ink-muted">
                            Not shown. A stack trace is developer information, not an interface.
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="py-2 pr-3 text-left font-semibold text-ink-strong">Reference</th>
                        <td class="py-2 font-mono text-ink-muted">
                            A short id, when the server logs one.
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="mt-3 text-sm text-ink-muted">
                <span aria-hidden="true" class="text-status-positive">○</span>
                <span>
                    The same rule that removed file paths from the interface applies here: a 500 page
                    shows what a Trainer can act on and nothing else. The log entry is where the stack
                    trace belongs.
                </span>
            </p>
        </div>
    </main>
</body>
</html>
