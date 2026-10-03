{{-- Tokens only: no zinc utility and no `dark:` fork, the same rule the shell screens follow
     (D-101, G-18, G-19). --}}
<x-layout title="Preferences">
    <h1 class="text-2xl font-semibold text-ink-strong">Preferences</h1>
    <p class="mt-1 text-sm text-ink-muted">
        Two keys, stored in this app's database and read back on the next request. Nothing here is
        kept in the browser, because PRD §6 non-goal 12 cuts a second source of truth.
    </p>

    <form method="POST" action="{{ route('preferences.update') }}" class="mt-6 grid max-w-3xl grid-cols-1 gap-4 rounded-md border border-rule bg-raised p-4 text-sm sm:grid-cols-2">
        @csrf
        @method('PUT')

        <label class="flex flex-col gap-1">
            <span class="font-medium text-ink">Theme</span>
            <select name="theme" class="min-h-11 rounded-md border border-rule bg-raised px-2 text-ink">
                {{-- US-11 authorizes three values and the third is the absence of a row, so
                     "follow the system" writes nothing rather than storing the word `system`,
                     which the theme resolution would honour as no theme anyway. --}}
                <option value="" @selected($theme === null)>Follow the system</option>
                <option value="light" @selected($theme === 'light')>Light</option>
                <option value="dark" @selected($theme === 'dark')>Dark</option>
            </select>
        </label>

        {{-- The hidden field is what turns an unchecked box into a stated `off` rather than an
             absent key: both values are sent and the last wins, so the request always receives
             one of the two and the Trainer's answer is never inferred. --}}
        <label class="inline-flex min-h-11 items-center gap-2 self-end">
            <input type="hidden" name="failure_estimate" value="off">
            <input type="checkbox" name="failure_estimate" value="on" class="h-5 w-5 accent-chrome" @checked($failureEstimate === 'on')>
            <span class="font-medium text-ink">Numeric failure estimate</span>
        </label>

        @error('theme')<p class="w-full text-risk">{{ $message }}</p>@enderror
        @error('failure_estimate')<p class="w-full text-risk">{{ $message }}</p>@enderror
        @error('preferences')<p class="w-full text-risk">{{ $message }}</p>@enderror

        <p class="text-xs text-ink-muted sm:col-span-2">
            Turning the estimate on changes no screen yet. `ADR-0001` §3 records that no source
            publishes a failure curve and requires any number this tool prints to show its formula
            beside it, so there is nothing honest to render until a model exists. The choice is
            stored, and the band the app can source from Energy (Safe, Caution, Danger) shows
            either way.
        </p>

        <x-app-button type="submit" variant="secondary" class="justify-self-start">Save preferences</x-app-button>
    </form>
</x-layout>
