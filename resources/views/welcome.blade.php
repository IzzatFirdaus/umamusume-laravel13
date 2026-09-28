<x-layout title="Trainer Desk">
    {{-- Replaces the framework skeleton splash, which shipped a "Deploy now" button to
         cloud.laravel.com and a documentation nav on a tool whose PRD §6.10 cuts any
         hosting path and §6.1 cuts accounts: the copy described a product this is not
         (KI-3 residual, audit F-1). It also rendered no `data-theme`, so the one page
         every Trainer arrives on could not be dark-audited at all. This is the shell
         like every other surface: tokens only, both themes, no off-origin request. --}}
    <h1 class="text-2xl font-semibold text-ink-strong">Trainer Desk</h1>

    <p class="mt-3 max-w-prose text-sm text-ink">
        A local desk for one Trainer of the Global English version of Umamusume: Pretty Derby.
        It keeps the catalog cross-referenced between the two servers, holds the turns you log
        against a run, and exports what you entered. Nothing here plays the run for you, and no
        number appears unless you typed it or a source is named beside it.
    </p>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <a href="{{ route('runs.index') }}" class="rounded-md border border-rule bg-panel p-4">
            <span class="block text-sm font-bold text-ink-strong">Training runs</span>
            <span class="mt-1 block text-sm text-ink-muted">
                Log a turn, read the strip, and export the run as CSV or JSON.
            </span>
        </a>
        <a href="{{ route('catalog.index') }}" class="rounded-md border border-rule bg-panel p-4">
            <span class="block text-sm font-bold text-ink-strong">Catalog</span>
            <span class="mt-1 block text-sm text-ink-muted">
                Names in English and Japanese, filtered by what is released on Global.
            </span>
        </a>
        <a href="{{ route('review.index') }}" class="rounded-md border border-rule bg-panel p-4">
            <span class="block text-sm font-bold text-ink-strong">Review queue</span>
            <span class="mt-1 block text-sm text-ink-muted">
                Confirm, alias or reject the matches a fetch was not sure about.
            </span>
        </a>
    </div>

    <p class="mt-6 text-xs text-ink-muted">
        Runs on this machine against a SQLite file. There is no account, no server, and nothing
        to deploy.
    </p>
</x-layout>
