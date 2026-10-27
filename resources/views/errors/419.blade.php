{{--
    Same treatment as the 404: a standalone document, because the product's shell is
    `AppLayout.vue` now and the Blade shell it used to share was retired with the Inertia
    port (ADR-0020 §1).

    Structure is the HFW error-419.html: the timeout named in the head, the reload action
    as the one filled button, then the Cause / Your data / What is lost table. Its `.keybox`
    annotation block is wireframe documentation and is not shipped.

    This tool has no login (ARCHITECTURE §8), so a 419 does not mean "sign in again": it
    means the page the form was opened from is older than the session behind it, which is
    what a tab left open across `migrate:fresh` or a restart produces. `$theme` is composed
    server-side by AppServiceProvider; the inline script is the fallback for no stored
    preference and stays in the head so first paint is not the wrong theme.

    The wireframe labels its primary "Back to the cockpit". This view cannot know which
    screen submitted the form, so the same slot carries the previous URL under a label that
    is true whatever it points at; `url()->previous()` reads the session, not the database.
--}}
<!DOCTYPE html>
<html lang="en" @if (! empty($theme)) data-theme="{{ $theme }}" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your session timed out</title>
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
        <p class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Session expired</p>
        <h1 class="mt-1 text-3xl font-semibold text-ink-strong">Your session timed out</h1>
        <p class="mt-2 max-w-3xl text-sm text-ink-muted">
            The form you submitted was not accepted because the page had been open too long.
            Your last saved turn is unaffected.
        </p>

        <div class="mt-6 rounded-md border border-dashed border-rule bg-panel p-6 text-center">
            <h2 class="text-lg font-semibold text-ink-strong">Reload and try again</h2>
            <p class="mx-auto mt-1 max-w-3xl text-sm text-ink-muted">
                Reload the screen you were on. Anything you had typed but not saved needs
                re-entering; anything already saved is safe.
            </p>
            <div class="mt-4 flex flex-wrap justify-center gap-3 text-sm">
                <a href="{{ url()->previous() }}"
                   class="inline-flex min-h-11 items-center rounded-full bg-primary-action px-4 font-bold text-on-primary-action">
                    Back to the screen you were on
                </a>
                <a href="{{ route('home') }}"
                   class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-4 font-semibold text-ink hover:text-ink-strong">
                    Dashboard
                </a>
                {{-- The third exit (`error-419.html`): one filled action plus two safe places. Careers
                     is read-only on arrival, so it cannot repeat the write that just expired. --}}
                <a href="{{ route('runs.index') }}"
                   class="inline-flex min-h-11 items-center rounded-md border border-rule bg-raised px-4 font-semibold text-ink hover:text-ink-strong">
                    Careers
                </a>
            </div>
        </div>

        <div class="mt-4 rounded-md border border-rule bg-panel p-6">
            <h2 class="text-lg font-semibold text-ink-strong">Why this happens</h2>
            <table class="mt-2 w-full border-collapse text-sm">
                <tbody>
                    <tr class="border-b border-rule">
                        <th scope="row" class="py-2 pr-3 text-left font-semibold text-ink-strong">Cause</th>
                        <td class="py-2 text-ink-muted">
                            The page's security token expired while the tab sat open.
                        </td>
                    </tr>
                    <tr class="border-b border-rule">
                        <th scope="row" class="py-2 pr-3 text-left font-semibold text-ink-strong">Your data</th>
                        <td class="py-2 text-ink-muted">
                            Everything saved before the submission is intact.
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="py-2 pr-3 text-left font-semibold text-ink-strong">What is lost</th>
                        <td class="py-2 text-ink-muted">
                            Only the unsaved contents of the form you just submitted.
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="mt-3 rounded-md border border-risk bg-raised px-3 py-2 text-sm text-ink">
                <span aria-hidden="true">!</span>
                <span>
                    Submitting again after the reload is the whole remedy. Nothing needs repairing
                    behind it.
                </span>
            </p>
        </div>
    </main>
</body>
</html>
