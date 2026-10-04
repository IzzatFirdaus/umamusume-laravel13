<x-layout title="Sign-in session expired">
    <h1 class="text-2xl font-semibold text-ink-strong">Sign-in session expired</h1>

    {{-- Same treatment as SCR-SYS-001: the shell, the theme, and one action back to work. This
         tool has no login (ARCHITECTURE §8), so a 419 does not mean "sign in again": it means the
         page the form was opened from is older than the session behind it, which is what a tab left
         open across `migrate:fresh` or a restart produces. --}}
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
</x-layout>
