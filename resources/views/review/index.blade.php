{{-- Tokens only: no zinc utility and no `dark:` fork. See catalog/index for the
     reason and the gate this satisfies (D-101, G-18, G-19). --}}
<x-layout title="Review queue">
    <h1 class="text-2xl font-semibold text-ink-strong">Match review queue</h1>
    <p class="mt-1 text-sm text-ink-muted">Fuzzy and unmatched fetch results land here. The engine never merges them without your decision.</p>

    @if ($candidates->count() === 0)
        <p class="mt-8 rounded-md border border-dashed border-rule bg-raised p-6 text-sm text-ink-muted">
            Nothing pending. Run `php artisan uma:fetch` to populate.
        </p>
    @else
        <ul class="mt-6 space-y-4">
            @foreach ($candidates as $candidate)
                <li class="rounded-md border border-rule bg-raised p-4 text-sm">
                    <div class="flex items-baseline justify-between">
                        <span class="font-medium text-ink-strong">{{ $candidate->proposed_name }}</span>
                        <span class="text-xs text-ink-muted">{{ $candidate->match_tier->label() }} · {{ $candidate->source_key }} · {{ $candidate->created_by_fetch_at->toDateString() }}</span>
                    </div>
                    @if ($candidate->proposed_name_ja)
                        <p class="text-ink-muted">{{ $candidate->proposed_name_ja }}</p>
                    @endif
                    @if ($candidate->suggestedUmamusume)
                        <p class="text-ink-muted">Suggestion: {{ $candidate->suggestedUmamusume->name }}</p>
                    @endif

                    <form method="POST" action="{{ route('review.resolve', $candidate) }}" class="mt-3 flex flex-wrap items-center gap-2">
                        @csrf
                        <select name="status" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                            <option value="Confirmed">Confirm (merge into suggestion, or create)</option>
                            <option value="Aliased">Add as alias of…</option>
                            <option value="Rejected">Reject</option>
                        </select>
                        <input type="number" name="umamusume_id" placeholder="Umamusume id (optional)" class="w-44 rounded-md border border-rule bg-raised px-2 py-1 text-ink" value="{{ $candidate->suggested_umamusume_id }}">
                        <select name="alias_language" class="rounded-md border border-rule bg-raised px-2 py-1 text-ink">
                            <option value="">Alias language</option>
                            @foreach (\App\Enums\AliasLanguage::cases() as $language)
                                <option value="{{ $language->value }}">{{ $language->label() }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="enamel rounded-full bg-chrome px-3 py-1.5 font-semibold text-on-chrome">Resolve</button>
                    </form>
                    @error('status')<p class="mt-1 text-sm text-risk">{{ $message }}</p>@enderror
                </li>
            @endforeach
        </ul>

        <div class="mt-4 text-sm">{{ $candidates->links() }}</div>
    @endif
</x-layout>
